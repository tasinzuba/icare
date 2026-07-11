<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;

class SpeakingTextAnalyzer
{
    /**
     * Common filler words and phrases
     */
    protected array $fillerPatterns = [
        'um' => '/\bum+\b/i',
        'uh' => '/\buh+\b/i',
        'er' => '/\ber+\b/i',
        'ah' => '/\bah+\b/i',
        'like' => '/\blike,?\s/i',
        'you know' => '/\byou know\b/i',
        'I mean' => '/\bi mean\b/i',
        'basically' => '/\bbasically\b/i',
        'actually' => '/\bactually\b/i',
        'kind of' => '/\bkind of\b/i',
        'sort of' => '/\bsort of\b/i',
        'so' => '/\bso,?\s+(?:um|uh|like|yeah)\b/i',
    ];

    /**
     * Coherence/discourse markers by category
     */
    protected array $coherenceMarkers = [
        'addition' => ['also', 'additionally', 'furthermore', 'moreover', 'besides', 'in addition', 'as well'],
        'contrast' => ['however', 'but', 'although', 'nevertheless', 'on the other hand', 'whereas', 'despite', 'yet'],
        'cause_effect' => ['because', 'therefore', 'so', 'as a result', 'consequently', 'thus', 'hence', 'due to'],
        'sequence' => ['first', 'firstly', 'second', 'secondly', 'then', 'next', 'finally', 'after that', 'before'],
        'example' => ['for example', 'for instance', 'such as', 'including', 'to illustrate'],
        'emphasis' => ['especially', 'particularly', 'mainly', 'most importantly', 'above all'],
        'summary' => ['in conclusion', 'to sum up', 'overall', 'in summary', 'to conclude'],
        'opinion' => ['I think', 'I believe', 'in my opinion', 'from my perspective', 'personally', 'I feel'],
        'comparison' => ['similarly', 'likewise', 'compared to', 'in comparison', 'just like'],
    ];

    /**
     * Academic word list (subset of common academic vocabulary)
     */
    protected array $academicWords = [
        'analyze', 'approach', 'area', 'assess', 'assume', 'benefit', 'concept', 'consist',
        'context', 'create', 'data', 'define', 'derive', 'distribute', 'economy', 'environment',
        'establish', 'estimate', 'evident', 'factor', 'feature', 'function', 'identify', 'impact',
        'indicate', 'individual', 'interpret', 'involve', 'issue', 'method', 'occur', 'percent',
        'period', 'policy', 'principle', 'process', 'require', 'research', 'respond', 'role',
        'section', 'significant', 'similar', 'source', 'specific', 'structure', 'theory', 'vary',
        'furthermore', 'however', 'therefore', 'consequently', 'nevertheless', 'perspective',
        'substantial', 'fundamental', 'comprehensive', 'considerable', 'demonstrate', 'contribute',
    ];

    /**
     * Analyze transcription and return comprehensive metrics
     */
    public function analyze(string $transcription, int $partNumber, ?string $cueCardPoints = null): array
    {
        if (empty(trim($transcription))) {
            return $this->getEmptyAnalysis();
        }

        return [
            'basic_stats' => $this->calculateBasicStats($transcription),
            'lexical_analysis' => $this->analyzeLexicalResource($transcription),
            'fluency_indicators' => $this->analyzeFluencyIndicators($transcription),
            'coherence_markers' => $this->detectCoherenceMarkers($transcription),
            'part_specific' => $this->analyzePartSpecific($transcription, $partNumber, $cueCardPoints),
        ];
    }

    /**
     * Calculate basic text statistics
     */
    protected function calculateBasicStats(string $transcription): array
    {
        $words = preg_split('/\s+/', trim($transcription));
        $words = array_filter($words, fn($w) => strlen(trim($w)) > 0);
        $wordCount = count($words);

        // Split into sentences
        $sentences = preg_split('/[.!?]+/', $transcription, -1, PREG_SPLIT_NO_EMPTY);
        $sentences = array_filter($sentences, fn($s) => str_word_count(trim($s)) >= 2);
        $sentenceCount = count($sentences);

        // Calculate average word length
        $totalChars = strlen(preg_replace('/\s+/', '', $transcription));
        $avgWordLength = $wordCount > 0 ? round($totalChars / $wordCount, 1) : 0;

        return [
            'word_count' => $wordCount,
            'sentence_count' => $sentenceCount,
            'avg_sentence_length' => $sentenceCount > 0 ? round($wordCount / $sentenceCount, 1) : 0,
            'avg_word_length' => $avgWordLength,
            'character_count' => $totalChars,
        ];
    }

