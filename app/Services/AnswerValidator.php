<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * AnswerValidator Service
 *
 * Centralized service for validating and comparing student answers
 * across all test types (Listening, Reading, Writing, Speaking)
 *
 * Usage:
 *   $validator = app(AnswerValidator::class);
 *   $isCorrect = $validator->compareAnswers($studentAnswer, $correctAnswer);
 */
class AnswerValidator
{
    /**
     * British to American spelling mappings
     */
    protected array $spellingVariations = [
        'colour' => 'color',
        'honour' => 'honor',
        'favour' => 'favor',
        'labour' => 'labor',
        'centre' => 'center',
        'theatre' => 'theater',
        'metre' => 'meter',
        'litre' => 'liter',
        'defence' => 'defense',
        'licence' => 'license',
        'practise' => 'practice',
        'organisation' => 'organization',
        'specialise' => 'specialize',
        'analyse' => 'analyze',
        'programme' => 'program',
        'behaviour' => 'behavior',
        'neighbour' => 'neighbor',
        'travelling' => 'traveling',
        'cancelled' => 'canceled',
        'judgement' => 'judgment',
        'acknowledgement' => 'acknowledgment',
    ];

    /**
     * Contraction expansions
     */
    protected array $contractions = [
        "don't" => "do not",
        "won't" => "will not",
        "can't" => "cannot",
        "shouldn't" => "should not",
        "wouldn't" => "would not",
        "couldn't" => "could not",
        "isn't" => "is not",
        "aren't" => "are not",
        "wasn't" => "was not",
        "weren't" => "were not",
        "hasn't" => "has not",
        "haven't" => "have not",
        "hadn't" => "had not",
        "doesn't" => "does not",
        "didn't" => "did not",
        "it's" => "it is",
        "he's" => "he is",
        "she's" => "she is",
        "they're" => "they are",
        "we're" => "we are",
        "you're" => "you are",
        "i'm" => "i am",
        "let's" => "let us",
        "that's" => "that is",
        "what's" => "what is",
        "where's" => "where is",
        "who's" => "who is",
        "there's" => "there is",
    ];

    /**
     * Number word to digit mappings
     */
    protected array $numberWords = [
        'zero' => '0', 'one' => '1', 'two' => '2', 'three' => '3',
        'four' => '4', 'five' => '5', 'six' => '6', 'seven' => '7',
        'eight' => '8', 'nine' => '9', 'ten' => '10',
        'eleven' => '11', 'twelve' => '12', 'thirteen' => '13',
        'fourteen' => '14', 'fifteen' => '15', 'sixteen' => '16',
        'seventeen' => '17', 'eighteen' => '18', 'nineteen' => '19',
        'twenty' => '20', 'thirty' => '30', 'forty' => '40',
        'fifty' => '50', 'sixty' => '60', 'seventy' => '70',
        'eighty' => '80', 'ninety' => '90', 'hundred' => '100',
        'thousand' => '1000',
    ];

    /**
     * Common word variations
     */
    protected array $commonVariations = [
        'ok' => 'okay',
        'alright' => 'all right',
        '&' => 'and',
        'per cent' => 'percent',
        'per-cent' => 'percent',
    ];

    /**
     * Compare two answers with improved flexibility
     *
     * @param mixed $studentAnswer The student's answer
     * @param mixed $correctAnswer The correct answer
     * @param bool $enableLogging Whether to log comparison details
     * @return bool
     */
    public function compareAnswers($studentAnswer, $correctAnswer, bool $enableLogging = false): bool
    {
        // Extract string from array if needed
        $studentAnswer = $this->extractAnswerFromArray($studentAnswer);
        $correctAnswer = $this->extractAnswerFromArray($correctAnswer);

        // Handle null/empty cases
        if (empty($studentAnswer) && empty($correctAnswer)) {
            return true;
        }

        if (empty($studentAnswer) || empty($correctAnswer)) {
            return false;
        }

        // Normalize both answers
        $studentNormalized = $this->normalizeAnswer($studentAnswer);
        $correctNormalized = $this->normalizeAnswer($correctAnswer);

        // Check for exact match after normalization
        if ($studentNormalized === $correctNormalized) {
            return true;
        }

        // Check for alternative answers (if correct answer contains '/')
        if ($this->checkAlternativeAnswers($studentNormalized, $correctAnswer)) {
            return true;
        }

        // Check for acceptable variations with parentheses
        // e.g., "color(s)" accepts "color" or "colors"
        if ($this->checkParenthesesVariations($studentNormalized, $correctAnswer)) {
            return true;
        }

        // Log failed comparison if enabled
        if ($enableLogging) {
            Log::info('Answer comparison failed', [
                'student_original' => $studentAnswer,
                'correct_original' => $correctAnswer,
                'student_normalized' => $studentNormalized,
                'correct_normalized' => $correctNormalized,
            ]);
        }

        return false;
    }

