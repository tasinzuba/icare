<?php

namespace App\Console\Commands;

use App\Helpers\ScoreCalculator;
use App\Models\FullTestAttempt;
use App\Models\StudentAttempt;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixBuggyBandScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:band-scores {--dry-run : Show what would be fixed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix buggy band scores where band_score incorrectly equals correct_answers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        $this->info('');
        $this->info('===========================================');
        $this->info('  Fixing Buggy Band Scores');
        $this->info('===========================================');
        $this->info('');

        // Find buggy StudentAttempts where band_score == correct_answers
        // This is the bug pattern where client-side score was used
        $buggyAttempts = StudentAttempt::whereNotNull('band_score')
            ->whereNotNull('correct_answers')
            ->whereColumn('band_score', '=', 'correct_answers')
            ->where('correct_answers', '>', 0)
            ->where('correct_answers', '<', 40) // Exclude edge cases
            ->get();

        $this->info("Found {$buggyAttempts->count()} buggy StudentAttempt records");
        $this->info('');

        if ($buggyAttempts->isEmpty()) {
            $this->info('No buggy records found. All good!');
            return 0;
        }

        $fixed = 0;
        $errors = 0;
        $skipped = 0;

        $this->output->progressStart($buggyAttempts->count());

        foreach ($buggyAttempts as $attempt) {
            try {
                $correctAnswers = (int) $attempt->correct_answers;
                $totalQuestions = (int) ($attempt->total_questions ?: 40);
                $currentBand = $attempt->band_score;

                // Determine section type from test set
                $section = 'listening'; // Default
                if ($attempt->testSet && $attempt->testSet->section) {
                    $section = $attempt->testSet->section->name ?? 'listening';
                }

                // Calculate correct band score
                if ($section === 'reading') {
                    $correctBand = ScoreCalculator::calculateReadingBandScore($correctAnswers, $totalQuestions);
                } else {
                    $correctBand = ScoreCalculator::calculateListeningBandScore($correctAnswers, $totalQuestions);
                }

                // Skip if already correct (edge case where correct_answers happens to match valid band)
                if ($currentBand == $correctBand) {
                    $skipped++;
                    $this->output->progressAdvance();
                    continue;
                }

                if (!$dryRun) {
                    DB::beginTransaction();
                    try {
                        // Update StudentAttempt
                        $attempt->update(['band_score' => $correctBand]);

                        // Also update FullTestAttempt if this is part of a full test
                        $fullTestSectionAttempt = $attempt->fullTestSectionAttempt;
                        if ($fullTestSectionAttempt) {
                            $fullTestAttempt = $fullTestSectionAttempt->fullTestAttempt;
                            if ($fullTestAttempt) {
                                $scoreField = $section . '_score';
                                $fullTestAttempt->update([$scoreField => $correctBand]);

                                // Recalculate overall score
                                if ($fullTestAttempt->hasAllSectionScores()) {
                                    $overallScore = $fullTestAttempt->calculateOverallScore();
                                    $fullTestAttempt->update(['overall_band_score' => $overallScore]);
                                }
                            }
                        }

                        DB::commit();
                        $fixed++;
                    } catch (\Exception $e) {
                        DB::rollBack();
                        throw $e;
                    }
                } else {
                    // Dry run - just count
                    $fixed++;
                }

                $this->output->progressAdvance();

            } catch (\Exception $e) {
                $errors++;
                $this->output->progressAdvance();
                $this->newLine();
                $this->error("Error fixing attempt #{$attempt->id}: {$e->getMessage()}");
            }
        }

        $this->output->progressFinish();

        $this->info('');
        $this->info('===========================================');
        $this->info('  Summary');
        $this->info('===========================================');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Buggy Records', $buggyAttempts->count()],
                [$dryRun ? 'Would Fix' : 'Fixed', $fixed],
                ['Skipped (Already Correct)', $skipped],
                ['Errors', $errors],
            ]
        );

        if ($dryRun) {
            $this->info('');
            $this->warn('This was a DRY RUN. Run without --dry-run to apply fixes.');
            $this->info('');

            // Show sample of what would be fixed
            $this->info('Sample of records that would be fixed:');
            $samples = $buggyAttempts->take(10);
            $tableData = [];
            foreach ($samples as $attempt) {
                $correctAnswers = (int) $attempt->correct_answers;
                $totalQuestions = (int) ($attempt->total_questions ?: 40);
                $correctBand = ScoreCalculator::calculateListeningBandScore($correctAnswers, $totalQuestions);

                $tableData[] = [
                    $attempt->id,
                    "{$correctAnswers}/{$totalQuestions}",
                    $attempt->band_score,
                    $correctBand,
                ];
            }
            $this->table(['ID', 'Correct/Total', 'Current Band', 'Correct Band'], $tableData);
        }

        return $errors > 0 ? 1 : 0;
    }
}
