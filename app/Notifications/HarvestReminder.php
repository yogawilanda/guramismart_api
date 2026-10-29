<?php

namespace App\Notifications;

use App\Models\Cycle;
use Illuminate\Notifications\Notification;

class HarvestReminder extends Notification
{
    public function __construct(public Cycle $cycle) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'harvest_reminder',
            'cycle_id' => $this->cycle->id,
            'message' => 'Siklus '.($this->cycle->name ?? "#{$this->cycle->id}")." sudah mencapai target {$this->cycle->target_days} hari. Saatnya panen.",
        ];
    }
}
