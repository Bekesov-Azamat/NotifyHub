<?php

namespace Tests\Unit;

use App\Enums\NotificationChannel;
use App\Models\Notification;
use App\Services\Notifications\Channels\EmailNotificationChannel;
use PHPUnit\Framework\TestCase;

class EmailNotificationChannelTest extends TestCase
{
    public function test_email_channel_fake_send_keeps_notification_unchanged(): void
    {
        $channel = new EmailNotificationChannel;

        $notification = new Notification([
            'user_id' => 1,
            'channel' => NotificationChannel::Email,
            'text' => 'Email test message',
        ]);

        $channel->send($notification);

        $this->assertSame(1, $notification->user_id);
        $this->assertSame(NotificationChannel::Email, $notification->channel);
        $this->assertSame('Email test message', $notification->text);
    }
}
