<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class FeedReminder extends Notification
{
    public function __construct(public string $session) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'feed_reminder',
            'session' => $this->session,
            'message' => "Waktunya memberi pakan {$this->session} dan mencatatnya di aplikasi.",
        ];
    }
}
