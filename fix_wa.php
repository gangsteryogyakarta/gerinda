<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Event Settings...\n";
$events = App\Models\Event::where('status', 'published')->get();

if ($events->isEmpty()) {
    echo "No published events found.\n";
}

foreach ($events as $event) {
    echo "Event: " . $event->name . " (ID: " . $event->id . ")\n";
    echo "Current Status: " . ($event->send_wa_notification ? "ENABLED" : "DISABLED") . "\n";
    
    if (!$event->send_wa_notification) {
        $event->send_wa_notification = true;
        // Make sure we fill in other required fields if missing/strict, usually update is safer
        $event->save();
        echo " -> UPDATED: Notification ENABLED\n";
    }
}
echo "Done.\n";
