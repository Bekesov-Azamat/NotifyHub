<?php

namespace Tests\Feature;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_notification_can_be_created(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/notifications', [
            'user_id' => 1001,
            'channel' => NotificationChannel::Email->value,
            'text' => 'Test notification message.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.user_id', 1001)
            ->assertJsonPath('data.channel', NotificationChannel::Email->value)
            ->assertJsonPath('data.text', 'Test notification message.')
            ->assertJsonPath('data.status', NotificationStatus::Pending->value)
            ->assertJsonPath('data.attempts', 0);

        $this->assertDatabaseHas('notifications', [
            'user_id' => 1001,
            'channel' => NotificationChannel::Email->value,
            'text' => 'Test notification message.',
            'status' => NotificationStatus::Pending->value,
        ]);

        Queue::assertPushed(SendNotificationJob::class);
    }

    public function test_notification_creation_requires_valid_payload(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/notifications', [
            'user_id' => 0,
            'channel' => 'sms',
            'text' => str_repeat('a', 501),
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'user_id',
                'channel',
                'text',
            ]);

        Queue::assertNothingPushed();
    }

    public function test_notification_status_can_be_retrieved(): void
    {
        $notification = Notification::factory()->create([
            'user_id' => 2001,
            'channel' => NotificationChannel::Telegram,
            'status' => NotificationStatus::Sent,
            'attempts' => 1,
            'sent_at' => now(),
        ]);

        $response = $this->getJson('/api/notifications/'.$notification->id.'/status');

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $notification->id)
            ->assertJsonPath('data.user_id', 2001)
            ->assertJsonPath('data.channel', NotificationChannel::Telegram->value)
            ->assertJsonPath('data.status', NotificationStatus::Sent->value)
            ->assertJsonPath('data.attempts', 1);
    }

    public function test_user_notification_history_can_be_retrieved(): void
    {
        Notification::factory()->count(2)->create([
            'user_id' => 3001,
            'channel' => NotificationChannel::Email,
            'status' => NotificationStatus::Sent,
        ]);

        Notification::factory()->create([
            'user_id' => 9999,
            'channel' => NotificationChannel::Telegram,
            'status' => NotificationStatus::Failed,
        ]);

        $response = $this->getJson('/api/users/3001/notifications');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_notification_history_can_be_filtered_by_status(): void
    {
        Notification::factory()->create([
            'user_id' => 4001,
            'channel' => NotificationChannel::Email,
            'status' => NotificationStatus::Sent,
        ]);

        Notification::factory()->create([
            'user_id' => 4001,
            'channel' => NotificationChannel::Telegram,
            'status' => NotificationStatus::Failed,
        ]);

        $response = $this->getJson('/api/users/4001/notifications?status=failed');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', NotificationStatus::Failed->value);
    }

    public function test_user_notification_history_can_be_filtered_by_channel(): void
    {
        Notification::factory()->create([
            'user_id' => 5001,
            'channel' => NotificationChannel::Email,
            'status' => NotificationStatus::Sent,
        ]);

        Notification::factory()->create([
            'user_id' => 5001,
            'channel' => NotificationChannel::Telegram,
            'status' => NotificationStatus::Sent,
        ]);

        $response = $this->getJson('/api/users/5001/notifications?channel=telegram');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.channel', NotificationChannel::Telegram->value);
    }
}
