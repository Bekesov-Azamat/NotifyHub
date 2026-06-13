<?php

namespace App\Services\Notifications;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use Throwable;

class NotificationSenderService
{
    public function __construct(
        private readonly NotificationChannelFactory $channelFactory,
    ) {}

    public function send(Notification $notification): void
    {
        try {
            $notification->update([
                'status' => NotificationStatus::Processing,
                'attempts' => $notification->attempts + 1,
                'last_error' => null,
            ]);

            $this->channelFactory
                ->make($notification->channel)
                ->send($notification);

            $notification->update([
                'status' => NotificationStatus::Sent,
                'sent_at' => now(),
                'last_error' => null,
            ]);
        } catch (Throwable $exception) {
            $notification->update([
                'status' => NotificationStatus::Failed,
                'last_error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
