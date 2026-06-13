<?php

namespace App\Services\Notifications\Channels;

use App\Models\Notification;

class TelegramNotificationChannel implements NotificationChannelInterface
{
    public function send(Notification $notification): void
    {
        // Fake telegram sending.
        // Real integration can be added here without changing the service layer.
    }
}
