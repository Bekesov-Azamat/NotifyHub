<?php

namespace App\Jobs;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Services\Notifications\NotificationSenderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $notificationId,
    ) {}

    public function tries(): int
    {
        return 3;
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [5, 15, 30];
    }

    public function handle(NotificationSenderService $notificationSender): void
    {
        $notification = Notification::query()->findOrFail($this->notificationId);

        $notificationSender->send($notification);
    }

    public function failed(Throwable $exception): void
    {
        $notification = Notification::query()->find($this->notificationId);

        if ($notification === null) {
            return;
        }

        $notification->update([
            'status' => NotificationStatus::Failed,
            'last_error' => $exception->getMessage(),
        ]);
    }
}
