<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentAttempt;
use App\Models\Question;
use App\Models\HumanEvaluationRequest;
use App\Services\AnswerValidator;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ResultController extends Controller
{
    protected AnswerValidator $answerValidator;

    public function __construct(AnswerValidator $answerValidator)
    {
        $this->answerValidator = $answerValidator;
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
    public function show(Request $request, StudentAttempt $attempt): View
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
                // Process passage content to convert markers to HTML
                $passage->processed_content = Question::processPassageForDisplay(
                    $passage->passage_text ?? $passage->content,
                    true // hide markers for student view
                );
                return $passage;
            });
        
        // Process questions to include marker info
        $questionsWithMarkers = collect();
        foreach ($attempt->answers as $answer) {
            if ($answer->question->question_type !== 'passage') {
                $question = $answer->question;
                
                // Check if question has marker and get marker text
                if ($question->marker_id) {
                    $question->marker_text = $question->getMarkerText();
                }
                
                // Process explanation to make marker references clickable
                if ($question->explanation) {
                    $question->processed_explanation = $question->processExplanation();
                }
                
                $questionsWithMarkers->push($answer);
            }
        }
        
        // Calculate statistics for automatically scored sections
        if (in_array($attempt->testSet->section->name, ['listening', 'reading'])) {
            $correctAnswers = 0;
            $answeredQuestions = 0; // Track actual attempted questions
            $totalQuestions = 0;

            // Get questions with options (eager loaded) excluding passages
            $questions = $attempt->testSet->questions()
                ->with('options')
                ->where('question_type', '!=', 'passage')
                ->orderBy('part_number')
                ->orderBy('order_number')
                ->get();
            
            // Calculate total question count INCLUDING all sub-questions
            $totalQuestions = $this->calculateTotalQuestions($questions);
            
            // Calculate answers and corrections with improved counting
            $calculationResult = $this->calculateAnswersAndCorrections($questions, $attempt);
            $correctAnswers = $calculationResult['correct'];
            $answeredQuestions = $calculationResult['attempted'];
            
            // Calculate accuracy based on attempted questions only
            // Accuracy = (Correct / Attempted) * 100
            if ($answeredQuestions > 0) {
                $accuracy = ($correctAnswers / $answeredQuestions) * 100;
            } else {
                $accuracy = 0;
            }
            
            // Calculate band score using official IELTS scoring
            // Band score is based on correct answers out of total questions
            $testType = $attempt->testSet->test_type ?? 'academic';
            
            $scoreData = \App\Helpers\ScoreCalculator::calculatePartialTestScore(
                $correctAnswers, 
                $answeredQuestions, 
                $totalQuestions, 
                $attempt->testSet->section->name,
                $testType
            );
            
            $bandScore = $scoreData['band_score'] ?? 0;
            $performanceLevel = $scoreData['performance_level'] ?? 'Not Attempted';
            $scoreMessage = $scoreData['message'] ?? '';
            
            // Update the attempt with calculated band score if not already set
            if (!$attempt->band_score || $attempt->band_score == 0) {
                $attempt->band_score = $bandScore;
                $attempt->save();
            }
            
            // Get current page from request
            $currentPage = $request->get('page', 1);
            $perPage = 10;
            
            return view('student.results.show', compact(
                'attempt', 
                'correctAnswers', 
                'totalQuestions',
                'answeredQuestions',
                'accuracy',
                'bandScore',
                'performanceLevel',
                'passages',
                'questionsWithMarkers',
                'currentPage',
                'perPage',
                'scoreMessage'
            ));
        }
        
        // Check for human evaluation request
        $humanEvaluationRequest = null;
        if (in_array($attempt->testSet->section->name, ['writing', 'speaking'])) {
            $humanEvaluationRequest = HumanEvaluationRequest::with(['teacher.user', 'humanEvaluation'])
                ->where('student_attempt_id', $attempt->id)
                ->first();
        }
        
        // For manually evaluated sections (Writing and Speaking)
        $currentPage = $request->get('page', 1);
        $perPage = 10;
        
        return view('student.results.show', compact('attempt', 'passages', 'humanEvaluationRequest', 'currentPage', 'perPage'));
    }
    
    /**
     * Calculate total questions for a test (handles all question types)
     */
    private function calculateTotalQuestions($questions): int
    {
        $totalQuestions = 0;
        
        foreach ($questions as $question) {
            if ($question->isMasterMatchingHeading()) {
                // Count individual sub-questions from mappings
                $mappings = $question->section_specific_data['mappings'] ?? [];
                $totalQuestions += count($mappings);
            } elseif ($question->question_type === 'sentence_completion' && isset($question->section_specific_data['sentence_completion'])) {
                // Handle enhanced sentence completion questions
                $scData = $question->section_specific_data['sentence_completion'];
                $sentences = $scData['sentences'] ?? [];
                $totalQuestions += count($sentences);
            } elseif ($question->question_type === 'drag_drop') {
                // Handle drag & drop questions - use drop_zones (same as result page display)
                $dragDropData = $question->section_specific_data ?? [];
                $dropZones = $dragDropData['drop_zones'] ?? [];
                $totalQuestions += max(count($dropZones), 1);
            } elseif ($question->question_type === 'multiple_choice') {
                // For multiple choice, count correct answers as individual questions
                $correctCount = $question->options->where('is_correct', true)->count();
                $totalQuestions += max($correctCount, 1);
            } else {
                // Count blanks and dropdowns for other question types
                $blankCount = 0;
                
                // Count content-based blanks and dropdowns
                $content = $question->content;
                preg_match_all('/\[____\d+____\]/', $content, $blankMatches);
                preg_match_all('/\[DROPDOWN_\d+\]/', $content, $dropdownMatches);
                $blankCount = count($blankMatches[0]) + count($dropdownMatches[0]);
                
                // Count section_specific_data dropdowns
                $dropdownCount = 0;
                if ($question->section_specific_data && isset($question->section_specific_data['dropdown_correct'])) {
                    $dropdownCount = count($question->section_specific_data['dropdown_correct']);
                }
                
                // Count fill_blanks placeholders
                if ($question->question_type === 'fill_blanks') {
                    preg_match_all('/\[____\d+____\]/', $content, $fillBlankMatches);
                    $fillBlankCount = count($fillBlankMatches[0]);
                    $blankCount = max($blankCount, $fillBlankCount);
                }
                
                // Count dropdown_selection placeholders
                if ($question->question_type === 'dropdown_selection') {
                    preg_match_all('/\[DROPDOWN_\d+\]/', $content, $dropdownSelectionMatches);
                    $dropdownSelectionCount = count($dropdownSelectionMatches[0]);
                    $blankCount = max($blankCount, $dropdownSelectionCount);
                }
                
                $totalCount = max($blankCount, $dropdownCount);
                $totalQuestions += max($totalCount, 1);
            }
        }
        
        return $totalQuestions;
    }
    
    /**
     * Calculate answered questions and correct answers (handles all question types)
     */
    private function calculateAnswersAndCorrections($questions, $attempt): array
    {
        $correctAnswers = 0;
        $answeredQuestions = 0;
        
        // Group answers by question ID
        $answersByQuestion = $attempt->answers->groupBy('question_id');
        
        foreach ($questions as $question) {
            $questionAnswers = $answersByQuestion->get($question->id, collect());
            
            if ($question->isMasterMatchingHeading()) {
                // Handle master matching headings - each answer is a sub-question
                $mappings = $question->section_specific_data['mappings'] ?? [];
                foreach ($questionAnswers as $answer) {
                    if ($answer->answer) {
                        $answeredQuestions++;
                        $answerData = json_decode($answer->answer, true);
                        if (isset($answerData['sub_question']) && isset($answerData['selected_letter'])) {
                            // Check if correct based on mappings
                            foreach ($mappings as $mapping) {
                                if ($mapping['question'] == $answerData['sub_question'] && 
                                    $mapping['correct'] == $answerData['selected_letter']) {
                                    $correctAnswers++;
                                    break;
                                }
                            }
                        }
                    }
                }
            } elseif ($question->question_type === 'sentence_completion' && isset($question->section_specific_data['sentence_completion'])) {
                // Handle enhanced sentence completion questions
                $scData = $question->section_specific_data['sentence_completion'];
                $sentences = $scData['sentences'] ?? [];
                
                foreach ($sentences as $sentenceIndex => $sentence) {
                    $questionNumber = $sentence['questionNumber'] ?? ($sentenceIndex + 1);
                    
                    // Find answer for this specific sentence
                    $sentenceAnswer = $questionAnswers->first(function($ans) use ($questionNumber) {
                        $answerData = json_decode($ans->answer, true);
                        if (is_array($answerData) && isset($answerData['sub_question'])) {
                            return (int)$answerData['sub_question'] === $questionNumber;
                        }
                        return false;
                    });
                    
                    if ($sentenceAnswer && $sentenceAnswer->answer) {
                        $answeredQuestions++;
                        $answerData = json_decode($sentenceAnswer->answer, true);
                        
                        if (is_array($answerData) && isset($answerData['selected_answer'])) {
                            $studentAnswer = $answerData['selected_answer'];
                            $correctAnswer = $sentence['correctAnswer'] ?? $sentence['correct_answer'] ?? $sentence['correct'] ?? null;
                            
                            if ($correctAnswer && $studentAnswer === $correctAnswer) {
                                $correctAnswers++;
                            }
                        }
                    }
                }
            } elseif ($question->question_type === 'drag_drop') {
                // Handle drag & drop questions - use drop_zones (same as result page display)
                $answer = $questionAnswers->first();
                if ($answer && $answer->answer) {
                    $answerData = json_decode($answer->answer, true);
                    if (is_array($answerData)) {
                        $dragDropData = $question->section_specific_data ?? [];
                        $dropZones = $dragDropData['drop_zones'] ?? [];

                        // Use drop_zones with zone index as key
                        // Support both zone_0 (new) and zone_1 (old from content [DRAG_1]) formats
                        foreach ($dropZones as $zoneIndex => $zone) {
                            $zoneKey = 'zone_' . $zoneIndex;
                            $oldZoneKey = 'zone_' . ($zoneIndex + 1); // Old format from [DRAG_X] content

                            // Try new format first, then old format
                            $studentAnswer = null;
                            if (isset($answerData[$zoneKey]) && $answerData[$zoneKey] !== '' && $answerData[$zoneKey] !== null) {
                                $studentAnswer = $answerData[$zoneKey];
                            } elseif (isset($answerData[$oldZoneKey]) && $answerData[$oldZoneKey] !== '' && $answerData[$oldZoneKey] !== null) {
                                $studentAnswer = $answerData[$oldZoneKey];
                            }

                            if ($studentAnswer !== null) {
                                $answeredQuestions++;
                                $correctAnswer = $zone['correct_answer'] ?? $zone['answer'] ?? null;
                                if ($correctAnswer && $this->compareAnswers($studentAnswer, $correctAnswer)) {
                                    $correctAnswers++;
                                }
                            }
                        }
                    }
                }
            } elseif ($question->question_type === 'multiple_choice') {
                // Handle multiple choice with multiple correct answers
                $correctOptions = $question->options->where('is_correct', true)->values();
                $correctCount = $correctOptions->count();

                // Get selected option IDs from ALL answer records for this question
                // (Submit creates multiple StudentAnswer records, one per selection)
                $selectedOptionIds = [];

                foreach ($questionAnswers as $answer) {
                    if ($answer->selected_option_id) {
                        $selectedOptionIds[] = $answer->selected_option_id;
                    } elseif ($answer->answer) {
                        // Also handle JSON array format (backward compatibility)
                        $decoded = json_decode($answer->answer, true);
                        if (is_array($decoded)) {
                            $selectedOptionIds = array_merge($selectedOptionIds, $decoded);
                        }
                    }
                }

                // Remove duplicates and re-index
                $selectedOptionIds = array_values(array_unique($selectedOptionIds));

                // Count each selected answer (up to correctCount)
                foreach ($selectedOptionIds as $i => $optionId) {
                    if ($i >= $correctCount) break; // Don't count more than correct options

                    $answeredQuestions++;
                    $selectedOption = $question->options->firstWhere('id', $optionId);
                    if ($selectedOption && $selectedOption->is_correct) {
                        $correctAnswers++;
                    }
                }
            } elseif ($question->question_type === 'fill_blanks') {
                // Handle fill in the blanks
                $answer = $questionAnswers->first();
                if ($answer && $answer->answer) {
                    if ($this->isJson($answer->answer)) {
                        $studentAnswers = json_decode($answer->answer, true);
                        
                        // Count each blank separately
                        preg_match_all('/\[____\d+____\]/', $question->content, $matches);
                        foreach ($matches[0] as $match) {
                            preg_match('/\d+/', $match, $numberMatch);
                            $blankNum = $numberMatch[0] ?? null;
                            
                            if ($blankNum && isset($studentAnswers['blank_' . $blankNum])) {
                                $studentAnswer = trim($studentAnswers['blank_' . $blankNum]);
                                if ($studentAnswer !== '') {
                                    $answeredQuestions++;
                                    if ($question->checkBlankAnswer($blankNum, $studentAnswer)) {
                                        $correctAnswers++;
                                    }
                                }
                            }
                        }
                    } else {
                        // Single blank answer
                        if (trim($answer->answer) !== '') {
                            $answeredQuestions++;
                            if ($this->checkTextAnswer($answer)) {
                                $correctAnswers++;
                            }
                        }
                    }
                }
            } elseif ($question->question_type === 'dropdown_selection') {
                // Handle dropdown selection
                $answer = $questionAnswers->first();
                if ($answer && $answer->answer) {
                    if ($this->isJson($answer->answer)) {
                        $studentAnswers = json_decode($answer->answer, true);
                        
                        // Count each dropdown separately
                        preg_match_all('/\[DROPDOWN_\d+\]/', $question->content, $matches);
                        foreach ($matches[0] as $match) {
                            preg_match('/\d+/', $match, $numberMatch);
                            $dropdownNum = $numberMatch[0] ?? null;
                            
                            if ($dropdownNum && isset($studentAnswers['dropdown_' . $dropdownNum])) {
                                $studentAnswer = trim($studentAnswers['dropdown_' . $dropdownNum]);
                                if ($studentAnswer !== '') {
                                    $answeredQuestions++;
                                    
                                    // Check if correct
                                    $sectionData = $question->section_specific_data;
                                    if ($sectionData && isset($sectionData['dropdown_correct'][$dropdownNum])) {
                                        $correctIndex = $sectionData['dropdown_correct'][$dropdownNum];
                                        $dropdownOptions = $sectionData['dropdown_options'][$dropdownNum] ?? '';
                                        
                                        if ($dropdownOptions) {
                                            $options = array_map('trim', explode(',', $dropdownOptions));
                                            $correctOption = $options[$correctIndex] ?? '';
                                            
                                            if ($this->compareAnswers($studentAnswer, $correctOption)) {
                                                $correctAnswers++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                // Handle regular questions (single choice, text answers, etc.)
                $answer = $questionAnswers->first();
                if ($answer) {
                    if ($answer->question->options->count() > 0) {
                        // Multiple/single choice questions
                        if ($answer->selected_option_id) {
                            $answeredQuestions++;
                            if ($answer->selectedOption && $answer->selectedOption->is_correct) {
                                $correctAnswers++;
                            }
                        }
                    } else {
                        // Text-based answers
                        if ($answer->answer && trim($answer->answer) !== '') {
                            $answeredQuestions++;
                            if ($this->checkTextAnswer($answer)) {
                                $correctAnswers++;
                            }
                        }
                    }
                }
            }
        }
        
        return [
            'correct' => $correctAnswers,
            'attempted' => $answeredQuestions
        ];
    }
    
    /**
     * Check if a text-based answer is correct
     */
    private function checkTextAnswer($answer): bool
    {
        $question = $answer->question;
        $studentAnswer = $answer->answer;
        
        // Debug log
        \Log::info('Checking answer', [
            'question_id' => $question->id,
            'student_answer' => $studentAnswer,
            'question_data' => $question->section_specific_data,
            'has_blanks' => $question->blanks()->exists(),
            'blank_count' => $question->blanks()->count(),
            'blanks' => $question->blanks()->get()->toArray()
        ]);
        
        // Handle JSON answers (fill-in-the-blanks with multiple blanks or dropdowns)
        if ($this->isJson($studentAnswer)) {
            $studentAnswers = json_decode($studentAnswer, true);
            
            // Check blanks first
            $results = $question->checkMultipleBlanks($studentAnswers);
            $allCorrect = ($results['total'] > 0 && $results['correct'] === $results['total']);
            
            // Check dropdowns
            $sectionData = $question->section_specific_data;
            if ($sectionData && isset($sectionData['dropdown_correct']) && is_array($sectionData['dropdown_correct'])) {
                // If there are dropdowns, check them
                foreach ($sectionData['dropdown_correct'] as $num => $correctIndex) {
                    $studentDropdownAnswer = null;
                    
                    if (isset($studentAnswers['dropdown_' . $num])) {
                        $studentDropdownAnswer = $studentAnswers['dropdown_' . $num];
                    } elseif (isset($studentAnswers[$num])) {
                        $studentDropdownAnswer = $studentAnswers[$num];
                    }
                    
                    $dropdownOptions = $sectionData['dropdown_options'][$num] ?? '';
                    
                    if ($dropdownOptions) {
                        $options = array_map('trim', explode(',', $dropdownOptions));
                        $correctOption = $options[$correctIndex] ?? '';
                        
                        if (!$this->compareAnswers($studentDropdownAnswer ?? '', $correctOption)) {
                            $allCorrect = false;
                            break;
                        }
                    }
                }
                
                // If only dropdowns exist (no blanks), set allCorrect based on dropdown check
                if ($results['total'] === 0 && count($sectionData['dropdown_correct']) > 0) {
                    $allCorrect = true;
                    foreach ($sectionData['dropdown_correct'] as $num => $correctIndex) {
                        $studentDropdownAnswer = null;
                        
                        if (isset($studentAnswers['dropdown_' . $num])) {
                            $studentDropdownAnswer = $studentAnswers['dropdown_' . $num];
                        } elseif (isset($studentAnswers[$num])) {
                            $studentDropdownAnswer = $studentAnswers[$num];
                        }
                        
                        $dropdownOptions = $sectionData['dropdown_options'][$num] ?? '';
                        
                        if ($dropdownOptions) {
                            $options = array_map('trim', explode(',', $dropdownOptions));
                            $correctOption = $options[$correctIndex] ?? '';
                            
                            if (!$this->compareAnswers($studentDropdownAnswer ?? '', $correctOption)) {
                                $allCorrect = false;
                                break;
                            }
                        }
                    }
                }
            }
            
            return $allCorrect;
        }
        
        // Single text answer - check if it's a single blank
        $blankAnswers = $question->getBlankAnswersArray();
        if (!empty($blankAnswers) && count($blankAnswers) === 1) {
            // Get the first (and only) blank answer
            reset($blankAnswers);
            $blankNum = key($blankAnswers);
            return $question->checkBlankAnswer($blankNum, $studentAnswer);
        }
        
        return false;
    }

    /**
     * Compare two answers with improved flexibility
     * Delegates to AnswerValidator service
     */
    private function compareAnswers($studentAnswer, $correctAnswer): bool
    {
        return $this->answerValidator->compareAnswers($studentAnswer, $correctAnswer, true);
    }

    /**
     * Normalize answer for comparison
     * Delegates to AnswerValidator service
     */
    private function normalizeAnswer($answer): string
    {
        return $this->answerValidator->normalizeAnswer($answer);
    }

    /**
     * Check if a string is valid JSON
     * Delegates to AnswerValidator service
     */
    private function isJson($string): bool
    {
        return $this->answerValidator->isJson($string);
    }
    
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
                $isCorrect = $this->checkTextAnswer($answer);
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
        
        // Get the test section name
        $sectionName = $attempt->testSet->section->name;
        
        // Redirect to the appropriate test section onboarding
        switch ($sectionName) {
            case 'listening':
                return redirect()->route('student.listening.onboarding.confirm-details', $attempt->testSet->id)
                    ->with('info', 'Starting test retake...');
                
            case 'reading':
                return redirect()->route('student.reading.onboarding.confirm-details', $attempt->testSet->id)
                    ->with('info', 'Starting test retake...');
                
            case 'writing':
                return redirect()->route('student.writing.onboarding.confirm-details', $attempt->testSet->id)
                    ->with('info', 'Starting test retake...');
                
            case 'speaking':
                return redirect()->route('student.speaking.onboarding.confirm-details', $attempt->testSet->id)
                    ->with('info', 'Starting test retake...');
                
            default:
                return redirect()->back()->with('error', 'Invalid test section.');
        }
    }
}