    /**
     * Extract answer string from array formats
     * Handles various array structures from different question types
     *
     * @param mixed $answer
     * @return mixed
     */
    public function extractAnswerFromArray($answer)
    {
        if (!is_array($answer)) {
            return $answer;
        }

        // Drag & drop zone format
        if (isset($answer['zone_0'])) {
            return $answer['zone_0'];
        }

        // Blank fill format
        if (isset($answer['blank_1'])) {
            return $answer['blank_1'];
        }

        // Get first non-empty value from array
        return !empty($answer) ? reset($answer) : '';
    }

    /**
     * Normalize answer for comparison
     *
     * @param mixed $answer
     * @return string
     */
    public function normalizeAnswer($answer): string
    {
        // Handle array answers
        if (is_array($answer)) {
            $answer = $this->extractAnswerFromArray($answer);
        }

        // Handle null
        if (is_null($answer)) {
            return '';
        }

        // Convert to string
        $answer = (string) $answer;

        // Lowercase
        $answer = strtolower($answer);

        // Trim whitespace
        $answer = trim($answer);

        // Remove extra spaces
        $answer = preg_replace('/\s+/', ' ', $answer);

        // Remove punctuation except apostrophes in contractions and hyphens
        $answer = preg_replace("/[^\w\s'\-]/", '', $answer);

        // Build replacement array
        $replacements = array_merge(
            $this->contractions,
            $this->numberWords,
            $this->commonVariations,
            $this->spellingVariations,
            [
                // Articles (remove them for flexibility)
                ' the ' => ' ',
                ' a ' => ' ',
                ' an ' => ' ',
            ]
        );

        // Apply string replacements
        $answer = strtr($answer, $replacements);

        // Remove articles at the beginning
        $answer = preg_replace('/^the\s+/i', '', $answer);
        $answer = preg_replace('/^a\s+/i', '', $answer);
        $answer = preg_replace('/^an\s+/i', '', $answer);

        // Remove multiple spaces again after replacements
        $answer = preg_replace('/\s+/', ' ', $answer);

        // Final trim
        return trim($answer);
    }

