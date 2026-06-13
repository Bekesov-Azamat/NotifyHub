<?php

namespace App\Http\Controllers\Api;

use App\Enums\NotificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListUserNotificationsRequest;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Services\Notifications\NotificationSenderService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationSenderService $notificationSender,
    ) {}

    public function store(StoreNotificationRequest $request): NotificationResource
    {
        $notification = Notification::create([
            'user_id' => $request->integer('user_id'),
            'channel' => $request->input('channel'),
            'text' => $request->string('text')->toString(),
            'status' => NotificationStatus::Pending,
        ]);

        $this->notificationSender->send($notification);

        return NotificationResource::make($notification->refresh());
    }

    public function status(Notification $notification): NotificationResource
    {
        return NotificationResource::make($notification);
    }

    public function userHistory(
        ListUserNotificationsRequest $request,
        int $userId,
    ): AnonymousResourceCollection {
        $query = Notification::query()
            ->where('user_id', $userId)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->input('channel'));
        }

        return NotificationResource::collection(
            $query->paginate(15)
        );
    }
}
