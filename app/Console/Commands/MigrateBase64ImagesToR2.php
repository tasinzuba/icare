<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrateBase64ImagesToR2 extends Command
{
    protected $signature = 'images:migrate-to-r2';
    protected $description = 'Migrate base64 images to Cloudflare R2';
    
    protected $disk;
    
    public function handle()
    {
        $this->disk = Storage::disk('r2');
        
        $this->info('Starting migration...');
        
        $questions = Question::where('content', 'like', '%data:image%')
            ->orWhere('passage_text', 'like', '%data:image%')
            ->get();
            
        $bar = $this->output->createProgressBar(count($questions));
        
        foreach ($questions as $question) {
            $this->migrateQuestionImages($question);
            $bar->advance();
        }
        
        $bar->finish();
        $this->info("\nMigration completed!");
    }
    
    protected function migrateQuestionImages($question)
    {
        // Migrate content images
        if ($question->content) {
            $question->content = $this->replaceBase64Images($question->content);
        }
        
        // Migrate passage_text images
        if ($question->passage_text) {
            $question->passage_text = $this->replaceBase64Images($question->passage_text);
        }
        
        $question->save();
    }
    
    protected function replaceBase64Images($content)
    {
        return preg_replace_callback(
            '/<img[^>]+src="(data:image\/([^;]+);base64,([^"]+))"[^>]*>/i',
            function ($matches) {
                try {
                    $base64Data = $matches[3];
                    $imageType = $matches[2];
                    $extension = explode('/', $imageType)[1] ?? 'jpg';
                    
                    // Decode base64
                    $imageData = base64_decode($base64Data);
                    
                    // Generate filename
                    $filename = date('Y/m/d/') . Str::random(40) . '.' . $extension;
                    $path = 'questions/' . $filename;
                    
                    // Upload to R2
                    $this->disk->put($path, $imageData, [
                        'CacheControl' => 'public, max-age=31536000',
                        'ContentType' => 'image/' . $extension,
                    ]);
                    
                    // Get public URL
                    $url = rtrim(env('R2_URL'), '/') . '/' . $path;
                    
                    // Replace src with new URL
                    return str_replace($matches[1], $url, $matches[0]);
                    
                } catch (\Exception $e) {
                    $this->error('Failed to migrate image: ' . $e->getMessage());
                    return $matches[0]; // Return original if failed
                }
            },
            $content
        );
    }
}
