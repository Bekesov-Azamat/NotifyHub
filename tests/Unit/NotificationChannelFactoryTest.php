<?php

namespace Tests\Unit;

use App\Enums\NotificationChannel;
use App\Services\Notifications\Channels\EmailNotificationChannel;
use App\Services\Notifications\Channels\TelegramNotificationChannel;
use App\Services\Notifications\NotificationChannelFactory;
use PHPUnit\Framework\TestCase;

class NotificationChannelFactoryTest extends TestCase
{
    public function test_it_returns_email_channel(): void
    {
        $factory = new NotificationChannelFactory;

        $channel = $factory->make(NotificationChannel::Email);

        $this->assertInstanceOf(EmailNotificationChannel::class, $channel);
    }

    public function test_it_returns_telegram_channel(): void
    {
        $factory = new NotificationChannelFactory;

        $channel = $factory->make(NotificationChannel::Telegram);

        $this->assertInstanceOf(TelegramNotificationChannel::class, $channel);
    }
}