    /**
     * Analyze lexical resource (vocabulary)
     */
    protected function analyzeLexicalResource(string $transcription): array
    {
        // Clean and extract words
        $text = strtolower($transcription);
        $words = preg_split('/\s+/', $text);
        $words = array_map(fn($w) => preg_replace('/[^a-z\']/', '', $w), $words);
        $words = array_filter($words, fn($w) => strlen($w) > 1);

        $wordCount = count($words);
        $uniqueWords = array_unique($words);
        $uniqueCount = count($uniqueWords);

        // Type-Token Ratio (lexical diversity)
        $ttr = $wordCount > 0 ? round(($uniqueCount / $wordCount) * 100, 1) : 0;

        // Complex words (approximated by length >= 7 characters)
        $complexWords = array_filter($words, fn($w) => strlen($w) >= 7);
        $complexCount = count($complexWords);

        // Academic words detection
        $academicFound = [];
        foreach ($this->academicWords as $academic) {
            if (stripos($transcription, $academic) !== false) {
                $academicFound[] = $academic;
            }
        }

        // Common idioms detection
        $idioms = $this->detectIdioms($transcription);

        return [
            'unique_words' => $uniqueCount,
            'lexical_diversity_ttr' => $ttr,
            'lexical_diversity_rating' => $this->rateLexicalDiversity($ttr),
            'complex_word_count' => $complexCount,
            'complex_word_percentage' => $wordCount > 0 ? round(($complexCount / $wordCount) * 100, 1) : 0,
            'academic_words' => $academicFound,
            'academic_word_count' => count($academicFound),
            'idioms_detected' => $idioms,
            'vocabulary_level' => $this->estimateVocabularyLevel($ttr, count($academicFound), $complexCount),
        ];
    }

    /**
     * Analyze fluency indicators (filler words, repetitions)
     */
    protected function analyzeFluencyIndicators(string $transcription): array
    {
        $fillerCounts = [];
        $totalFillers = 0;

        foreach ($this->fillerPatterns as $filler => $pattern) {
            $count = preg_match_all($pattern, $transcription, $matches);
            if ($count > 0) {
                $fillerCounts[$filler] = $count;
                $totalFillers += $count;
            }
        }

        $wordCount = str_word_count($transcription);
        $fillerPercentage = $wordCount > 0 ? round(($totalFillers / $wordCount) * 100, 1) : 0;

        // Detect repetitions
        $repetitions = $this->detectRepetitions($transcription);

        // Detect self-corrections ("I mean", "sorry", "what I meant")
        $selfCorrections = preg_match_all('/\b(I mean|sorry|what I meant|actually no|let me rephrase)\b/i', $transcription);

        return [
            'filler_words' => $fillerCounts,
            'filler_count_total' => $totalFillers,
            'filler_percentage' => $fillerPercentage,
            'filler_rating' => $this->rateFillerUsage($fillerPercentage),
            'repetition_count' => $repetitions['count'],
            'repetition_examples' => array_slice($repetitions['examples'], 0, 5),
            'self_corrections' => $selfCorrections,
        ];
    }

    /**
     * Detect coherence/discourse markers
     */
    protected function detectCoherenceMarkers(string $transcription): array
    {
        $detected = [];
        $totalCount = 0;

        foreach ($this->coherenceMarkers as $category => $markers) {
            $categoryMatches = [];
            foreach ($markers as $marker) {
                if (preg_match('/\b' . preg_quote($marker, '/') . '\b/i', $transcription)) {
                    $categoryMatches[] = $marker;
                    $totalCount++;
                }
            }
            if (!empty($categoryMatches)) {
                $detected[$category] = $categoryMatches;
            }
        }

        $categoryCount = count($detected);
        $missingCategories = array_diff(array_keys($this->coherenceMarkers), array_keys($detected));

        return [
            'markers_by_category' => $detected,
            'total_markers' => $totalCount,
            'category_count' => $categoryCount,
            'coherence_rating' => $this->rateCoherence($totalCount, $categoryCount),
            'missing_categories' => array_values($missingCategories),
        ];
    }

    /**
     * Analyze part-specific requirements
     */
    protected function analyzePartSpecific(string $transcription, int $partNumber, ?string $cueCardPoints): array
    {
        switch ($partNumber) {
            case 1:
                return $this->analyzePart1($transcription);
            case 2:
                return $this->analyzePart2($transcription, $cueCardPoints);
            case 3:
                return $this->analyzePart3($transcription);
            default:
                return ['part' => $partNumber, 'analysis' => 'General speaking response'];
        }
    }

