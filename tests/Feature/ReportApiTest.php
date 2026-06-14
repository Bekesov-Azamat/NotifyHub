<?php

namespace Tests\Feature;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Enums\ReportStatus;
use App\Jobs\GenerateNotificationReportJob;
use App\Models\Notification;
use App\Models\ReportJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_report_generation_can_be_requested(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/reports', [
            'user_id' => 7001,
            'from_date' => '2026-06-01',
            'to_date' => '2026-06-30',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.user_id', 7001)
            ->assertJsonPath('data.status', ReportStatus::Pending->value)
            ->assertJsonPath('data.file_path', null);

        $this->assertDatabaseHas('report_jobs', [
            'user_id' => 7001,
            'status' => ReportStatus::Pending->value,
        ]);

        Queue::assertPushed(GenerateNotificationReportJob::class);
    }

    public function test_report_request_requires_valid_payload(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/reports', [
            'user_id' => 0,
            'from_date' => '2026-07-31',
            'to_date' => '2026-07-01',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'user_id',
                'to_date',
            ]);

        Queue::assertNothingPushed();
    }

    public function test_duplicate_active_report_returns_conflict(): void
    {
        Queue::fake();

        ReportJob::create([
            'user_id' => 8001,
            'from_date' => '2026-06-01',
            'to_date' => '2026-06-30',
            'status' => ReportStatus::Pending,
        ]);

        $response = $this->postJson('/api/reports', [
            'user_id' => 8001,
            'from_date' => '2026-06-01',
            'to_date' => '2026-06-30',
        ]);

        $response
            ->assertConflict()
            ->assertJsonPath('message', 'Report generation for this period is already in progress.');

        Queue::assertNothingPushed();
    }

    public function test_report_status_can_be_retrieved(): void
    {
        $reportJob = ReportJob::create([
            'user_id' => 9001,
            'from_date' => '2026-06-01',
            'to_date' => '2026-06-30',
            'status' => ReportStatus::Completed,
            'file_path' => 'reports/notification-report-test.json',
        ]);

        $response = $this->getJson('/api/reports/'.$reportJob->id);

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $reportJob->id)
            ->assertJsonPath('data.user_id', 9001)
            ->assertJsonPath('data.status', ReportStatus::Completed->value)
            ->assertJsonPath('data.file_path', 'reports/notification-report-test.json');
    }

    public function test_completed_report_file_can_be_downloaded(): void
    {
        Storage::fake('local');

        $filePath = 'reports/notification-report-test.json';

        Storage::disk('local')->put($filePath, json_encode([
            'user_id' => 10001,
            'total_notifications' => 2,
            'total_errors' => 1,
            'notifications_by_channel' => [
                NotificationChannel::Email->value => 1,
                NotificationChannel::Telegram->value => 1,
            ],
            'errors_by_channel' => [
                NotificationChannel::Telegram->value => 1,
            ],
        ], JSON_THROW_ON_ERROR));

        $reportJob = ReportJob::create([
            'user_id' => 10001,
            'from_date' => '2026-06-01',
            'to_date' => '2026-06-30',
            'status' => ReportStatus::Completed,
            'file_path' => $filePath,
        ]);

        $response = $this->get('/api/reports/'.$reportJob->id.'/download');

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json');
    }

    public function test_report_download_returns_not_found_when_file_is_not_ready(): void
    {
        $reportJob = ReportJob::create([
            'user_id' => 11001,
            'from_date' => '2026-06-01',
            'to_date' => '2026-06-30',
            'status' => ReportStatus::Pending,
            'file_path' => null,
        ]);

        $response = $this->getJson('/api/reports/'.$reportJob->id.'/download');

        $response->assertNotFound();
    }

    public function test_report_job_generates_expected_json_file(): void
    {
        Storage::fake('local');

        Notification::factory()->create([
            'user_id' => 12001,
            'channel' => NotificationChannel::Email,
            'status' => NotificationStatus::Sent,
        ]);

        Notification::factory()->create([
            'user_id' => 12001,
            'channel' => NotificationChannel::Telegram,
            'status' => NotificationStatus::Failed,
        ]);

        Notification::factory()->create([
            'user_id' => 99999,
            'channel' => NotificationChannel::Email,
            'status' => NotificationStatus::Failed,
        ]);

        $reportJob = ReportJob::create([
            'user_id' => 12001,
            'from_date' => null,
            'to_date' => null,
            'status' => ReportStatus::Pending,
        ]);

        (new GenerateNotificationReportJob($reportJob->id))->handle();

        $reportJob->refresh();

        $this->assertSame(ReportStatus::Completed, $reportJob->status);
        $this->assertNotNull($reportJob->file_path);

        $this->assertTrue(
            Storage::disk('local')->exists($reportJob->file_path)
        );

        $content = Storage::disk('local')->get($reportJob->file_path);
        $payload = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(12001, $payload['user_id']);
        $this->assertSame(2, $payload['total_notifications']);
        $this->assertSame(1, $payload['total_errors']);
        $this->assertSame(1, $payload['notifications_by_channel']['email']);
        $this->assertSame(1, $payload['notifications_by_channel']['telegram']);
        $this->assertSame(1, $payload['errors_by_channel']['telegram']);
    }
}
