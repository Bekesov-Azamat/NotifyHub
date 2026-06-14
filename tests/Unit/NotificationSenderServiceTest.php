<?php

namespace Tests\Unit;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Services\Notifications\NotificationChannelFactory;
use App\Services\Notifications\NotificationSenderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NotificationSenderServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_sender_service_marks_notification_as_sent(): void
    {
        $notification = Notification::factory()->create([
            'user_id' => 500,
            'channel' => NotificationChannel::Email,
            'status' => NotificationStatus::Pending,
            'attempts' => 0,
        ]);

        $service = new NotificationSenderService(
            new NotificationChannelFactory
        );

        $service->send($notification);

        $notification->refresh();

        $this->assertSame(
            NotificationStatus::Sent,
            $notification->status
        );

        $this->assertSame(
            1,
            $notification->attempts
        );

        $this->assertNotNull(
            $notification->sent_at
        );

        $this->assertNull(
            $notification->last_error
        );
    }
}
