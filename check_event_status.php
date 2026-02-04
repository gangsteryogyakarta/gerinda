<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "App Timezone: " . config('app.timezone') . "\n";
echo "DB Timezone: " . DB::select('SELECT TIMEDIFF(NOW(), UTC_TIMESTAMP) as diff')[0]->diff . "\n";
echo "Server Time (Now): " . now()->format('Y-m-d H:i:s') . "\n";

$event = App\Models\Event::where('name', 'LIKE', '%HAMBALANG%')->first();

if (!$event) {
    echo "Event not found.\n";
    exit;
}

echo "Event ID: " . $event->id . "\n";
echo "Start: " . $event->registration_start . "\n";
echo "End: " . $event->registration_end . "\n";
echo "Max Participants: " . var_export($event->max_participants, true) . "\n";

// Fix Time
echo "Fixing start time...\n";
$event->registration_start = now()->subDay(); // Set to yesterday
$event->registration_end = now()->addDays(30);

// Fix Quota if null
if (is_null($event->max_participants) || $event->max_participants == 0) {
    echo "Fixing max participants (was null/0)...\n";
    $event->max_participants = 1000;
}

$event->save();

echo "UPDATED:\n";
echo "Start: " . $event->registration_start . "\n";
echo "End: " . $event->registration_end . "\n";
echo "Max: " . $event->max_participants . "\n";
