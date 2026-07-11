<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class EmailComponentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register email component aliases
        Blade::component('emails.components.button', 'email-button');
        Blade::component('emails.components.info-box', 'email-info-box');
    }
}
