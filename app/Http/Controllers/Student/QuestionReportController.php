<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionReport;
use App\Models\StudentAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionReportController extends Controller
{
    /**
     * File a report against a question from the result page.
     *
     * The Report button has been posting here since it was written; the route never existed, so
     * every report 404'd and the modal — which only console.errors on failure — showed the student
     * nothing at all.
     */
    public function store(Request $request, Question $question): JsonResponse
    {
        $validated = $request->validate([
            'issue_type' => ['required', 'string', 'in:' . implode(',', array_keys(QuestionReport::ISSUE_TYPES))],
            'description' => ['nullable', 'string', 'max:2000'],
            'attempt_id' => ['nullable', 'integer'],
        ]);

        $user = $request->user();

        // Only accept an attempt id that belongs to this student, so a report cannot be filed
        // against somebody else's sitting by editing the request.
        $attemptId = null;
        if (!empty($validated['attempt_id'])) {
            $attemptId = StudentAttempt::where('id', $validated['attempt_id'])
                ->where('user_id', $user->id)
                ->value('id');
        }

        // updateOrCreate against the unique (question, user) pair: reporting the same question
        // again corrects the earlier report rather than queueing a duplicate. A report already
        // dealt with is reopened, since the student is saying it is still wrong.
        $report = QuestionReport::updateOrCreate(
            ['question_id' => $question->id, 'user_id' => $user->id],
            [
                'student_attempt_id' => $attemptId,
                'issue_type' => $validated['issue_type'],
                'description' => $validated['description'] ?? null,
                'status' => 'pending',
                'admin_note' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Thank you — your report has been sent for review.',
            'report_id' => $report->id,
        ]);
    }
}
