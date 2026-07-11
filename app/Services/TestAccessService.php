<?php

namespace App\Services;

use App\Exceptions\TestAccessDeniedException;
use App\Models\FullTest;
use App\Models\OfflineEnrollment;
use App\Models\TestSet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Centralized Test Access Service
 *
 * Single source of truth for ALL test access decisions:
 * - Premium content access (unified key: 'premium_content')
 * - Offline enrollment validation (validity, quota, test assignment)
 * - Online subscription validation (plan features, monthly limits)
 * - Quota consumption with atomic DB locking (race condition proof)
 *
 * Replaces scattered checks across 12+ controller methods and 2 middlewares.
 */
class TestAccessService
{
    // =====================
    // Access Result DTOs
    // =====================

    /**
     * Check if user can access a full test.
     * Returns ['allowed' => bool, 'reason' => string|null, 'redirect' => string|null]
     */
    public function canAccessFullTest(User $user, FullTest $fullTest): array
    {
        $isOffline = $user->isOfflineStudent();

        // 1. Student type vs test visibility
        $canAccess = ($isOffline && $fullTest->is_for_offline) ||
                     (!$isOffline && $fullTest->is_for_online);

        if (!$canAccess) {
            return $this->denied(
                'This test is not available for your student type.',
                $isOffline ? 'offline.dashboard' : 'student.dashboard'
            );
        }

        // 2. Premium content check
        $premiumCheck = $this->checkPremiumAccess($user, $fullTest->is_premium);
        if (!$premiumCheck['allowed']) {
            return $premiumCheck;
        }

        // 3. Test configuration check
        if (!$fullTest->hasMinimumSections()) {
            return $this->denied(
                'This test is not properly configured. Minimum 3 sections are required.',
                'student.full-test.index'
            );
        }

        // 4. Quota / enrollment check
        if ($isOffline) {
            return $this->checkOfflineFullTestAccess($user, $fullTest);
        } else {
            return $this->checkOnlineTestAccess($user);
        }
    }

    /**
     * Check if user can access a section test (TestSet).
     * Returns ['allowed' => bool, 'reason' => string|null, 'redirect' => string|null]
     */
    public function canAccessSectionTest(User $user, TestSet $testSet, ?string $sectionType = null): array
    {
        $isOffline = $user->isOfflineStudent();

        // 1. Student type vs test visibility
        $canAccess = ($isOffline && $testSet->is_for_offline) ||
                     (!$isOffline && $testSet->is_for_online);

        if (!$canAccess) {
            return $this->denied(
                'This test is not available for your student type.',
                $isOffline ? 'offline.dashboard' : 'student.dashboard'
            );
        }

        // 2. Premium content check (unified — replaces 'premium_test_sets')
        $premiumCheck = $this->checkPremiumAccess($user, $testSet->is_premium);
        if (!$premiumCheck['allowed']) {
            return $premiumCheck;
        }

        // 3. Quota / enrollment check
        if ($isOffline) {
            return $this->checkOfflineSectionTestAccess($user, $sectionType, $testSet->id);
        } else {
            return $this->checkOnlineTestAccess($user);
        }
    }

    // =====================
    // Premium Access
    // =====================

    /**
     * Centralized premium content check.
     *
     * Policy:
     * - Offline students with valid enrollment → ALL premium content accessible
     * - Online students → must have 'premium_content' feature in their plan
     */
    public function checkPremiumAccess(User $user, bool $isPremium): array
    {
        if (!$isPremium) {
            return $this->allowed();
        }

        // Offline students with valid enrollment bypass ALL premium checks
        if ($user->isOfflineStudent()) {
            $enrollment = $user->getActiveEnrollment();
            if ($enrollment) {
                return $this->allowed();
            }
            return $this->denied(
                'No active enrollment found. Please contact your branch.',
                'offline.dashboard'
            );
        }

        // Online students — check unified premium_content feature
        if (!$user->hasFeature('premium_content')) {
            return $this->denied(
                'This test is available for premium users only. Please upgrade your plan.',
                'welcome'
            );
        }

        return $this->allowed();
    }

    // =====================
    // Offline Access Checks
    // =====================

    /**
     * Offline full test access: enrollment validity + quota + test assignment
     * For retakes (branch allows + test already completed), access is permitted even if quota exhausted.
     */
    protected function checkOfflineFullTestAccess(User $user, FullTest $fullTest): array
    {
        $enrollment = $user->getActiveEnrollment();
        if (!$enrollment) {
            return $this->denied(
                'No active enrollment found. Please contact your branch.',
                'offline.dashboard'
            );
        }

        if (!$enrollment->canAccessFullTest($fullTest->id)) {
            return $this->denied(
                'This test is not included in your enrollment package.',
                'offline.dashboard'
            );
        }

        // Check if this is a retake (branch allows retakes + test already completed)
        $isRetake = $enrollment->branchAllowsRetakes() && $enrollment->isFullTestRetake($fullTest->id);

        // If it's a retake, skip the quota check — retakes are free
        if (!$isRetake) {
            // SECURITY (C8): previously gated on $user->canTakeMoreTests(), which returns true for
            // ANY active enrollment (it never compares taken vs allowed) — so the full-test quota
            // was never enforced and students could take unlimited paid tests. Gate on the real
            // enrollment quota (isValid() && full_tests_taken < full_tests_allowed).
            if (!$enrollment->canTakeFullTest()) {
                return $this->denied(
                    'You have reached your test limit. Please contact your branch.',
                    'offline.dashboard'
                );
            }
        }

        return $this->allowed();
    }

