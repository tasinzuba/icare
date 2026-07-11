<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestR2Connection extends Command
{
    protected $signature = 'r2:test';
    protected $description = 'Test R2 connection and configuration';

    public function handle()
    {
        $this->info('Testing R2 Configuration...');
        
        // Check configuration
        $config = config('filesystems.disks.r2');
        
        if (empty($config['key']) || empty($config['secret'])) {
            $this->error('R2 credentials not configured in .env file');
            return 1;
        }
        
        $this->table(['Config Key', 'Value'], [
            ['Bucket', $config['bucket']],
            ['Region', $config['region']],
            ['Endpoint', $config['endpoint']],
            ['URL', $config['url']],
            ['Has Credentials', !empty($config['key']) ? 'Yes' : 'No']
        ]);
        
        // Test upload
        try {
            $testFile = 'test-' . time() . '.txt';
            $content = 'R2 test at ' . now()->toDateTimeString();
            
            $this->info("\nTesting file upload...");
            $result = Storage::disk('r2')->put($testFile, $content);
            
            if ($result) {
                $this->info("✓ File uploaded successfully: $testFile");
                
                // Test URL generation
                $url = Storage::disk('r2')->url($testFile);
                $this->info("✓ File URL: $url");
                
                // Test file exists
                if (Storage::disk('r2')->exists($testFile)) {
                    $this->info("✓ File exists check passed");
                }
                
                // Clean up
                Storage::disk('r2')->delete($testFile);
                $this->info("✓ Test file deleted");
                
                $this->newLine();
                $this->info('R2 connection test passed! ✅');
            } else {
                $this->error('Failed to upload test file');
            }
            
        } catch (\Exception $e) {
            $this->error('R2 connection test failed!');
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
