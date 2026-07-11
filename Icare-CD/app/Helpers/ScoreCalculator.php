<?php

namespace App\Helpers;

class ScoreCalculator
{
    /**
     * Calculate IELTS Listening band score based on correct answers
     * Using official IELTS band score conversion (40 questions base)
     *
     * @param int $correctAnswers Number of correct answers
     * @param int $totalQuestions Total questions in test
     * @return float Band score
     */
    public static function calculateListeningBandScore(int $correctAnswers, int $totalQuestions): float
    {
        if ($totalQuestions <= 0) {
            return 0.0;
        }
        
        // STRICT: Use actual correct answers, don't scale
        // Official IELTS Listening band score conversion
        return match(true) {
            $correctAnswers >= 39 => 9.0,    // 39-40 = Band 9.0
            $correctAnswers >= 37 => 8.5,    // 37-38 = Band 8.5
            $correctAnswers >= 35 => 8.0,    // 35-36 = Band 8.0
            $correctAnswers >= 32 => 7.5,    // 32-34 = Band 7.5 (slightly adjusted)
            $correctAnswers >= 30 => 7.0,    // 30-31 = Band 7.0
            $correctAnswers >= 26 => 6.5,    // 26-29 = Band 6.5 (slightly adjusted)
            $correctAnswers >= 23 => 6.0,    // 23-25 = Band 6.0
            $correctAnswers >= 18 => 5.5,    // 18-22 = Band 5.5 (slightly adjusted)
            $correctAnswers >= 16 => 5.0,    // 16-17 = Band 5.0
            $correctAnswers >= 13 => 4.5,    // 13-15 = Band 4.5
            $correctAnswers >= 10 => 4.0,    // 10-12 = Band 4.0
            $correctAnswers >= 8  => 3.5,    // 8-9 = Band 3.5
            $correctAnswers >= 6  => 3.0,    // 6-7 = Band 3.0
            $correctAnswers >= 4  => 2.5,    // 4-5 = Band 2.5
            default => 0.0
        };
    }
    
    /**
     * Calculate IELTS Reading band score based on correct answers
     * Using official IELTS band score conversion (40 questions base)
     *
     * @param int $correctAnswers Number of correct answers
     * @param int $totalQuestions Total questions in test
     * @param string $testType 'academic' or 'general'
     * @return float Band score
     */
    public static function calculateReadingBandScore(int $correctAnswers, int $totalQuestions, string $testType = 'academic'): float
    {
        if ($totalQuestions <= 0) {
            return 0.0;
        }
        
        // STRICT: Use actual correct answers, don't scale
        if ($testType === 'academic') {
            // Academic Reading - Official IELTS conversion
            return match(true) {
                $correctAnswers >= 39 => 9.0,    // 39-40 = Band 9.0
                $correctAnswers >= 37 => 8.5,    // 37-38 = Band 8.5
                $correctAnswers >= 35 => 8.0,    // 35-36 = Band 8.0
                $correctAnswers >= 33 => 7.5,    // 33-34 = Band 7.5
                $correctAnswers >= 30 => 7.0,    // 30-32 = Band 7.0
                $correctAnswers >= 27 => 6.5,    // 27-29 = Band 6.5
                $correctAnswers >= 23 => 6.0,    // 23-26 = Band 6.0
                $correctAnswers >= 19 => 5.5,    // 19-22 = Band 5.5
                $correctAnswers >= 15 => 5.0,    // 15-18 = Band 5.0
                $correctAnswers >= 13 => 4.5,    // 13-14 = Band 4.5
                $correctAnswers >= 10 => 4.0,    // 10-12 = Band 4.0
                $correctAnswers >= 8  => 3.5,    // 8-9 = Band 3.5
                $correctAnswers >= 6  => 3.0,    // 6-7 = Band 3.0
                $correctAnswers >= 4  => 2.5,    // 4-5 = Band 2.5
                default => 0.0
            };
        } else {
            // General Training Reading - Official IELTS conversion
            return match(true) {
                $correctAnswers >= 40 => 9.0,    // 40 = Band 9.0
                $correctAnswers >= 39 => 8.5,    // 39 = Band 8.5
                $correctAnswers >= 37 => 8.0,    // 37-38 = Band 8.0
                $correctAnswers >= 36 => 7.5,    // 36 = Band 7.5
                $correctAnswers >= 34 => 7.0,    // 34-35 = Band 7.0
                $correctAnswers >= 32 => 6.5,    // 32-33 = Band 6.5
                $correctAnswers >= 30 => 6.0,    // 30-31 = Band 6.0
                $correctAnswers >= 27 => 5.5,    // 27-29 = Band 5.5
                $correctAnswers >= 23 => 5.0,    // 23-26 = Band 5.0
                $correctAnswers >= 19 => 4.5,    // 19-22 = Band 4.5
                $correctAnswers >= 15 => 4.0,    // 15-18 = Band 4.0
                $correctAnswers >= 12 => 3.5,    // 12-14 = Band 3.5
                $correctAnswers >= 9  => 3.0,    // 9-11 = Band 3.0
                $correctAnswers >= 6  => 2.5,    // 6-8 = Band 2.5
                default => 0.0
            };
        }
    }
    
