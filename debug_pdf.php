<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$event = App\Models\Event::find(15);
$registrations = $event->registrations()
    ->whereHas('massa')
    ->whereIn('registration_status', ['confirmed', 'pending', 'waitlist'])
    ->whereNotNull('ticket_number')
    ->get();

echo "EVENT: " . $event->name . "\n";
echo "COUNT: " . $registrations->count() . "\n";
if ($registrations->count() > 0) {
    $first = $registrations->first();
    echo "FIRST ID: " . $first->id . "\n";
    echo "FIRST TICKET: " . $first->ticket_number . "\n";
    echo "FIRST STATUS: " . $first->registration_status . "\n";
    echo "FIRST MASSA: " . ($first->massa ? $first->massa->nama_lengkap : 'NULL') . "\n";
    echo "QR PATH: " . $first->qr_code_path . "\n";
    echo "QR EXISTS: " . (Storage::disk('public')->exists($first->qr_code_path) ? 'YES' : 'NO') . "\n";
}
