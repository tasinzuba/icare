<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\TestSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * QuestionService - Handles all question-related business logic
 *
 * Extracted from QuestionController to follow Single Responsibility Principle
 */
class QuestionService
{
    /**
     * Question types that need traditional options (radio/checkbox)
     */
    protected array $optionBasedTypes = [
        'single_choice',
        'multiple_choice',
        'true_false',
        'yes_no',
        'matching_information',
        'matching_features'
    ];

    /**
     * Question types with special handling (no traditional options)
     */
    protected array $specialTypes = [
        'matching',
        'form_completion',
        'plan_map_diagram',
        'matching_headings',
        'dropdown_selection',
        'matching_grid',
        'drag_drop'
    ];

    /**
     * Text input question types
     */
    protected array $textInputTypes = [
        'short_answer',
        'sentence_completion',
        'note_completion',
        'summary_completion',
        'fill_blanks'
    ];

    /**
     * Check if question type needs traditional options
     */
    public function needsOptions(string $questionType): bool
    {
        if (in_array($questionType, $this->specialTypes) || in_array($questionType, $this->textInputTypes)) {
            return false;
        }

        return in_array($questionType, $this->optionBasedTypes);
    }

    /**
     * Create a new question with all related data
     */
    public function create(Request $request, TestSet $testSet): Question
    {
        $section = $testSet->section->name;
        $mediaPath = $this->handleMediaUpload($request, $section);

        // Handle type-specific data
        $typeSpecificData = $this->extractTypeSpecificData($request);
        $sectionSpecificData = $this->extractSectionSpecificData($request, $testSet);

        return DB::transaction(function () use ($request, $testSet, $section, $mediaPath, $typeSpecificData, &$sectionSpecificData) {
            // Process fill-in-the-blank questions
            $this->processFillInBlankData($request, $sectionSpecificData);

            // Build question data
            $questionData = $this->buildQuestionData(
                $request,
                $testSet,
                $section,
                $mediaPath,
                $typeSpecificData,
                $sectionSpecificData
            );

            Log::info('Creating question with data:', $questionData);

            $question = Question::create($questionData);

            // Save blanks if applicable
            $this->saveBlanks($question, $request);

            // Create options if applicable
            $this->createOptions($question, $request);

            return $question;
        });
    }

    /**
     * Update an existing question
     */
    public function update(Question $question, Request $request): Question
    {
        $testSet = $question->testSet;
        $section = $testSet->section->name;

        $updateData = $this->buildUpdateData($request, $section);

        // Handle matching headings data
        if ($request->question_type === 'matching_headings' && $request->has('matching_headings_json')) {
            $this->processMatchingHeadingsUpdate($question, $request, $updateData);
        }

        // Handle sentence completion data
        if ($request->question_type === 'sentence_completion' && $request->has('sentence_completion_json')) {
            $this->processSentenceCompletionUpdate($question, $request, $updateData);
        }

        $question->update($updateData);

        // Update blanks if applicable
        $this->updateBlanks($question, $request);

        // Update options if applicable
        $this->updateOptions($question, $request);

        return $question;
    }

    /**
     * Delete a question and its related data
     */
    public function delete(Question $question): bool
    {
        if ($question->media_path) {
            Storage::disk('public')->delete($question->media_path);
        }

        return $question->delete();
    }

    /**
     * Handle media file upload
     */
    protected function handleMediaUpload(Request $request, string $section): ?string
    {
        $mediaPath = null;

        if ($request->hasFile('media')) {
            $mediaPath = $request->file('media')->store('questions/' . $section, 'public');
        }

        // Handle diagram image for plan_map_diagram
        if ($request->question_type === 'plan_map_diagram' && $request->hasFile('diagram_image')) {
            $mediaPath = $request->file('diagram_image')->store('questions/diagrams', 'public');
        }

        return $mediaPath;
    }

