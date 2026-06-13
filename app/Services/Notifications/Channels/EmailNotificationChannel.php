<?php

namespace App\Services\Notifications\Channels;

use App\Models\Notification;

class EmailNotificationChannel implements NotificationChannelInterface
{
    public function send(Notification $notification): void
    {
        // Fake email sending.
        // Real integration can be added here without changing teh service layer
    }
}