    /**
     * Calculate band score for partial/incomplete tests
     * STRICT: Based on actual correct answers, no projection
     *
     * @param int $correctAnswers Number of correct answers
     * @param int $answeredQuestions Number of questions attempted
     * @param int $totalQuestions Total questions in test
     * @param string $section 'listening' or 'reading'
     * @param string $testType 'academic' or 'general' (for reading)
     * @return array Contains band_score, confidence, and other details
     */
    public static function calculatePartialTestScore(
        int $correctAnswers, 
        int $answeredQuestions, 
        int $totalQuestions, 
        string $section = 'listening',
        string $testType = 'academic'
    ): array
    {
        // Don't give score if no questions attempted
        if ($answeredQuestions == 0) {
            return [
                'band_score' => 0.0,
                'message' => 'No questions attempted',
                'answered' => 0,
                'total' => $totalQuestions,
                'correct' => 0,
                'accuracy_percentage' => 0,
                'completion_percentage' => 0,
                'performance_level' => 'Not Attempted'
            ];
        }
        
        // STRICT: Calculate band score based on actual correct answers
        // Using official IELTS conversion (no scaling/percentage)
        if ($section === 'listening') {
            $bandScore = self::calculateListeningBandScore($correctAnswers, $totalQuestions);
        } else {
            $bandScore = self::calculateReadingBandScore($correctAnswers, $totalQuestions, $testType);
        }
        
        // Determine performance level and feedback based on band score
        $performanceLevel = match(true) {
            $bandScore >= 8.0 => 'Expert',
            $bandScore >= 7.0 => 'Very Good',
            $bandScore >= 6.0 => 'Good',
            $bandScore >= 5.0 => 'Modest',
            $bandScore >= 4.0 => 'Limited',
            $bandScore >= 3.0 => 'Extremely Limited',
            default => 'Very Poor'
        };

        $sectionLabel = ucfirst($section);
        $feedbackMessage = match(true) {
            $bandScore >= 8.0 => "Outstanding {$sectionLabel} skills! You demonstrate an expert command of English with excellent accuracy. Keep up the great work!",
            $bandScore >= 7.0 => "Great job! Your {$sectionLabel} skills are strong with only occasional inaccuracies. A bit more fine-tuning and you'll reach the top.",
            $bandScore >= 6.0 => "Good effort! You show a competent grasp of {$sectionLabel}. Focus on tricky question types and time management to push your score higher.",
            $bandScore >= 5.0 => "Fair performance. Your {$sectionLabel} foundation is there but needs strengthening. Practice with more test materials and review common question patterns.",
            $bandScore >= 4.0 => "Keep practicing! Work on building your vocabulary and understanding of question formats. Consistent practice will help improve your {$sectionLabel} score.",
            $bandScore >= 3.0 => "More practice is needed. Focus on fundamentals — start with easier passages and gradually build your {$sectionLabel} skills step by step.",
            default => "Don't give up! Start with beginner-level materials and build your confidence. Daily practice with {$sectionLabel} exercises will make a real difference."
        };

        return [
            'band_score' => $bandScore,
            'answered' => $answeredQuestions,
            'total' => $totalQuestions,
            'correct' => $correctAnswers,
            'performance_level' => $performanceLevel,
            'message' => $feedbackMessage,
            'is_complete' => $answeredQuestions === $totalQuestions,
            'is_standard_test' => $totalQuestions == 40
        ];
    }


