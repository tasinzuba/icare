<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupFullTests extends Command
{
    protected $signature = 'setup:full-tests';
    protected $description = 'Run migrations and setup full tests feature';

    public function handle()
    {
        $this->info('Setting up Full Tests feature...');
        
        // Run migrations
        $this->info('Running migrations...');
        Artisan::call('migrate', ['--force' => true]);
        
        $this->info('Full Tests feature setup completed!');
        $this->info('You can now:');
        $this->info('1. Create full tests from Admin Panel > Full Tests');
        $this->info('2. Students can access full tests from their test dashboard');
        
        return Command::SUCCESS;
    }
}
