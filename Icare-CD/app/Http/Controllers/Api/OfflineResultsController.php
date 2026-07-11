<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FullTestAttempt;
use App\Models\StudentAttempt;
use Illuminate\Http\Request;

class OfflineResultsController extends Controller
{
    /**
     * Return results data as JSON for desktop app
     * GET /api/offline/results
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Full test results
        $fullTestAttempts = FullTestAttempt::where('user_id', $user->id)
            ->with(['fullTest', 'sectionAttempts.studentAttempt.testSet.section'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        $fullResults = $fullTestAttempts->map(function ($attempt) {
            return [
                'id' => $attempt->id,
                'attempt_id' => $attempt->id,
                'test_title' => $attempt->fullTest?->title ?? 'Unknown Test',
                'overall_score' => $attempt->overall_band_score,
                'listening_score' => $attempt->listening_score,
                'reading_score' => $attempt->reading_score,
                'writing_score' => $attempt->writing_score,
                'speaking_score' => $attempt->speaking_score,
                'completed_at' => $attempt->end_time?->format('M d, Y h:i A') ?? $attempt->created_at->format('M d, Y h:i A'),
                'result_url' => '/student/test/full-test/attempt/' . $attempt->id . '/results',
            ];
        })->values();

        // Section test IDs that belong to full tests (exclude from section results)
        $fullTestStudentAttemptIds = $fullTestAttempts->flatMap(function ($fta) {
            return $fta->sectionAttempts->pluck('student_attempt_id');
        })->filter()->toArray();

        // Individual section results
        $sectionAttempts = StudentAttempt::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereNotIn('id', $fullTestStudentAttemptIds)
            ->with('testSet.section')
            ->orderBy('created_at', 'desc')
            ->get();

        $sectionResults = $sectionAttempts->map(function ($attempt) {
            return [
                'id' => $attempt->id,
                'attempt_id' => $attempt->id,
                'test_title' => $attempt->testSet?->title ?? $attempt->testSet?->name ?? 'Unknown Test',
                'section_type' => $attempt->testSet?->section?->name ?? 'unknown',
                'score' => $attempt->band_score,
                'completed_at' => $attempt->created_at->format('M d, Y h:i A'),
                'result_url' => '/student/test/results/' . $attempt->id,
            ];
        })->values();

        return response()->json([
            'full_results' => $fullResults,
            'section_results' => $sectionResults,
        ]);
    }
}
