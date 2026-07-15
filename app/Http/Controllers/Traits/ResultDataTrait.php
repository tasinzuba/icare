<?php

namespace App\Http\Controllers\Traits;

use App\Services\AnswerValidator;

/**
 * Shared result data preparation logic used by both
 * ResultController (section tests) and FullTestController (full tests).
 */
trait ResultDataTrait
{
    /**
     * Must be implemented by the using controller to return its AnswerValidator instance.
     */
    abstract protected function getAnswerValidator(): AnswerValidator;

    /**
     * Build questions analysis array from raw questions + attempt.
     * Expands sub-questions (master matching, sentence completion, drag-drop, etc.)
     */
    protected function buildQuestionsAnalysis($questions, $attempt): array
    {
        $questionsAnalysis = [];
        $currentNumber = 1;
        $masterQuestionIds = [];

        foreach ($questions as $question) {
            if ($question->isMasterMatchingHeading()) {
                if (!in_array($question->id, $masterQuestionIds)) {
                    $masterQuestionIds[] = $question->id;
                    $mappings = $question->section_specific_data['mappings'] ?? [];
                    $headings = $question->section_specific_data['headings'] ?? [];
                    $masterAnswers = $attempt->answers->filter(fn($a) => $a->question_id == $question->id);

                    foreach ($mappings as $mapping) {
                        $subQuestionNum = $mapping['question'] ?? $mapping['number'] ?? $currentNumber;
                        $paragraphLabel = $mapping['paragraph'] ?? chr(65 + array_search($mapping, $mappings));
                        $correctLetter = $mapping['correct'] ?? null;
                        $correctHeadingText = null;
                        if ($correctLetter) {
                            foreach ($headings as $heading) {
                                if ($heading['id'] === $correctLetter) { $correctHeadingText = $heading['text'] ?? null; break; }
                            }
                        }
                        $specificAnswer = $masterAnswers->first(function($answer) use ($subQuestionNum) {
                            if ($answer->answer) {
                                $decoded = json_decode($answer->answer, true);
                                return isset($decoded['sub_question']) && $decoded['sub_question'] == $subQuestionNum;
                            }
                            return false;
                        });
                        $questionsAnalysis[] = [
                            'id' => $question->id . '_' . $subQuestionNum, 'question_id' => $question->id,
                            'number' => $currentNumber, 'content' => "Choose the correct heading for Paragraph {$paragraphLabel}",
                            'raw_answer' => $specificAnswer, 'type' => 'master_sub', 'sub_question' => $subQuestionNum,
                            'correct_letter' => $correctLetter, 'correct_heading_text' => $correctHeadingText,
                            'all_headings' => $headings, 'part_number' => $question->part_number,
                            'explanation' => $question->processed_explanation ?? $question->explanation, 'raw_question' => $question
                        ];
                        $currentNumber++;
                    }
                }
            } elseif ($question->question_type === 'sentence_completion' && isset($question->section_specific_data['sentence_completion'])) {
                $sentences = $question->section_specific_data['sentence_completion']['sentences'] ?? [];
                foreach ($sentences as $si => $sentence) {
                    $qn = $sentence['questionNumber'] ?? ($si + 1);
                    $sa = $attempt->answers->first(function($ans) use ($question, $qn) {
                        if ($ans->question_id != $question->id) return false;
                        $d = json_decode($ans->answer, true);
                        return is_array($d) && isset($d['sub_question']) && (int)$d['sub_question'] === $qn;
                    });
                    $questionsAnalysis[] = [
                        'id' => $question->id . '_' . $qn, 'question_id' => $question->id,
                        'number' => $currentNumber, 'content' => $sentence['text'] ?? "Sentence " . ($si + 1),
                        'raw_answer' => $sa, 'type' => 'sentence_completion', 'sentence_index' => $si,
                        'question_number' => $qn, 'correct_answer' => $sentence['correctAnswer'] ?? $sentence['correct_answer'] ?? null,
                        'part_number' => $question->part_number,
                        'explanation' => $question->processed_explanation ?? $question->explanation, 'raw_question' => $question
                    ];
                    $currentNumber++;
                }
            } elseif ($question->question_type === 'drag_drop') {
                $dropZones = ($question->section_specific_data ?? [])['drop_zones'] ?? [];
                $answer = $attempt->answers->where('question_id', $question->id)->first();
                foreach ($dropZones as $zi => $zone) {
                    $questionsAnalysis[] = [
                        'id' => $question->id . '_' . $zi, 'question_id' => $question->id,
                        'number' => $currentNumber, 'content' => $zone['text'] ?? "Drop Zone " . ($zi + 1),
                        'raw_answer' => $answer, 'type' => 'drag_drop', 'zone_index' => $zi,
                        'correct_answer' => $zone['correct_answer'] ?? $zone['answer'] ?? null,
                        'part_number' => $question->part_number,
                        'explanation' => $question->processed_explanation ?? $question->explanation, 'raw_question' => $question
                    ];
                    $currentNumber++;
                }
            } elseif ($question->question_type === 'fill_blanks') {
                $answer = $attempt->answers->where('question_id', $question->id)->first();
                preg_match_all('/\[____(\d+)____\]/', $question->content, $matches, PREG_SET_ORDER);
                if (count($matches) > 0) {
                    foreach ($matches as $index => $match) {
                        $blankNum = $match[1] ?? ($index + 1);
                        $cleanContent = strip_tags(preg_replace('/\[____\d+____\]/', '___', $question->content));
                        $questionsAnalysis[] = [
                            'id' => $question->id . '_' . $blankNum, 'question_id' => $question->id,
                            'number' => $currentNumber, 'content' => $cleanContent,
                            'raw_answer' => $answer, 'type' => 'fill_blank', 'blank_number' => $blankNum,
                            'part_number' => $question->part_number,
                            'explanation' => $question->processed_explanation ?? $question->explanation, 'raw_question' => $question
                        ];
                        $currentNumber++;
                    }
                } else {
                    $questionsAnalysis[] = [
                        'id' => $question->id . '_1', 'question_id' => $question->id,
                        'number' => $currentNumber, 'content' => strip_tags($question->content),
                        'raw_answer' => $answer, 'type' => 'fill_blank', 'blank_number' => 1,
                        'part_number' => $question->part_number,
                        'explanation' => $question->processed_explanation ?? $question->explanation, 'raw_question' => $question
                    ];
                    $currentNumber++;
                }
            } elseif (in_array($question->question_type, ['dropdown_selection', 'matching_grid'])) {
                $answer = $attempt->answers->where('question_id', $question->id)->first();
                preg_match_all('/\[DROPDOWN_(\d+)\]/', $question->content, $matches, PREG_SET_ORDER);
                if (count($matches) > 0) {
                    foreach ($matches as $index => $match) {
                        $ddNum = $match[1] ?? ($index + 1);
                        $cleanContent = strip_tags(preg_replace('/\[DROPDOWN_\d+\]/', '___', $question->content));
                        $questionsAnalysis[] = [
                            'id' => $question->id . '_' . $ddNum, 'question_id' => $question->id,
                            'number' => $currentNumber, 'content' => $cleanContent,
                            'raw_answer' => $answer, 'type' => 'dropdown', 'dropdown_index' => $ddNum,
                            'part_number' => $question->part_number,
                            'explanation' => $question->processed_explanation ?? $question->explanation, 'raw_question' => $question
                        ];
                        $currentNumber++;
                    }
                } else {
                    $questionsAnalysis[] = [
                        'id' => $question->id . '_1', 'question_id' => $question->id,
                        'number' => $currentNumber, 'content' => strip_tags($question->content),
                        'raw_answer' => $answer, 'type' => 'dropdown', 'dropdown_index' => 1,
                        'part_number' => $question->part_number,
                        'explanation' => $question->processed_explanation ?? $question->explanation, 'raw_question' => $question
                    ];
                    $currentNumber++;
                }
            } elseif ($question->question_type === 'multiple_choice') {
                $correctCount = $question->options->where('is_correct', true)->count();
                $correctOptions = $question->options->where('is_correct', true)->values();
                $questionAnswers = $attempt->answers->where('question_id', $question->id);
                $answer = $questionAnswers->first();
                $selectedOptionIds = [];
                foreach ($questionAnswers as $ans) {
                    if ($ans->selected_option_id) { $selectedOptionIds[] = $ans->selected_option_id; }
                    elseif ($ans->answer) { $d = @json_decode($ans->answer, true); if (is_array($d)) { $selectedOptionIds = array_merge($selectedOptionIds, $d); } }
                }
                $selectedOptionIds = array_values(array_unique($selectedOptionIds));

                if ($correctCount > 1) {
                    for ($i = 0; $i < $correctCount; $i++) {
                        $selOptId = $selectedOptionIds[$i] ?? null;
                        $selOpt = $selOptId ? $question->options->firstWhere('id', $selOptId) : null;
                        $questionsAnalysis[] = [
                            'id' => $question->id . '_' . $i, 'question_id' => $question->id,
                            'number' => $currentNumber, 'content' => strip_tags($question->content),
                            'raw_answer' => $answer, 'type' => 'multiple_choice', 'choice_index' => $i,
                            'all_selected_ids' => $selectedOptionIds, 'selected_option' => $selOpt,
                            'correct_option' => $correctOptions[$i] ?? null, 'part_number' => $question->part_number,
                            'explanation' => $question->processed_explanation ?? $question->explanation, 'raw_question' => $question
                        ];
                        $currentNumber++;
                    }
                } else {
                    $selOpt = !empty($selectedOptionIds) ? $question->options->firstWhere('id', $selectedOptionIds[0]) : null;
                    $questionsAnalysis[] = [
                        'id' => $question->id . '_1', 'question_id' => $question->id,
                        'number' => $currentNumber, 'content' => strip_tags($question->content),
                        'raw_answer' => $answer, 'type' => 'multiple_choice', 'choice_index' => 0,
                        'all_selected_ids' => $selectedOptionIds, 'selected_option' => $selOpt,
                        'correct_option' => $correctOptions[0] ?? null, 'part_number' => $question->part_number,
                        'explanation' => $question->processed_explanation ?? $question->explanation, 'raw_question' => $question
                    ];
                    $currentNumber++;
                }
            } else {
                $answer = $attempt->answers->where('question_id', $question->id)->first();
                $questionsAnalysis[] = [
                    'id' => $question->id, 'question_id' => $question->id,
                    'number' => $currentNumber, 'content' => strip_tags($question->content),
                    'raw_answer' => $answer, 'type' => 'regular', 'part_number' => $question->part_number,
                    'explanation' => $question->processed_explanation ?? $question->explanation, 'raw_question' => $question
                ];
                $currentNumber++;
            }
        }

        return $questionsAnalysis;
    }

