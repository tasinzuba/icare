<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'usage.limit' => \App\Http\Middleware\TrackUsageLimit::class,
            'verify.webhook' => \App\Http\Middleware\VerifyWebhookSignature::class,
            'teacher' => \App\Http\Middleware\IsTeacher::class,
            'trust.device' => \App\Http\Middleware\CheckTrustedDevice::class,
            'admin.access' => \App\Http\Middleware\CheckAdminAccess::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'branch.access' => \App\Http\Middleware\CheckBranchAccess::class,
            'onboarding' => \App\Http\Middleware\CheckOnboarding::class,
            'offline.restrict' => \App\Http\Middleware\RestrictOfflineStudentRoutes::class,
        ]);

        // Set priority - CheckBanned should run early
        $middleware->priority([
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\CheckTrustedDevice::class,
            \App\Http\Middleware\CheckBanned::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Web middleware group
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\CheckTrustedDevice::class,
            \App\Http\Middleware\CheckBanned::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // API middleware group
        $middleware->api(append: [
            //
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Check stale avatar generation tasks (fallback for webhook failures)
        $schedule->job(new \App\Jobs\CheckStaleAvatarTasksJob())
            ->everyFiveMinutes()
            ->withoutOverlapping();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom test exceptions are self-rendering (have render() method)
        // They will automatically redirect with proper error messages

        // Log test exceptions for debugging
        $exceptions->report(function (\App\Exceptions\TestException $e) {
            \Log::warning('Test Exception: ' . class_basename($e), [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return false; // Don't report to default handler
        });
    })->create();