    /**
     * Offline section test access: enrollment validity + quota + per-section limits
     * For retakes (branch allows + test already completed), access is permitted even if quota exhausted.
     */
    protected function checkOfflineSectionTestAccess(User $user, ?string $sectionType = null, ?int $testSetId = null): array
    {
        $enrollment = $user->getActiveEnrollment();
        if (!$enrollment) {
            return $this->denied(
                'No active enrollment found. Please contact your branch.',
                'offline.dashboard'
            );
        }

        // Check if this specific section test is in the allowed list
        if ($testSetId && !$enrollment->canAccessSectionTest($testSetId)) {
            return $this->denied(
                'This test is not included in your enrollment package.',
                'offline.dashboard'
            );
        }

        // Check if this is a retake (branch allows retakes + test already completed)
        $isRetake = $testSetId && $enrollment->branchAllowsRetakes() && $enrollment->isSectionTestRetake($testSetId);

        // If it's a retake, skip quota checks — retakes are free
        if (!$isRetake) {
            // SECURITY (C8): enforce the real enrollment quota instead of $user->canTakeMoreTests()
            // (always true for an active enrollment). Use the per-type cap when the enrollment
            // defines per-section limits, otherwise the legacy total cap.
            if ($sectionType && $enrollment->hasPerSectionLimits()) {
                if (!$enrollment->canTakeSectionTestOfType($sectionType)) {
                    return $this->denied(
                        "You have reached your {$sectionType} test limit. Please contact your branch.",
                        'offline.dashboard'
                    );
                }
            } elseif (!$enrollment->canTakeSectionTest()) {
                return $this->denied(
                    'You have reached your test limit. Please contact your branch.',
                    'offline.dashboard'
                );
            }
        }

        return $this->allowed();
    }

    // =====================
    // Online Access Checks
    // =====================

    /**
     * Online test access: subscription validity + monthly limits
     */
    protected function checkOnlineTestAccess(User $user): array
    {
        if (!$user->canTakeMoreTests()) {
            return $this->denied(
                'You have reached your monthly test limit. Please upgrade your plan.',
                'welcome'
            );
        }

        return $this->allowed();
    }

    // =====================
    // Quota Consumption (Atomic)
    // =====================

    /**
     * Atomically consume a full test quota.
     * Uses DB locking to prevent race conditions.
     * When $isRetake is true, skip all quota increments (free retake).
     */
    public function consumeFullTestQuota(User $user, bool $isRetake = false): void
    {
        // Retakes are free — skip quota consumption entirely
        if ($isRetake) {
            return;
        }

        if ($user->isOfflineStudent()) {
            DB::transaction(function () use ($user) {
                $enrollment = OfflineEnrollment::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->where('valid_until', '>=', now()->toDateString())
                    ->lockForUpdate()
                    ->first();

                if ($enrollment) {
                    $enrollment->increment('full_tests_taken');
                    // NOTE: Do NOT mark enrollment as 'completed' here.
                    // This runs at test START — the student hasn't finished yet.
                    // Enrollment completion is checked via canTakeFullTest() on next access.
                }
            });
        }

        // Also increment monthly counter for tracking (both online and offline)
        $user->increment('tests_taken_this_month');
    }

    /**
     * Atomically consume a section test quota for a specific type.
     * Uses DB locking to prevent race conditions.
     * When $isRetake is true, skip all quota increments (free retake).
     */
    public function consumeSectionTestQuota(User $user, string $sectionType, bool $isRetake = false): void
    {
        // Retakes are free — skip quota consumption entirely
        if ($isRetake) {
            return;
        }

        if (!$user->isOfflineStudent()) {
            return;
        }

        DB::transaction(function () use ($user, $sectionType) {
            $enrollment = OfflineEnrollment::where('user_id', $user->id)
                ->where('status', 'active')
                ->where('valid_until', '>=', now()->toDateString())
                ->lockForUpdate()
                ->first();

            if (!$enrollment) {
                return;
            }

            $sectionType = strtolower($sectionType);

            // Always update per-type tracking (even without per-section limits)
            $taken = $enrollment->section_tests_taken_by_type ?? [
                'listening' => 0, 'reading' => 0, 'writing' => 0, 'speaking' => 0,
            ];
            $taken[$sectionType] = ($taken[$sectionType] ?? 0) + 1;

            $enrollment->update([
                'section_tests_taken_by_type' => $taken,
                'section_tests_taken' => $enrollment->section_tests_taken + 1,
            ]);
            // NOTE: Do NOT mark enrollment as 'completed' here.
            // This runs at test START — completion is checked on next access.
        });
    }

    // =====================
    // Helpers
    // =====================

    /**
     * Throw TestAccessDeniedException if access is denied.
     * Convenience method for controllers that want exception-based flow.
     */
    public function assertCanAccessFullTest(User $user, FullTest $fullTest): void
    {
        $result = $this->canAccessFullTest($user, $fullTest);

        if (!$result['allowed']) {
            throw new TestAccessDeniedException($result['reason']);
        }
    }

    /**
     * Throw TestAccessDeniedException if access is denied.
     */
    public function assertCanAccessSectionTest(User $user, TestSet $testSet, ?string $sectionType = null): void
    {
        $result = $this->canAccessSectionTest($user, $testSet, $sectionType);

        if (!$result['allowed']) {
            throw new TestAccessDeniedException($result['reason']);
        }
    }

    protected function allowed(): array
    {
        return ['allowed' => true, 'reason' => null, 'redirect' => null];
    }

    protected function denied(string $reason, string $redirect): array
    {
        return ['allowed' => false, 'reason' => $reason, 'redirect' => $redirect];
    }
}
