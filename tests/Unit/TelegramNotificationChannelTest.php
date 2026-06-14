<?php

namespace Tests\Unit;

use App\Enums\NotificationChannel;
use App\Models\Notification;
use App\Services\Notifications\Channels\TelegramNotificationChannel;
use PHPUnit\Framework\TestCase;

class TelegramNotificationChannelTest extends TestCase
{
    public function test_telegram_channel_fake_send_keeps_notification_unchanged(): void
    {
        $channel = new TelegramNotificationChannel;

        $notification = new Notification([
            'user_id' => 1,
            'channel' => NotificationChannel::Telegram,
            'text' => 'Telegram test message',
        ]);

        $channel->send($notification);

        $this->assertSame(1, $notification->user_id);
        $this->assertSame(NotificationChannel::Telegram, $notification->channel);
        $this->assertSame('Telegram test message', $notification->text);
    }
}
