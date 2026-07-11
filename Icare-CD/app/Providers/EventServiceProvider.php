<?php

namespace App\Providers;

use App\Events\TestCompleted;
use App\Listeners\UpdateFullTestScore;
use App\Listeners\UpdateUserTestStats;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        TestCompleted::class => [
            UpdateUserTestStats::class,      // Sync: Update user's test count immediately
            UpdateFullTestScore::class,      // Sync: Update full test section score
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
