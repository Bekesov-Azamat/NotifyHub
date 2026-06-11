<?php

namespace Database\Factories;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'user_id' => fake()->numberBetween(1, 1000),
            'channel' => fake()->randomElement(NotificationChannel::cases()),
            'text' => fake()->realText(200),
            'status' => NotificationStatus::Pending,
            'attempts' => 0,
            'last_error' => null,
            'sent_at' => null,
        ];
    }
}
