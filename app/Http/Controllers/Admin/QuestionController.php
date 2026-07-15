<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\TestSet;
use App\Models\TestSection;
use App\Services\QuestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class QuestionController extends Controller
{
    public function __construct(
        protected QuestionService $questionService
    ) {}
    /**
     * Display a listing of the questions.
     */
    public function index(Request $request): View
    {
        $query = Question::with(['testSet', 'testSet.section', 'options']);

        // Filter by section
        if ($request->filled('section')) {
            $query->whereHas('testSet.section', function ($q) use ($request) {
                $q->where('name', $request->section);
            });
        }

        // Filter by test set
        if ($request->filled('test_set')) {
            $query->where('test_set_id', $request->test_set);
        }

        // Filter by part
        if ($request->filled('part')) {
            $query->where('part_number', $request->part);
        }

        // Filter by question type
        if ($request->filled('question_type')) {
            $query->where('question_type', $request->question_type);
        }

        $questions = $query->orderBy('test_set_id')
                          ->orderBy('part_number')
                          ->orderBy('order_number')
                          ->paginate(30);

        // Get test sets for filtering with question count
        $testSets = TestSet::with('section')
                          ->withCount('questions')
                          ->orderBy('section_id')
                          ->orderBy('title')
                          ->get();

        return view('admin.questions.index', compact('questions', 'testSets'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create(Request $request): View
    {
        // If no test set selected, show selection page
        if (!$request->has('test_set')) {
            $testSets = TestSet::with('section')->get();
            $sections = TestSection::all();

            return view('admin.questions.select-test-set', compact('testSets', 'sections'));
        }
        
        // Get test set with section (and avatarTeacher for speaking)
        $testSet = TestSet::with(['section', 'avatarTeacher'])->findOrFail($request->test_set);

        // Get section name
        $section = $testSet->section->name;

        // Common data for all sections
        $existingQuestions = $testSet->questions()
            ->where('question_type', '!=', 'passage')
            ->orderBy('part_number')
            ->orderBy('order_number')
            ->get();

        $nextQuestionNumber = $this->questionService->calculateNextQuestionNumber($testSet);

        // Section-specific data
        $data = [
            'testSet' => $testSet,
            'existingQuestions' => $existingQuestions,
            'nextQuestionNumber' => $nextQuestionNumber,
        ];

        // Avatar teacher is now managed at test set level (no longer needed here)

        // Return section-specific view
        return view('admin.questions.create.' . $section, $data);
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Debug: Check what's coming in
        \Log::info('Question Store Request:', $request->all());
        
        // Get test set to determine section
        $testSet = TestSet::with('section')->findOrFail($request->test_set_id);
        $section = $testSet->section->name;
        
        // Base validation rules
        $rules = [
            'test_set_id' => 'required|exists:test_sets,id',
            'question_type' => 'required|string',
            'title' => 'nullable|string|max:255',
            'order_number' => 'required|integer|min:0',
            'part_number' => 'nullable|integer',
            'marks' => 'nullable|integer|min:0|max:10',
            'instructions' => 'nullable|string',
        ];

        // Handle content validation based on question type
        if ($request->question_type === 'passage') {
            if (!$request->filled('content') && !$request->filled('passage_text')) {
                return redirect()->back()
                    ->withErrors(['content' => 'Passage content is required'])
                    ->withInput();
            }
            $rules['content'] = 'nullable|string';
            $rules['passage_text'] = 'nullable|string';
        } else if ($request->question_type === 'plan_map_diagram') {
            // For diagram questions, content can be auto-generated
            $rules['content'] = 'nullable|string';
        } else if ($request->question_type === 'matching_headings') {
            // For matching headings, content will be auto-generated
            $rules['content'] = 'nullable|string';
        } else if (in_array($request->question_type, ['fill_blanks', 'sentence_completion', 'note_completion', 'summary_completion'])) {
            // For fill-in-the-blank types, content is required as it contains the blanks
            $rules['content'] = 'required|string';
        } else {
            $rules['content'] = 'required|string';
        }
        
        // Section-specific validation
        switch ($section) {
            case 'listening':
                // Check if part audio exists
                $partAudio = $testSet->getPartAudio($request->part_number ?? 1);
                
                // Make media conditional based on question type and part audio
                if (in_array($request->question_type, ['plan_map_diagram'])) {
                    // Plan/map/diagram might have their own image instead of audio
                    $rules['media'] = 'nullable|file|mimes:mp3,wav,ogg|max:51200';
                } else {
                    // Audio is always optional - can use Part audio or individual audio
                    $rules['media'] = 'nullable|file|mimes:mp3,wav,ogg|max:51200';
                }
                
                $rules['part_number'] = 'required|integer|min:1|max:4';
                break;
                
            case 'reading':
                $rules['part_number'] = 'required|integer|min:1|max:3';
                break;
                
            case 'writing':
                $rules['word_limit'] = 'required|integer|min:50|max:500';
                $rules['time_limit'] = 'required|integer|min:1|max:60';
                if (strpos($request->question_type, 'task1') !== false) {
                    $rules['media'] = 'nullable|file|mimes:jpg,jpeg,png,gif|max:5120';
                }
                break;
                
            case 'speaking':
                $rules['time_limit'] = 'required|integer|min:1|max:10';

                // Add progressive card validation rules
                $rules['read_time'] = 'nullable|integer|min:3|max:60';
                $rules['min_response_time'] = 'nullable|integer|min:10|max:120';
                $rules['max_response_time'] = 'nullable|integer|min:30|max:300';
                $rules['auto_progress'] = 'nullable|boolean';
                $rules['card_theme'] = 'nullable|string|in:blue,purple,green,red';
                $rules['speaking_tips'] = 'nullable|string|max:500';
                $rules['cue_card_points_text'] = 'nullable|string';

                // Avatar teacher is managed at test set level (not editable per question)
                break;
        }
        
        // IMPORTANT: Only add options validation if question type needs it
        if ($this->questionService->needsOptions($request->question_type)) {
            $rules['options'] = 'required|array|min:2';
            $rules['options.*.content'] = 'required|string';
            
            // For multiple choice, validate correct_options array instead of correct_option
            if ($request->question_type === 'multiple_choice') {
                $rules['correct_options'] = 'required|array|min:1';
                $rules['correct_options.*'] = 'integer|min:0';
            } else {
                $rules['correct_option'] = 'required|integer|min:0';
            }
        }
        
        // Add type-specific validation rules only if JSON data not provided
        if ($request->question_type === 'matching' && !$request->has('matching_pairs_json')) {
            $rules['matching_pairs'] = 'required|array|min:2';
            $rules['matching_pairs.*.left'] = 'required|string';
            $rules['matching_pairs.*.right'] = 'required|string';
        }
        
        // Add sentence completion validation
        if ($request->question_type === 'sentence_completion') {
            if ($request->has('sentence_completion_json')) {
                $rules['sentence_completion_json'] = 'required|json';
            }
        }
        
        // Add matching headings validation
        if ($request->question_type === 'matching_headings') {
            // Check for JSON data first
            if ($request->has('matching_headings_json')) {
                $rules['matching_headings_json'] = 'required|json';
            }
            // Don't require traditional options for matching_headings
            // Options will be extracted from the JSON data
        }

        if ($request->question_type === 'form_completion' && !$request->has('form_structure_json')) {
            $rules['form_structure.title'] = 'required|string';
            $rules['form_structure.fields'] = 'required|array|min:1';
            $rules['form_structure.fields.*.label'] = 'required|string';
            $rules['form_structure.fields.*.answer'] = 'required|string';
        }

        
        try {
            $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', $e->errors());
            throw $e;
        }
        
        // Handle file upload — try R2 if configured, else local public
        $mediaPath = null;
        $storageDisk = $this->resolveStorageDisk();
        if ($request->hasFile('media')) {
            $mediaPath = $request->file('media')->store('questions/' . $section, $storageDisk);
        }

        // Handle diagram image for plan_map_diagram
        if ($request->question_type === 'plan_map_diagram' && $request->hasFile('diagram_image')) {
            $diagramPath = $request->file('diagram_image')->store('questions/diagrams', $storageDisk);
            $mediaPath = $diagramPath; // Override media path with diagram
        }

        // Handle type-specific data
        $typeSpecificData = [];
        $sectionSpecificData = []; // Initialize here

        // Drag & Drop Question Type
        if ($request->question_type === 'drag_drop') {
            $dropZones = [];
            $options = [];
            $allowReuse = $request->has('drag_drop_allow_reuse');
            
            // Process drag zones from content [DRAG_X] markers
            if ($request->has('drag_zones')) {
                foreach ($request->drag_zones as $num => $zone) {
                    if (!empty($zone['answer'])) {
                        $dropZones[] = [
                            'zone_number' => $num,
                            'answer' => trim($zone['answer'])
                        ];
                    }
                }
            }
            
            // Process draggable options
            if ($request->has('drag_drop_options')) {
                foreach ($request->drag_drop_options as $option) {
                    if (!empty($option)) {
                        $options[] = trim($option);
                    }
                }
            }
            
            // Store in section specific data
            $sectionSpecificData['drop_zones'] = $dropZones;
            $sectionSpecificData['draggable_options'] = $options;
            $sectionSpecificData['allow_reuse'] = $allowReuse;
            
            \Log::info('Drag & Drop data:', [
                'drop_zones' => $dropZones,
                'drop_zone_count' => count($dropZones),
                'options' => $options,
                'allow_reuse' => $allowReuse
            ]);
        }

        // Check for JSON data first (new approach)
        if ($request->has('matching_pairs_json')) {
            $matchingPairs = json_decode($request->matching_pairs_json, true);
            if ($matchingPairs) {
                $typeSpecificData['matching_pairs'] = $matchingPairs;
                \Log::info('Matching pairs from JSON:', $matchingPairs);
            }
        } elseif ($request->question_type === 'matching' && $request->has('matching_pairs')) {
            // Fallback to old approach
            if ($request->has('matching_pairs')) {
                $matchingPairs = [];
                foreach ($request->matching_pairs as $pair) {
                    if (!empty($pair['left']) && !empty($pair['right'])) {
                        $matchingPairs[] = [
                            'left' => trim($pair['left']),
                            'right' => trim($pair['right'])
                        ];
                    }
                }
                $typeSpecificData['matching_pairs'] = $matchingPairs;
                \Log::info('Matching pairs from form:', $matchingPairs);
            }
        }
        
        if ($request->has('form_structure_json')) {
            $formStructure = json_decode($request->form_structure_json, true);
            if ($formStructure) {
                $typeSpecificData['form_structure'] = $formStructure;
                \Log::info('Form structure from JSON:', $formStructure);
            }
        } elseif ($request->question_type === 'form_completion' && $request->has('form_structure')) {
            // Fallback to old approach
            if ($request->has('form_structure')) {
                $formStructure = [
                    'title' => $request->form_structure['title'] ?? 'Form',
                    'fields' => []
                ];
                
                if (isset($request->form_structure['fields'])) {
                    foreach ($request->form_structure['fields'] as $index => $field) {
                        if (!empty($field['label']) && !empty($field['answer'])) {
                            $formStructure['fields'][] = [
                                'label' => trim($field['label']),
                                'blank_id' => $index + 1,
                                'answer' => trim($field['answer'])
                            ];
                        }
                    }
                }
                $typeSpecificData['form_structure'] = $formStructure;
                \Log::info('Form structure from form:', $formStructure);
            }
        }
        
        // Handle matching headings JSON data
        if ($request->question_type === 'matching_headings' && $request->has('matching_headings_json')) {
            $matchingHeadingsData = json_decode($request->matching_headings_json, true);
            if ($matchingHeadingsData) {
                $typeSpecificData['matching_headings'] = $matchingHeadingsData;
                
                // Store mappings in section specific data
                $sectionSpecificData['headings'] = $matchingHeadingsData['headings'] ?? [];
                $sectionSpecificData['mappings'] = $matchingHeadingsData['mappings'] ?? [];
                
                \Log::info('Matching headings data from JSON:', $matchingHeadingsData);
            }
        }
        
        // Handle sentence completion JSON data
        if ($request->question_type === 'sentence_completion' && $request->has('sentence_completion_json')) {
            $sentenceCompletionData = json_decode($request->sentence_completion_json, true);
            if ($sentenceCompletionData) {
                // Store the sentence completion data
                $sectionSpecificData['sentence_completion'] = $sentenceCompletionData;
                
                // Store dropdown options in expected format
                if (isset($sentenceCompletionData['options'])) {
                    $dropdownOptions = [];
                    foreach ($sentenceCompletionData['sentences'] as $index => $sentence) {
                        $questionNum = $sentence['questionNumber'];
                        // Create options string from available options
                        $optionsArray = array_column($sentenceCompletionData['options'], 'text');
                        $dropdownOptions[$questionNum] = implode(',', $optionsArray);
                    }
                    $sectionSpecificData['dropdown_options'] = $dropdownOptions;
                    
                    // Store correct answers
                    $dropdownCorrect = [];
                    foreach ($sentenceCompletionData['sentences'] as $index => $sentence) {
                        $questionNum = $sentence['questionNumber'];
                        // Find index of correct answer
                        $correctLetter = $sentence['correctAnswer'];
                        $correctIndex = ord($correctLetter) - ord('A');
                        $dropdownCorrect[$questionNum] = $correctIndex;
                    }
                    $sectionSpecificData['dropdown_correct'] = $dropdownCorrect;
                }
                
                \Log::info('Sentence completion data from JSON:', $sentenceCompletionData);
            }
        }
        
        if ($request->question_type === 'plan_map_diagram') {
            // diagram_hotspots is a JSON string from the diagram-manager partial.
            // diagram_hotspots_json is a legacy alternative.
            $raw = $request->input('diagram_hotspots_json') ?: $request->input('diagram_hotspots');
            $diagramData = is_string($raw) ? json_decode($raw, true) : (is_array($raw) ? $raw : []);

            if (is_array($diagramData) && !empty($diagramData['hotspots'])) {
                // Derive word bank from hotspot labels (drag-drop on student side)
                $wordBank = collect($diagramData['hotspots'])
                    ->pluck('label')
                    ->filter(fn($v) => is_string($v) && trim($v) !== '')
                    ->map(fn($v) => trim($v))
                    ->unique()
                    ->values()
                    ->toArray();

                $payload = [
                    'hotspots' => array_values(array_map(function ($h) {
                        return [
                            'id' => $h['id'] ?? null,
                            'x' => isset($h['x']) ? (float)$h['x'] : 0,
                            'y' => isset($h['y']) ? (float)$h['y'] : 0,
                            'label' => isset($h['label']) ? trim($h['label']) : '',
                        ];
                    }, $diagramData['hotspots'])),
                    'dropdown_options' => $wordBank,
                    'start_number' => (int)($diagramData['start_number'] ?? 1),
                    'image_width' => $diagramData['imageWidth'] ?? null,
                    'image_height' => $diagramData['imageHeight'] ?? null,
                ];

                $typeSpecificData['diagram_hotspots'] = $payload;
                $sectionSpecificData['diagram_type'] = 'map_plan_diagram';
                $sectionSpecificData['answer_type'] = 'drag_drop';
                $sectionSpecificData['dropdown_options'] = $wordBank;
                $sectionSpecificData['start_number'] = $payload['start_number'];

                \Log::info('Diagram saved', ['payload' => $payload]);
            }
        }
        
        // Get diagram data before transaction (use what was already parsed above)
        $diagramData = $typeSpecificData['diagram_hotspots'] ?? [];
        
        DB::transaction(function () use ($request, $testSet, $section, $mediaPath, $typeSpecificData, $diagramData, &$sectionSpecificData, $storageDisk) {
            // Process fill-in-the-blank questions
            // $sectionSpecificData already initialized above
            
            // Handle fill-in-the-blank answers (for fill_blanks, note_completion, summary_completion)
            if (in_array($request->question_type, ['fill_blanks', 'note_completion', 'summary_completion'])) {
                // Extract blank answers from content
                $content = $request->content;
                $blankAnswers = [];
                
                // Process [____N____] format blanks
                if (preg_match_all('/\[____\d+____\]/', $content, $matches)) {
                    // Get all blank answers from request
                    $requestBlankAnswers = $request->input('blank_answers', []);
                    
                    // Re-index to 1-based
                    foreach ($requestBlankAnswers as $index => $answer) {
                        if (!empty($answer)) {
                            $blankAnswers[$index + 1] = $answer;
                        }
                    }
                }
                
                if (!empty($blankAnswers)) {
                    $sectionSpecificData['blank_answers'] = $blankAnswers;
                }
            }
            
            // Handle dropdown selection, matching grid and form completion (all use dropdowns)
            if (in_array($request->question_type, ['dropdown_selection', 'matching_grid', 'form_completion'])) {
                // Process dropdown options
                if ($request->has('dropdown_options')) {
                    $dropdownOptions = [];
                    $dropdownCorrect = [];
                    $requestDropdownOptions = $request->input('dropdown_options', []);
                    $requestDropdownCorrect = $request->input('dropdown_correct', []);
                    
                    foreach ($requestDropdownOptions as $index => $optionsString) {
                        if (!empty($optionsString)) {
                            $dropdownOptions[$index + 1] = $optionsString;
                            if (isset($requestDropdownCorrect[$index])) {
                                $dropdownCorrect[$index + 1] = (int)$requestDropdownCorrect[$index];
                            }
                        }
                    }
                    
                    if (!empty($dropdownOptions)) {
                        $sectionSpecificData['dropdown_options'] = $dropdownOptions;
                        $sectionSpecificData['dropdown_correct'] = $dropdownCorrect;
                    }
                }
            }
            
            // Handle sentence completion separately (it has its own structure)
            if ($request->question_type === 'sentence_completion' && !$request->has('sentence_completion_json')) {
                // Legacy handling for sentence completion without JSON
                $content = $request->content;
                $blankAnswers = [];
                
                if (preg_match_all('/\[____\d+____\]/', $content, $matches)) {
                    $requestBlankAnswers = $request->input('blank_answers', []);
                    foreach ($requestBlankAnswers as $index => $answer) {
                        if (!empty($answer)) {
                            $blankAnswers[$index + 1] = $answer;
                        }
                    }
                }
                
                if (!empty($blankAnswers)) {
                    $sectionSpecificData['blank_answers'] = $blankAnswers;
                }
            }
            // Determine if question should use part audio
            $usePartAudio = false; // Default to false
            
            if ($section === 'listening') {
                $partAudio = $testSet->getPartAudio($request->part_number ?? 1);
                
                // Use part audio if:
                // 1. Part audio exists AND
                // 2. User didn't upload custom audio AND
                // 3. User didn't explicitly choose custom audio
                if ($partAudio && !$mediaPath && $request->input('use_custom_audio') != '1') {
                    $usePartAudio = true;
                }
            }
            
            // Generate content for diagram questions if not provided
            $content = $request->content;
            if ($request->question_type === 'plan_map_diagram' && empty($content)) {
                $optionCount = is_array($diagramData) && isset($diagramData['dropdown_options']) ? 
                              count($diagramData['dropdown_options']) : 4;
                $startNum = is_array($diagramData) && isset($diagramData['start_number']) ? 
                           $diagramData['start_number'] : 1;
                $endNum = $startNum + $optionCount - 1;
                
                $content = "Label the diagram below. Write the correct letter, A-" . 
                          chr(64 + $optionCount) . 
                          ", next to questions $startNum-$endNum.";
            }
            
            // Generate content for matching headings if not provided
            if ($request->question_type === 'matching_headings' && empty($content)) {
                $startNum = $request->order_number ?? 1;
                if (isset($typeSpecificData['matching_headings']['mappings'])) {
                    $count = count($typeSpecificData['matching_headings']['mappings']);
                } else {
                    $count = 5; // default
                }
                $endNum = $startNum + $count - 1;
                $content = "Questions {$startNum}-{$endNum}\n\nChoose the correct heading for each paragraph from the list of headings below.";
            }
            
            // Calculate marks for multiple choice questions
            $marks = $request->marks ?? 1;
            if ($request->question_type === 'multiple_choice' && $request->has('correct_options')) {
                // For multiple choice, marks = number of correct options
                $marks = count($request->correct_options);
            }
            
            // Calculate marks for drag & drop questions
            if ($request->question_type === 'drag_drop') {
                // For drag & drop, marks = number of drag zones
                if (isset($sectionSpecificData['drop_zones'])) {
                    $marks = $request->marks ?? count($sectionSpecificData['drop_zones']);
                }
            }
            
            // Prepare question data
            $questionData = [
                'test_set_id' => $request->test_set_id,
                'question_type' => $request->question_type,
                'title' => $request->title,
                'content' => $content,
                'order_number' => $request->order_number,
                'part_number' => $request->part_number ?? 1,
                'marks' => $marks,
                'instructions' => $request->instructions,
                'media_path' => $mediaPath,
                'media_url' => null,
                'storage_disk' => $storageDisk,
                'use_part_audio' => $usePartAudio,
                'audio_transcript' => $request->audio_transcript ?? null,
                'word_limit' => $request->word_limit ?? null,
                'time_limit' => $request->time_limit ?? null,
            ];
            
            // Set marks based on question type
            if ($request->question_type === 'matching_headings' && isset($typeSpecificData['matching_headings']['mappings'])) {
                $questionData['marks'] = $request->marks ?? count($typeSpecificData['matching_headings']['mappings']);
            }
            
            // Add progressive card fields for speaking section
            if ($section === 'speaking') {
                $questionData['read_time'] = $request->read_time ?? $this->questionService->getDefaultReadTime($request->question_type);
                $questionData['min_response_time'] = $request->min_response_time ?? $this->questionService->getDefaultMinResponse($request->question_type);
                $questionData['max_response_time'] = $request->max_response_time ?? $this->questionService->getDefaultMaxResponse($request->question_type);
                $questionData['auto_progress'] = $request->has('auto_progress') ? (bool)$request->auto_progress : true;
                $questionData['card_theme'] = $request->card_theme ?? 'blue';
                $questionData['speaking_tips'] = $request->speaking_tips;

                // Avatar settings - always inherited from test set level
                if ($testSet->avatar_teacher_id) {
                    $questionData['avatar_teacher_id'] = $testSet->avatar_teacher_id;
                    $questionData['avatar_status'] = 'pending'; // Will trigger video generation
                }
                $questionData['pause_before_record'] = $request->pause_before_record ?? 2;

                // Handle cue card points for Part 2
                if ($request->question_type === 'part2_cue_card' && $request->has('form_structure_json')) {
                    $questionData['form_structure'] = json_decode($request->form_structure_json, true);
                } elseif ($request->question_type === 'part2_cue_card' && $request->has('cue_card_points_text')) {
                    // Convert text to structure
                    $points = array_filter(array_map('trim', explode("\n", $request->cue_card_points_text)));
                    if (!empty($points)) {
                        $questionData['form_structure'] = [
                            'fields' => array_map(function($point) {
                                return ['label' => $point];
                            }, $points)
                        ];
                    }
                }
            }
            
            // Add type-specific fields directly
            if (isset($typeSpecificData['matching_pairs'])) {
                $questionData['matching_pairs'] = $typeSpecificData['matching_pairs'];
            }
            if (isset($typeSpecificData['form_structure']) && !isset($questionData['form_structure'])) {
                $questionData['form_structure'] = $typeSpecificData['form_structure'];
            }
            if (isset($typeSpecificData['diagram_hotspots'])) {
                $questionData['diagram_hotspots'] = $typeSpecificData['diagram_hotspots'];
                // Set blank count for diagram questions based on number of options
                if ($request->question_type === 'plan_map_diagram') {
                    $questionData['blank_count'] = count($typeSpecificData['diagram_hotspots']['dropdown_options'] ?? []);
                }
            }
            
            // Merge all section specific data
            $allSectionData = array_merge($sectionSpecificData, $typeSpecificData);
            if (!empty($allSectionData)) {
                $questionData['section_specific_data'] = $allSectionData;
            }
            
            \Log::info('Creating question with data:', $questionData);
            
            $question = Question::create($questionData);
            
            \Log::info('Question created:', ['id' => $question->id, 'use_part_audio' => $question->use_part_audio]);
            
            // IMPORTANT: Save blank answers to QuestionBlank table
            if (in_array($request->question_type, ['sentence_completion', 'note_completion', 'summary_completion', 'form_completion', 'fill_blanks'])) {
                // Extract blanks from content
                preg_match_all('/\[____(\d+)____\]/', $question->content, $matches);
                $blankNumbers = array_unique($matches[1]);
                
                // Get blank answers from request
                $requestBlankAnswers = $request->input('blank_answers', []);
                
                // Re-index request array to match blank numbers
                $blankAnswersByNumber = [];
                $arrayIndex = 0;
                foreach ($blankNumbers as $blankNum) {
                    if (isset($requestBlankAnswers[$arrayIndex])) {
                        $blankAnswersByNumber[$blankNum] = $requestBlankAnswers[$arrayIndex];
                    }
                    $arrayIndex++;
                }
                
                \Log::info('Blank answers mapping', [
                    'blank_numbers' => $blankNumbers,
                    'request_answers' => $requestBlankAnswers,
                    'mapped_answers' => $blankAnswersByNumber
                ]);
                
                foreach ($blankAnswersByNumber as $blankNum => $answerText) {
                    if (!empty($answerText)) {
                        // Check for alternates (separated by |)
                        $alternates = null;
                        if (strpos($answerText, '|') !== false) {
                            $parts = array_map('trim', explode('|', $answerText));
                            $answerText = $parts[0]; // Primary answer
                            $alternates = array_slice($parts, 1); // Alternate answers
                        }
                        
                        // Create QuestionBlank entry
                        $question->blanks()->create([
                            'blank_number' => $blankNum,
                            'correct_answer' => $answerText,
                            'alternate_answers' => $alternates
                        ]);
                        
                        \Log::info("Created blank {$blankNum} with answer: {$answerText}");
                    }
                }
                
                // Update section_specific_data to have clean format
                $cleanBlanks = [];
                foreach ($question->blanks as $blank) {
                    $cleanBlanks[$blank->blank_number] = $blank->correct_answer;
                }
                
                $sectionData = $question->section_specific_data ?? [];
                $sectionData['blank_answers'] = $cleanBlanks;
                $question->section_specific_data = $sectionData;
                $question->save();
            }
            
            // Create options if applicable (using service method)
            if ($this->questionService->needsOptions($request->question_type) && isset($request->options)) {
                foreach ($request->options as $index => $option) {
                    if (!empty($option['content'])) {
                        // For matching_headings, check if it's a correct heading based on mappings
                        $isCorrect = false;
                        if ($request->question_type === 'matching_headings') {
                            // For matching headings, we'll mark options as correct based on JSON data
                            // This is handled differently as mappings determine correctness
                            $isCorrect = false; // Default to false, actual mapping is in section_specific_data
                        } else if ($request->question_type === 'multiple_choice') {
                            // For multiple choice, check if this index is in the correct_options array
                            $correctOptions = $request->input('correct_options', []);
                            $isCorrect = in_array($index, $correctOptions);
                        } else if ($request->question_type === 'single_choice') {
                            // For single choice (radio), check against correct_option
                            $correctOption = $request->input('correct_option');
                            $isCorrect = ($correctOption !== null && $correctOption == $index);
                        } else {
                            // For other types (true_false, yes_no, etc.)
                            $isCorrect = ($request->correct_option == $index);
                        }
                        
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'content' => $option['content'],
                            'is_correct' => $isCorrect,
                        ]);
                    }
                }
            }
            
            // Handle matching_headings options separately
            if ($request->question_type === 'matching_headings' && isset($request->options)) {
                foreach ($request->options as $index => $option) {
                    if (!empty($option['content'])) {
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'content' => $option['content'],
                            'is_correct' => false, // For matching headings, correctness is determined by mappings
                        ]);
                    }
                }
            }
        });
        
        // Redirect based on action
        if ($request->action === 'save_and_new') {
            return redirect()->route('admin.questions.create', ['test_set' => $request->test_set_id])
                ->with('success', 'Question created successfully. Add another question.');
        }
        
        return redirect()->route('admin.test-sets.show', $request->test_set_id)
            ->with('success', 'Question created successfully.');
    }

    /**
     * #6: Bulk-create TRUE/FALSE/NOT-GIVEN or YES/NO/NOT-GIVEN questions in one submission.
     * Each item becomes a separate question (statement -> content) with the three options and the
     * chosen answer flagged — identical in shape to a single true_false/yes_no question.
     */
    public function bulkStoreSimple(Request $request, TestSet $testSet): \Illuminate\Http\JsonResponse
    {
        abort_unless(auth()->user()->hasPermission('questions.create'), 403);

        $validated = $request->validate([
            'question_type' => 'required|in:true_false,yes_no',
            'part_number' => 'nullable|integer|min:0|max:4',
            'instruction' => 'nullable|string|max:5000',
            'items' => 'required|array|min:1',
            'items.*.statement' => 'required|string|max:2000',
            'items.*.answer' => 'required|string',
        ]);

        $optionSet = $validated['question_type'] === 'yes_no'
            ? ['YES', 'NO', 'NOT GIVEN']
            : ['TRUE', 'FALSE', 'NOT GIVEN'];
        $allowedAnswers = array_map('strtoupper', $optionSet);
        $partNumber = $validated['part_number'] ?? 1;

        // Shared instruction ("Do the following statements agree...") is applied to EVERY question in
        // the batch. The student renderer groups consecutive questions by their instruction string and
        // shows it once as a group heading, so an identical value across the batch renders correctly.
        // The instruction comes from the same rich-text "Instructions / Notes" editor the single-question
        // form uses, so store the HTML as-is (identical to store()/update()). Treat a text-empty editor
        // (e.g. "<p></p>") as null so it doesn't create an empty instruction heading.
        $instruction = trim((string) ($validated['instruction'] ?? ''));
        $instructionHtml = trim(strip_tags($instruction)) !== '' ? $instruction : null;

        $created = 0;
        DB::transaction(function () use ($validated, $testSet, $optionSet, $allowedAnswers, $partNumber, $instructionHtml, &$created) {
            // Continue numbering from where the test set currently ends — same convention as the
            // single-question form (which auto-fills order_number with calculateNextQuestionNumber,
            // counting multi-blank/matching items). Each TF/YN row is exactly one answerable
            // question, so increment by 1 per created row.
            $nextOrder = $this->questionService->calculateNextQuestionNumber($testSet);
            foreach ($validated['items'] as $item) {
                $answer = strtoupper(trim($item['answer']));
                if (!in_array($answer, $allowedAnswers, true)) {
                    continue; // skip rows whose answer is outside the allowed set
                }
                $question = Question::create([
                    'test_set_id' => $testSet->id,
                    'question_type' => $validated['question_type'],
                    'content' => trim($item['statement']),
                    'instructions' => $instructionHtml,
                    'part_number' => $partNumber,
                    'order_number' => $nextOrder,
                    'marks' => 1,
                ]);
                foreach ($optionSet as $opt) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'content' => $opt,
                        'is_correct' => (strtoupper($opt) === $answer),
                    ]);
                }
                $nextOrder++;
                $created++;
            }
        });

        return response()->json([
            'success' => true,
            'created' => $created,
            'message' => "{$created} question(s) created successfully.",
        ]);
    }

    /**
     * Display the specified question.
     */
    public function show(Question $question): View
    {
        $question->load(['testSet', 'testSet.section', 'options']);
        return view('admin.questions.show', compact('question'));
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(Question $question): View
    {
        // Load relationships
        $question->load(['testSet', 'testSet.section', 'options', 'blanks']);
        
        // Get section name
        $section = $question->testSet->section->name;
        
        // Prepare common data
        $data = [
            'question' => $question,
            'testSet' => $question->testSet,
        ];
        
        // Add section-specific question types
        switch ($section) {
            case 'listening':
                $data['questionTypes'] = [
                    'multiple_choice' => 'Multiple Choice',
                    'form_completion' => 'Form Completion',
                    'note_completion' => 'Note Completion',
                    'sentence_completion' => 'Sentence Completion',
                    'short_answer' => 'Short Answer',
                    'matching' => 'Matching',
                    'plan_map_diagram' => 'Plan/Map/Diagram Labeling'
                ];
                break;
                
            case 'reading':
                $data['questionTypes'] = [
                    'passage' => '📄 Reading Passage',
                    'multiple_choice' => 'Multiple Choice',
                    'true_false' => 'True/False/Not Given',
                    'yes_no' => 'Yes/No/Not Given',
                    'matching_headings' => 'Matching Headings',
                    'matching_information' => 'Matching Information',
                    'matching_features' => 'Matching Features',
                    'sentence_completion' => 'Sentence Completion',
                    'summary_completion' => 'Summary Completion',
                    'short_answer' => 'Short Answer',
                    'fill_blanks' => 'Fill in the Blanks',
                    'dropdown_selection' => 'Matching Letters'
                ];
                break;
                
            case 'writing':
                $data['questionTypes'] = [
                    'task1_line_graph' => 'Task 1: Line Graph',
                    'task1_bar_chart' => 'Task 1: Bar Chart',
                    'task1_pie_chart' => 'Task 1: Pie Chart',
                    'task1_table' => 'Task 1: Table',
                    'task1_process' => 'Task 1: Process Diagram',
                    'task1_map' => 'Task 1: Map',
                    'task2_opinion' => 'Task 2: Opinion Essay',
                    'task2_discussion' => 'Task 2: Discussion Essay',
                    'task2_problem_solution' => 'Task 2: Problem/Solution',
                    'task2_advantage_disadvantage' => 'Task 2: Advantages/Disadvantages'
                ];
                break;
                
            case 'speaking':
                $data['questionTypes'] = [
                    'part1_personal' => 'Part 1: Personal Questions',
                    'part2_cue_card' => 'Part 2: Cue Card',
                    'part3_discussion' => 'Part 3: Discussion'
                ];
                // Avatar teacher is managed at test set level (shown as read-only in view)
                break;
        }
        
        // Check if section-specific edit view exists
        $sectionView = 'admin.questions.edit.' . $section;
        if (view()->exists($sectionView)) {
            return view($sectionView, $data);
        }
        
        // Otherwise use common edit view
        return view('admin.questions.edit.common', $data);
    }

    /**
     * Update the specified question in storage.
     */
    public function update(Request $request, Question $question): RedirectResponse
    {
        // Get test set to determine section
        $testSet = TestSet::with('section')->findOrFail($question->test_set_id);
        $section = $testSet->section->name;
        
        // Base validation rules
        $rules = [
            'question_type' => 'required|string',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'order_number' => 'required|integer|min:0',
            'part_number' => 'nullable|integer',
            'marks' => 'nullable|integer|min:0|max:10',
            'instructions' => 'nullable|string',
        ];

        // Section-specific validation
        if ($section === 'speaking') {
            $rules['time_limit'] = 'required|integer|min:1|max:10';
            $rules['read_time'] = 'nullable|integer|min:3|max:60';
            $rules['min_response_time'] = 'nullable|integer|min:10|max:120';
            $rules['max_response_time'] = 'nullable|integer|min:30|max:300';
            $rules['auto_progress'] = 'nullable|boolean';
            $rules['card_theme'] = 'nullable|string|in:blue,purple,green,red';
            $rules['speaking_tips'] = 'nullable|string|max:500';
            // Avatar teacher is managed at test set level (not editable per question)
        }
        
        // Add matching headings validation
        if ($request->question_type === 'matching_headings') {
            if ($request->has('matching_headings_json')) {
                $rules['matching_headings_json'] = 'required|json';
            }
        }
        
        // Add sentence completion validation
        if ($request->question_type === 'sentence_completion') {
            if ($request->has('sentence_completion_json')) {
                $rules['sentence_completion_json'] = 'required|json';
            }
        }
        
        // Add options validation for multiple choice
        if ($question->question_type === 'multiple_choice' && $request->has('options')) {
            $rules['options'] = 'required|array|min:2';
            $rules['options.*.content'] = 'required|string';
            $rules['correct_options'] = 'required|array|min:1';
            $rules['correct_options.*'] = 'integer|min:0';
        }

        $request->validate($rules);

        // Handle media file upload if new file is provided
        $newStorageDisk = $this->resolveStorageDisk();
        if ($request->hasFile('media')) {
            // Delete old media if exists
            if ($question->media_path) {
                $oldDisk = $question->storage_disk ?? 'public';
                Storage::disk($oldDisk)->delete($question->media_path);
            }

            // Upload new media
            $mediaPath = $request->file('media')->store('questions/' . $section, $newStorageDisk);

            // Will be added to updateData below
        }

        // Update data array
        $updateData = [
            'question_type' => $request->question_type,
            'title' => $request->title,
            'content' => $request->content,
            'order_number' => $request->order_number,
            'part_number' => $request->part_number ?? 1,
            'marks' => $request->marks ?? 1,
            'instructions' => $request->instructions,
            'word_limit' => $request->word_limit ?? null,
            'time_limit' => $request->time_limit ?? null,
        ];

        // Add media path and storage_disk if new file was uploaded
        if (isset($mediaPath)) {
            $updateData['media_path'] = $mediaPath;
            $updateData['storage_disk'] = $newStorageDisk;
            $updateData['media_url'] = null;
        }

        // Add progressive card fields for speaking
        if ($section === 'speaking') {
            $updateData['read_time'] = $request->read_time ?? $this->questionService->getDefaultReadTime($request->question_type);
            $updateData['min_response_time'] = $request->min_response_time ?? $this->questionService->getDefaultMinResponse($request->question_type);
            $updateData['max_response_time'] = $request->max_response_time ?? $this->questionService->getDefaultMaxResponse($request->question_type);
            $updateData['auto_progress'] = $request->has('auto_progress') ? (bool)$request->auto_progress : true;
            $updateData['card_theme'] = $request->card_theme ?? 'blue';
            $updateData['speaking_tips'] = $request->speaking_tips;

            // Avatar teacher is managed at test set level (not editable per question)
            // Only pause_before_record can be adjusted if form submits it
            // Note: avatar_teacher_id changes are handled via TestSetController

            // Handle cue card structure
            if ($request->question_type === 'part2_cue_card' && $request->has('form_structure_json')) {
                $updateData['form_structure'] = json_decode($request->form_structure_json, true);
            }
        }
        
        // Handle matching headings data
        if ($request->question_type === 'matching_headings' && $request->has('matching_headings_json')) {
            $matchingHeadingsData = json_decode($request->matching_headings_json, true);
            if ($matchingHeadingsData) {
                // Get existing section specific data
                $sectionSpecificData = $question->section_specific_data ?? [];
                
                // Update with new data
                $sectionSpecificData['headings'] = $matchingHeadingsData['headings'] ?? [];
                $sectionSpecificData['mappings'] = $matchingHeadingsData['mappings'] ?? [];
                
                $updateData['section_specific_data'] = $sectionSpecificData;
                
                // Update marks based on mappings count
                if (isset($matchingHeadingsData['mappings'])) {
                    $updateData['marks'] = $request->marks ?? count($matchingHeadingsData['mappings']);
                }
                
                \Log::info('Updating matching headings data for question #' . $question->id, [
                    'headings_count' => count($matchingHeadingsData['headings'] ?? []),
                    'mappings_count' => count($matchingHeadingsData['mappings'] ?? []),
                    'data' => $matchingHeadingsData
                ]);
            }
        }
        
        // Handle sentence completion data
        if ($request->question_type === 'sentence_completion' && $request->has('sentence_completion_json')) {
            $sentenceCompletionData = json_decode($request->sentence_completion_json, true);
            if ($sentenceCompletionData) {
                // Get existing section specific data
                $sectionSpecificData = $question->section_specific_data ?? [];
                
                // Store the sentence completion data
                $sectionSpecificData['sentence_completion'] = $sentenceCompletionData;
                
                // Store dropdown options in expected format
                if (isset($sentenceCompletionData['options'])) {
                    $dropdownOptions = [];
                    foreach ($sentenceCompletionData['sentences'] as $index => $sentence) {
                        $questionNum = $sentence['questionNumber'];
                        // Create options string from available options
                        $optionsArray = array_column($sentenceCompletionData['options'], 'text');
                        $dropdownOptions[$questionNum] = implode(',', $optionsArray);
                    }
                    $sectionSpecificData['dropdown_options'] = $dropdownOptions;
                    
                    // Store correct answers
                    $dropdownCorrect = [];
                    foreach ($sentenceCompletionData['sentences'] as $index => $sentence) {
                        $questionNum = $sentence['questionNumber'];
                        // Find index of correct answer
                        $correctLetter = $sentence['correctAnswer'];
                        $correctIndex = ord($correctLetter) - ord('A');
                        $dropdownCorrect[$questionNum] = $correctIndex;
                    }
                    $sectionSpecificData['dropdown_correct'] = $dropdownCorrect;
                }
                
                $updateData['section_specific_data'] = $sectionSpecificData;
                
                // Update marks based on sentences count
                if (isset($sentenceCompletionData['sentences'])) {
                    $updateData['marks'] = $request->marks ?? count($sentenceCompletionData['sentences']);
                }
                
                \Log::info('Updating sentence completion data for question #' . $question->id, [
                    'sentences_count' => count($sentenceCompletionData['sentences'] ?? []),
                    'options_count' => count($sentenceCompletionData['options'] ?? [])
                ]);
            }
        }

        // Handle dropdown_selection / matching_grid section_specific_data update
        if (in_array($request->question_type, ['dropdown_selection', 'matching_grid', 'form_completion']) && $request->has('dropdown_options')) {
            $sectionSpecificData = $question->section_specific_data ?? [];
            $dropdownOptions = [];
            $dropdownCorrect = [];
            $requestDropdownOptions = $request->input('dropdown_options', []);
            $requestDropdownCorrect = $request->input('dropdown_correct', []);

            foreach ($requestDropdownOptions as $index => $optionsString) {
                if (!empty($optionsString)) {
                    $dropdownOptions[$index + 1] = $optionsString;
                    if (isset($requestDropdownCorrect[$index])) {
                        $dropdownCorrect[$index + 1] = (int)$requestDropdownCorrect[$index];
                    }
                }
            }

            if (!empty($dropdownOptions)) {
                $sectionSpecificData['dropdown_options'] = $dropdownOptions;
                $sectionSpecificData['dropdown_correct'] = $dropdownCorrect;
                $updateData['section_specific_data'] = $sectionSpecificData;
            }
        }

        $question->update($updateData);

        // Handle blank answers for fill-in-the-blank questions
        if (in_array($request->question_type, ['fill_blanks', 'note_completion', 'summary_completion', 'sentence_completion', 'form_completion'])) {
            // Clear existing blanks
            $question->blanks()->delete();
            
            // Extract blanks from content
            preg_match_all('/\[____(\d+)____\]/', $request->content, $matches);
            $blankNumbers = array_unique($matches[1]);
            
            // Get blank answers from request
            $requestBlankAnswers = $request->input('blank_answers', []);
            
            // Re-index request array to match blank numbers
            $blankAnswersByNumber = [];
            $arrayIndex = 0;
            foreach ($blankNumbers as $blankNum) {
                if (isset($requestBlankAnswers[$arrayIndex])) {
                    $blankAnswersByNumber[$blankNum] = $requestBlankAnswers[$arrayIndex];
                }
                $arrayIndex++;
            }
            
            foreach ($blankAnswersByNumber as $blankNum => $answerText) {
                if (!empty($answerText)) {
                    // Check for alternates (separated by |)
                    $alternates = null;
                    if (strpos($answerText, '|') !== false) {
                        $parts = array_map('trim', explode('|', $answerText));
                        $answerText = $parts[0]; // Primary answer
                        $alternates = array_slice($parts, 1); // Alternate answers
                    }
                    
                    // Create QuestionBlank entry
                    $question->blanks()->create([
                        'blank_number' => $blankNum,
                        'correct_answer' => $answerText,
                        'alternate_answers' => $alternates
                    ]);
                }
            }
            
            // Update section_specific_data
            $cleanBlanks = [];
            foreach ($question->blanks as $blank) {
                $cleanBlanks[$blank->blank_number] = $blank->correct_answer;
            }
            
            $sectionData = $question->section_specific_data ?? [];
            $sectionData['blank_answers'] = $cleanBlanks;
            $question->section_specific_data = $sectionData;
            $question->save();
        }

        // Handle options update for matching headings
        if ($question->question_type === 'matching_headings' && isset($request->options)) {
            // Delete existing options
            $question->options()->delete();
            
            // Create new options
            foreach ($request->options as $index => $option) {
                if (!empty($option['content'])) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'content' => $option['content'],
                        'is_correct' => false, // For matching headings, correctness is in mappings
                    ]);
                }
            }
        }
        
        // Handle options update for multiple choice and other option-based questions
        if (in_array($question->question_type, ['single_choice', 'multiple_choice', 'true_false', 'yes_no', 'matching_information', 'matching_features']) && isset($request->options)) {
            // Delete existing options
            $question->options()->delete();
            
            // Create new options
            foreach ($request->options as $index => $option) {
                if (!empty($option['content'])) {
                    $isCorrect = false;
                    
                    if ($question->question_type === 'multiple_choice') {
                        // For multiple choice, check if this index is in correct_options array
                        $correctOptions = $request->input('correct_options', []);
                        $isCorrect = in_array($index, $correctOptions);
                    } else {
                        // For single choice questions
                        $isCorrect = ($request->input('correct_option') == $index);
                    }
                    
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'content' => $option['content'],
                        'is_correct' => $isCorrect,
                    ]);
                }
            }
            
            // Update marks for multiple choice based on correct options count
            if ($question->question_type === 'multiple_choice' && $request->has('correct_options')) {
                $correctCount = count($request->correct_options);
                $updateData['marks'] = $correctCount;
            }
        }
        
        return redirect()->route('admin.test-sets.show', $question->test_set_id)
            ->with('success', 'Question updated successfully.');
    }

    /**
     * Remove the specified question from storage.
     */
    public function destroy(Question $question): RedirectResponse
    {
        // Delete media if exists
        if ($question->media_path) {
            // Use stored storage_disk, fallback to section-based detection
            $section = $question->testSet->section->name ?? '';
            $disk = $question->storage_disk ?? 'public';

            // Delete from the appropriate disk
            Storage::disk($disk)->delete($question->media_path);
        }

        $testSetId = $question->test_set_id;
        $question->delete();

        return redirect()->route('admin.test-sets.show', $testSetId)
            ->with('success', 'Question deleted successfully.');
    }

    /**
     * Get questions by part (AJAX)
     */
    public function getByPart(TestSet $testSet, $part): JsonResponse
    {
        $questions = Question::where('test_set_id', $testSet->id)
                            ->where('part_number', $part)
                            ->orderBy('order_number')
                            ->with('options')
                            ->get();

        return response()->json($questions);
    }

    /**
     * Get questions for a test set via AJAX
     */
    public function ajaxTestSet($testSetId)
    {
        $questions = Question::with(['testSet', 'testSet.section', 'options'])
                            ->where('test_set_id', $testSetId)
                            ->orderBy('part_number')
                            ->orderBy('order_number')
                            ->get();
        
        $selectedTestSet = TestSet::with('section')->find($testSetId);
        
        if (!$selectedTestSet) {
            return response()->json(['error' => 'Test set not found'], 404);
        }
        
        return view('admin.questions.partials.questions-list', compact('questions', 'selectedTestSet'));
    }

    /**
     * Handle image upload from TinyMCE editor
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240' // 10MB max
        ]);
        
        try {
            if ($request->hasFile('image')) {
                $image = $request->file('image');

                // Generate unique filename
                $filename = 'tinymce_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                // Try R2 if configured, else local public
                $disk = $this->resolveStorageDisk();
                $path = $image->storeAs('questions/images', $filename, $disk);

                // Build URL: R2 CDN if disk=r2, else local /storage/
                if ($disk === 'r2') {
                    $url = rtrim(config('filesystems.disks.r2.url'), '/') . '/' . ltrim($path, '/');
                } else {
                    $url = asset('storage/' . $path);
                }

                \Log::info('TinyMCE image uploaded', [
                    'path' => $path,
                    'disk' => $disk,
                    'url' => $url,
                    'filename' => $filename
                ]);
                
                return response()->json([
                    'success' => true,
                    'url' => $url,
                    'location' => $url // TinyMCE expects 'location' key
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No image file provided'
            ], 400);
            
        } catch (\Exception $e) {
            \Log::error('TinyMCE image upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Image upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resolve storage disk for new uploads.
     * Uses R2 if configured (bucket + URL set), otherwise falls back to local 'public'.
     */
    protected function resolveStorageDisk(): string
    {
        $r2Bucket = config('filesystems.disks.r2.bucket');
        $r2Url = config('filesystems.disks.r2.url');
        $r2Key = config('filesystems.disks.r2.key');

        if (!empty($r2Bucket) && !empty($r2Url) && !empty($r2Key)) {
            return 'r2';
        }

        return 'public';
    }
}
