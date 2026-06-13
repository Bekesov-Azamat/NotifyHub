<?php

namespace App\Services\Notifications\Channels;

use App\Models\Notification;

interface NotificationChannelInterface
{
    public function send(Notification $notification): void;
}
