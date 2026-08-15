<?php

namespace App\Console\Commands;

use App\Models\OfflineEnrollment;
use Illuminate\Console\Command;

/**
 * Flip offline enrollments whose validity has passed to 'expired'.
 *
 * OfflineEnrollment::updateStatusIfNeeded() existed for this but nothing ever called it, so every
 * row stayed 'active' forever: 95 of 110 live enrollments had a valid_until in the past while still
 * reporting as active. Access control was unaffected (it checks the date as well as the status),
 * but every status-only report - branch "active students", batch counts, admin totals - was wrong.
 */
class ExpireEnrollments extends Command
{
    protected $signature = 'enrollments:expire {--dry-run : List what would change without writing}';

    protected $description = 'Mark offline enrollments whose valid_until has passed as expired';

    public function handle(): int
    {
        $query = OfflineEnrollment::where('status', OfflineEnrollment::STATUS_ACTIVE)
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<', now()->toDateString());

        $count = (clone $query)->count();

        if ($count === 0) {
            $this->info('No enrollments to expire.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn("[dry run] {$count} enrollment(s) would be marked expired:");

            (clone $query)->with('student')->orderBy('valid_until')->limit(20)
                ->get()
                ->each(fn ($e) => $this->line(sprintf(
                    '  #%d  %s  valid_until %s',
                    $e->id,
                    optional($e->student)->name ?? 'unknown student',
                    optional($e->valid_until)->toDateString() ?? '-'
                )));

            if ($count > 20) {
                $this->line('  ... and ' . ($count - 20) . ' more');
            }

            return self::SUCCESS;
        }

        $updated = $query->update(['status' => OfflineEnrollment::STATUS_EXPIRED]);

        $this->info("Marked {$updated} enrollment(s) as expired.");

        return self::SUCCESS;
    }
}
