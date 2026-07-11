<?php

namespace App\Console\Commands;

use App\Models\TestPartAudio;
use App\Models\SpeakingRecording;
use App\Models\Question;
use App\Traits\HandlesFileUploads;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateAudioToR2 extends Command
{
    use HandlesFileUploads;
    
    protected $signature = 'audio:migrate-to-r2 
                            {--dry-run : Run without making changes}
                            {--type=all : Type of audio to migrate (all, listening, speaking, questions)}';
                            
    protected $description = 'Migrate existing audio files from local storage to R2 CDN';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $type = $this->option('type');
        
        $this->info('Starting audio migration to R2...');
        $this->info('Dry run: ' . ($isDryRun ? 'YES' : 'NO'));
        $this->info('Type: ' . $type);
        $this->newLine();
        
        if (!$this->shouldUseR2()) {
            $this->error('R2 is not configured. Please check your .env file.');
            return 1;
        }
        
        $stats = [
            'listening' => ['total' => 0, 'migrated' => 0, 'failed' => 0],
            'speaking' => ['total' => 0, 'migrated' => 0, 'failed' => 0],
            'questions' => ['total' => 0, 'migrated' => 0, 'failed' => 0],
        ];
        
        // Migrate listening test audios
        if (in_array($type, ['all', 'listening'])) {
            $this->info('Migrating listening test audios...');
            $stats['listening'] = $this->migrateListeningAudios($isDryRun);
        }
        
        // Migrate speaking recordings
        if (in_array($type, ['all', 'speaking'])) {
            $this->info('Migrating speaking recordings...');
            $stats['speaking'] = $this->migrateSpeakingRecordings($isDryRun);
        }
        
        // Migrate question media
        if (in_array($type, ['all', 'questions'])) {
            $this->info('Migrating question media files...');
            $stats['questions'] = $this->migrateQuestionMedia($isDryRun);
        }
        
        // Display summary
        $this->newLine();
        $this->info('Migration Summary:');
        $this->table(
            ['Type', 'Total', 'Migrated', 'Failed'],
            [
                ['Listening', $stats['listening']['total'], $stats['listening']['migrated'], $stats['listening']['failed']],
                ['Speaking', $stats['speaking']['total'], $stats['speaking']['migrated'], $stats['speaking']['failed']],
                ['Questions', $stats['questions']['total'], $stats['questions']['migrated'], $stats['questions']['failed']],
            ]
        );
        
        return 0;
    }
    
    private function migrateListeningAudios(bool $isDryRun): array
    {
        $audios = TestPartAudio::where('storage_disk', 'public')->get();
        $total = $audios->count();
        $migrated = 0;
        $failed = 0;
        
        $bar = $this->output->createProgressBar($total);
        $bar->start();
        
        foreach ($audios as $audio) {
            try {
                if (Storage::disk('public')->exists($audio->audio_path)) {
                    if (!$isDryRun) {
                        // Read file content
                        $content = Storage::disk('public')->get($audio->audio_path);
                        
                        // Upload to R2
                        $newPath = 'test-audios/' . basename($audio->audio_path);
                        Storage::disk('r2')->put($newPath, $content, 'public');
                        
                        // Update record
                        $audio->update([
                            'audio_path' => $newPath,
                            'audio_url' => $this->getFileUrl($newPath, 'r2'),
                            'storage_disk' => 'r2'
                        ]);
                        
                        // Delete from local storage
                        Storage::disk('public')->delete($audio->audio_path);
                    }
                    
                    $migrated++;
                }
            } catch (\Exception $e) {
                $failed++;
                $this->error("\nFailed to migrate audio ID {$audio->id}: " . $e->getMessage());
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        return ['total' => $total, 'migrated' => $migrated, 'failed' => $failed];
    }
    
    private function migrateSpeakingRecordings(bool $isDryRun): array
    {
        $recordings = SpeakingRecording::where('storage_disk', 'public')
            ->orWhereNull('storage_disk')
            ->get();
            
        $total = $recordings->count();
        $migrated = 0;
        $failed = 0;
        
        $bar = $this->output->createProgressBar($total);
        $bar->start();
        
        foreach ($recordings as $recording) {
            try {
                $disk = $recording->storage_disk ?? 'public';
                
                if (Storage::disk($disk)->exists($recording->file_path)) {
                    if (!$isDryRun) {
                        // Read file content
                        $content = Storage::disk($disk)->get($recording->file_path);
                        
                        // Upload to R2
                        $newPath = 'speaking-recordings/' . basename($recording->file_path);
                        Storage::disk('r2')->put($newPath, $content, 'public');
                        
                        // Update record
                        $recording->update([
                            'file_path' => $newPath,
                            'file_url' => $this->getFileUrl($newPath, 'r2'),
                            'storage_disk' => 'r2',
                            'file_size' => strlen($content)
                        ]);
                        
                        // Delete from local storage
                        Storage::disk($disk)->delete($recording->file_path);
                    }
                    
                    $migrated++;
                }
            } catch (\Exception $e) {
                $failed++;
                $this->error("\nFailed to migrate recording ID {$recording->id}: " . $e->getMessage());
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        return ['total' => $total, 'migrated' => $migrated, 'failed' => $failed];
    }
    
    private function migrateQuestionMedia(bool $isDryRun): array
    {
        $questions = Question::whereNotNull('media_path')->get();
        $total = $questions->count();
        $migrated = 0;
        $failed = 0;
        
        if ($total === 0) {
            return ['total' => 0, 'migrated' => 0, 'failed' => 0];
        }
        
        $bar = $this->output->createProgressBar($total);
        $bar->start();
        
        foreach ($questions as $question) {
            try {
                if (Storage::disk('public')->exists($question->media_path)) {
                    if (!$isDryRun) {
                        // Read file content
                        $content = Storage::disk('public')->get($question->media_path);
                        
                        // Determine directory based on file type
                        $extension = pathinfo($question->media_path, PATHINFO_EXTENSION);
                        $directory = in_array($extension, ['mp3', 'wav', 'ogg', 'webm']) ? 'question-audios' : 'question-images';
                        
                        // Upload to R2
                        $newPath = $directory . '/' . basename($question->media_path);
                        Storage::disk('r2')->put($newPath, $content, 'public');
                        
                        // Update record
                        $question->update([
                            'media_path' => $newPath,
                            'media_url' => $this->getFileUrl($newPath, 'r2'),
                            'media_storage_disk' => 'r2'
                        ]);
                        
                        // Delete from local storage
                        Storage::disk('public')->delete($question->media_path);
                    }
                    
                    $migrated++;
                }
            } catch (\Exception $e) {
                $failed++;
                $this->error("\nFailed to migrate question media ID {$question->id}: " . $e->getMessage());
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        return ['total' => $total, 'migrated' => $migrated, 'failed' => $failed];
    }
}