    /**
     * Extract type-specific data from request
     */
    protected function extractTypeSpecificData(Request $request): array
    {
        $data = [];

        // Matching pairs
        if ($request->has('matching_pairs_json')) {
            $matchingPairs = json_decode($request->matching_pairs_json, true);
            if ($matchingPairs) {
                $data['matching_pairs'] = $matchingPairs;
            }
        } elseif ($request->question_type === 'matching' && $request->has('matching_pairs')) {
            $data['matching_pairs'] = $this->processMatchingPairs($request->matching_pairs);
        }

        // Form structure
        if ($request->has('form_structure_json')) {
            $formStructure = json_decode($request->form_structure_json, true);
            if ($formStructure) {
                $data['form_structure'] = $formStructure;
            }
        } elseif ($request->question_type === 'form_completion' && $request->has('form_structure')) {
            $data['form_structure'] = $this->processFormStructure($request->form_structure);
        }

        // Matching headings
        if ($request->question_type === 'matching_headings' && $request->has('matching_headings_json')) {
            $matchingHeadingsData = json_decode($request->matching_headings_json, true);
            if ($matchingHeadingsData) {
                $data['matching_headings'] = $matchingHeadingsData;
            }
        }

        // Diagram hotspots
        if ($request->has('diagram_hotspots_json')) {
            $diagramData = json_decode($request->diagram_hotspots_json, true);
            if ($diagramData) {
                $data['diagram_hotspots'] = $diagramData;
            }
        } elseif ($request->question_type === 'plan_map_diagram' && $request->has('diagram_hotspots')) {
            $data['diagram_hotspots'] = $this->processDiagramHotspots($request->diagram_hotspots);
        }

        return $data;
    }

    /**
     * Extract section-specific data from request
     */
    protected function extractSectionSpecificData(Request $request, TestSet $testSet): array
    {
        $data = [];

        // Drag & Drop handling
        if ($request->question_type === 'drag_drop') {
            $data = $this->processDragDropData($request);
        }

        // Matching headings
        if ($request->question_type === 'matching_headings' && $request->has('matching_headings_json')) {
            $matchingHeadingsData = json_decode($request->matching_headings_json, true);
            if ($matchingHeadingsData) {
                $data['headings'] = $matchingHeadingsData['headings'] ?? [];
                $data['mappings'] = $matchingHeadingsData['mappings'] ?? [];
            }
        }

        // Sentence completion
        if ($request->question_type === 'sentence_completion' && $request->has('sentence_completion_json')) {
            $sentenceCompletionData = json_decode($request->sentence_completion_json, true);
            if ($sentenceCompletionData) {
                $data['sentence_completion'] = $sentenceCompletionData;
                $this->processSentenceCompletionOptions($sentenceCompletionData, $data);
            }
        }

        // Diagram data
        if ($request->has('diagram_hotspots_json')) {
            $diagramData = json_decode($request->diagram_hotspots_json, true);
            if ($diagramData) {
                $data['diagram_type'] = 'map_plan_diagram';
                $data['answer_type'] = 'dropdown';
                $data['dropdown_options'] = $diagramData['dropdown_options'] ?? [];
                $data['start_number'] = $diagramData['start_number'] ?? 1;
                $data['correct_answers'] = $diagramData['correct_answers'] ?? [];
            }
        }

        return $data;
    }

