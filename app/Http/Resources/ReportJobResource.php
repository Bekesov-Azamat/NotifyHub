<?php

namespace App\Http\Resources;

use App\Models\ReportJob;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ReportJob
 */
class ReportJobResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ReportJob $reportJob */
        $reportJob = $this->resource;

        return [
            'id' => $reportJob->id,
            'user_id' => $reportJob->user_id,
            'from_date' => $reportJob->from_date?->toISOString(),
            'to_date' => $reportJob->to_date?->toISOString(),
            'status' => $reportJob->status->value,
            'file_path' => $reportJob->file_path,
            'last_error' => $reportJob->last_error,
            'created_at' => $reportJob->created_at?->toISOString(),
            'updated_at' => $reportJob->updated_at?->toISOString(),
        ];
    }
}
