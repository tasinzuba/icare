<?php

namespace App\Services\AI;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class AIEvaluationService
{
    private $model = 'gpt-4';
    private $temperature = 0.3;
    private $timeout = 120; // 2 minutes timeout
    private $currentTaskNumber = null; // Track current task number

    /**
     * Evaluate IELTS Writing
     */
    public function evaluateWriting(string $text, string $question, int $taskNumber): array
    {
        try {
            Log::info('Starting writing evaluation', [
                'text_length' => strlen($text),
                'task_number' => $taskNumber,
                'has_api_key' => !empty(config('openai.api_key'))
            ]);

            // Check API key
            if (empty(config('openai.api_key'))) {
                throw new Exception('OpenAI API key is not configured');
            }

            $prompt = $this->buildWritingPrompt($text, $question, $taskNumber);
            
            // Fix: Use the facade directly without creating new client
            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $this->getWritingSystemPrompt()],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => $this->temperature,
                'max_tokens' => 2000,
            ]);

            $content = $response->choices[0]->message->content;
            Log::info('AI Response received', ['response_length' => strlen($content)]);

            $evaluation = json_decode($content, true);
            
            if (!$evaluation) {
                Log::error('Failed to parse AI response', ['response' => $content]);
                throw new Exception('Failed to parse AI response');
            }

            return $this->formatWritingEvaluation($evaluation, $text);

        } catch (\Exception $e) {
            Log::error('AI Writing evaluation failed', [
                'error' => $e->getMessage(),
                'text_length' => strlen($text),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Evaluate IELTS Speaking with enhanced analysis
     */
    public function evaluateSpeaking(string $audioPath, string $question, int $partNumber, ?string $cueCardPoints = null, ?float $audioDuration = null): array
    {
        try {
            Log::info('Starting enhanced speaking evaluation', [
                'audio_path' => $audioPath,
                'part_number' => $partNumber,
                'has_cue_card' => !empty($cueCardPoints)
            ]);

            // First, transcribe the audio
            $transcription = $this->transcribeAudio($audioPath);

            if (empty($transcription)) {
                throw new Exception('Failed to transcribe audio - no speech detected');
            }

            Log::info('Transcription complete', ['length' => strlen($transcription)]);

            // Pre-evaluation text analysis
            $textAnalyzer = new SpeakingTextAnalyzer();
            $preAnalysis = $textAnalyzer->analyze($transcription, $partNumber, $cueCardPoints);

            Log::info('Pre-analysis complete', [
                'filler_count' => $preAnalysis['fluency_indicators']['filler_count_total'] ?? 0,
                'lexical_diversity' => $preAnalysis['lexical_analysis']['lexical_diversity_ttr'] ?? 0,
                'coherence_markers' => $preAnalysis['coherence_markers']['total_markers'] ?? 0
            ]);

            // Calculate speech rate if duration available
            $speechMetrics = $this->calculateSpeechMetrics(str_word_count($transcription), $audioDuration);

            // Build enhanced prompt with pre-analysis data
            $prompt = $this->buildEnhancedSpeakingPrompt($transcription, $question, $partNumber, $preAnalysis, $speechMetrics, $cueCardPoints);

            // Call GPT-4 with enhanced prompt
            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $this->getSpeakingSystemPrompt()],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => $this->temperature,
                'max_tokens' => 3000,
            ]);

            $content = $response->choices[0]->message->content;
            $evaluation = json_decode($content, true);

            if (!$evaluation) {
                Log::error('Failed to parse AI response for speaking');
                throw new Exception('Failed to parse AI response');
            }

            return $this->formatEnhancedSpeakingEvaluation($evaluation, $transcription, $preAnalysis, $speechMetrics, $audioDuration);

        } catch (\Exception $e) {
            Log::error('AI Speaking evaluation failed', [
                'error' => $e->getMessage(),
                'audio_path' => $audioPath,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate speech rate metrics from word count and duration
     */
    protected function calculateSpeechMetrics(int $wordCount, ?float $duration): array
    {
        if (!$duration || $duration <= 0) {
            return [
                'speech_rate_wpm' => 0,
                'speech_rate_rating' => 'Unknown',
                'duration_seconds' => 0,
            ];
        }

        $wpm = round(($wordCount / $duration) * 60);

        $rating = 'Normal';
        if ($wpm < 100) $rating = 'Slow (may indicate hesitation)';
        elseif ($wpm < 120) $rating = 'Slightly Slow';
        elseif ($wpm > 180) $rating = 'Very Fast (may affect clarity)';
        elseif ($wpm > 160) $rating = 'Fast';

        return [
            'speech_rate_wpm' => $wpm,
            'speech_rate_rating' => $rating,
            'duration_seconds' => round($duration, 1),
        ];
    }

    /**
     * Transcribe audio using Whisper API
     */
    protected function transcribeAudio(string $audioPath): string
    {
        try {
            $fullPath = $audioPath;
            
            if (!file_exists($fullPath)) {
                throw new Exception("Audio file not found: {$fullPath}");
            }
            
            $fileSize = filesize($fullPath);
            if ($fileSize > 25 * 1024 * 1024) {
                throw new Exception("Audio file too large: {$fileSize} bytes");
            }
            
            Log::info('Starting audio transcription', [
                'path' => $audioPath,
                'size' => $fileSize
            ]);
            
            // Fix: Use OpenAI facade for transcription
            $response = OpenAI::audio()->transcribe([
                'model' => 'whisper-1',
                'file' => fopen($fullPath, 'r'),
                'response_format' => 'json',
                'language' => 'en'
            ]);
            
            if (!isset($response->text)) {
                throw new Exception('No transcription text in response');
            }
            
            Log::info('Transcription successful', [
                'text_length' => strlen($response->text)
            ]);
            
            return $response->text;
            
        } catch (\Exception $e) {
            Log::error('Audio transcription failed', [
                'error' => $e->getMessage(),
                'path' => $audioPath
            ]);
            throw $e;
        }
    }

    // Rest of the methods remain the same...
    protected function buildWritingPrompt(string $text, string $question, int $taskNumber): string
    {
        // Set current task number for system prompt
        $this->currentTaskNumber = $taskNumber;

        $wordCount = str_word_count($text);
        $requiredWords = $taskNumber === 1 ? 150 : 250;

        // Calculate word count penalty
        $wordCountNote = "";
        if ($wordCount < $requiredWords) {
            $penalty = $requiredWords - $wordCount;
            $wordCountNote = "CRITICAL: Essay is {$penalty} words SHORT of minimum requirement. This MUST significantly reduce Task Achievement score (max 5.0 if under word count).";
        }

        return "STRICT IELTS Writing Task {$taskNumber} Evaluation

Question: {$question}
Word Count: {$wordCount} (Required: {$requiredWords}+)
{$wordCountNote}

Essay:
{$text}

=== STRICT SCORING RULES (MUST FOLLOW) ===

1. WORD COUNT PENALTY:
   - Under {$requiredWords} words = Task Achievement CANNOT exceed 5.0
   - Under " . ($requiredWords - 50) . " words = Task Achievement CANNOT exceed 4.0

2. BAND SCORE GUIDELINES (Be STRICT, not generous):
   - Band 9.0: Near-perfect, native-level writing (VERY RARE)
   - Band 8.0-8.5: Excellent with minimal errors (uncommon)
   - Band 7.0-7.5: Good control, some errors, clear ideas
   - Band 6.0-6.5: Competent but noticeable errors, basic vocabulary
   - Band 5.0-5.5: Limited control, frequent errors, repetitive vocabulary
   - Band 4.0-4.5: Very limited, many errors, unclear meaning
   - Below 4.0: Extremely weak, incomprehensible

3. OVERALL BAND CALCULATION:
   overall_band = (task_achievement + coherence_cohesion + lexical_resource + grammar) / 4
   Round to nearest 0.5 (e.g., 6.25 → 6.5, 6.1 → 6.0)

4. DO NOT inflate scores to make user happy. Real feedback helps improvement.

5. Common issues that REDUCE scores:
   - Memorized phrases/templates = reduce authenticity
   - Off-topic content = severely reduce Task Achievement
   - Repetitive vocabulary = reduce Lexical Resource
   - Simple sentences only = reduce Grammar score
   - No paragraphs = reduce Coherence score

Provide evaluation in JSON format:
{
    \"overall_band_score\": 0.0,
    \"task_achievement_score\": 0.0,
    \"coherence_cohesion_score\": 0.0,
    \"lexical_resource_score\": 0.0,
    \"grammar_score\": 0.0,
    \"task_achievement_feedback\": \"Specific feedback...\",
    \"coherence_cohesion_feedback\": \"Specific feedback...\",
    \"lexical_resource_feedback\": \"Specific feedback...\",
    \"grammar_feedback\": \"Specific feedback...\",
    \"grammar_errors\": [\"Error 1\", \"Error 2\"],
    \"grammar_error_types\": [{\"type\": \"Articles\", \"count\": 2}],
    \"vocabulary_suggestions\": [{\"original\": \"word\", \"suggested\": \"better_word\", \"reason\": \"Why\"}],
    \"grammar_corrections\": [{\"original\": \"error\", \"corrected\": \"correct\", \"type\": \"Error type\"}],
    \"improvement_tips\": [\"Specific actionable tip 1\", \"Specific actionable tip 2\", \"Specific actionable tip 3\"],
    \"overall_strengths\": [\"Specific strength 1 based on essay\", \"Specific strength 2 based on essay\"],
    \"overall_improvements\": [\"Specific area to improve 1\", \"Specific area to improve 2\"],
    \"vocabulary_level\": \"A1/A2/B1/B2/C1/C2\",
    \"academic_words_used\": [\"word1\", \"word2\"],
    \"cohesive_devices\": [\"however\", \"therefore\"],
    \"sentence_variety_score\": 5,
    \"paragraph_structure\": [{\"paragraph\": 1, \"strength\": \"...\", \"suggestion\": \"...\"}]
}";
    }

    protected function buildSpeakingPrompt(string $transcription, string $question, int $partNumber): string
    {
        $wordCount = str_word_count($transcription);
        $duration = $this->estimateSpeakingDuration($wordCount);

        // Part-specific expectations
        $partExpectations = $this->getPartExpectations($partNumber);

        return "STRICT IELTS Speaking Part {$partNumber} Evaluation

=== QUESTION ===
{$question}

=== CANDIDATE RESPONSE ===
Duration: {$duration} | Word Count: {$wordCount}

\"{$transcription}\"

=== PART {$partNumber} EXPECTATIONS ===
{$partExpectations}

=== OFFICIAL IELTS BAND DESCRIPTORS (SPEAKING) ===

FLUENCY AND COHERENCE:
- 9: Fluent with only rare repetition or self-correction; any hesitation is content-related
- 8: Fluent with occasional repetition or self-correction; hesitation is usually content-related
- 7: Speaks at length without noticeable effort; may demonstrate language-related hesitation
- 6: Willing to speak at length but coherence is lost at times; uses connectives but not always appropriately
- 5: Usually maintains flow of speech but uses repetition and self-correction; may slow down
- 4: Cannot respond without noticeable pauses; may speak slowly with frequent repetition

LEXICAL RESOURCE:
- 9: Uses vocabulary with full flexibility and precision; uses idiomatic language naturally
- 8: Uses a wide vocabulary readily and flexibly; uses less common and idiomatic vocabulary skillfully
- 7: Uses vocabulary resource flexibly; uses some less common and idiomatic vocabulary
- 6: Has a wide enough vocabulary to discuss topics at length; generally paraphrases successfully
- 5: Manages to talk about familiar and unfamiliar topics but uses vocabulary with limited flexibility
- 4: Uses basic vocabulary; unable to express precise meanings

GRAMMATICAL RANGE AND ACCURACY:
- 9: Uses a full range of structures naturally and appropriately; produces consistently accurate structures
- 8: Uses a wide range of structures flexibly; produces a majority of error-free sentences
- 7: Uses a range of complex structures with some flexibility; frequently produces error-free sentences
- 6: Uses a mix of simple and complex structures but with limited flexibility; errors may occur but rarely cause problems
- 5: Produces basic sentence forms with reasonable accuracy; uses subordinate clauses but errors are frequent
- 4: Produces basic sentence forms; makes frequent errors that may impede communication

PRONUNCIATION:
- 9: Uses full range of pronunciation features with precision and subtlety
- 8: Uses a wide range of pronunciation features; sustains flexible use of features with only occasional lapses
- 7: Shows all positive features of Band 6 and some features of Band 8
- 6: Uses a range of pronunciation features with mixed control; can generally be understood throughout
- 5: Shows limited control of pronunciation features; mispronunciations are frequent
- 4: Uses a limited range of pronunciation features; frequent mispronunciations cause comprehension problems

=== SCORING RULES ===
1. Be STRICT and REALISTIC - most candidates score 5.0-6.5
2. Band 7.0+ requires genuinely good performance
3. Band 8.0+ is RARE and requires near-native ability
4. Calculate: overall_band = (fluency + lexical + grammar + pronunciation) / 4, rounded to nearest 0.5
5. Consider response LENGTH - very short responses cannot score high on fluency

=== RESPONSE FORMAT (JSON ONLY) ===
{
    \"overall_band_score\": 0.0,
    \"fluency_coherence_score\": 0.0,
    \"lexical_resource_score\": 0.0,
    \"grammar_score\": 0.0,
    \"pronunciation_score\": 0.0,
    \"fluency_coherence_feedback\": \"Detailed feedback citing specific examples from the response...\",
    \"lexical_resource_feedback\": \"Detailed feedback on vocabulary usage with examples...\",
    \"grammar_feedback\": \"Detailed feedback on grammar with specific error examples...\",
    \"pronunciation_feedback\": \"Feedback based on word choices indicating pronunciation patterns...\",
    \"pronunciation_issues\": [\"Specific words/sounds that may be challenging\"],
    \"vocabulary_range\": [\"Notable vocabulary used by candidate\"],
    \"grammar_errors\": [\"Specific grammar errors found in the response\"],
    \"improvement_tips\": [\"Actionable tip 1\", \"Actionable tip 2\", \"Actionable tip 3\"],
    \"overall_strengths\": [\"Specific strength 1\", \"Specific strength 2\"],
    \"overall_improvements\": [\"Priority improvement area 1\", \"Priority improvement area 2\"],
    \"study_plan\": [\"Daily practice recommendation\", \"Resource recommendation\"]
}";
    }

    /**
     * Get part-specific expectations for speaking evaluation
     */
    protected function getPartExpectations(int $partNumber): string
    {
        switch ($partNumber) {
            case 1:
                return "Part 1 (Introduction): 4-5 minutes, familiar topics about home, work, studies, interests.
Expected: Natural, conversational responses. 2-3 sentences per answer. Shows personality.
Good responses: Extend answers naturally, give reasons, use personal examples.
Weak responses: One-word answers, memorized phrases, off-topic tangents.";

            case 2:
                return "Part 2 (Long Turn/Cue Card): 1 minute preparation, 1-2 minutes speaking.
Expected: Organized monologue covering all cue card points. Uses discourse markers.
Good responses: Clear structure, covers all points, adds relevant details, concludes naturally.
Weak responses: Too short (<1 minute), misses points, disorganized, runs out of ideas.
CRITICAL: Response should be 150-200 words minimum for adequate evaluation.";

            case 3:
                return "Part 3 (Discussion): 4-5 minutes, abstract/analytical discussion related to Part 2 topic.
Expected: Express and justify opinions, discuss abstract ideas, speculate, compare.
Good responses: Develops ideas fully, uses complex structures, shows critical thinking.
Weak responses: Simple yes/no, cannot expand ideas, relies on Part 2 content.";

            default:
                return "General IELTS Speaking: Natural conversation demonstrating English proficiency.";
        }
    }

    /**
     * Build enhanced speaking prompt with pre-analysis metrics
     */
    protected function buildEnhancedSpeakingPrompt(
        string $transcription,
        string $question,
        int $partNumber,
        array $preAnalysis,
        array $speechMetrics,
        ?string $cueCardPoints
    ): string {
        $wordCount = str_word_count($transcription);
        $duration = $this->estimateSpeakingDuration($wordCount);
        $partExpectations = $this->getPartExpectations($partNumber);

        // Format pre-analysis metrics
        $fillerInfo = $this->formatFillerAnalysis($preAnalysis['fluency_indicators'] ?? []);
        $lexicalInfo = $this->formatLexicalAnalysis($preAnalysis['lexical_analysis'] ?? []);
        $coherenceInfo = $this->formatCoherenceAnalysis($preAnalysis['coherence_markers'] ?? []);
        $partSpecificInfo = $this->formatPartSpecificAnalysis($preAnalysis['part_specific'] ?? [], $partNumber);

        // Cue card coverage for Part 2
        $cueCardInfo = '';
        if ($partNumber == 2 && !empty($preAnalysis['part_specific']['cue_card_coverage'])) {
            $coverage = $preAnalysis['part_specific']['cue_card_coverage'];
            $cueCardInfo = "\n=== CUE CARD COVERAGE ===
Points covered: {$coverage['covered_points']}/{$coverage['total_points']} ({$coverage['coverage_percentage']}%)";
            if (!empty($coverage['details'])) {
                foreach ($coverage['details'] as $detail) {
                    $status = $detail['covered'] ? '✓' : '✗';
                    $cueCardInfo .= "\n{$status} {$detail['point']}";
                }
            }
        }

        // Speech rate info
        $speechInfo = '';
        if ($speechMetrics['speech_rate_wpm'] > 0) {
            $speechInfo = "\nSpeech Rate: {$speechMetrics['speech_rate_wpm']} WPM ({$speechMetrics['speech_rate_rating']})";
        }

        return "STRICT IELTS Speaking Part {$partNumber} Evaluation

=== QUESTION ===
{$question}

=== CANDIDATE RESPONSE ===
Duration: {$duration} | Word Count: {$wordCount}{$speechInfo}

\"{$transcription}\"

=== PRE-ANALYSIS METRICS ===
{$fillerInfo}
{$lexicalInfo}
{$coherenceInfo}
{$partSpecificInfo}
{$cueCardInfo}

=== PART {$partNumber} EXPECTATIONS ===
{$partExpectations}

=== SCORING GUIDELINES BASED ON METRICS ===
1. Filler words >5% = max Fluency score 6.0
2. Lexical diversity <35% = max Lexical score 5.5
3. No coherence markers = max Coherence 5.5
4. Part 2 with <75% cue card coverage = max Task Achievement 6.0
5. Response under minimum length = reduce all scores by 0.5-1.0

=== OFFICIAL IELTS BAND DESCRIPTORS (SPEAKING) ===

FLUENCY AND COHERENCE:
- 9: Fluent with only rare repetition; any hesitation is content-related
- 8: Fluent with occasional repetition; hesitation usually content-related
- 7: Speaks at length without noticeable effort; may have language-related hesitation
- 6: Willing to speak at length but coherence lost at times
- 5: Usually maintains flow but uses repetition and self-correction
- 4: Cannot respond without noticeable pauses; may speak slowly

LEXICAL RESOURCE:
- 9: Uses vocabulary with full flexibility; idiomatic language naturally
- 8: Wide vocabulary readily and flexibly; less common vocabulary skillfully
- 7: Vocabulary resource flexibly; some idiomatic vocabulary
- 6: Wide enough vocabulary to discuss topics at length
- 5: Manages topics but uses vocabulary with limited flexibility
- 4: Uses basic vocabulary; unable to express precise meanings

GRAMMATICAL RANGE AND ACCURACY:
- 9: Full range of structures naturally; consistently accurate
- 8: Wide range of structures flexibly; majority error-free sentences
- 7: Range of complex structures; frequently error-free sentences
- 6: Mix of simple and complex structures with limited flexibility
- 5: Basic sentence forms with reasonable accuracy
- 4: Basic sentence forms; frequent errors may impede communication

PRONUNCIATION:
- 9: Full range of pronunciation features with precision
- 8: Wide range of features; sustains flexible use with occasional lapses
- 7: Shows Band 6 positives and some Band 8 features
- 6: Range of features with mixed control; generally understood
- 5: Limited control; mispronunciations frequent
- 4: Limited range; frequent mispronunciations cause problems

=== RESPONSE FORMAT (JSON ONLY) ===
{
    \"overall_band_score\": 0.0,
    \"fluency_coherence_score\": 0.0,
    \"lexical_resource_score\": 0.0,
    \"grammar_score\": 0.0,
    \"pronunciation_score\": 0.0,
    \"task_achievement\": {
        \"answered_question\": true,
        \"relevance_percentage\": 0,
        \"on_topic\": true,
        \"issues\": []
    },
    \"fluency_coherence_feedback\": \"Detailed feedback with specific examples...\",
    \"lexical_resource_feedback\": \"Vocabulary analysis with examples...\",
    \"grammar_feedback\": \"Grammar analysis with specific errors...\",
    \"pronunciation_feedback\": \"Inferred pronunciation patterns...\",
    \"coherence_analysis\": {
        \"logical_flow\": \"Good/Fair/Poor\",
        \"organization_issues\": []
    },
    \"grammar_errors\": [
        {\"error\": \"original text\", \"correction\": \"corrected\", \"type\": \"Error type\"}
    ],
    \"pronunciation_issues\": [\"specific words/sounds\"],
    \"vocabulary_range\": [\"notable vocabulary used\"],
    \"improvement_tips\": [\"Tip 1\", \"Tip 2\", \"Tip 3\"],
    \"overall_strengths\": [\"Strength 1\", \"Strength 2\"],
    \"overall_improvements\": [\"Area 1\", \"Area 2\"],
    \"study_plan\": [\"Recommendation 1\", \"Recommendation 2\"]
}";
    }

    /**
     * Format filler word analysis for prompt
     */
    protected function formatFillerAnalysis(array $fluency): string
    {
        $count = $fluency['filler_count_total'] ?? 0;
        $percentage = $fluency['filler_percentage'] ?? 0;
        $rating = $fluency['filler_rating'] ?? 'Unknown';

        $fillers = [];
        foreach (($fluency['filler_words'] ?? []) as $word => $wordCount) {
            $fillers[] = "{$word}({$wordCount}x)";
        }
        $fillerList = !empty($fillers) ? implode(', ', $fillers) : 'None detected';

        return "FILLER WORDS: {$count} total ({$percentage}%) - {$rating}
  Detected: {$fillerList}
  Repetitions: " . ($fluency['repetition_count'] ?? 0);
    }

    /**
     * Format lexical analysis for prompt
     */
    protected function formatLexicalAnalysis(array $lexical): string
    {
        $ttr = $lexical['lexical_diversity_ttr'] ?? 0;
        $rating = $lexical['lexical_diversity_rating'] ?? 'Unknown';
        $academicCount = $lexical['academic_word_count'] ?? 0;
        $complexCount = $lexical['complex_word_count'] ?? 0;
        $idioms = count($lexical['idioms_detected'] ?? []);

        return "LEXICAL RESOURCE:
  Diversity (TTR): {$ttr}% ({$rating})
  Academic words: {$academicCount}
  Complex words: {$complexCount}
  Idioms: {$idioms}
  Level: " . ($lexical['vocabulary_level'] ?? 'Unknown');
    }

    /**
     * Format coherence analysis for prompt
     */
    protected function formatCoherenceAnalysis(array $coherence): string
    {
        $total = $coherence['total_markers'] ?? 0;
        $categories = $coherence['category_count'] ?? 0;
        $rating = $coherence['coherence_rating'] ?? 'Unknown';

        $markers = [];
        foreach (($coherence['markers_by_category'] ?? []) as $category => $words) {
            $markers[] = ucfirst(str_replace('_', ' ', $category)) . ': ' . implode(', ', $words);
        }
        $markerList = !empty($markers) ? implode('; ', $markers) : 'None detected';

        return "COHERENCE MARKERS: {$total} total, {$categories} categories ({$rating})
  {$markerList}";
    }

    /**
     * Format part-specific analysis for prompt
     */
    protected function formatPartSpecificAnalysis(array $partAnalysis, int $partNumber): string
    {
        if (empty($partAnalysis)) return '';

        $issues = $partAnalysis['issues'] ?? [];
        $issueList = !empty($issues) ? implode('; ', $issues) : 'None';

        $info = "\nPART {$partNumber} ANALYSIS:
  Length: {$partAnalysis['actual_length']} words ({$partAnalysis['length_status']})
  Issues: {$issueList}";

        if ($partNumber == 1) {
            $info .= "\n  Has reasons: " . ($partAnalysis['has_reasons'] ? 'Yes' : 'No');
            $info .= "\n  Personal examples: " . ($partAnalysis['has_personal_examples'] ? 'Yes' : 'No');
        } elseif ($partNumber == 2) {
            $info .= "\n  Has introduction: " . ($partAnalysis['has_introduction'] ? 'Yes' : 'No');
            $info .= "\n  Has conclusion: " . ($partAnalysis['has_conclusion'] ? 'Yes' : 'No');
            $info .= "\n  Structure markers: " . ($partAnalysis['structure_markers_count'] ?? 0);
        } elseif ($partNumber == 3) {
            $info .= "\n  Has opinion: " . ($partAnalysis['has_opinion'] ? 'Yes' : 'No');
            $info .= "\n  Has justification: " . ($partAnalysis['has_justification'] ? 'Yes' : 'No');
            $info .= "\n  Abstract thinking: " . ($partAnalysis['abstract_thinking_count'] ?? 0);
            $info .= "\n  Analytical depth: " . ($partAnalysis['analytical_depth'] ?? 'Unknown');
        }

        return $info;
    }

    protected function getWritingSystemPrompt(): string
    {
        // Determine which writing task prompt to use based on task number
        $taskType = ($this->currentTaskNumber === 1) ? 'writing_task1' : 'writing_task2';

        // Get prompt from config
        $prompt = config("ai-prompts.{$taskType}");

        // Fallback to strict default if config not found
        if (!$prompt) {
            return "You are a STRICT certified IELTS examiner with 20+ years experience.

YOUR SCORING PHILOSOPHY:
- You evaluate EXACTLY like a real IELTS examiner
- You NEVER inflate scores to make candidates happy
- Honest, accurate feedback helps candidates improve
- Most candidates score between 5.0-6.5 (this is normal)
- Band 7.0+ requires genuinely good writing
- Band 8.0+ is rare and requires near-perfect writing

OFFICIAL IELTS BAND DESCRIPTORS:

TASK ACHIEVEMENT (Task 2) / TASK RESPONSE (Task 1):
- 9: Fully addresses all parts, fully developed position, relevant, extended ideas
- 7: Addresses all parts, clear position throughout, main ideas extended but may over-generalize
- 6: Addresses all parts but some more fully than others, presents position but not always clear
- 5: Addresses task only partially, position unclear, limited development
- 4: Responds minimally, position unclear, few ideas

COHERENCE AND COHESION:
- 9: Skilful paragraphing, cohesion is never noticeable
- 7: Logically organized, clear progression, good use of cohesive devices
- 6: Arranges ideas coherently, uses cohesive devices but may be mechanical
- 5: Some organization but lacks overall progression, inadequate/overused linking
- 4: Information and ideas not arranged coherently, limited linking

LEXICAL RESOURCE:
- 9: Wide range, natural, sophisticated, rare errors
- 7: Sufficient range, some less common items, occasional errors in word choice
- 6: Adequate range, attempts less common vocabulary with some errors
- 5: Limited range, noticeable errors, repetitive
- 4: Basic vocabulary, frequent errors, meaning unclear

GRAMMATICAL RANGE AND ACCURACY:
- 9: Wide range of structures, full flexibility, rare errors
- 7: Variety of complex structures, majority error-free, good control
- 6: Mix of simple/complex sentences, some errors but rarely reduce communication
- 5: Limited range, frequent errors, may cause difficulty
- 4: Very limited range, errors are frequent and may distort meaning

CRITICAL RULES:
1. Calculate overall_band = average of 4 criteria, rounded to nearest 0.5
2. Under word count = max Task Achievement 5.0
3. Off-topic = max Task Achievement 4.0
4. Return ONLY valid JSON, no other text";
        }

        return $prompt;
    }

    protected function getSpeakingSystemPrompt(): string
    {
        // Get prompt from config
        $prompt = config('ai-prompts.speaking');

        // Fallback to comprehensive default if config not found
        if (!$prompt) {
            return "You are a STRICT certified IELTS Speaking examiner with 20+ years of experience.

YOUR ROLE:
- You evaluate spoken English responses based on transcriptions
- You follow OFFICIAL IELTS band descriptors exactly
- You provide HONEST, ACCURATE assessments that help candidates improve
- You NEVER inflate scores to make candidates happy

IMPORTANT CONTEXT:
- You are evaluating a TRANSCRIPTION of spoken audio
- Focus on grammar, vocabulary, coherence, and content quality
- For pronunciation, analyze word choice patterns and likely pronunciation challenges
- Consider that transcriptions may not perfectly capture spoken delivery

SCORING PHILOSOPHY:
- Most candidates genuinely score between 5.0-6.5 (this is realistic)
- Band 7.0+ requires genuinely strong performance with few errors
- Band 8.0+ is RARE - requires near-native fluency and sophistication
- Band 9.0 is almost NEVER given - requires native-level mastery
- Short responses (< 50 words) cannot score above 5.0 for fluency
- Very short responses (< 20 words) indicate Band 4.0 or below

EVALUATION APPROACH:
1. Read the full transcription carefully
2. Identify specific examples of good/weak language use
3. Count and categorize grammar errors
4. Assess vocabulary range and sophistication
5. Evaluate coherence and organization
6. Consider appropriateness for the part type (1, 2, or 3)
7. Calculate the average of 4 criteria, round to nearest 0.5

OUTPUT REQUIREMENTS:
- Return ONLY valid JSON, no other text
- Provide specific examples from the response in feedback
- List actual grammar errors found
- Suggest concrete improvements
- Be constructive but honest";
        }

        return $prompt;
    }

    protected function formatWritingEvaluation(array $evaluation, string $originalText): array
    {
        // Calculate text statistics
        $textStats = $this->calculateTextStatistics($originalText);

        // Get individual criteria scores
        $taskAchievement = floatval($evaluation['task_achievement_score'] ?? 0);
        $coherenceCohesion = floatval($evaluation['coherence_cohesion_score'] ?? 0);
        $lexicalResource = floatval($evaluation['lexical_resource_score'] ?? 0);
        $grammar = floatval($evaluation['grammar_score'] ?? 0);

        // Calculate overall band score properly (average of 4 criteria, rounded to nearest 0.5)
        $averageScore = ($taskAchievement + $coherenceCohesion + $lexicalResource + $grammar) / 4;
        $overallBand = round($averageScore * 2) / 2; // Round to nearest 0.5

        // Ensure scores are within valid IELTS range (0-9)
        $overallBand = max(0, min(9, $overallBand));
        $taskAchievement = max(0, min(9, $taskAchievement));
        $coherenceCohesion = max(0, min(9, $coherenceCohesion));
        $lexicalResource = max(0, min(9, $lexicalResource));
        $grammar = max(0, min(9, $grammar));

        return [
            'band_score' => $overallBand,
            'criteria' => [
                'Task Achievement' => $taskAchievement,
                'Coherence and Cohesion' => $coherenceCohesion,
                'Lexical Resource' => $lexicalResource,
                'Grammar' => $grammar,
            ],
            'feedback' => [
                'task_achievement' => $evaluation['task_achievement_feedback'] ?? '',
                'coherence_cohesion' => $evaluation['coherence_cohesion_feedback'] ?? '',
                'lexical_resource' => $evaluation['lexical_resource_feedback'] ?? '',
                'grammar' => $evaluation['grammar_feedback'] ?? '',
            ],
            'word_count' => str_word_count($originalText),
            'grammar_errors' => $evaluation['grammar_errors'] ?? [],
            'grammar_error_types' => $evaluation['grammar_error_types'] ?? [],
            'grammar_corrections' => $evaluation['grammar_corrections'] ?? [],
            'vocabulary_suggestions' => $evaluation['vocabulary_suggestions'] ?? [],
            'improvement_tips' => $evaluation['improvement_tips'] ?? [],
            'overall_strengths' => $evaluation['overall_strengths'] ?? [],
            'overall_improvements' => $evaluation['overall_improvements'] ?? [],
            'original_text' => $originalText,
            // New data-driven fields
            'vocabulary_level' => $evaluation['vocabulary_level'] ?? 'B1',
            'academic_words_used' => $evaluation['academic_words_used'] ?? [],
            'cohesive_devices' => $evaluation['cohesive_devices'] ?? [],
            'sentence_variety_score' => $evaluation['sentence_variety_score'] ?? 5,
            'paragraph_structure' => $evaluation['paragraph_structure'] ?? [],
            'text_statistics' => $textStats,
        ];
    }

    /**
     * Calculate text statistics for data visualization
     */
    protected function calculateTextStatistics(string $text): array
    {
        // Word count
        $words = str_word_count($text, 1);
        $wordCount = count($words);

        // Unique words
        $uniqueWords = count(array_unique(array_map('strtolower', $words)));

        // Sentences (split by . ! ?)
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $sentenceCount = count($sentences);

        // Average sentence length
        $avgSentenceLength = $sentenceCount > 0 ? round($wordCount / $sentenceCount, 1) : 0;

        // Paragraphs
        $paragraphs = preg_split('/\n\s*\n/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        $paragraphCount = count($paragraphs);

        // Vocabulary richness (unique words / total words * 100)
        $vocabularyRichness = $wordCount > 0 ? round(($uniqueWords / $wordCount) * 100) : 0;

        // Long words (6+ characters)
        $longWords = array_filter($words, fn($w) => strlen($w) >= 6);
        $longWordPercentage = $wordCount > 0 ? round((count($longWords) / $wordCount) * 100) : 0;

        return [
            'word_count' => $wordCount,
            'unique_words' => $uniqueWords,
            'sentence_count' => $sentenceCount,
            'avg_sentence_length' => $avgSentenceLength,
            'paragraph_count' => $paragraphCount,
            'vocabulary_richness' => $vocabularyRichness,
            'long_word_percentage' => $longWordPercentage,
        ];
    }

    protected function formatSpeakingEvaluation(array $evaluation, string $transcription): array
    {
        // Get individual criteria scores
        $fluencyCoherence = floatval($evaluation['fluency_coherence_score'] ?? 0);
        $lexicalResource = floatval($evaluation['lexical_resource_score'] ?? 0);
        $grammar = floatval($evaluation['grammar_score'] ?? 0);
        $pronunciation = floatval($evaluation['pronunciation_score'] ?? 0);

        // Calculate overall band score properly (average of 4 criteria, rounded to nearest 0.5)
        $averageScore = ($fluencyCoherence + $lexicalResource + $grammar + $pronunciation) / 4;
        $overallBand = round($averageScore * 2) / 2; // Round to nearest 0.5

        // Ensure scores are within valid IELTS range (0-9)
        $overallBand = max(0, min(9, $overallBand));

        // Calculate additional metrics
        $wordCount = str_word_count($transcription);
        $sentenceCount = preg_match_all('/[.!?]+/', $transcription, $matches);
        $avgSentenceLength = $sentenceCount > 0 ? round($wordCount / $sentenceCount, 1) : $wordCount;

        return [
            'band_score' => $overallBand,
            'criteria' => [
                'Fluency and Coherence' => max(0, min(9, $fluencyCoherence)),
                'Lexical Resource' => max(0, min(9, $lexicalResource)),
                'Grammar' => max(0, min(9, $grammar)),
                'Pronunciation' => max(0, min(9, $pronunciation)),
            ],
            'feedback' => [
                'fluency_coherence' => $evaluation['fluency_coherence_feedback'] ?? '',
                'lexical_resource' => $evaluation['lexical_resource_feedback'] ?? '',
                'grammar' => $evaluation['grammar_feedback'] ?? '',
                'pronunciation' => $evaluation['pronunciation_feedback'] ?? '',
            ],
            'transcription' => $transcription,
            'word_count' => $wordCount,
            'sentence_count' => $sentenceCount,
            'avg_sentence_length' => $avgSentenceLength,
            'pronunciation_issues' => $evaluation['pronunciation_issues'] ?? [],
            'vocabulary_range' => $evaluation['vocabulary_range'] ?? [],
            'grammar_errors' => $evaluation['grammar_errors'] ?? [],
            'tips' => $evaluation['improvement_tips'] ?? [],
            'overall_strengths' => $evaluation['overall_strengths'] ?? [],
            'overall_improvements' => $evaluation['overall_improvements'] ?? [],
            'study_plan' => $evaluation['study_plan'] ?? [],
        ];
    }

    /**
     * Format enhanced speaking evaluation with all pre-analysis metrics
     */
    protected function formatEnhancedSpeakingEvaluation(
        array $evaluation,
        string $transcription,
        array $preAnalysis,
        array $speechMetrics,
        ?float $audioDuration
    ): array {
        // Get individual criteria scores
        $fluencyCoherence = floatval($evaluation['fluency_coherence_score'] ?? 0);
        $lexicalResource = floatval($evaluation['lexical_resource_score'] ?? 0);
        $grammar = floatval($evaluation['grammar_score'] ?? 0);
        $pronunciation = floatval($evaluation['pronunciation_score'] ?? 0);

        // Calculate overall band score properly (average of 4 criteria, rounded to nearest 0.5)
        $averageScore = ($fluencyCoherence + $lexicalResource + $grammar + $pronunciation) / 4;
        $overallBand = round($averageScore * 2) / 2;

        // Ensure scores are within valid IELTS range (0-9)
        $overallBand = max(0, min(9, $overallBand));

        // Calculate additional metrics
        $wordCount = str_word_count($transcription);
        $sentenceCount = preg_match_all('/[.!?]+/', $transcription, $matches);
        $avgSentenceLength = $sentenceCount > 0 ? round($wordCount / $sentenceCount, 1) : $wordCount;

        return [
            // Core evaluation
            'band_score' => $overallBand,
            'criteria' => [
                'Fluency and Coherence' => max(0, min(9, $fluencyCoherence)),
                'Lexical Resource' => max(0, min(9, $lexicalResource)),
                'Grammar' => max(0, min(9, $grammar)),
                'Pronunciation' => max(0, min(9, $pronunciation)),
            ],
            'feedback' => [
                'fluency_coherence' => $evaluation['fluency_coherence_feedback'] ?? '',
                'lexical_resource' => $evaluation['lexical_resource_feedback'] ?? '',
                'grammar' => $evaluation['grammar_feedback'] ?? '',
                'pronunciation' => $evaluation['pronunciation_feedback'] ?? '',
            ],
            'transcription' => $transcription,
            'word_count' => $wordCount,
            'sentence_count' => $sentenceCount,
            'avg_sentence_length' => $avgSentenceLength,

            // Task achievement analysis (from AI)
            'task_achievement' => $evaluation['task_achievement'] ?? null,

            // Coherence analysis (from AI)
            'coherence_analysis' => $evaluation['coherence_analysis'] ?? null,

            // Speech metrics
            'speech_metrics' => [
                'duration_seconds' => $audioDuration ?? ($speechMetrics['duration_seconds'] ?? 0),
                'speech_rate_wpm' => $speechMetrics['speech_rate_wpm'] ?? 0,
                'speech_rate_rating' => $speechMetrics['speech_rate_rating'] ?? 'Unknown',
            ],

            // Fluency metrics (filler words)
            'fluency_metrics' => [
                'filler_words' => $preAnalysis['fluency_indicators']['filler_words'] ?? [],
                'filler_count' => $preAnalysis['fluency_indicators']['filler_count_total'] ?? 0,
                'filler_percentage' => $preAnalysis['fluency_indicators']['filler_percentage'] ?? 0,
                'filler_rating' => $preAnalysis['fluency_indicators']['filler_rating'] ?? 'Unknown',
                'repetitions' => $preAnalysis['fluency_indicators']['repetition_count'] ?? 0,
                'self_corrections' => $preAnalysis['fluency_indicators']['self_corrections'] ?? 0,
            ],

            // Lexical metrics
            'lexical_metrics' => [
                'lexical_diversity' => $preAnalysis['lexical_analysis']['lexical_diversity_ttr'] ?? 0,
                'lexical_rating' => $preAnalysis['lexical_analysis']['lexical_diversity_rating'] ?? 'Unknown',
                'vocabulary_level' => $preAnalysis['lexical_analysis']['vocabulary_level'] ?? 'Unknown',
                'academic_words' => $preAnalysis['lexical_analysis']['academic_words'] ?? [],
                'idioms_detected' => $preAnalysis['lexical_analysis']['idioms_detected'] ?? [],
                'complex_word_count' => $preAnalysis['lexical_analysis']['complex_word_count'] ?? 0,
            ],

            // Coherence markers
            'coherence_metrics' => [
                'markers' => $preAnalysis['coherence_markers']['markers_by_category'] ?? [],
                'marker_count' => $preAnalysis['coherence_markers']['total_markers'] ?? 0,
                'category_count' => $preAnalysis['coherence_markers']['category_count'] ?? 0,
                'coherence_rating' => $preAnalysis['coherence_markers']['coherence_rating'] ?? 'Unknown',
                'missing_categories' => $preAnalysis['coherence_markers']['missing_categories'] ?? [],
            ],

            // Part-specific analysis
            'part_analysis' => $preAnalysis['part_specific'] ?? [],

            // Grammar errors (from AI with corrections)
            'grammar_errors' => $evaluation['grammar_errors'] ?? [],
            'pronunciation_issues' => $evaluation['pronunciation_issues'] ?? [],
            'vocabulary_range' => $evaluation['vocabulary_range'] ?? [],
            'tips' => $evaluation['improvement_tips'] ?? [],
            'overall_strengths' => $evaluation['overall_strengths'] ?? [],
            'overall_improvements' => $evaluation['overall_improvements'] ?? [],
            'study_plan' => $evaluation['study_plan'] ?? [],
        ];
    }

    protected function estimateSpeakingDuration(int $wordCount): string
    {
        $minutes = round($wordCount / 155, 1);

        if ($minutes < 1) {
            return round($minutes * 60) . ' seconds';
        }

        return $minutes . ' minutes';
    }

    /**
     * Generate explanation for a wrong answer
     */
    public function generateAnswerExplanation(
        string $questionContent,
        string $questionType,
        string $studentAnswer,
        string $correctAnswer,
        ?string $context = null,
        ?array $options = null
    ): array {
        try {
            Log::info('Generating answer explanation', [
                'question_type' => $questionType,
                'student_answer' => $studentAnswer,
                'correct_answer' => $correctAnswer
            ]);

            if (empty(config('openai.api_key'))) {
                throw new Exception('OpenAI API key is not configured');
            }

            $prompt = $this->buildExplanationPrompt(
                $questionContent,
                $questionType,
                $studentAnswer,
                $correctAnswer,
                $context,
                $options
            );

            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $this->getExplanationSystemPrompt()],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.3,
                'max_tokens' => 500,
            ]);

            $content = $response->choices[0]->message->content;
            Log::info('Explanation generated', ['response_length' => strlen($content)]);

            $explanation = json_decode($content, true);

            if (!$explanation) {
                // If JSON parsing fails, use the raw content
                return [
                    'explanation' => $content,
                    'tip' => null,
                    'success' => true
                ];
            }

            return [
                'explanation' => $explanation['explanation'] ?? $content,
                'tip' => $explanation['tip'] ?? null,
                'success' => true
            ];

        } catch (\Exception $e) {
            Log::error('Answer explanation generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Check for quota/billing errors
            $errorMessage = $e->getMessage();
            $userFriendlyMessage = 'Unable to generate explanation right now. Please try again later.';

            if (str_contains($errorMessage, 'quota') || str_contains($errorMessage, 'billing') || str_contains($errorMessage, 'exceeded')) {
                $userFriendlyMessage = 'AI explanation is temporarily unavailable. Please try again later.';
            } elseif (str_contains($errorMessage, 'rate limit') || str_contains($errorMessage, 'Rate limit')) {
                $userFriendlyMessage = 'Too many requests. Please wait a moment and try again.';
            } elseif (str_contains($errorMessage, 'API key') || str_contains($errorMessage, 'Unauthorized')) {
                $userFriendlyMessage = 'AI service is currently unavailable. Please try again later.';
            }

            return [
                'explanation' => null,
                'tip' => null,
                'success' => false,
                'error' => $userFriendlyMessage
            ];
        }
    }

    /**
     * Build prompt for answer explanation
     */
    protected function buildExplanationPrompt(
        string $questionContent,
        string $questionType,
        string $studentAnswer,
        string $correctAnswer,
        ?string $context,
        ?array $options
    ): string {
        $optionsText = '';
        if ($options && count($options) > 0) {
            $optionsText = "\n\nAvailable options:\n";
            foreach ($options as $opt) {
                $optionsText .= "- {$opt}\n";
            }
        }

        $contextText = $context ? "\n\nContext/Passage excerpt:\n{$context}" : '';

        return "IELTS {$questionType} Question Analysis

Question: {$questionContent}
{$optionsText}{$contextText}
Student Answer: {$studentAnswer}
Correct Answer: {$correctAnswer}

Task: Explain the mistake. Be strict and accurate. Use simple words.";
    }

    /**
     * System prompt for explanations
     */
    protected function getExplanationSystemPrompt(): string
    {
        return "You are a certified IELTS examiner with 15+ years of experience and Band 9 proficiency.

STRICT RULES:
- Be direct and precise. No unnecessary words.
- Explain WHY the correct answer is right using evidence from the question.
- Point out the specific mistake in student's answer.
- Use simple English that Band 5-6 students can understand.
- No flattery or emotional language. Just facts.
- Response MUST be valid JSON only. No extra text.

FORMAT:
{
    \"explanation\": \"2-3 sentences max. Direct explanation.\",
    \"tip\": \"One practical tip for this question type.\"
}";
    }
}