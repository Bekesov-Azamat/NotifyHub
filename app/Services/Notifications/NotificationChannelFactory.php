<?php

namespace App\Services\Notifications;

use App\Enums\NotificationChannel;
use App\Services\Notifications\Channels\EmailNotificationChannel;
use App\Services\Notifications\Channels\NotificationChannelInterface;
use App\Services\Notifications\Channels\TelegramNotificationChannel;

class NotificationChannelFactory
{
    public function make(NotificationChannel $channel): NotificationChannelInterface
    {
        return match ($channel) {
            NotificationChannel::Email => new EmailNotificationChannel,
            NotificationChannel::Telegram => new TelegramNotificationChannel
        };
    }
}
