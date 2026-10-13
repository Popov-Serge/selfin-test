<?php

use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\PartnerWebhookController;
use App\Http\Middleware\AuthenticatePartner;
use App\Services\DeliveryMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('webhook/status', [PartnerWebhookController::class, 'handle'])
    ->middleware(AuthenticatePartner::class);
Route::post('/leads', [LeadController::class, 'store']);
Route::get('/metrics/deliveries', function (DeliveryMetricsService $metrics) {
    return response()->json($metrics->snapshot());
});
