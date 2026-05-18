<?php

use App\Http\Controllers\v1;
use App\Http\Controllers\v2;
use App\Http\Controllers\v3;
use App\Jobs\GenerateDailySalesReportJobV1;
use App\Jobs\GenerateDailySalesReportJobV2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/server', function () {
    Log::info("Server Port: " . request()->server('SERVER_PORT'));
    return response()->json(['port' => request()->server('SERVER_PORT')]);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('/bookings', [v1\BookingController::class, 'store']);

    Route::get('/test-report', function () {
        GenerateDailySalesReportJobV1::dispatch();
        return 'Report job dispatched';
    });
});

Route::prefix('v2')->group(function () {
    Route::post('/bookings', [v2\BookingController::class, 'store']);

    Route::get('/test-report', function () {
        GenerateDailySalesReportJobV2::dispatch();
        GenerateDailySalesReportJobV2::dispatch();
        GenerateDailySalesReportJobV2::dispatch();
        GenerateDailySalesReportJobV2::dispatch();
        GenerateDailySalesReportJobV2::dispatch();
        return 'Report job dispatched';
    });
});

Route::prefix('v3')->group(function () {
    Route::post('/bookings', [v3\BookingController::class, 'store']);
});
