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
     * Intentionally EMPTY. Laravel 11/12 discovers listeners in app/Listeners at the framework
     * level regardless of shouldDiscoverEvents() below, so mapping them here as well registered
     * UpdateUserTestStats and UpdateFullTestScore twice: every finished test fired both listeners
     * twice, double-counting the student's consumed quota and re-applying section scores.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [];

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
