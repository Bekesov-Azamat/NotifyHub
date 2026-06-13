<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

Route::post('/notifications', [NotificationController::class, 'store']);
Route::get('/notifications/{notification}/status', [NotificationController::class, 'status']);
Route::get('/users/{userId}/notifications', [NotificationController::class, 'userHistory']);

Route::post('/reports', [ReportController::class, 'store']);
Route::get('/reports/{reportJob}', [ReportController::class, 'show']);
Route::get('/reports/{reportJob}/download', [ReportController::class, 'download']);
