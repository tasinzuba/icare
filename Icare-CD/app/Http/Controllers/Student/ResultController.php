<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentAttempt;
use App\Models\Question;
use App\Models\HumanEvaluationRequest;
use App\Models\WebsiteSetting;
use App\Services\AnswerValidator;
use App\Services\BandScoreRecalculator;
use App\Http\Controllers\Traits\ResultDataTrait;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ResultController extends Controller
{
    use ResultDataTrait;

    protected AnswerValidator $answerValidator;
    protected BandScoreRecalculator $bandScoreRecalculator;

    public function __construct(AnswerValidator $answerValidator, BandScoreRecalculator $bandScoreRecalculator)
    {
        $this->answerValidator = $answerValidator;
        $this->bandScoreRecalculator = $bandScoreRecalculator;
    }

    protected function getAnswerValidator(): AnswerValidator
    {
        return $this->answerValidator;
    }
    /**
     * Display a listing of the student's results.
     */
    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $query = StudentAttempt::where('user_id', auth()->id())
            ->with(['testSet', 'testSet.section'])
            ->whereHas('testSet'); // Only get attempts where testSet exists

        // Exclude attempts that are part of full tests (only show standalone section attempts)
        $query->whereDoesntHave('fullTestSectionAttempt');

        // Filter by section - exclude full-test filter from regular attempts
        if ($request->has('section') && $request->section !== 'all' && $request->section !== 'full-test') {
            $query->whereHas('testSet.section', function($q) use ($request) {
                $q->where('name', $request->section);
            });
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('testSet', function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Score status filter
        if ($request->filled('score_status')) {
            if ($request->score_status === 'scored') {
                $query->whereNotNull('band_score');
            } else {
                $query->whereNull('band_score');
            }
        }

        // If filtering for full tests only, return empty collection for regular attempts
        if ($request->has('section') && $request->section === 'full-test') {
            // Create an empty paginator for consistency
            $attempts = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]), // empty collection
                0, // total items
                10, // per page
                1, // current page
                ['path' => $request->url()]
            );
        } else {
            // Filter by time period for regular attempts
            if ($request->has('period') && $request->period !== 'all') {
                switch($request->period) {
                    case '30days':
                        $query->where('created_at', '>=', now()->subDays(30));
                        break;
                    case '3months':
                        $query->where('created_at', '>=', now()->subMonths(3));
                        break;
                    case '6months':
                        $query->where('created_at', '>=', now()->subMonths(6));
                        break;
                }
            }

            // Sorting
            $sort = $request->get('sort', 'latest');
            switch($sort) {
                case 'oldest':
                    $query->oldest();
                    break;
                case 'score_high':
                    $query->orderByDesc('band_score');
                    break;
                case 'score_low':
                    $query->orderBy('band_score');
                    break;
                default:
                    $query->latest();
            }

            $attempts = $query->paginate(10)->withQueryString();

            // Sync stored band_score with the same calculation used on the individual
            // result page so listing numbers match the red band-score box.
            $this->bandScoreRecalculator->recalculateMany($attempts->getCollection());
        }

        // Get full test attempts separately
        $fullTestAttempts = collect();

        // Only fetch full test attempts if not filtering by specific section or if showing all/full-test
        if (!$request->has('section') || in_array($request->section, ['all', 'full-test'])) {
            $fullTestQuery = \App\Models\FullTestAttempt::where('user_id', auth()->id())
                ->with('fullTest')
                ->whereHas('fullTest'); // Only get attempts where fullTest exists

            // Search filter for full tests
            if ($request->filled('search')) {
                $search = $request->search;
                $fullTestQuery->whereHas('fullTest', function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
                });
            }

            // Status filter for full tests
            if ($request->filled('status')) {
                $fullTestQuery->where('status', $request->status);
            }

            // Score status filter for full tests
            if ($request->filled('score_status')) {
                if ($request->score_status === 'scored') {
                    $fullTestQuery->whereNotNull('overall_band_score');
                } else {
                    $fullTestQuery->whereNull('overall_band_score');
                }
            }

            // Apply time period filter
            if ($request->has('period') && $request->period !== 'all') {
                switch($request->period) {
                    case '30days':
                        $fullTestQuery->where('created_at', '>=', now()->subDays(30));
                        break;
                    case '3months':
                        $fullTestQuery->where('created_at', '>=', now()->subMonths(3));
                        break;
                    case '6months':
                        $fullTestQuery->where('created_at', '>=', now()->subMonths(6));
                        break;
                }
            }

            // Sorting for full tests
            $sort = $request->get('sort', 'latest');
            switch($sort) {
                case 'oldest':
                    $fullTestQuery->oldest();
                    break;
                case 'score_high':
                    $fullTestQuery->orderByDesc('overall_band_score');
                    break;
                case 'score_low':
                    $fullTestQuery->orderBy('overall_band_score');
                    break;
                default:
                    $fullTestQuery->latest();
            }

            $fullTestAttempts = $fullTestQuery->get();
        }

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('student.results.partials.table-body', compact('attempts', 'fullTestAttempts'))->render(),
                'pagination' => $attempts->count() > 0 ? $attempts->appends($request->query())->links()->render() : '',
                'total' => $attempts->total() + $fullTestAttempts->count(),
            ]);
        }

        return view('student.results.index', compact('attempts', 'fullTestAttempts'));
    }
    
    /**
     * Display the specified result.
     */
    public function show(Request $request, StudentAttempt $attempt)
    {
        // Ensure the attempt belongs to the authenticated user
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }
        
        // Eager load all necessary relationships to avoid N+1 queries
        $attempt->load([
            'testSet.section',
            'answers.question.options',
            'answers.selectedOption',
        ]);

        // Load passages separately and process them
        $passages = $attempt->testSet->questions()
            ->where('question_type', 'passage')
            ->orderBy('part_number')
            ->orderBy('order_number')
            ->get()
            ->map(function($passage) {
                $passage->processed_content = Question::processPassageForDisplay(
                    $passage->passage_text ?? $passage->content,
                    true
                );
                return $passage;
            });

        $formattedQuestions = [];
        $correctAnswers = 0;
        $totalQuestions = 0;
        $answeredQuestions = 0;
        $accuracy = 0;
        $bandScore = $attempt->band_score ?? 0;
        $performanceLevel = '';
        $scoreMessage = '';
        
        // Calculate statistics for automatically scored sections
        if (in_array($attempt->testSet->section->name, ['listening', 'reading'])) {
            $questions = $attempt->testSet->questions()
                ->with('options')
                ->where('question_type', '!=', 'passage')
                ->orderBy('part_number')
                ->orderBy('order_number')
                ->get();
            
            $totalQuestions = $this->calculateTotalQuestions($questions);
            
            $calculationResult = $this->calculateAnswersAndCorrections($questions, $attempt);
            $correctAnswers = $calculationResult['correct'];
            $answeredQuestions = $calculationResult['attempted'];
            
            if ($answeredQuestions > 0) {
                $accuracy = ($correctAnswers / $answeredQuestions) * 100;
            }
            
            $testType = $attempt->testSet->test_type ?? 'academic';
            $scoreData = \App\Helpers\ScoreCalculator::calculatePartialTestScore(
                $correctAnswers, $answeredQuestions, $totalQuestions, 
                $attempt->testSet->section->name, $testType
            );
            
            $bandScore = $scoreData['band_score'] ?? 0;
            $performanceLevel = $scoreData['performance_level'] ?? 'Not Attempted';
            $scoreMessage = $scoreData['message'] ?? '';

            // Always sync stored values with the freshly calculated score so
            // All Results listing + Dashboard cards match this page (red band-score box).
            try {
                $saved = $attempt->forceFill([
                    'band_score' => $bandScore,
                    'total_questions' => $totalQuestions,
                    'answered_questions' => $answeredQuestions,
                    'correct_answers' => $correctAnswers,
                ])->saveQuietly();

                if (!$saved) {
                    \Log::warning('ResultController: saveQuietly returned false for attempt', [
                        'attempt_id' => $attempt->id,
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::warning('ResultController: failed to sync attempt score', [
                    'attempt_id' => $attempt->id,
                    'error' => $e->getMessage(),
                ]);
            }
            
            // Build Vue questionsAnalysis using shared trait method
            $questionsAnalysis = $this->buildQuestionsAnalysis($questions, $attempt);
            $formattedQuestions = $this->formatQuestionsForVue($questionsAnalysis);
        }

        // Check for human evaluation request
        $humanEvaluationRequest = null;
        $sectionName = $attempt->testSet->section->name;
        if (in_array($sectionName, ['writing', 'speaking'])) {
            $humanEvaluationRequest = HumanEvaluationRequest::with(['teacher.user', 'humanEvaluation'])
                ->where('student_attempt_id', $attempt->id)
                ->first();
        }

        // Build student answers for writing/speaking sections
        $studentAnswers = [];
        $canRetake = $attempt->status === 'completed';
        $latestAttempt = StudentAttempt::getLatestAttempt($attempt->user_id, $attempt->test_set_id);
        $isLatestAttempt = $latestAttempt && $attempt->id === $latestAttempt->id;
        $hasAiFeature = auth()->user()?->isOfflineStudent()
            ? false
            : auth()->user()->hasFeature('ai_' . $sectionName . '_evaluation');

        // Human evaluation: online students → global toggle, offline students → enrollment-based
        $user = auth()->user();
        if ($user->isOfflineStudent()) {
            $hasHumanEvaluationFeature = $user->hasFeature('human_evaluation');
        } else {
            $hasHumanEvaluationFeature = WebsiteSetting::getSettings()->human_evaluation_enabled;
        }

        if (in_array($sectionName, ['writing', 'speaking'])) {
            $attempt->load(['answers.question', 'answers.speakingRecording']);
            foreach ($attempt->answers->sortBy('question.order_number') as $answer) {
                $studentAnswers[] = [
                    'task_number' => $answer->question->order_number,
                    'part_number' => $answer->question->part_number ?? $answer->question->order_number,
                    'question_text' => $answer->question->content,
                    'question_title' => $answer->question->title,
                    'question_image' => $answer->question->media_path ? $answer->question->media_url : null,
                    'answer_text' => $answer->answer,
                    'word_count' => $answer->answer ? str_word_count($answer->answer) : 0,
                    'recording_url' => $answer->speakingRecording ? route('audio.stream', $answer->speakingRecording->id) : null,
                    'recording_mime_type' => $answer->speakingRecording->mime_type ?? 'audio/webm',
                ];
            }
        }

        return Inertia::render('Student/Results/Show', [
            'attempt' => $attempt,
            'testSet' => $attempt->testSet,
            'sectionName' => $sectionName,
            'passages' => $passages ?? [],
            'correctAnswers' => $correctAnswers,
            'totalQuestions' => $totalQuestions,
            'answeredQuestions' => $answeredQuestions,
            'accuracy' => $accuracy,
            'bandScore' => $bandScore,
            'performanceLevel' => $performanceLevel,
            'scoreMessage' => $scoreMessage,
            'formattedQuestions' => $formattedQuestions,
            'humanEvaluationRequest' => $humanEvaluationRequest,
            'studentAnswers' => $studentAnswers,
            'canRetake' => $canRetake,
            'isLatestAttempt' => $isLatestAttempt,
            'hasAiFeature' => $hasAiFeature,
            'hasHumanEvaluationFeature' => $hasHumanEvaluationFeature,
            'aiEvaluated' => (bool) $attempt->ai_evaluated_at,
            'aiBandScore' => $attempt->ai_band_score,
            'completionRate' => $attempt->completion_rate ?? 0,
            'aiEvaluation' => $this->getAiEvaluationData($attempt, $sectionName),
        ]);
    }
    
    // All data preparation methods moved to ResultDataTrait:
    // buildQuestionsAnalysis, formatQuestionsForVue, calculateTotalQuestions,
    // calculateAnswersAndCorrections, getAiEvaluationData,
    // traitCheckTextAnswer, traitCompareAnswers, traitNormalizeAnswer, traitIsJson
    
    /**
     * Get detailed results data (for AJAX requests)
     */
    public function getDetails(StudentAttempt $attempt): \Illuminate\Http\JsonResponse
    {
        // Ensure the attempt belongs to the authenticated user
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }
        
        $attempt->load([
            'answers.question.options',
            'answers.selectedOption',
            'answers.question.correctOption'
        ]);
        
        $details = [];
        
        foreach ($attempt->answers as $answer) {
            $isCorrect = false;
            $correctAnswer = '';
            
            if ($answer->question->options->count() > 0) {
                // Multiple choice
                $isCorrect = $answer->selectedOption && $answer->selectedOption->is_correct;
                $correctAnswer = $answer->question->correctOption()->content ?? '';
            } else {
                // Text answer
                $isCorrect = $this->traitCheckTextAnswer($answer);
                $correctAnswer = 'See Explanation';
            }
            
            $details[] = [
                'question_id' => $answer->question->id,
                'question_number' => $answer->question->order_number,
                'is_correct' => $isCorrect,
                'student_answer' => $answer->selectedOption->content ?? $answer->answer ?? 'Not answered',
                'correct_answer' => $correctAnswer,
                'explanation' => $answer->question->explanation,
                'passage_reference' => $answer->question->passage_reference,
                'tips' => $answer->question->tips,
                'difficulty' => $answer->question->difficulty_level,
                'marker_id' => $answer->question->marker_id,
                'marker_text' => $answer->question->getMarkerText(),
            ];
        }
        
        return response()->json([
            'success' => true,
            'details' => $details,
            'summary' => [
                'total_questions' => count($details),
                'correct_answers' => collect($details)->where('is_correct', true)->count(),
                'band_score' => $attempt->band_score,
            ]
        ]);
    }
    
    /**
     * Initiate a test retake
     */
    public function retake(StudentAttempt $attempt): RedirectResponse
    {
        // Ensure the attempt belongs to the authenticated user
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if retake is allowed
        if (!$attempt->canRetake()) {
            return redirect()->back()->with('error', 'You cannot retake this test.');
        }

        // Abandon any in_progress attempt on this test set so a fresh one is created
        StudentAttempt::where('user_id', auth()->id())
            ->where('test_set_id', $attempt->test_set_id)
            ->where('status', 'in_progress')
            ->update(['status' => 'abandoned']);

        $sectionName = $attempt->testSet->section->name;

        // Go directly to test start (skip onboarding for retakes — user already knows the format)
        if (in_array($sectionName, ['listening', 'reading', 'writing', 'speaking'])) {
            return redirect()->route("student.{$sectionName}.start", [$attempt->testSet->id, 'fresh' => 1])
                ->with('info', 'Starting test retake...');
        }

        return redirect()->back()->with('error', 'Invalid test section.');
    }
}