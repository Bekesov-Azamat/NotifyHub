<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon|null $from_date
 * @property Carbon|null $to_date
 * @property ReportStatus $status
 * @property string|null $file_path
 * @property string|null $last_error
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ReportJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_date',
        'to_date',
        'status',
        'file_path',
        'last_error',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'from_date' => 'datetime',
            'to_date' => 'datetime',
            'status' => ReportStatus::class,
        ];
    }
}
