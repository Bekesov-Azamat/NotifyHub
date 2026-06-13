<?php

namespace App\Jobs;

use App\Enums\ReportStatus;
use App\Models\Notification;
use App\Models\ReportJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateNotificationReportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $reportJobId,
    ) {}

    public function handle(): void
    {
        $reportJob = ReportJob::query()->findOrFail($this->reportJobId);

        try {
            $reportJob->update([
                'status' => ReportStatus::Processing,
                'last_error' => null,
            ]);

            $query = Notification::query();

            if ($reportJob->from_date !== null) {
                $query->where('created_at', '>=', $reportJob->from_date);
            }

            if ($reportJob->to_date !== null) {
                $query->where('created_at', '<=', $reportJob->to_date);
            }

            $notificationsByChannel = (clone $query)
                ->selectRaw('channel, COUNT(*) as total')
                ->groupBy('channel')
                ->pluck('total', 'channel');

            $errorsByChannel = (clone $query)
                ->where('status', 'failed')
                ->selectRaw('channel, COUNT(*) as total')
                ->groupBy('channel')
                ->pluck('total', 'channel');

            $reportData = [
                'period' => [
                    'from_date' => $reportJob->from_date?->toISOString(),
                    'to_date' => $reportJob->to_date?->toISOString(),
                ],
                'notifications_by_channel' => $notificationsByChannel,
                'errors_by_channel' => $errorsByChannel,
                'generated_at' => now()->toISOString(),
            ];

            $filePath = sprintf(
                'reports/notification-report-%d.json',
                $reportJob->id
            );

            Storage::disk('local')->put(
                $filePath,
                json_encode($reportData, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)
            );

            $reportJob->update([
                'status' => ReportStatus::Completed,
                'file_path' => $filePath,
                'last_error' => null,
            ]);
        } catch (Throwable $exception) {
            $reportJob->update([
                'status' => ReportStatus::Failed,
                'last_error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