    /**
     * Part 1: Introduction/familiar topics analysis
     */
    protected function analyzePart1(string $transcription): array
    {
        $wordCount = str_word_count($transcription);
        $hasPersonalExamples = preg_match('/\b(I|my|me)\b.*\b(example|instance|time|remember|experience)\b/i', $transcription) > 0
            || preg_match('/\bfor (me|example)\b/i', $transcription) > 0;
        $hasReasons = preg_match('/\b(because|since|as|due to|the reason)\b/i', $transcription) > 0;
        $hasOpinion = preg_match('/\b(I think|I believe|I feel|in my opinion|personally)\b/i', $transcription) > 0;

        $issues = [];
        if ($wordCount < 15) $issues[] = 'Response too short - aim for 20-40 words';
        if ($wordCount > 60) $issues[] = 'Response may be too long for Part 1';
        if (!$hasReasons && !$hasPersonalExamples) $issues[] = 'Try adding reasons or personal examples';

        return [
            'part' => 1,
            'expected_length' => '20-40 words',
            'actual_length' => $wordCount,
            'length_status' => $this->evaluatePartLength($wordCount, 15, 50),
            'has_personal_examples' => $hasPersonalExamples,
            'has_reasons' => $hasReasons,
            'has_opinion' => $hasOpinion,
            'issues' => $issues,
        ];
    }

    /**
     * Part 2: Cue card/long turn analysis
     */
    protected function analyzePart2(string $transcription, ?string $cueCardPoints): array
    {
        $wordCount = str_word_count($transcription);

        // Check for introduction
        $hasIntroduction = preg_match('/^(I\'d like to|I want to|I\'m going to|Let me|Today I|Well,? I)/i', trim($transcription)) > 0;

        // Check for conclusion
        $hasConclusion = preg_match('/(In conclusion|To conclude|Overall|That\'s why|So that\'s|and that\'s)[\s\S]{0,100}$/i', $transcription) > 0;

        // Count structure markers
        $structureMarkers = preg_match_all('/\b(First|Firstly|Second|Secondly|Moreover|Additionally|Also|Finally|Lastly)\b/i', $transcription);

        // Analyze cue card coverage
        $cueCardCoverage = null;
        if ($cueCardPoints) {
            $cueCardCoverage = $this->analyzeCueCardCoverage($transcription, $cueCardPoints);
        }

        $issues = [];
        if ($wordCount < 100) $issues[] = 'Response too short - aim for 150-200 words';
        if ($wordCount < 150 && $wordCount >= 100) $issues[] = 'Response slightly short - try to expand more';
        if (!$hasIntroduction) $issues[] = 'Missing introduction - start with "I\'d like to talk about..."';
        if (!$hasConclusion) $issues[] = 'Missing conclusion - end with a summary statement';
        if ($structureMarkers < 2) $issues[] = 'Add more sequencing words (First, Then, Finally)';

        return [
            'part' => 2,
            'expected_length' => '150-200 words',
            'actual_length' => $wordCount,
            'length_status' => $this->evaluatePartLength($wordCount, 120, 220),
            'has_introduction' => $hasIntroduction,
            'has_conclusion' => $hasConclusion,
            'structure_markers_count' => $structureMarkers,
            'cue_card_coverage' => $cueCardCoverage,
            'issues' => $issues,
        ];
    }

    /**
     * Part 3: Discussion/abstract topics analysis
     */
    protected function analyzePart3(string $transcription): array
    {
        $wordCount = str_word_count($transcription);

        // Check for abstract thinking indicators
        $abstractPatterns = preg_match_all('/\b(society|generally|people tend to|it depends|in general|from a broader perspective|globally|as a whole)\b/i', $transcription);

        // Check for opinion with justification
        $hasOpinion = preg_match('/\b(I think|I believe|In my opinion|I feel|personally)\b/i', $transcription) > 0;
        $hasJustification = preg_match('/\b(because|since|as|the reason|this is due to|this is because)\b/i', $transcription) > 0;

        // Check for speculation
        $speculation = preg_match_all('/\b(might|could|would|may|possibly|probably|perhaps|likely)\b/i', $transcription);

        // Check for comparisons
        $comparisons = preg_match_all('/\b(compared to|whereas|while|on the other hand|however|in contrast|unlike|similarly)\b/i', $transcription);

        // Check for examples
        $hasExamples = preg_match('/\b(for example|for instance|such as|like when)\b/i', $transcription) > 0;

        $issues = [];
        if ($wordCount < 40) $issues[] = 'Response too short for Part 3 discussion';
        if (!$hasOpinion) $issues[] = 'Express your opinion clearly';
        if ($hasOpinion && !$hasJustification) $issues[] = 'Justify your opinion with reasons';
        if ($abstractPatterns < 1) $issues[] = 'Try to discuss broader/societal perspectives';
        if ($speculation < 1) $issues[] = 'Use speculative language (might, could, probably)';

        return [
            'part' => 3,
            'expected_length' => '60-100 words',
            'actual_length' => $wordCount,
            'length_status' => $this->evaluatePartLength($wordCount, 40, 120),
            'abstract_thinking_count' => $abstractPatterns,
            'has_opinion' => $hasOpinion,
            'has_justification' => $hasJustification,
            'speculation_count' => $speculation,
            'comparison_count' => $comparisons,
            'has_examples' => $hasExamples,
            'analytical_depth' => $this->ratePart3Depth($abstractPatterns, $speculation, $comparisons, $hasJustification),
            'issues' => $issues,
        ];
    }

