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
        // Behind Railway's TLS-terminating proxy the app receives plain HTTP with
        // X-Forwarded-* headers. Trust the proxy so Laravel knows the request is
        // HTTPS — otherwise it generates http:// URLs / non-secure cookies and the
        // browser fails to round-trip the session cookie (CSRF 419 on login).
        $middleware->trustProxies(at: '*');

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

        // Mark lapsed offline enrollments as expired. Nothing did this before, so every row stayed
        // 'active' and all status-only reports over-counted active students.
        $schedule->command('enrollments:expire')
            ->dailyAt('00:30')
            ->withoutOverlapping();

        // Score attempts the student walked away from. A test finalises itself when the page is
        // open at the deadline, or when the student opens it again afterwards — neither happens if
        // they close the browser and never return, and the work autosaved into draft_answers was
        // then never scored. Only attempts holding real answers are touched.
        $schedule->command('attempts:finalize-stale --apply')
            ->dailyAt('01:00')
            ->withoutOverlapping();

        // Safety net for the full test counter. It drifted badly once — a section test completing
        // charged a full test as well — and a student whose counter runs ahead is refused tests
        // they have paid for, with nothing on screen to explain why. Rebuilding it weekly from the
        // attempts that exist means any future drift corrects itself within days instead of
        // accumulating unnoticed.
        $schedule->command('enrollments:fix-full-test-counters --apply')
            ->weeklyOn(1, '02:00')
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