    /**
     * Process fill-in-the-blank data
     */
    protected function processFillInBlankData(Request $request, array &$sectionSpecificData): void
    {
        // Handle fill-in-the-blank answers
        if (in_array($request->question_type, ['fill_blanks', 'note_completion', 'summary_completion'])) {
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

        // Handle dropdown selection / matching grid
        if (in_array($request->question_type, ['dropdown_selection', 'matching_grid', 'form_completion'])) {
            if ($request->has('dropdown_options')) {
                $this->processDropdownOptions($request, $sectionSpecificData);
            }
        }
    }

    /**
     * Build question data array
     */
    protected function buildQuestionData(
        Request $request,
        TestSet $testSet,
        string $section,
        ?string $mediaPath,
        array $typeSpecificData,
        array $sectionSpecificData
    ): array {
        // Determine if question should use part audio
        $usePartAudio = $this->shouldUsePartAudio($request, $testSet, $section, $mediaPath);

        // Generate content if needed
        $content = $this->generateContent($request, $typeSpecificData);

        // Calculate marks
        $marks = $this->calculateMarks($request, $typeSpecificData, $sectionSpecificData);

        $questionData = [
            'test_set_id' => $request->test_set_id,
            'question_type' => $request->question_type,
            'content' => $content,
            'order_number' => $request->order_number,
            'part_number' => $request->part_number ?? 1,
            'marks' => $marks,
            'instructions' => $request->instructions,
            'media_path' => $mediaPath,
            'use_part_audio' => $usePartAudio,
            'audio_transcript' => $request->audio_transcript ?? null,
            'word_limit' => $request->word_limit ?? null,
            'time_limit' => $request->time_limit ?? null,
        ];

        // Add speaking-specific fields
        if ($section === 'speaking') {
            $questionData = array_merge($questionData, $this->getSpeakingFields($request));
        }

        // Add type-specific fields
        if (isset($typeSpecificData['matching_pairs'])) {
            $questionData['matching_pairs'] = $typeSpecificData['matching_pairs'];
        }
        if (isset($typeSpecificData['form_structure'])) {
            $questionData['form_structure'] = $typeSpecificData['form_structure'];
        }
        if (isset($typeSpecificData['diagram_hotspots'])) {
            $questionData['diagram_hotspots'] = $typeSpecificData['diagram_hotspots'];
            if ($request->question_type === 'plan_map_diagram') {
                $questionData['blank_count'] = count($typeSpecificData['diagram_hotspots']['dropdown_options'] ?? []);
            }
        }

        // Merge section specific data
        $allSectionData = array_merge($sectionSpecificData, $typeSpecificData);
        if (!empty($allSectionData)) {
            $questionData['section_specific_data'] = $allSectionData;
        }

        return $questionData;
    }

    /**
     * Build update data array
     */
    protected function buildUpdateData(Request $request, string $section): array
    {
        $updateData = [
            'question_type' => $request->question_type,
            'content' => $request->content,
            'order_number' => $request->order_number,
            'part_number' => $request->part_number ?? 1,
            'marks' => $request->marks ?? 1,
            'instructions' => $request->instructions,
            'word_limit' => $request->word_limit ?? null,
            'time_limit' => $request->time_limit ?? null,
        ];

        if ($section === 'speaking') {
            $updateData = array_merge($updateData, $this->getSpeakingFields($request));
        }

        return $updateData;
    }

    /**
     * Get speaking-specific fields
     */
    protected function getSpeakingFields(Request $request): array
    {
        $fields = [
            'read_time' => $request->read_time ?? $this->getDefaultReadTime($request->question_type),
            'min_response_time' => $request->min_response_time ?? $this->getDefaultMinResponse($request->question_type),
            'max_response_time' => $request->max_response_time ?? $this->getDefaultMaxResponse($request->question_type),
            'auto_progress' => $request->has('auto_progress') ? (bool)$request->auto_progress : true,
            'card_theme' => $request->card_theme ?? 'blue',
            'speaking_tips' => $request->speaking_tips,
        ];

        // Handle cue card points for Part 2
        if ($request->question_type === 'part2_cue_card') {
            if ($request->has('form_structure_json')) {
                $fields['form_structure'] = json_decode($request->form_structure_json, true);
            } elseif ($request->has('cue_card_points_text')) {
                $points = array_filter(array_map('trim', explode("\n", $request->cue_card_points_text)));
                if (!empty($points)) {
                    $fields['form_structure'] = [
                        'fields' => array_map(fn($point) => ['label' => $point], $points)
                    ];
                }
            }
        }

        return $fields;
    }

    /**
     * Check if question should use part audio
     */
    protected function shouldUsePartAudio(Request $request, TestSet $testSet, string $section, ?string $mediaPath): bool
    {
        if ($section !== 'listening') {
            return false;
        }

        $partAudio = $testSet->getPartAudio($request->part_number ?? 1);

        return $partAudio && !$mediaPath && $request->input('use_custom_audio') != '1';
    }

    /**
     * Generate content for questions that need auto-generated content
     */
    protected function generateContent(Request $request, array $typeSpecificData): string
    {
        $content = $request->content;

        // Generate content for diagram questions
        if ($request->question_type === 'plan_map_diagram' && empty($content)) {
            $diagramData = $typeSpecificData['diagram_hotspots'] ?? [];
            $optionCount = count($diagramData['dropdown_options'] ?? []) ?: 4;
            $startNum = $diagramData['start_number'] ?? 1;
            $endNum = $startNum + $optionCount - 1;

            $content = "Label the diagram below. Write the correct letter, A-" .
                      chr(64 + $optionCount) .
                      ", next to questions $startNum-$endNum.";
        }

        // Generate content for matching headings
        if ($request->question_type === 'matching_headings' && empty($content)) {
            $startNum = $request->order_number ?? 1;
            $count = isset($typeSpecificData['matching_headings']['mappings'])
                ? count($typeSpecificData['matching_headings']['mappings'])
                : 5;
            $endNum = $startNum + $count - 1;
            $content = "Questions {$startNum}-{$endNum}\n\nChoose the correct heading for each paragraph from the list of headings below.";
        }

        return $content ?? '';
    }

    /**
     * Calculate marks for question
     */
    protected function calculateMarks(Request $request, array $typeSpecificData, array $sectionSpecificData): int
    {
        $marks = $request->marks ?? 1;

        // Multiple choice marks = number of correct options
        if ($request->question_type === 'multiple_choice' && $request->has('correct_options')) {
            $marks = count($request->correct_options);
        }

        // Drag & drop marks = number of drop zones
        if ($request->question_type === 'drag_drop' && isset($sectionSpecificData['drop_zones'])) {
            $marks = $request->marks ?? count($sectionSpecificData['drop_zones']);
        }

        // Matching headings marks = number of mappings
        if ($request->question_type === 'matching_headings' && isset($typeSpecificData['matching_headings']['mappings'])) {
            $marks = $request->marks ?? count($typeSpecificData['matching_headings']['mappings']);
        }

        return $marks;
    }

    /**
     * Save blanks for fill-in-the-blank questions
     */
    protected function saveBlanks(Question $question, Request $request): void
    {
        if (!in_array($request->question_type, ['sentence_completion', 'note_completion', 'summary_completion', 'form_completion', 'fill_blanks'])) {
            return;
        }

        preg_match_all('/\[____(\d+)____\]/', $question->content, $matches);
        $blankNumbers = array_unique($matches[1]);

        $requestBlankAnswers = $request->input('blank_answers', []);

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
                $alternates = null;
                if (strpos($answerText, '|') !== false) {
                    $parts = array_map('trim', explode('|', $answerText));
                    $answerText = $parts[0];
                    $alternates = array_slice($parts, 1);
                }

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

    /**
     * Update blanks for fill-in-the-blank questions
     */
    protected function updateBlanks(Question $question, Request $request): void
    {
        if (!in_array($request->question_type, ['fill_blanks', 'note_completion', 'summary_completion'])) {
            return;
        }

        $question->blanks()->delete();

        preg_match_all('/\[____(\d+)____\]/', $request->content, $matches);
        $blankNumbers = array_unique($matches[1]);

        $requestBlankAnswers = $request->input('blank_answers', []);

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
                $alternates = null;
                if (strpos($answerText, '|') !== false) {
                    $parts = array_map('trim', explode('|', $answerText));
                    $answerText = $parts[0];
                    $alternates = array_slice($parts, 1);
                }

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

    /**
     * Create options for question
     */
    protected function createOptions(Question $question, Request $request): void
    {
        // Standard options
        if ($this->needsOptions($request->question_type) && isset($request->options)) {
            foreach ($request->options as $index => $option) {
                if (!empty($option['content'])) {
                    $isCorrect = $this->isOptionCorrect($request, $index);

                    QuestionOption::create([
                        'question_id' => $question->id,
                        'content' => $option['content'],
                        'is_correct' => $isCorrect,
                    ]);
                }
            }
        }

        // Matching headings options
        if ($request->question_type === 'matching_headings' && isset($request->options)) {
            foreach ($request->options as $index => $option) {
                if (!empty($option['content'])) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'content' => $option['content'],
                        'is_correct' => false,
                    ]);
                }
            }
        }
    }

