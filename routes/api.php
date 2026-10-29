<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CycleController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\FeedLogController;
use App\Http\Controllers\Api\V1\WaterLogController;
use App\Http\Controllers\Api\V1\GuideController;
use App\Http\Controllers\Api\V1\HarvestController;
use App\Http\Controllers\Api\V1\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::apiResource('cycles', CycleController::class);
        Route::apiResource('cycles.feed-logs', FeedLogController::class)->except('show');
        Route::apiResource('cycles.water-logs', WaterLogController::class)->except('show');
        Route::get('cycles/{cycle}/harvest', [HarvestController::class, 'show']);
        Route::post('cycles/{cycle}/harvest', [HarvestController::class, 'store']);
        Route::put('cycles/{cycle}/harvest', [HarvestController::class, 'update']);
        Route::get('cycles/{cycle}/dashboard', [DashboardController::class, 'cycle']);
        Route::get('dashboard', [DashboardController::class, 'summary']);
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::patch('notifications/{id}/read', [NotificationController::class, 'markRead']);
        Route::get('guides', [GuideController::class, 'index']);
        Route::get('guides/{slug}', [GuideController::class, 'show']);
    });
});




