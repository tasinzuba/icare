<?php

namespace App\Traits;

trait HandlesDiagramQuestions
{
    /**
     * Get detailed results for diagram questions
     */
    public function getDetailedResultsAttribute(): array
    {
        if (!$this->question || $this->question->question_type !== 'plan_map_diagram') {
            return [];
        }
        
        $diagramData = $this->question->diagram_hotspots ?? [];
        $answerData = json_decode($this->answer, true) ?? [];
        
        $totalLabels = 0;
        $correctCount = 0;
        $labelResults = [];
        
        // Count based on labels
        if (isset($diagramData['labels']) && is_array($diagramData['labels'])) {
            $totalLabels = count($diagramData['labels']);
            
            foreach ($diagramData['labels'] as $index => $label) {
                $questionNumber = $label['question_number'] ?? ($index + 1);
                $studentAnswer = null;
                $correctAnswer = null;
                $isCorrect = false;
                
                // Find student answer for this label
                foreach ($answerData as $key => $value) {
                    if (isset($value['sub_index']) && $value['sub_index'] == $index) {
                        $studentAnswer = $value['answer'] ?? null;
                        $isCorrect = $value['is_correct'] ?? false;
                        break;
                    }
                }
                
                // Get correct answer
                if (isset($diagramData['correct_answers'][$index])) {
                    $correctAnswer = $diagramData['correct_answers'][$index];
                }
                
                if ($isCorrect) {
                    $correctCount++;
                }
                
                $labelResults[$index] = [
                    'question_number' => $questionNumber,
                    'student_answer' => $studentAnswer,
                    'correct_answer' => $correctAnswer,
                    'is_correct' => $isCorrect,
                    'x' => $label['x'] ?? 0,
                    'y' => $label['y'] ?? 0
                ];
            }
        }
        
        $percentage = $totalLabels > 0 ? round(($correctCount / $totalLabels) * 100, 1) : 0;
        
        return [
            'total_labels' => $totalLabels,
            'correct_count' => $correctCount,
            'percentage' => $percentage,
            'label_results' => $labelResults
        ];
    }
}