    /**
     * Format processed question data for Vue to match view output
     */
    protected function formatQuestionsForVue(array $questionsAnalysis): array
    {
        $formatted = [];

        foreach ($questionsAnalysis as $item) {
            $question = $item['raw_question'];
            $answer = $item['raw_answer'];
            $isAnswered = !empty($answer);
            $isCorrect = false;
            $displayAnswer = 'No answer';

            if ($item['type'] === 'drag_drop' && $isAnswered && $answer) {
                $answerData = @json_decode($answer->answer, true);
                if (is_array($answerData)) {
                    $zoneIndex = $item['zone_index'];
                    $studentAnswer = $answerData['zone_' . $zoneIndex] ?? $answerData['zone_' . ($zoneIndex + 1)] ?? null;
                    if ($studentAnswer !== null) {
                        $displayAnswer = $studentAnswer;
                        $isCorrect = ($item['correct_answer'] && $studentAnswer === $item['correct_answer']);
                    }
                }
            } elseif ($item['type'] === 'fill_blank' && $isAnswered && $answer) {
                $answerData = @json_decode($answer->answer, true);
                if (is_array($answerData)) {
                    $studentAnswer = $answerData['blank_' . $item['blank_number']] ?? null;
                    if ($studentAnswer !== null) {
                        $displayAnswer = $studentAnswer;
                        $isCorrect = $question->checkBlankAnswer($item['blank_number'], $studentAnswer);
                    }
                }
            } elseif ($item['type'] === 'dropdown' && $isAnswered && $answer) {
                $answerData = @json_decode($answer->answer, true);
                if (is_array($answerData)) {
                    $studentDropdownAnswer = $answerData['dropdown_' . $item['dropdown_index']] ?? null;
                    if ($studentDropdownAnswer !== null) {
                        $displayAnswer = $studentDropdownAnswer;
                        if ($question->section_specific_data && isset($question->section_specific_data['dropdown_correct'][$item['dropdown_index']])) {
                            $correctIndex = $question->section_specific_data['dropdown_correct'][$item['dropdown_index']];
                            $dropdownOptions = $question->section_specific_data['dropdown_options'][$item['dropdown_index']] ?? '';
                            if ($dropdownOptions) {
                                $options = array_map('trim', explode(',', $dropdownOptions));
                                $correctOption = $options[$correctIndex] ?? '';
                                $isCorrect = (strtolower(trim($studentDropdownAnswer)) === strtolower(trim($correctOption)));
                            }
                        }
                    }
                }
            } elseif ($item['type'] === 'multiple_choice') {
                $selectedOption = $item['selected_option'] ?? null;
                if ($selectedOption) {
                    $displayAnswer = $selectedOption->content;
                    $isCorrect = $selectedOption->is_correct;
                    $isAnswered = true;
                } elseif ($answer) {
                    $choiceIdx = $item['choice_index'] ?? 0;
                    $allSelectedIds = $item['all_selected_ids'] ?? [];
                    if (!empty($allSelectedIds) && isset($allSelectedIds[$choiceIdx])) {
                        $opt = $question->options->firstWhere('id', $allSelectedIds[$choiceIdx]);
                        if ($opt) { $displayAnswer = $opt->content; $isCorrect = $opt->is_correct; $isAnswered = true; }
                    } elseif ($answer->selected_option_id) {
                        $opt = $question->options->firstWhere('id', $answer->selected_option_id);
                        if ($opt) { $displayAnswer = $opt->content; $isCorrect = $opt->is_correct; $isAnswered = true; }
                    } elseif ($answer->answer) {
                        $decoded = @json_decode($answer->answer, true);
                        if (is_array($decoded) && isset($decoded[$choiceIdx])) {
                            $opt = $question->options->firstWhere('id', $decoded[$choiceIdx]);
                            if ($opt) { $displayAnswer = $opt->content; $isCorrect = $opt->is_correct; $isAnswered = true; }
                        }
                    }
                    if (!$isAnswered) { $displayAnswer = 'Not attempted'; }
                } else {
                    $displayAnswer = 'Not attempted'; $isAnswered = false;
                }
            } elseif ($isAnswered && $answer) {
                if ($item['type'] === 'master_sub') {
                    $decoded = json_decode($answer->answer, true);
                    $selectedLetter = $decoded['selected_letter'] ?? null;
                    $selectedHeadingText = null;
                    if ($selectedLetter && isset($item['all_headings'])) {
                        foreach ($item['all_headings'] as $heading) {
                            if ($heading['id'] === $selectedLetter) { $selectedHeadingText = $heading['text'] ?? null; break; }
                        }
                    }
                    $displayAnswer = $selectedHeadingText ?: ($selectedLetter ? "Option {$selectedLetter}" : 'No answer');
                    $isCorrect = $selectedLetter && $selectedLetter === $item['correct_letter'];
                } elseif ($answer->selectedOption) {
                    $displayAnswer = $answer->selectedOption->content;
                    $isCorrect = $answer->selectedOption->is_correct;
                } elseif ($item['type'] === 'sentence_completion') {
                    $answerData = json_decode($answer->answer, true);
                    if (is_array($answerData) && isset($answerData['sub_question'], $answerData['selected_answer'])) {
                        if ((int)$answerData['sub_question'] == ($item['question_number'] ?? $item['number'])) {
                            $displayAnswer = $answerData['selected_answer'] ? "Option {$answerData['selected_answer']}" : 'No answer';
                            $isCorrect = $answerData['selected_answer'] && $answerData['selected_answer'] === $item['correct_answer'];
                        }
                    }
                } elseif ($answer->answer) {
                    $answerData = @json_decode($answer->answer, true);
                    if (is_array($answerData)) {
                        $displayParts = [];
                        foreach ($answerData as $value) { if (!empty($value)) $displayParts[] = $value; }
                        $displayAnswer = implode(', ', $displayParts);
                        $allCorrect = true;
                        if ($question->section_specific_data && isset($question->section_specific_data['blank_answers'])) {
                            foreach ($question->section_specific_data['blank_answers'] as $num => $ca) {
                                if (!$question->checkBlankAnswer($num, $answerData['blank_' . $num] ?? '')) { $allCorrect = false; break; }
                            }
                        }
                        $isCorrect = $allCorrect;
                    } else {
                        $displayAnswer = $answer->answer;
                    }
                }
            }

            // Ensure consistency: if no actual answer was resolved, mark as not answered
            // This handles composite questions (drag_drop, fill_blank, etc.) where
            // an answer record exists but specific sub-answers are empty
            if (in_array($displayAnswer, ['No answer', 'Not attempted'])) {
                $isAnswered = false;
            }

            // Correct answer string
            $correctAnswerForExplain = '';
            if ($item['type'] === 'drag_drop') { $correctAnswerForExplain = $item['correct_answer'] ?? ''; }
            elseif ($item['type'] === 'fill_blank') { $ba = $question->getBlankAnswersArray(); $correctAnswerForExplain = $ba[$item['blank_number']] ?? ''; }
            elseif ($item['type'] === 'dropdown') {
                $ci = $question->section_specific_data['dropdown_correct'][$item['dropdown_index']] ?? null;
                $do = $question->section_specific_data['dropdown_options'][$item['dropdown_index']] ?? '';
                if ($do && $ci !== null) { $opts = array_map('trim', explode(',', $do)); $correctAnswerForExplain = $opts[$ci] ?? ''; }
            } elseif ($item['type'] === 'master_sub') { $correctAnswerForExplain = $item['correct_heading_text'] ?? 'Option ' . $item['correct_letter']; }
            elseif ($item['type'] === 'sentence_completion') { $correctAnswerForExplain = 'Option ' . $item['correct_answer']; }
            elseif ($item['type'] === 'multiple_choice') { $correctAnswerForExplain = ($item['correct_option']->content ?? ''); }
            else { $correctAnswerForExplain = $question->getCorrectAnswerForDisplay(); }

            $formatted[] = [
                'id' => $item['id'], 'question_id' => $item['question_id'],
                'number' => $item['number'], 'part_number' => $item['part_number'],
                'content' => $item['content'], 'student_answer' => $displayAnswer,
                'correct_answer' => $correctAnswerForExplain, 'is_correct' => $isCorrect,
                'is_answered' => $isAnswered, 'explanation' => $item['explanation'],
            ];
        }

        return $formatted;
    }

