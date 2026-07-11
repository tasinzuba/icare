<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserOnboardingController extends Controller
{
    /**
     * Show the onboarding page
     */
    public function index()
    {
        $user = auth()->user();

        // If already completed onboarding, redirect to dashboard
        if ($user->onboarding_completed) {
            return redirect()->route('student.dashboard');
        }

        // Goal feature removed
        $existingGoal = null;

        return view('student.onboarding.index', [
            'user' => $user,
            'existingGoal' => $existingGoal,
        ]);
    }

    /**
     * Save onboarding data (goal storage removed; just mark completed).
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'exam_type' => 'nullable|in:academic,general',
                'target_band' => 'nullable|numeric|min:4|max:9',
                'timeline' => 'nullable|in:1_month,2_months,3_months,not_sure',
            ]);

            $user = auth()->user();
            $user->update(['onboarding_completed' => true]);

            return response()->json([
                'success' => true,
                'redirect' => route('student.dashboard'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Onboarding store error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Skip onboarding (mark as completed without setting goal)
     */
    public function skip()
    {
        $user = auth()->user();
        $user->update(['onboarding_completed' => true]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('student.dashboard');
    }
}
