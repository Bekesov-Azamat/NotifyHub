<?php

use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

Route::post('/notifications', [NotificationController::class, 'store']);
Route::get('/notifications/{notification}/status', [NotificationController::class, 'status']);
Route::get('/users/{userId}/notifications', [NotificationController::class, 'userHistory']);
