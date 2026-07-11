<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification
{
    use Queueable;

    protected $details;

    public function __construct(array $details)
    {
        $this->details = $details;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Password Changed Successfully')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your password was successfully changed.')
            ->line('**Details:**')
            ->line('Time: ' . $this->details['time']->format('M d, Y H:i'))
            ->line('IP Address: ' . $this->details['ip'])
            ->line('Location: ' . $this->details['location'])
            ->line('Browser: ' . substr($this->details['browser'], 0, 50) . '...')
            ->line('If you didn\'t make this change, please contact support immediately.')
            ->action('Contact Support', url('/support'))
            ->salutation('Best regards, IELTS Mock Test Team');
    }
}