    /**
     * Analyze cue card point coverage
     */
    protected function analyzeCueCardCoverage(string $transcription, string $cueCardPoints): array
    {
        // Parse cue card points (handle bullet points, newlines, etc.)
        $points = preg_split('/[\n\r]+/', $cueCardPoints);
        $points = array_map('trim', $points);
        $points = array_filter($points, fn($p) => strlen($p) > 5);

        // Clean up bullet markers
        $points = array_map(fn($p) => preg_replace('/^[\-\*\•\d\.]+\s*/', '', $p), $points);
        $points = array_filter($points);
        $points = array_values($points);

        if (empty($points)) {
            return ['total_points' => 0, 'covered_points' => 0, 'coverage_percentage' => 100, 'details' => []];
        }

        $coverage = [];
        $coveredCount = 0;

        foreach ($points as $point) {
            // Extract key concepts from the point
            $keywords = $this->extractKeywords($point);
            $isCovered = false;
            $matchedKeywords = [];

            foreach ($keywords as $keyword) {
                if (stripos($transcription, $keyword) !== false) {
                    $isCovered = true;
                    $matchedKeywords[] = $keyword;
                }
            }

            $coverage[] = [
                'point' => $point,
                'covered' => $isCovered,
                'matched_keywords' => $matchedKeywords,
            ];

            if ($isCovered) $coveredCount++;
        }

        $totalPoints = count($points);

        return [
            'total_points' => $totalPoints,
            'covered_points' => $coveredCount,
            'coverage_percentage' => $totalPoints > 0 ? round(($coveredCount / $totalPoints) * 100, 1) : 0,
            'details' => $coverage,
        ];
    }

    /**
     * Extract keywords from a cue card point
     */
    protected function extractKeywords(string $point): array
    {
        // Remove common words and extract meaningful keywords
        $stopWords = ['the', 'a', 'an', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had',
            'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'shall',
            'this', 'that', 'these', 'those', 'it', 'its', 'what', 'which', 'who', 'whom', 'whose',
            'where', 'when', 'why', 'how', 'and', 'or', 'but', 'if', 'because', 'as', 'until', 'while',
            'of', 'at', 'by', 'for', 'with', 'about', 'against', 'between', 'into', 'through', 'during',
            'before', 'after', 'above', 'below', 'to', 'from', 'up', 'down', 'in', 'out', 'on', 'off',
            'over', 'under', 'again', 'further', 'then', 'once', 'you', 'your', 'describe', 'explain', 'say'];

        $words = preg_split('/\s+/', strtolower($point));
        $words = array_map(fn($w) => preg_replace('/[^a-z]/', '', $w), $words);
        $words = array_filter($words, fn($w) => strlen($w) > 2 && !in_array($w, $stopWords));

        return array_values(array_unique($words));
    }

    /**
     * Detect word/phrase repetitions
     */
    protected function detectRepetitions(string $transcription): array
    {
        $words = preg_split('/\s+/', strtolower($transcription));
        $repetitions = [];
        $count = 0;

        for ($i = 0; $i < count($words) - 1; $i++) {
            if ($words[$i] === $words[$i + 1] && strlen($words[$i]) > 2) {
                $repetitions[] = $words[$i] . ' ' . $words[$i + 1];
                $count++;
            }
        }

        return [
            'count' => $count,
            'examples' => array_unique($repetitions),
        ];
    }

