<?php

namespace App\Http\Controllers\Api;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateReportRequest;
use App\Http\Resources\ReportJobResource;
use App\Jobs\GenerateNotificationReportJob;
use App\Models\ReportJob;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function store(CreateReportRequest $request): ReportJobResource
    {
        $reportJob = ReportJob::create([
            'user_id' => $request->integer('user_id'),
            'from_date' => $request->date('from_date'),
            'to_date' => $request->date('to_date'),
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
            200,
            [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="notification-report-'.$reportJob->id.'.json"',
            ]
        );
    }
}