    /**
     * Calculate overall band score from individual section scores
     *
     * @param float $listening
     * @param float $reading
     * @param float $writing
     * @param float $speaking
     * @return float Overall band score
     */
    public static function calculateOverallBandScore(float $listening, float $reading, float $writing, float $speaking): float
    {
        // Calculate average
        $average = ($listening + $reading + $writing + $speaking) / 4;
        
        // Round to nearest 0.5 according to official IELTS rules
        // .25 rounds UP to .5 (e.g. 6.25 → 6.5), .75 rounds UP to next whole (e.g. 6.75 → 7.0)
        $decimal = $average - floor($average);
        
        if ($decimal < 0.25) {
            return floor($average);
        } elseif ($decimal < 0.75) {
            return floor($average) + 0.5;
        } else {
            return ceil($average);
        }
    }
    
    /**
     * Get band score description
     */
    public static function getBandDescription($bandScore): string
    {
        return match(true) {
            $bandScore >= 9.0 => 'Expert User',
            $bandScore >= 8.0 => 'Very Good User',
            $bandScore >= 7.0 => 'Good User',
            $bandScore >= 6.0 => 'Competent User',
            $bandScore >= 5.0 => 'Modest User',
            $bandScore >= 4.0 => 'Limited User',
            $bandScore >= 3.0 => 'Extremely Limited User',
            $bandScore >= 2.0 => 'Intermittent User',
            $bandScore >= 1.0 => 'Non User',
            default => 'Did not attempt'
        };
    }
    
    /**
     * Get color class for band score display
     */
    public static function getBandColorClass($bandScore): string
    {
        return match(true) {
            $bandScore >= 7.0 => 'text-green-600 bg-green-100',
            $bandScore >= 6.0 => 'text-blue-600 bg-blue-100',
            $bandScore >= 5.0 => 'text-yellow-600 bg-yellow-100',
            $bandScore >= 4.0 => 'text-orange-600 bg-orange-100',
            default => 'text-red-600 bg-red-100'
        };
    }

    /**
     * Calculate total answerable items in a test
     * This properly counts sub-questions like:
     * - Matching headings mappings
     * - Sentence completion sentences
     * - Drag & drop zones
     * - Multiple choice with multiple correct answers
     * - Fill-in-blanks with multiple blanks
     * - Dropdown selections
     *
     * @param \Illuminate\Support\Collection $questions Collection of Question models
     * @return int Total number of answerable items
     */
    public static function calculateTotalAnswerableItems($questions): int
    {
        $totalItems = 0;

        foreach ($questions as $question) {
            // Skip passages - they're not answerable
            if ($question->question_type === 'passage') {
                continue;
            }

            // Master matching headings - count individual mappings
            if (method_exists($question, 'isMasterMatchingHeading') && $question->isMasterMatchingHeading()) {
                $mappings = $question->section_specific_data['mappings'] ?? [];
                $totalItems += max(count($mappings), 1);
                continue;
            }

            // Sentence completion - count individual sentences
            if ($question->question_type === 'sentence_completion' &&
                isset($question->section_specific_data['sentence_completion']['sentences'])) {
                $sentences = $question->section_specific_data['sentence_completion']['sentences'];
                $totalItems += max(count($sentences), 1);
                continue;
            }

            // Drag & drop - count zones
            if ($question->question_type === 'drag_drop') {
                $dropZones = $question->section_specific_data['drop_zones'] ??
                            $question->section_specific_data['drag_zones'] ?? [];
                $totalItems += max(count($dropZones), 1);
                continue;
            }

            // Multiple choice with multiple correct answers
            if ($question->question_type === 'multiple_choice') {
                $correctCount = $question->options->where('is_correct', true)->count();
                $totalItems += max($correctCount, 1);
                continue;
            }

            // Fill-in-blanks and dropdown selections - count individual blanks/dropdowns
            $blankCount = 0;
            $content = $question->content ?? '';

            // Count content-based blanks
            preg_match_all('/\[____\d+____\]/', $content, $blankMatches);
            preg_match_all('/\[DROPDOWN_\d+\]/', $content, $dropdownMatches);
            $blankCount = count($blankMatches[0]) + count($dropdownMatches[0]);

            // Count from section_specific_data
            $sectionData = $question->section_specific_data;
            if ($sectionData) {
                if (isset($sectionData['blank_answers']) && is_array($sectionData['blank_answers'])) {
                    $blankCount = max($blankCount, count($sectionData['blank_answers']));
                }
                if (isset($sectionData['dropdown_correct']) && is_array($sectionData['dropdown_correct'])) {
                    $blankCount = max($blankCount, count($sectionData['dropdown_correct']));
                }
            }

            // If we found blanks/dropdowns, use that count; otherwise count as 1 question
            $totalItems += max($blankCount, 1);
        }

        return $totalItems;
    }
}