    /**
     * Detect common idioms
     */
    protected function detectIdioms(string $transcription): array
    {
        $idioms = [
            'piece of cake', 'break the ice', 'hit the books', 'once in a blue moon',
            'the best of both worlds', 'speak of the devil', 'see eye to eye', 'under the weather',
            'cost an arm and a leg', 'break a leg', 'bite the bullet', 'call it a day',
            'get out of hand', 'hang in there', 'it takes two to tango', 'let the cat out of the bag',
            'miss the boat', 'on the ball', 'pull someone\'s leg', 'so far so good',
            'the ball is in your court', 'time flies', 'to get bent out of shape', 'your guess is as good as mine',
            'at the end of the day', 'in a nutshell', 'on the same page', 'think outside the box',
        ];

        $found = [];
        foreach ($idioms as $idiom) {
            if (stripos($transcription, $idiom) !== false) {
                $found[] = $idiom;
            }
        }

        return $found;
    }

    /**
     * Rate lexical diversity based on TTR
     */
    protected function rateLexicalDiversity(float $ttr): string
    {
        if ($ttr >= 60) return 'Excellent';
        if ($ttr >= 50) return 'Good';
        if ($ttr >= 40) return 'Adequate';
        if ($ttr >= 30) return 'Limited';
        return 'Very Limited';
    }

    /**
     * Rate filler word usage
     */
    protected function rateFillerUsage(float $percentage): string
    {
        if ($percentage <= 1) return 'Minimal';
        if ($percentage <= 3) return 'Occasional';
        if ($percentage <= 5) return 'Noticeable';
        if ($percentage <= 8) return 'Frequent';
        return 'Excessive';
    }

    /**
     * Rate coherence based on marker usage
     */
    protected function rateCoherence(int $totalMarkers, int $categoryCount): string
    {
        if ($totalMarkers >= 8 && $categoryCount >= 5) return 'Excellent';
        if ($totalMarkers >= 5 && $categoryCount >= 3) return 'Good';
        if ($totalMarkers >= 3 && $categoryCount >= 2) return 'Adequate';
        if ($totalMarkers >= 1) return 'Limited';
        return 'Minimal';
    }

    /**
     * Estimate vocabulary level (CEFR-style)
     */
    protected function estimateVocabularyLevel(float $ttr, int $academicCount, int $complexCount): string
    {
        $score = 0;
        if ($ttr >= 50) $score += 2;
        elseif ($ttr >= 40) $score += 1;

        if ($academicCount >= 5) $score += 2;
        elseif ($academicCount >= 2) $score += 1;

        if ($complexCount >= 10) $score += 2;
        elseif ($complexCount >= 5) $score += 1;

        if ($score >= 5) return 'C1-C2 (Advanced)';
        if ($score >= 3) return 'B2 (Upper-Intermediate)';
        if ($score >= 2) return 'B1 (Intermediate)';
        return 'A2-B1 (Elementary-Intermediate)';
    }

    /**
     * Rate Part 3 analytical depth
     */
    protected function ratePart3Depth(int $abstract, int $speculation, int $comparisons, bool $hasJustification): string
    {
        $score = $abstract + $speculation + $comparisons + ($hasJustification ? 2 : 0);

        if ($score >= 6) return 'Excellent analytical depth';
        if ($score >= 4) return 'Good analytical depth';
        if ($score >= 2) return 'Adequate analysis';
        return 'Limited analysis - try to expand ideas';
    }

    /**
     * Evaluate part length status
     */
    protected function evaluatePartLength(int $wordCount, int $minExpected, int $maxExpected): string
    {
        if ($wordCount < $minExpected * 0.6) return 'Too Short';
        if ($wordCount < $minExpected) return 'Slightly Short';
        if ($wordCount > $maxExpected * 1.3) return 'Too Long';
        if ($wordCount > $maxExpected) return 'Slightly Long';
        return 'Appropriate';
    }

    /**
     * Return empty analysis structure
     */
    protected function getEmptyAnalysis(): array
    {
        return [
            'basic_stats' => [
                'word_count' => 0,
                'sentence_count' => 0,
                'avg_sentence_length' => 0,
                'avg_word_length' => 0,
            ],
            'lexical_analysis' => [
                'unique_words' => 0,
                'lexical_diversity_ttr' => 0,
                'lexical_diversity_rating' => 'N/A',
                'complex_word_count' => 0,
                'academic_words' => [],
                'idioms_detected' => [],
            ],
            'fluency_indicators' => [
                'filler_words' => [],
                'filler_count_total' => 0,
                'filler_percentage' => 0,
                'filler_rating' => 'N/A',
                'repetition_count' => 0,
            ],
            'coherence_markers' => [
                'markers_by_category' => [],
                'total_markers' => 0,
                'coherence_rating' => 'N/A',
            ],
            'part_specific' => [],
        ];
    }
}
