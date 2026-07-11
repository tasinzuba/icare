<?php

namespace App\Notifications;

use App\Models\Branch;
use App\Models\OfflineEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfflineStudentWelcome extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $password;
    protected OfflineEnrollment $enrollment;
    protected Branch $branch;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $password, OfflineEnrollment $enrollment, Branch $branch)
    {
        $this->password = $password;
        $this->enrollment = $enrollment;
        $this->branch = $branch;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to CD IELTS - Your Account Details')
            ->view('emails.offline-student-welcome', [
                'user' => $notifiable,
                'password' => $this->password,
                'enrollment' => $this->enrollment,
                'branch' => $this->branch,
                'loginUrl' => route('offline.login'),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'branch_id' => $this->branch->id,
            'enrollment_id' => $this->enrollment->id,
            'student_id' => $this->enrollment->student_id,
        ];
    }
}
