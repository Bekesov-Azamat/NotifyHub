<?php

namespace App\Http\Resources;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Notification
 */
class NotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Notification $notification */
        $notification = $this->resource;

        return [
            'id' => $notification->id,
            'user_id' => $notification->user_id,
            'channel' => $notification->channel->value,
            'text' => $notification->text,
            'status' => $notification->status->value,
            'attempts' => $notification->attempts,
            'last_error' => $notification->last_error,
            'sent_at' => $notification->sent_at?->toISOString(),
            'created_at' => $notification->created_at?->toISOString(),
            'updated_at' => $notification->updated_at?->toISOString(),
        ];
    }
}
