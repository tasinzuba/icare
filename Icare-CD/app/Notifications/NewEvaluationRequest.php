<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\HumanEvaluationRequest;

class NewEvaluationRequest extends Notification implements ShouldQueue
{
    use Queueable;

    protected $evaluationRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(HumanEvaluationRequest $evaluationRequest)
    {
        $this->evaluationRequest = $evaluationRequest;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $section = ucfirst($this->evaluationRequest->studentAttempt->testSet->section->name);
        $priority = $this->evaluationRequest->priority === 'urgent' ? 'URGENT - ' : '';
        
        // Get teacher stats
        $pendingCount = $notifiable->teacher->evaluationRequests()->where('status', 'pending')->count();
        $completedThisMonth = $notifiable->teacher->evaluationRequests()
            ->where('status', 'completed')
            ->whereMonth('updated_at', now()->month)
            ->count();
        $averageRating = $notifiable->teacher->average_rating ?? 0;
        
        return (new MailMessage)
            ->subject($priority . 'New ' . $section . ' Evaluation Request')
            ->view('emails.teacher-evaluation-request', [
                'evaluationRequest' => $this->evaluationRequest->load('studentAttempt.testSet.section', 'studentAttempt.user'),
                'pendingCount' => $pendingCount,
                'completedThisMonth' => $completedThisMonth,
                'averageRating' => $averageRating
            ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_evaluation_request',
            'evaluation_request_id' => $this->evaluationRequest->id,
            'student_name' => $this->evaluationRequest->student->name,
            'section' => $this->evaluationRequest->studentAttempt->testSet->section->name,
            'priority' => $this->evaluationRequest->priority,
            'deadline' => $this->evaluationRequest->deadline_at->toIso8601String(),
            'tokens' => $this->evaluationRequest->tokens_used,
            'message' => 'New ' . ucfirst($this->evaluationRequest->studentAttempt->testSet->section->name) . 
                         ' evaluation request from ' . $this->evaluationRequest->student->name
        ];
    }
}