    /**
     * Update options for question
     */
    protected function updateOptions(Question $question, Request $request): void
    {
        // Matching headings options
        if ($question->question_type === 'matching_headings' && isset($request->options)) {
            $question->options()->delete();

            foreach ($request->options as $index => $option) {
                if (!empty($option['content'])) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'content' => $option['content'],
                        'is_correct' => false,
                    ]);
                }
            }
        }

        // Option-based questions
        $optionQuestionTypes = ['single_choice', 'multiple_choice', 'true_false', 'yes_no', 'matching_information', 'matching_features'];
        if (in_array($question->question_type, $optionQuestionTypes) && isset($request->options)) {
            $question->options()->delete();

            foreach ($request->options as $index => $option) {
                if (!empty($option['content'])) {
                    $isCorrect = $this->isOptionCorrect($request, $index, $question->question_type);

                    QuestionOption::create([
                        'question_id' => $question->id,
                        'content' => $option['content'],
                        'is_correct' => $isCorrect,
                    ]);
                }
            }
        }
    }

    /**
     * Determine if an option is correct
     */
    protected function isOptionCorrect(Request $request, int $index, ?string $questionType = null): bool
    {
        $questionType = $questionType ?? $request->question_type;

        if ($questionType === 'matching_headings') {
            return false;
        }

        if ($questionType === 'multiple_choice') {
            $correctOptions = $request->input('correct_options', []);
            return in_array($index, $correctOptions);
        }

        if ($questionType === 'single_choice') {
            $correctOption = $request->input('correct_option');
            return $correctOption !== null && $correctOption == $index;
        }

        return $request->correct_option == $index;
    }

    /**
     * Process matching headings update
     */
    protected function processMatchingHeadingsUpdate(Question $question, Request $request, array &$updateData): void
    {
        $matchingHeadingsData = json_decode($request->matching_headings_json, true);
        if (!$matchingHeadingsData) {
            return;
        }

        $sectionSpecificData = $question->section_specific_data ?? [];
        $sectionSpecificData['headings'] = $matchingHeadingsData['headings'] ?? [];
        $sectionSpecificData['mappings'] = $matchingHeadingsData['mappings'] ?? [];

        $updateData['section_specific_data'] = $sectionSpecificData;

        if (isset($matchingHeadingsData['mappings'])) {
            $updateData['marks'] = $request->marks ?? count($matchingHeadingsData['mappings']);
        }
    }

    /**
     * Process sentence completion update
     */
    protected function processSentenceCompletionUpdate(Question $question, Request $request, array &$updateData): void
    {
        $sentenceCompletionData = json_decode($request->sentence_completion_json, true);
        if (!$sentenceCompletionData) {
            return;
        }

        $sectionSpecificData = $question->section_specific_data ?? [];
        $sectionSpecificData['sentence_completion'] = $sentenceCompletionData;

        if (isset($sentenceCompletionData['options'])) {
            $this->processSentenceCompletionOptions($sentenceCompletionData, $sectionSpecificData);
        }

        $updateData['section_specific_data'] = $sectionSpecificData;

        if (isset($sentenceCompletionData['sentences'])) {
            $updateData['marks'] = $request->marks ?? count($sentenceCompletionData['sentences']);
        }
    }

    /**
     * Process sentence completion options
     */
    protected function processSentenceCompletionOptions(array $sentenceCompletionData, array &$data): void
    {
        if (!isset($sentenceCompletionData['options'])) {
            return;
        }

        $dropdownOptions = [];
        $dropdownCorrect = [];

        foreach ($sentenceCompletionData['sentences'] as $sentence) {
            $questionNum = $sentence['questionNumber'];
            $optionsArray = array_column($sentenceCompletionData['options'], 'text');
            $dropdownOptions[$questionNum] = implode(',', $optionsArray);

            $correctLetter = $sentence['correctAnswer'];
            $correctIndex = ord($correctLetter) - ord('A');
            $dropdownCorrect[$questionNum] = $correctIndex;
        }

        $data['dropdown_options'] = $dropdownOptions;
        $data['dropdown_correct'] = $dropdownCorrect;
    }

    /**
     * Process drag & drop data
     */
    protected function processDragDropData(Request $request): array
    {
        $data = [];
        $dropZones = [];
        $options = [];
        $allowReuse = $request->has('drag_drop_allow_reuse');

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

        if ($request->has('drag_drop_options')) {
            foreach ($request->drag_drop_options as $option) {
                if (!empty($option)) {
                    $options[] = trim($option);
                }
            }
        }

        $data['drop_zones'] = $dropZones;
        $data['draggable_options'] = $options;
        $data['allow_reuse'] = $allowReuse;

        return $data;
    }

    /**
     * Process dropdown options
     */
    protected function processDropdownOptions(Request $request, array &$data): void
    {
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
            $data['dropdown_options'] = $dropdownOptions;
            $data['dropdown_correct'] = $dropdownCorrect;
        }
    }

    /**
     * Process matching pairs from form input
     */
    protected function processMatchingPairs(array $pairs): array
    {
        $result = [];
        foreach ($pairs as $pair) {
            if (!empty($pair['left']) && !empty($pair['right'])) {
                $result[] = [
                    'left' => trim($pair['left']),
                    'right' => trim($pair['right'])
                ];
            }
        }
        return $result;
    }

    /**
     * Process form structure from form input
     */
    protected function processFormStructure(array $formStructure): array
    {
        $result = [
            'title' => $formStructure['title'] ?? 'Form',
            'fields' => []
        ];

        if (isset($formStructure['fields'])) {
            foreach ($formStructure['fields'] as $index => $field) {
                if (!empty($field['label']) && !empty($field['answer'])) {
                    $result['fields'][] = [
                        'label' => trim($field['label']),
                        'blank_id' => $index + 1,
                        'answer' => trim($field['answer'])
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Process diagram hotspots from form input
     */
    protected function processDiagramHotspots(array $hotspots): array
    {
        $result = [];
        foreach ($hotspots as $index => $hotspot) {
            if (!empty($hotspot['answer'])) {
                $result[] = [
                    'id' => $index + 1,
                    'x' => (int)$hotspot['x'],
                    'y' => (int)$hotspot['y'],
                    'label' => $hotspot['label'],
                    'answer' => trim($hotspot['answer'])
                ];
            }
        }
        return $result;
    }

    /**
     * Calculate next question number for a test set
     */
    public function calculateNextQuestionNumber(TestSet $testSet): int
    {
        $questions = $testSet->questions()
            ->where('question_type', '!=', 'passage')
            ->orderBy('part_number')
            ->orderBy('order_number')
            ->get();

        if ($questions->isEmpty()) {
            return 1;
        }

        $totalCount = 0;

        foreach ($questions as $question) {
            if ($question->question_type === 'matching_headings' && $question->isMasterMatchingHeading()) {
                $totalCount += $question->getActualQuestionCount();
            } elseif ($question->question_type === 'multiple_choice') {
                $correctCount = $question->options->where('is_correct', true)->count();
                $totalCount += $correctCount > 1 ? $correctCount : 1;
            } elseif ($blankCount = $question->countBlanks()) {
                $totalCount += $blankCount;
            } else {
                $totalCount += 1;
            }
        }

        return $totalCount + 1;
    }

    /**
     * Get default read time based on question type
     */
    public function getDefaultReadTime(string $questionType): int
    {
        return match($questionType) {
            'part1_personal' => 5,
            'part2_cue_card' => 60,
            'part3_discussion' => 8,
            default => 5
        };
    }

    /**
     * Get default minimum response time based on question type
     */
    public function getDefaultMinResponse(string $questionType): int
    {
        return match($questionType) {
            'part1_personal' => 15,
            'part2_cue_card' => 60,
            'part3_discussion' => 30,
            default => 15
        };
    }

    /**
     * Get default maximum response time based on question type
     */
    public function getDefaultMaxResponse(string $questionType): int
    {
        return match($questionType) {
            'part1_personal' => 45,
            'part2_cue_card' => 120,
            'part3_discussion' => 90,
            default => 45
        };
    }
}
