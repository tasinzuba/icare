<?php

namespace App\Notifications;

use App\Models\UserDevice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDeviceNotification extends Notification
{
    use Queueable;

    protected $device;

    public function __construct(UserDevice $device)
    {
        $this->device = $device;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Device Login Alert')
            ->view('emails.new-device', [
                'user' => $notifiable,
                'device' => $this->device
            ]);
    }
}