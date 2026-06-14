<?php

namespace App\Http\Controllers\Api;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateReportRequest;
use App\Http\Resources\ReportJobResource;
use App\Jobs\GenerateNotificationReportJob;
use App\Models\ReportJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function store(CreateReportRequest $request): ReportJobResource|JsonResponse
    {
        $userId = $request->integer('user_id');
        $fromDate = $request->date('from_date');
        $toDate = $request->date('to_date');

        $activeReportExists = ReportJob::query()
            ->where('user_id', $userId)
            ->where('from_date', $fromDate)
            ->where('to_date', $toDate)
            ->whereIn('status', [
                ReportStatus::Pending,
                ReportStatus::Processing,
            ])
            ->exists();

        if ($activeReportExists) {
            return response()->json([
                'message' => 'Report generation for this period is already in progress.',
            ], Response::HTTP_CONFLICT);
        }

        $reportJob = ReportJob::create([
            'user_id' => $userId,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'status' => ReportStatus::Pending,
        ]);

        GenerateNotificationReportJob::dispatch($reportJob->id);

        return ReportJobResource::make($reportJob->refresh());
    }

    public function show(ReportJob $reportJob): ReportJobResource
    {
        return ReportJobResource::make($reportJob);
    }

    public function download(ReportJob $reportJob): Response
    {
        if ($reportJob->status !== ReportStatus::Completed || $reportJob->file_path === null) {
            abort(404, 'Report file is not ready.');
        }

        if (! Storage::disk('local')->exists($reportJob->file_path)) {
            abort(404, 'Report file does not exist.');
        }

        return response(
            Storage::disk('local')->get($reportJob->file_path),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="notification-report-'.$reportJob->id.'.json"',
            ]
        );
    }
}
