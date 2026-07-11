<?php

namespace App\Http\Middleware;

use App\Services\TestAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUsageLimit
{
    protected TestAccessService $testAccess;

    public function __construct(TestAccessService $testAccess)
    {
        $this->testAccess = $testAccess;
    }

    public function handle(Request $request, Closure $next, string $limitType): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        switch ($limitType) {
            case 'mock_test':
                $isOffline = $user->isOfflineStudent();

                // Offline students: all access control handled at controller level
                // (TestAccessService + FullTestController). No middleware check needed.
                if ($isOffline) {
                    break;
                }

                // Basic quota check (can user take more tests at all?)
                if (!$user->canTakeMoreTests()) {
                    if ($isOffline) {
                        return redirect()->route('offline.dashboard')
                            ->with('error', 'You have reached your test limit. Please contact your branch.');
                    }
                    return redirect()->route('welcome')
                        ->with('error', 'You have reached your monthly test limit. Please upgrade your plan.');
                }

                // Per-section limit check for offline students
                if ($isOffline) {
                    $enrollment = $user->getActiveEnrollment();
                    if ($enrollment && $enrollment->hasPerSectionLimits()) {
                        $sectionType = $this->determineSectionType($request);
                        if ($sectionType && !$enrollment->canTakeSectionTestOfType($sectionType)) {
                            return redirect()->route('offline.dashboard')
                                ->with('error', "You have reached your {$sectionType} test limit. Please contact your branch.");
                        }
                    }
                }
                break;
        }

        return $next($request);
    }

    /**
     * Determine the section type from the route or test set
     */
    protected function determineSectionType(Request $request): ?string
    {
        // Try from route name (e.g., student.listening.start => listening)
        $routeName = $request->route()?->getName() ?? '';
        $sectionTypes = ['listening', 'reading', 'writing', 'speaking'];

        foreach ($sectionTypes as $type) {
            if (str_contains($routeName, ".{$type}.")) {
                return $type;
            }
        }

        // Try from test set if available
        $testSet = $request->route('testSet');
        if ($testSet instanceof \App\Models\TestSet) {
            $testSet->loadMissing('section');
            return strtolower($testSet->section->name ?? '');
        }

        return null;
    }
}