    /**
     * Check if answer matches any alternative (separated by '/')
     *
     * @param string $studentNormalized
     * @param string $correctAnswer
     * @return bool
     */
    protected function checkAlternativeAnswers(string $studentNormalized, string $correctAnswer): bool
    {
        if (strpos($correctAnswer, '/') === false) {
            return false;
        }

        $alternatives = array_map('trim', explode('/', $correctAnswer));

        foreach ($alternatives as $alternative) {
            if ($this->normalizeAnswer($alternative) === $studentNormalized) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for acceptable variations with parentheses
     * e.g., "color(s)" accepts "color" or "colors"
     *
     * @param string $studentNormalized
     * @param string $correctAnswer
     * @return bool
     */
    protected function checkParenthesesVariations(string $studentNormalized, string $correctAnswer): bool
    {
        if (!preg_match('/\(([^)]+)\)/', $correctAnswer, $matches)) {
            return false;
        }

        // Try without the parenthetical part
        $withoutParentheses = str_replace($matches[0], '', $correctAnswer);
        if ($this->normalizeAnswer($withoutParentheses) === $studentNormalized) {
            return true;
        }

        // Try with the parenthetical part included
        $withParentheses = str_replace(['(', ')'], '', $correctAnswer);
        if ($this->normalizeAnswer($withParentheses) === $studentNormalized) {
            return true;
        }

        return false;
    }

    /**
     * Convert answer to string for database storage
     *
     * @param mixed $answer
     * @return string|null
     */
    public function answerToString($answer): ?string
    {
        if (is_null($answer)) {
            return null;
        }

        if (is_array($answer)) {
            return json_encode($answer);
        }

        if (is_bool($answer)) {
            return $answer ? '1' : '0';
        }

        return (string) $answer;
    }

    /**
     * Check if a string is valid JSON
     *
     * @param mixed $string
     * @return bool
     */
    public function isJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Compare multiple blank answers
     *
     * @param array $studentAnswers Array of student answers keyed by blank number
     * @param array $correctAnswers Array of correct answers keyed by blank number
     * @return array ['correct' => int, 'total' => int, 'details' => array]
     */
    public function compareBlankAnswers(array $studentAnswers, array $correctAnswers): array
    {
        $correct = 0;
        $total = count($correctAnswers);
        $details = [];

        foreach ($correctAnswers as $blankKey => $correctAnswer) {
            $studentAnswer = $studentAnswers[$blankKey] ?? '';
            $isCorrect = $this->compareAnswers($studentAnswer, $correctAnswer);

            if ($isCorrect) {
                $correct++;
            }

            $details[$blankKey] = [
                'student' => $studentAnswer,
                'correct' => $correctAnswer,
                'is_correct' => $isCorrect,
            ];
        }

        return [
            'correct' => $correct,
            'total' => $total,
            'details' => $details,
        ];
    }

    /**
     * Check matching pair answer (e.g., matching headings)
     *
     * @param string $studentAnswer The student's selected option (e.g., 'A', 'B', 'C')
     * @param string $correctAnswer The correct option
     * @return bool
     */
    public function compareMatchingAnswer(string $studentAnswer, string $correctAnswer): bool
    {
        return strtoupper(trim($studentAnswer)) === strtoupper(trim($correctAnswer));
    }

    /**
     * Get all spelling variations for a word
     * Useful for custom answer checking
     *
     * @param string $word
     * @return array
     */
    public function getSpellingVariations(string $word): array
    {
        $word = strtolower(trim($word));
        $variations = [$word];

        // Check if it's a British spelling
        if (isset($this->spellingVariations[$word])) {
            $variations[] = $this->spellingVariations[$word];
        }

        // Check if it's an American spelling (reverse lookup)
        $flipped = array_flip($this->spellingVariations);
        if (isset($flipped[$word])) {
            $variations[] = $flipped[$word];
        }

        return array_unique($variations);
    }

    /**
     * Check multi-blank answer and return results for IELTS scoring
     * Used by both Listening and Reading controllers
     *
     * @param object $question The question model with section_specific_data
     * @param array $studentAnswers Array of student answers
     * @return array ['answered' => int, 'correct' => int]
     */
    public function checkMultiBlankAnswer($question, array $studentAnswers): array
    {
        $sectionData = $question->section_specific_data;

        if (!$sectionData) {
            return ['answered' => 0, 'correct' => 0];
        }

        $answeredCount = 0;
        $correctCount = 0;

        // Check blank answers
        if (isset($sectionData['blank_answers']) && is_array($sectionData['blank_answers'])) {
            foreach ($sectionData['blank_answers'] as $num => $correctAnswer) {
                $studentBlankAnswer = $this->findStudentBlankAnswer($studentAnswers, $num);

                if (!empty($studentBlankAnswer)) {
                    $answeredCount++;

                    if ($this->compareAnswers($studentBlankAnswer, $correctAnswer)) {
                        $correctCount++;
                    }
                }
            }
        }

        // Check dropdown answers
        if (isset($sectionData['dropdown_correct']) && is_array($sectionData['dropdown_correct'])) {
            foreach ($sectionData['dropdown_correct'] as $num => $correctIndex) {
                $studentDropdownAnswer = $this->findStudentDropdownAnswer($studentAnswers, $num);

                if (!empty($studentDropdownAnswer)) {
                    $answeredCount++;

                    $dropdownOptions = $sectionData['dropdown_options'][$num] ?? '';
                    if ($dropdownOptions) {
                        $options = array_map('trim', explode(',', $dropdownOptions));
                        $correctOption = $options[$correctIndex] ?? '';

                        if ($this->compareAnswers($studentDropdownAnswer, $correctOption)) {
                            $correctCount++;
                        }
                    }
                }
            }
        }

        return [
            'answered' => $answeredCount,
            'correct' => $correctCount
        ];
    }

    /**
     * Find student's blank answer using multiple key formats
     *
     * @param array $studentAnswers
     * @param int|string $blankNum
     * @return string|null
     */
    protected function findStudentBlankAnswer(array $studentAnswers, $blankNum): ?string
    {
        $possibleKeys = [
            'blank_' . $blankNum,
            'blank' . $blankNum,
            (string) $blankNum,
            $blankNum,
        ];

        foreach ($possibleKeys as $key) {
            if (isset($studentAnswers[$key]) && !empty($studentAnswers[$key])) {
                return $studentAnswers[$key];
            }
        }

        return null;
    }

    /**
     * Find student's dropdown answer using multiple key formats
     *
     * @param array $studentAnswers
     * @param int|string $dropdownNum
     * @return string|null
     */
    protected function findStudentDropdownAnswer(array $studentAnswers, $dropdownNum): ?string
    {
        $possibleKeys = [
            'dropdown_' . $dropdownNum,
            'dropdown' . $dropdownNum,
            (string) $dropdownNum,
            $dropdownNum,
        ];

        foreach ($possibleKeys as $key) {
            if (isset($studentAnswers[$key]) && !empty($studentAnswers[$key])) {
                return $studentAnswers[$key];
            }
        }

        return null;
    }

    /**
     * Check single text answer against question's correct answer
     *
     * @param object $question The question model
     * @param mixed $studentAnswer
     * @return bool
     */
    public function checkSingleTextAnswer($question, $studentAnswer): bool
    {
        // Handle array student answers
        if (is_array($studentAnswer)) {
            $studentAnswer = !empty($studentAnswer) ? reset($studentAnswer) : '';
        }

        $sectionData = $question->section_specific_data;

        // Check if there's a correct answer defined
        if ($sectionData && isset($sectionData['correct_answer'])) {
            return $this->compareAnswers($studentAnswer, $sectionData['correct_answer']);
        }

        // For single blank questions, check blank_answers[1]
        if ($sectionData && isset($sectionData['blank_answers'][1])) {
            return $this->compareAnswers($studentAnswer, $sectionData['blank_answers'][1]);
        }

        return false;
    }

    /**
     * Check text answer (handles both single and multi-blank)
     *
     * @param object $question
     * @param mixed $studentAnswer
     * @return bool
     */
    public function checkTextAnswer($question, $studentAnswer): bool
    {
        // If it's an array, use multi-blank method
        if (is_array($studentAnswer)) {
            $results = $this->checkMultiBlankAnswer($question, $studentAnswer);

            // Return true only if ALL blanks are correct
            $totalBlanks = count($question->section_specific_data['blank_answers'] ?? []) +
                          count($question->section_specific_data['dropdown_correct'] ?? []);

            return $totalBlanks > 0 && $results['correct'] === $totalBlanks;
        }

        // Single text answer
        return $this->checkSingleTextAnswer($question, $studentAnswer);
    }

    /**
     * Check heading dropdown answers (Reading specific but kept here for consistency)
     *
     * @param array $studentAnswers
     * @param \Illuminate\Support\Collection $questionOptions
     * @return array ['answered' => int, 'correct' => int]
     */
    public function checkHeadingDropdownAnswers(array $studentAnswers, $questionOptions): array
    {
        $answeredCount = 0;
        $correctCount = 0;

        foreach ($studentAnswers as $key => $value) {
            if (strpos($key, 'heading_') === 0 && !empty($value)) {
                $answeredCount++;

                if (is_numeric($value)) {
                    $option = $questionOptions->firstWhere('id', $value);
                    if ($option && $option->is_correct) {
                        $correctCount++;
                    }
                }
            }
        }

        return [
            'answered' => $answeredCount,
            'correct' => $correctCount
        ];
    }
}
