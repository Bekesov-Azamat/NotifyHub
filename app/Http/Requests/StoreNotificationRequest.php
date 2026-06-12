<?php

namespace App\Http\Requests;

use App\Enums\NotificationChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'min:1'],
            'channel' => ['required', 'string', Rule::enum(NotificationChannel::class)],
            'text' => ['required', 'string', 'max:500'],
        ];
    }
}
