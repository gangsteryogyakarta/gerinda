<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\LotteryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication Routes
Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [App\Http\Controllers\AuthController::class, 'logout']); // Fallback get logout

// Protected Admin Routes
Route::middleware(['auth'])->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Events
    Route::resource('events', EventController::class);
    Route::patch('/events/{event}/status', [EventController::class, 'updateStatus'])->name('events.update-status');
    Route::get('/events/{event}/registrations', [EventController::class, 'registrations'])->name('events.registrations');
    Route::post('/events/{event}/batch-tickets', [EventController::class, 'batchGenerateTickets'])->name('events.batch-tickets');
    Route::get('/events/{event}/print-tickets', [EventController::class, 'printAllTickets'])->name('events.print-tickets');

    // Check-in
    Route::get('/checkin', [CheckinController::class, 'index'])->name('checkin.index');
    Route::get('/checkin/{event}', [CheckinController::class, 'event'])->name('checkin.event');
    Route::post('/checkin/process', [CheckinController::class, 'process'])->name('checkin.process');
    Route::post('/checkin/{registration}/manual', [CheckinController::class, 'manualCheckin'])->name('registrations.checkin');
    Route::get('/checkin/{event}/stats', [CheckinController::class, 'liveStats'])->name('checkin.stats');

    // Lottery
    Route::get('/lottery', [LotteryController::class, 'index'])->name('lottery.index');
    Route::get('/lottery/{event}', [LotteryController::class, 'event'])->name('lottery.event');
    Route::get('/lottery/{event}/shuffle', [LotteryController::class, 'shuffleNames'])->name('lottery.shuffle');
    Route::post('/lottery/{event}/draw', [LotteryController::class, 'draw'])->name('lottery.draw');
    Route::get('/lottery/{event}/prizes', [LotteryController::class, 'prizes'])->name('lottery.prizes');
    Route::post('/lottery/{event}/prizes', [LotteryController::class, 'storePrize'])->name('lottery.store-prize');
    Route::delete('/lottery/prizes/{prize}', [LotteryController::class, 'deletePrize'])->name('lottery.delete-prize');

    // Registrations Download
    Route::get('/registrations/{registration}/download', function(\App\Models\EventRegistration $registration) {
        $service = app(\App\Services\RegistrationService::class);
        if (!$registration->qr_code_path) {
            $service->generateQrCode($registration);
        }
        $pdfPath = $service->generateTicketPdf($registration);
        return \Illuminate\Support\Facades\Storage::disk('public')->download($pdfPath, "tiket-{$registration->ticket_number}.pdf");
    })->name('registrations.download-ticket');

    // Massa
    Route::resource('massa', App\Http\Controllers\MassaController::class);
    Route::post('/massa/lookup-nik', [App\Http\Controllers\MassaController::class, 'lookupNik'])->name('massa.lookup-nik');

    // Maps (WebGIS)
    Route::get('/maps', [App\Http\Controllers\MapController::class, 'index'])->name('maps.index');
    Route::get('/maps/markers', [App\Http\Controllers\MapController::class, 'markers'])->name('maps.markers');
    Route::get('/maps/heatmap', [App\Http\Controllers\MapController::class, 'heatmap'])->name('maps.heatmap');
    Route::get('/maps/province-stats', [App\Http\Controllers\MapController::class, 'provinceStats'])->name('maps.province-stats');
    Route::get('/maps/events', [App\Http\Controllers\MapController::class, 'eventMarkers'])->name('maps.events');

    // Reports & Statistics
    Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [App\Http\Controllers\ReportController::class, 'exportRegistrations'])->name('reports.export');

    // Settings
    Route::get('/settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/clear-cache', [App\Http\Controllers\SettingController::class, 'clearCache'])->name('settings.clear-cache');
    Route::post('/settings/test-wa', [App\Http\Controllers\SettingController::class, 'testWhatsapp'])->name('settings.test-wa');
    Route::post('/settings/migrate', [App\Http\Controllers\SettingController::class, 'migrate'])->name('settings.migrate');
    
    // Tickets
    Route::get('/tickets', [App\Http\Controllers\TicketController::class, 'index'])->name('tickets.index');
});

// Public Registration (Accessible without login)
Route::prefix('daftar')->group(function () {
    Route::get('/', [App\Http\Controllers\PublicController::class, 'index'])->name('public.index');
    Route::get('/event/{event}', [App\Http\Controllers\PublicController::class, 'register'])->name('public.register');
    Route::post('/event/{event}', [App\Http\Controllers\PublicController::class, 'store'])->name('public.store');
    Route::get('/success', [App\Http\Controllers\PublicController::class, 'success'])->name('public.success');
    Route::get('/check-nik', [App\Http\Controllers\PublicController::class, 'checkNik'])->name('public.check-nik');
    Route::get('/regencies', [App\Http\Controllers\PublicController::class, 'regencies'])->name('public.regencies');
    Route::get('/districts', [App\Http\Controllers\PublicController::class, 'districts'])->name('public.districts');
    Route::get('/villages', [App\Http\Controllers\PublicController::class, 'villages'])->name('public.villages');
});
