<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\StudentAttempt;
use App\Models\StudentAnswer;
use App\Models\HumanEvaluationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WritingPracticeController extends Controller
{
    /**
     * Show writing practice main page (Task 1/Task 2 selection)
     */
    public function index()
    {
        return view('student.writing-practice.index');
    }

    /**
     * Show Task 1 questions list
     */
    public function task1()
    {
        $user = auth()->user();

        // Get all Task 1 questions from active test sets
        // Support both database structures:
        // 1. New: question_type = 'writing_task' with part_number = 1
        // 2. Old: question_type starts with 'task1_'
        $questions = Question::where(function ($query) {
            $query->where(function ($q) {
                // New structure
                $q->where('question_type', 'writing_task')
                  ->where('part_number', 1);
            })->orWhere(function ($q) {
                // Old structure
                $q->where('question_type', 'like', 'task1_%');
            });
        })
        ->whereHas('testSet', function ($query) use ($user) {
            $query->where('active', true)
                  ->whereHas('section', function ($q) {
                      $q->where('name', 'writing');
                  })
                  ->forStudentType($user);
        })
        ->with('testSet')
        ->orderBy('created_at', 'desc')
        ->get();

        // Group by test set for better display
        $groupedQuestions = $questions->groupBy('test_set_id');

        return view('student.writing-practice.task1', compact('groupedQuestions', 'questions'));
    }

    /**
     * Show Task 2 questions list
     */
    public function task2()
    {
        $user = auth()->user();

        // Get all Task 2 questions from active test sets
        // Support both database structures:
        // 1. New: question_type = 'writing_task' with part_number = 2
        // 2. Old: question_type starts with 'task2_'
        $questions = Question::where(function ($query) {
            $query->where(function ($q) {
                // New structure
                $q->where('question_type', 'writing_task')
                  ->where('part_number', 2);
            })->orWhere(function ($q) {
                // Old structure
                $q->where('question_type', 'like', 'task2_%');
            });
        })
        ->whereHas('testSet', function ($query) use ($user) {
            $query->where('active', true)
                  ->whereHas('section', function ($q) {
                      $q->where('name', 'writing');
                  })
                  ->forStudentType($user);
        })
        ->with('testSet')
        ->orderBy('created_at', 'desc')
        ->get();

        // Group by test set for better display
        $groupedQuestions = $questions->groupBy('test_set_id');

        return view('student.writing-practice.task2', compact('groupedQuestions', 'questions'));
    }

    /**
     * Practice a single question
     */
    public function practiceQuestion(Question $question)
    {
        // Check if user has ongoing practice for this question
        $existingAttempt = StudentAttempt::where('user_id', auth()->id())
            ->where('practice_question_id', $question->id)
            ->where('status', 'in_progress')
            ->latest('created_at')
            ->first();

        if (!$existingAttempt) {
            // Create new practice attempt
            $attempt = StudentAttempt::create([
                'user_id' => auth()->id(),
                'test_set_id' => $question->test_set_id,
                'start_time' => now(),
                'status' => 'in_progress',
                'is_practice' => true,
                'practice_mode' => 'single_question',
                'practice_question_id' => $question->id,
                'attempt_number' => 1,
                'is_retake' => false,
            ]);

            // Pre-create answer record
            StudentAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'answer' => '',
            ]);
        } else {
            $attempt = $existingAttempt;
        }

        // Load answer
        $attempt->load('answers');

        return view('student.writing-practice.editor', compact('question', 'attempt'));
    }

    /**
     * Auto-save practice answer
     */
    public function autosave(Request $request, StudentAttempt $attempt, Question $question)
    {
        // Verify the attempt belongs to the current user
        if ($attempt->user_id !== auth()->id() || $attempt->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Invalid attempt']);
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        // Find or create the answer
        $answer = StudentAnswer::firstOrCreate(
            [
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
            ],
            [
                'answer' => $request->content,
            ]
        );

        // Update if already exists
        if (!$answer->wasRecentlyCreated) {
            $answer->update([
                'answer' => $request->content,
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Submit practice attempt
     */
    public function submit(Request $request, StudentAttempt $attempt)
    {
        // Verify the attempt belongs to the current user
        if ($attempt->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if already completed
        if ($attempt->status === 'completed') {
            return response()->json(['error' => 'Attempt already completed'], 400);
        }

        $request->validate([
            'answer' => 'required|string',
            'question_id' => 'required|exists:questions,id',
        ]);

        DB::transaction(function () use ($request, $attempt) {
            // Save answer
            $answer = StudentAnswer::where('attempt_id', $attempt->id)
                ->where('question_id', $request->question_id)
                ->first();

            if ($answer) {
                $answer->update(['answer' => $request->answer]);
            } else {
                StudentAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $request->question_id,
                    'answer' => $request->answer,
                ]);
            }

            // Mark attempt as completed
            $attempt->update([
                'end_time' => now(),
                'status' => 'completed',
                'completion_rate' => 100,
                'total_questions' => 1,
                'answered_questions' => 1,
            ]);

            // Auto-create evaluation request for offline students
            $user = auth()->user();
            if ($user->isOfflineStudent()) {
                HumanEvaluationRequest::createForOfflineStudent($attempt, $user, 'writing');
            }
        });

        return response()->json([
            'success' => true,
            'redirect' => route('student.results.show', $attempt)
        ]);
    }
}
