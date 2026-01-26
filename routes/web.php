<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\HealthController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Health Check Routes (for monitoring & load balancers)
Route::get('/health', [HealthController::class, 'check'])->name('health');
Route::get('/health/detailed', [HealthController::class, 'detailed'])
    ->middleware('auth')
    ->name('health.detailed');

// Authentication Routes
Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [App\Http\Controllers\AuthController::class, 'logout']); // Fallback get logout

// Protected Admin Routes
Route::middleware(['auth'])->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('users', App\Http\Controllers\UserController::class);
    
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
    
    // WhatsApp Blast
    Route::prefix('whatsapp')->group(function () {
        Route::get('/', [App\Http\Controllers\WhatsAppController::class, 'index'])->name('whatsapp.index');
        Route::get('/health', [App\Http\Controllers\WhatsAppController::class, 'health'])->name('whatsapp.health');
        Route::get('/qr', [App\Http\Controllers\WhatsAppController::class, 'qrCode'])->name('whatsapp.qr');
        Route::post('/session/start', [App\Http\Controllers\WhatsAppController::class, 'startSession'])->name('whatsapp.session.start');
        Route::post('/session/stop', [App\Http\Controllers\WhatsAppController::class, 'stopSession'])->name('whatsapp.session.stop');
        Route::post('/logout', [App\Http\Controllers\WhatsAppController::class, 'logout'])->name('whatsapp.logout');
        Route::post('/send', [App\Http\Controllers\WhatsAppController::class, 'send'])->name('whatsapp.send');
        Route::post('/blast', [App\Http\Controllers\WhatsAppController::class, 'blastToMassa'])->name('whatsapp.blast');
        Route::post('/event/{event}/notify', [App\Http\Controllers\WhatsAppController::class, 'notifyEventRegistrants'])->name('whatsapp.event.notify');
        Route::post('/check-number', [App\Http\Controllers\WhatsAppController::class, 'checkNumber'])->name('whatsapp.check-number');
        
        // Rate limit and status endpoints
        Route::get('/rate-limit', [App\Http\Controllers\WhatsAppController::class, 'rateLimitStatus'])->name('whatsapp.rate-limit');
        Route::get('/blast-status', [App\Http\Controllers\WhatsAppController::class, 'blastStatus'])->name('whatsapp.blast-status');
        
        // Scheduled Campaigns
        Route::get('/campaigns', [App\Http\Controllers\WhatsAppController::class, 'campaigns'])->name('whatsapp.campaigns');
        Route::post('/campaigns', [App\Http\Controllers\WhatsAppController::class, 'storeCampaign'])->name('whatsapp.campaigns.store');
        Route::post('/campaigns/{id}/cancel', [App\Http\Controllers\WhatsAppController::class, 'cancelCampaign'])->name('whatsapp.campaigns.cancel');
        Route::post('/send-template', [App\Http\Controllers\WhatsAppController::class, 'sendTemplate'])->name('whatsapp.send-template');
    });
});

// Public Registration (Accessible without login) with rate limiting
Route::prefix('daftar')->middleware('throttle:registration')->group(function () {
    Route::get('/', [App\Http\Controllers\PublicController::class, 'index'])->name('public.index');
    Route::get('/event/{event}', [App\Http\Controllers\PublicController::class, 'register'])->name('public.register');
    Route::post('/event/{event}', [App\Http\Controllers\PublicController::class, 'store'])->name('public.store');
    Route::get('/success', [App\Http\Controllers\PublicController::class, 'success'])->name('public.success');
    
    // NIK lookup with stricter rate limit
    Route::get('/check-nik', [App\Http\Controllers\PublicController::class, 'checkNik'])
        ->middleware('throttle:nik-lookup')
        ->name('public.check-nik');
    
    // Address lookups
    Route::get('/regencies', [App\Http\Controllers\PublicController::class, 'regencies'])->name('public.regencies');
    Route::get('/districts', [App\Http\Controllers\PublicController::class, 'districts'])->name('public.districts');
    Route::get('/villages', [App\Http\Controllers\PublicController::class, 'villages'])->name('public.villages');
});