    /**
     * Calculate total questions for a test (handles all question types)
     */
    protected function calculateTotalQuestions($questions): int
    {
        $totalQuestions = 0;

        foreach ($questions as $question) {
            if ($question->isMasterMatchingHeading()) {
                $mappings = $question->section_specific_data['mappings'] ?? [];
                $totalQuestions += count($mappings);
            } elseif ($question->question_type === 'sentence_completion' && isset($question->section_specific_data['sentence_completion'])) {
                $scData = $question->section_specific_data['sentence_completion'];
                $sentences = $scData['sentences'] ?? [];
                $totalQuestions += count($sentences);
            } elseif ($question->question_type === 'drag_drop') {
                $dragDropData = $question->section_specific_data ?? [];
                $dropZones = $dragDropData['drop_zones'] ?? [];
                $totalQuestions += max(count($dropZones), 1);
            } elseif ($question->question_type === 'multiple_choice') {
                $correctCount = $question->options->where('is_correct', true)->count();
                $totalQuestions += max($correctCount, 1);
            } else {
                $blankCount = 0;
                $content = $question->content;
                preg_match_all('/\[____\d+____\]/', $content, $blankMatches);
                preg_match_all('/\[DROPDOWN_\d+\]/', $content, $dropdownMatches);
                $blankCount = count($blankMatches[0]) + count($dropdownMatches[0]);

                $dropdownCount = 0;
                if ($question->section_specific_data && isset($question->section_specific_data['dropdown_correct'])) {
                    $dropdownCount = count($question->section_specific_data['dropdown_correct']);
                }

                if ($question->question_type === 'fill_blanks') {
                    preg_match_all('/\[____\d+____\]/', $content, $fillBlankMatches);
                    $fillBlankCount = count($fillBlankMatches[0]);
                    $blankCount = max($blankCount, $fillBlankCount);
                }

                if (in_array($question->question_type, ['dropdown_selection', 'matching_grid'])) {
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
    protected function calculateAnswersAndCorrections($questions, $attempt): array
    {
        $correctAnswers = 0;
        $answeredQuestions = 0;
        $answersByQuestion = $attempt->answers->groupBy('question_id');

        foreach ($questions as $question) {
            $questionAnswers = $answersByQuestion->get($question->id, collect());

            if ($question->isMasterMatchingHeading()) {
                $mappings = $question->section_specific_data['mappings'] ?? [];
                foreach ($questionAnswers as $answer) {
                    if ($answer->answer) {
                        $answeredQuestions++;
                        $answerData = json_decode($answer->answer, true);
                        if (isset($answerData['sub_question']) && isset($answerData['selected_letter'])) {
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
                $scData = $question->section_specific_data['sentence_completion'];
                $sentences = $scData['sentences'] ?? [];

                foreach ($sentences as $sentenceIndex => $sentence) {
                    $questionNumber = $sentence['questionNumber'] ?? ($sentenceIndex + 1);
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
                $answer = $questionAnswers->first();
                if ($answer && $answer->answer) {
                    $answerData = json_decode($answer->answer, true);
                    if (is_array($answerData)) {
                        $dragDropData = $question->section_specific_data ?? [];
                        $dropZones = $dragDropData['drop_zones'] ?? [];

                        foreach ($dropZones as $zoneIndex => $zone) {
                            $zoneKey = 'zone_' . $zoneIndex;
                            $oldZoneKey = 'zone_' . ($zoneIndex + 1);

                            $studentAnswer = null;
                            if (isset($answerData[$zoneKey]) && $answerData[$zoneKey] !== '' && $answerData[$zoneKey] !== null) {
                                $studentAnswer = $answerData[$zoneKey];
                            } elseif (isset($answerData[$oldZoneKey]) && $answerData[$oldZoneKey] !== '' && $answerData[$oldZoneKey] !== null) {
                                $studentAnswer = $answerData[$oldZoneKey];
                            }

                            if ($studentAnswer !== null) {
                                $answeredQuestions++;
                                $correctAnswer = $zone['correct_answer'] ?? $zone['answer'] ?? null;
                                if ($correctAnswer && $this->traitCompareAnswers($studentAnswer, $correctAnswer)) {
                                    $correctAnswers++;
                                }
                            }
                        }
                    }
                }
            } elseif ($question->question_type === 'multiple_choice') {
                $correctOptions = $question->options->where('is_correct', true)->values();
                $correctCount = $correctOptions->count();

                $selectedOptionIds = [];
                foreach ($questionAnswers as $answer) {
                    if ($answer->selected_option_id) {
                        $selectedOptionIds[] = $answer->selected_option_id;
                    } elseif ($answer->answer) {
                        $decoded = json_decode($answer->answer, true);
                        if (is_array($decoded)) {
                            $selectedOptionIds = array_merge($selectedOptionIds, $decoded);
                        }
                    }
                }
                $selectedOptionIds = array_values(array_unique($selectedOptionIds));

                // H18: net-floor scoring — each wrong tick cancels a correct tick (floored at 0),
                // so selecting every option can never yield full marks. Keeps this shared recompute
                // (used by BandScoreRecalculator + results) consistent with submit-time scoring.
                $correctSelected = 0;
                $incorrectSelected = 0;
                foreach ($selectedOptionIds as $optionId) {
                    $selectedOption = $question->options->firstWhere('id', $optionId);
                    if (!$selectedOption) {
                        continue;
                    }
                    if ($selectedOption->is_correct) {
                        $correctSelected++;
                    } else {
                        $incorrectSelected++;
                    }
                }
                $correctAnswers += max(0, $correctSelected - $incorrectSelected);
                $answeredQuestions += min(count($selectedOptionIds), max($correctCount, 1));
            } elseif ($question->question_type === 'fill_blanks') {
                $answer = $questionAnswers->first();
                if ($answer && $answer->answer) {
                    if ($this->traitIsJson($answer->answer)) {
                        $studentAnswers = json_decode($answer->answer, true);
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
                        if (trim($answer->answer) !== '') {
                            $answeredQuestions++;
                            if ($this->traitCheckTextAnswer($answer)) {
                                $correctAnswers++;
                            }
                        }
                    }
                }
            } elseif (in_array($question->question_type, ['dropdown_selection', 'matching_grid'])) {
                $answer = $questionAnswers->first();
                if ($answer && $answer->answer) {
                    if ($this->traitIsJson($answer->answer)) {
                        $studentAnswers = json_decode($answer->answer, true);
                        preg_match_all('/\[DROPDOWN_\d+\]/', $question->content, $matches);
                        foreach ($matches[0] as $match) {
                            preg_match('/\d+/', $match, $numberMatch);
                            $dropdownNum = $numberMatch[0] ?? null;
                            if ($dropdownNum && isset($studentAnswers['dropdown_' . $dropdownNum])) {
                                $studentAnswer = trim($studentAnswers['dropdown_' . $dropdownNum]);
                                if ($studentAnswer !== '') {
                                    $answeredQuestions++;
                                    $sectionData = $question->section_specific_data;
                                    if ($sectionData && isset($sectionData['dropdown_correct'][$dropdownNum])) {
                                        $correctIndex = $sectionData['dropdown_correct'][$dropdownNum];
                                        $dropdownOptions = $sectionData['dropdown_options'][$dropdownNum] ?? '';
                                        if ($dropdownOptions) {
                                            $options = array_map('trim', explode(',', $dropdownOptions));
                                            $correctOption = $options[$correctIndex] ?? '';
                                            if ($this->traitCompareAnswers($studentAnswer, $correctOption)) {
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
                $answer = $questionAnswers->first();
                if ($answer) {
                    if ($answer->question->options->count() > 0) {
                        if ($answer->selected_option_id) {
                            $answeredQuestions++;
                            if ($answer->selectedOption && $answer->selectedOption->is_correct) {
                                $correctAnswers++;
                            }
                        }
                    } else {
                        if ($answer->answer && trim($answer->answer) !== '') {
                            $answeredQuestions++;
                            if ($this->traitCheckTextAnswer($answer)) {
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
     * Get AI evaluation data for modal display.
     */
    protected function getAiEvaluationData($attempt, string $sectionName): ?array
    {
        if (!$attempt->ai_evaluated_at) {
            return null;
        }

        $evaluations = $attempt->answers()
            ->whereNotNull('ai_evaluation')
            ->with('question')
            ->get();

        if ($evaluations->isEmpty()) {
            return null;
        }

        if ($sectionName === 'writing') {
            return [
                'overall_band' => $attempt->ai_band_score,
                'tasks' => $evaluations->map(function ($answer) {
                    $eval = $answer->ai_evaluation;
                    return [
                        'task_number' => $answer->question->order_number,
                        'band_score' => $answer->ai_band_score,
                        'word_count' => $eval['word_count'] ?? 0,
                        'required_words' => $answer->question->order_number == 1 ? 150 : 250,
                        'criteria' => $eval['criteria'] ?? [],
                        'feedback' => $eval['feedback'] ?? [],
                        'grammar_corrections' => $eval['grammar_corrections'] ?? [],
                        'vocabulary_suggestions' => $eval['vocabulary_suggestions'] ?? [],
                        'improvement_tips' => $eval['improvement_tips'] ?? [],
                        'essay_text' => $eval['original_text'] ?? '',
                    ];
                })->values()->toArray(),
            ];
        }

        // Speaking — group evaluations by part_number
        $grouped = $evaluations->groupBy(fn($answer) => $answer->question->part_number ?? $answer->question->order_number);

        return [
            'overall_band' => $attempt->ai_band_score,
            'parts' => $grouped->map(function ($partAnswers, $partNum) {
                $mergedCriteria = [];
                $mergedFeedback = [];
                $mergedTips = [];
                $transcriptions = [];
                $bandScores = [];

                foreach ($partAnswers as $answer) {
                    $eval = $answer->ai_evaluation;
                    if ($answer->ai_band_score) {
                        $bandScores[] = (float) $answer->ai_band_score;
                    }
                    foreach (($eval['criteria'] ?? []) as $k => $v) {
                        $mergedCriteria[$k] = isset($mergedCriteria[$k])
                            ? round(($mergedCriteria[$k] + (float)$v) / 2, 1)
                            : (float)$v;
                    }
                    foreach (($eval['feedback'] ?? []) as $k => $v) {
                        $mergedFeedback[$k] = isset($mergedFeedback[$k])
                            ? $mergedFeedback[$k] . "\n\n" . $v
                            : $v;
                    }
                    foreach (($eval['tips'] ?? $eval['improvement_tips'] ?? []) as $tip) {
                        if ($tip && !in_array($tip, $mergedTips)) {
                            $mergedTips[] = $tip;
                        }
                    }
                    $trans = $answer->transcription ?? $eval['transcription'] ?? '';
                    if ($trans) {
                        $transcriptions[] = $trans;
                    }
                }

                return [
                    'part_number' => (int) $partNum,
                    'band_score' => $bandScores ? round(array_sum($bandScores) / count($bandScores), 1) : null,
                    'criteria' => $mergedCriteria,
                    'feedback' => $mergedFeedback,
                    'transcription' => implode("\n\n---\n\n", $transcriptions),
                    'improvement_tips' => $mergedTips,
                ];
            })->sortKeys()->values()->toArray(),
        ];
    }

    // ---- Helper delegates ----

    protected function traitCompareAnswers($studentAnswer, $correctAnswer): bool
    {
        return $this->getAnswerValidator()->compareAnswers($studentAnswer, $correctAnswer, true);
    }

    protected function traitNormalizeAnswer($answer): string
    {
        return $this->getAnswerValidator()->normalizeAnswer($answer);
    }

    protected function traitIsJson($string): bool
    {
        return $this->getAnswerValidator()->isJson($string);
    }

    /**
     * Check if a text-based answer is correct (used by calculateAnswersAndCorrections)
     */
    protected function traitCheckTextAnswer($answer): bool
    {
        $question = $answer->question;
        $studentAnswer = $answer->answer;

        if ($this->traitIsJson($studentAnswer)) {
            $studentAnswers = json_decode($studentAnswer, true);
            $results = $question->checkMultipleBlanks($studentAnswers);
            $allCorrect = ($results['total'] > 0 && $results['correct'] === $results['total']);

            $sectionData = $question->section_specific_data;
            if ($sectionData && isset($sectionData['dropdown_correct']) && is_array($sectionData['dropdown_correct'])) {
                foreach ($sectionData['dropdown_correct'] as $num => $correctIndex) {
                    $studentDropdownAnswer = $studentAnswers['dropdown_' . $num] ?? $studentAnswers[$num] ?? null;
                    $dropdownOptions = $sectionData['dropdown_options'][$num] ?? '';
                    if ($dropdownOptions) {
                        $options = array_map('trim', explode(',', $dropdownOptions));
                        $correctOption = $options[$correctIndex] ?? '';
                        if (!$this->traitCompareAnswers($studentDropdownAnswer ?? '', $correctOption)) {
                            $allCorrect = false;
                            break;
                        }
                    }
                }

                if ($results['total'] === 0 && count($sectionData['dropdown_correct']) > 0) {
                    $allCorrect = true;
                    foreach ($sectionData['dropdown_correct'] as $num => $correctIndex) {
                        $studentDropdownAnswer = $studentAnswers['dropdown_' . $num] ?? $studentAnswers[$num] ?? null;
                        $dropdownOptions = $sectionData['dropdown_options'][$num] ?? '';
                        if ($dropdownOptions) {
                            $options = array_map('trim', explode(',', $dropdownOptions));
                            $correctOption = $options[$correctIndex] ?? '';
                            if (!$this->traitCompareAnswers($studentDropdownAnswer ?? '', $correctOption)) {
                                $allCorrect = false;
                                break;
                            }
                        }
                    }
                }
            }

            return $allCorrect;
        }

        $blankAnswers = $question->getBlankAnswersArray();
        if (!empty($blankAnswers) && count($blankAnswers) === 1) {
            reset($blankAnswers);
            $blankNum = key($blankAnswers);
            return $question->checkBlankAnswer($blankNum, $studentAnswer);
        }

        return false;
    }
}
