<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\CheckinController;
use App\Http\Controllers\Api\LotteryController;
use App\Http\Controllers\Api\MassaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/


// Public routes with rate limiting
Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    
    // Event categories (public)
    Route::get('/event-categories', [EventController::class, 'categories']);
    
    // Public event listing
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show']);
    
    // Check NIK availability (for registration form) - stricter rate limit
    Route::post('/check-nik', [RegistrationController::class, 'checkNik'])
        ->middleware('throttle:30,1');
    
    // Validate ticket (for check-in preview)
    // Validate ticket (for check-in preview)
    Route::post('/validate-ticket', [CheckinController::class, 'validateTicket']);

    // Location Data (Public)
    Route::get('/locations/provinces', [\App\Http\Controllers\Api\LocationController::class, 'provinces']);
    Route::get('/locations/regencies/{province}', [\App\Http\Controllers\Api\LocationController::class, 'regencies']);
    Route::get('/locations/districts/{regency}', [\App\Http\Controllers\Api\LocationController::class, 'districts']);
    Route::get('/locations/villages/{district}', [\App\Http\Controllers\Api\LocationController::class, 'villages']);
    Route::get('/locations/postal-code/{village}', [\App\Http\Controllers\Api\LocationController::class, 'getPostalCode']);

    // Protected routes (require authentication)
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // Events Management
        Route::post('/events', [EventController::class, 'store']);
        Route::put('/events/{event}', [EventController::class, 'update']);
        Route::delete('/events/{event}', [EventController::class, 'destroy']);
        Route::patch('/events/{event}/status', [EventController::class, 'updateStatus']);
        Route::get('/events/{event}/statistics', [EventController::class, 'statistics']);
        
        // Registrations
        Route::get('/events/{event}/registrations', [RegistrationController::class, 'index']);
        Route::post('/events/{event}/registrations', [RegistrationController::class, 'store']);
        Route::get('/registrations/{registration}', [RegistrationController::class, 'show']);
        Route::post('/registrations/find-by-ticket', [RegistrationController::class, 'findByTicket']);
        Route::post('/registrations/{registration}/cancel', [RegistrationController::class, 'cancel']);
        Route::get('/registrations/{registration}/download-ticket', [RegistrationController::class, 'downloadTicket']);
        Route::post('/events/{event}/batch-generate-tickets', [RegistrationController::class, 'batchGenerateTickets']);
        
        // Check-in
        Route::post('/checkin/scan', [CheckinController::class, 'scanQr']);
        Route::post('/checkin/ticket', [CheckinController::class, 'checkinByTicket']);
        Route::post('/events/{event}/checkin/nik', [CheckinController::class, 'checkinByNik']);
        Route::post('/registrations/{registration}/checkin-override', [CheckinController::class, 'manualOverride']);
        Route::get('/events/{event}/attendance', [CheckinController::class, 'attendanceStats']);
        Route::get('/events/{event}/hourly-checkins', [CheckinController::class, 'hourlyCheckins']);
        Route::get('/events/{event}/recent-checkins', [CheckinController::class, 'recentCheckins']);
        
        // Lottery
        Route::get('/events/{event}/lottery', [LotteryController::class, 'index']);
        Route::get('/events/{event}/lottery/eligible', [LotteryController::class, 'eligibleCount']);
        Route::get('/events/{event}/lottery/shuffle', [LotteryController::class, 'shuffleNames']);
        Route::post('/events/{event}/lottery/draw', [LotteryController::class, 'draw']);
        Route::post('/events/{event}/lottery/draw-multiple', [LotteryController::class, 'drawMultiple']);
        Route::post('/lottery/{draw}/claim', [LotteryController::class, 'claim']);
        Route::delete('/lottery/{draw}', [LotteryController::class, 'undoDraw']);
        
        // Lottery Prizes
        Route::get('/events/{event}/prizes', [LotteryController::class, 'prizes']);
        Route::post('/events/{event}/prizes', [LotteryController::class, 'createPrize']);
        Route::put('/prizes/{prize}', [LotteryController::class, 'updatePrize']);
        Route::delete('/prizes/{prize}', [LotteryController::class, 'deletePrize']);
        
        // Massa
        Route::get('/massa', [MassaController::class, 'index']);
        Route::post('/massa', [MassaController::class, 'store']);
        Route::get('/massa/{massa}', [MassaController::class, 'show']);
        Route::put('/massa/{massa}', [MassaController::class, 'update']);
        Route::delete('/massa/{massa}', [MassaController::class, 'destroy']);
        Route::post('/massa/find-by-nik', [MassaController::class, 'findByNik']);
        Route::get('/massa/{massa}/events', [MassaController::class, 'eventHistory']);
        Route::post('/massa/{massa}/recalculate-loyalty', [MassaController::class, 'recalculateLoyalty']);
        Route::post('/massa/{massa}/geocode', [MassaController::class, 'geocode']);
        
        // Loyalty & Maps
        Route::get('/loyalty/leaderboard', [MassaController::class, 'loyaltyLeaderboard']);
        Route::get('/massa-locations', [MassaController::class, 'locationStats']);
        Route::get('/massa-map', [MassaController::class, 'mapData']);
        
        // User info
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});
