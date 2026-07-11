<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Exceptions\TestAccessDeniedException;
use App\Models\TestSet;
use App\Services\TestAccessService;
use Inertia\Inertia;

/**
 * Centralized Onboarding Controller
 *
 * Single Inertia-based onboarding flow for all test sections.
 * Replaces 3 separate Blade views with one Vue component.
 */
class OnboardingController extends Controller
{
    protected TestAccessService $testAccess;

    public function __construct(TestAccessService $testAccess)
    {
        $this->testAccess = $testAccess;
    }

    /**
     * Show the unified onboarding page (Vue/Inertia)
     */
    public function show(string $section, TestSet $testSet)
    {
        $this->validateSection($section, $testSet);
        $config = $this->getConfig($section);

        return Inertia::render('Test/Onboarding/Show', [
            'testSet' => $testSet->load('section'),
            'section' => $section,
            'config' => $config,
            'user' => [
                'name' => auth()->user()->name,
                'id' => auth()->id(),
            ],
            'candidateNumber' => 'CD-' . str_pad(auth()->id(), 6, '0', STR_PAD_LEFT),
            'testDate' => now()->format('d-m-Y'),
            'startRoute' => route("student.{$section}.start", $testSet),
            'audioCheckUrl' => $config['equipment_check'] === 'sound' ? asset('audio/cd-audio-check.mp3') : '',
        ]);
    }

    /**
     * Legacy: redirect old confirm-details URL to unified onboarding
     */
    public function confirmDetails(string $section, TestSet $testSet)
    {
        return $this->show($section, $testSet);
    }

    /**
     * Legacy: redirect old equipment check URL to unified onboarding
     */
    public function equipmentCheck(string $section, TestSet $testSet)
    {
        return $this->show($section, $testSet);
    }

    /**
     * Legacy: redirect old instructions URL to unified onboarding
     */
    public function instructions(string $section, TestSet $testSet)
    {
        return $this->show($section, $testSet);
    }

    /**
     * Validate that the section matches the test set
     */
    protected function validateSection(string $section, TestSet $testSet): void
    {
        if (!config("onboarding.{$section}")) {
            abort(404, 'Invalid section');
        }

        if ($testSet->section->name !== $section) {
            throw TestAccessDeniedException::wrongSection($section);
        }

        $this->testAccess->assertCanAccessSectionTest(auth()->user(), $testSet, $section);
    }

    /**
     * Get config for a section
     */
    protected function getConfig(string $section): array
    {
        return config("onboarding.{$section}");
    }

    /**
     * Get the next step in the onboarding flow (kept for backward compatibility)
     */
    public static function getNextStep(string $section, string $currentStep): string
    {
        $config = config("onboarding.{$section}");
        $steps = $config['steps'];

        $currentIndex = array_search($currentStep, $steps);

        if ($currentIndex === false || $currentIndex >= count($steps) - 1) {
            return 'start';
        }

        return $steps[$currentIndex + 1];
    }

    /**
     * Get route for the next step (kept for backward compatibility)
     */
    public static function getNextStepRoute(string $section, string $currentStep, TestSet $testSet): string
    {
        $nextStep = self::getNextStep($section, $currentStep);

        if ($nextStep === 'start') {
            return route("student.{$section}.start", $testSet);
        }

        return route("student.{$section}.onboarding.{$nextStep}", $testSet);
    }
}
