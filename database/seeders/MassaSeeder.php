<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Massa;
use App\Models\Province;
use App\Models\Regency;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class MassaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $now = Carbon::now();

        // Get DI Yogyakarta ID
        $diyProvince = Province::where('name', 'like', '%Yogyakarta%')->first();
        if (!$diyProvince) {
            $this->command->error('Provinsi DI Yogyakarta tidak ditemukan. Pastikan RegionSeeder sudah dijalankan.');
            return;
        }

        // Get Regencies in DIY for spreading
        $regencies = Regency::where('province_id', $diyProvince->id)->get();
        
        // Define coordinate bounds for DIY Regencies
        $locations = [
            'SLEMAN' => ['lat_min' => -7.75, 'lat_max' => -7.65, 'lng_min' => 110.30, 'lng_max' => 110.45],
            'BANTUL' => ['lat_min' => -8.00, 'lat_max' => -7.85, 'lng_min' => 110.30, 'lng_max' => 110.45],
            'GUNUNG KIDUL' => ['lat_min' => -8.05, 'lat_max' => -7.90, 'lng_min' => 110.50, 'lng_max' => 110.70],
            'KULON PROGO' => ['lat_min' => -7.90, 'lat_max' => -7.75, 'lng_min' => 110.10, 'lng_max' => 110.25],
            'KOTA YOGYAKARTA' => ['lat_min' => -7.82, 'lat_max' => -7.78, 'lng_min' => 110.34, 'lng_max' => 110.40],
        ];

        $existingEvents = Event::all();
        if ($existingEvents->isEmpty()) {
            $this->command->error('Tidak ada event ditemukan. Jalankan EventSeeder terlebih dahulu.');
            return;
        }

        $this->command->info('Creating 100 Massa records...');

        for ($i = 0; $i < 100; $i++) {
            // Pick random regency
            $regency = $regencies->random();
            $locKey = strtoupper($regency->name);
            // Remove KABUPATEN/KOTA prefix mapping if inconsistent, use generic matching
            $bounds = null;
            foreach ($locations as $key => $val) {
                if (str_contains($locKey, $key)) {
                    $bounds = $val;
                    break;
                }
            }
            
            // Fallback to generic DIY bounds if no specific match
            if (!$bounds) {
                $bounds = ['lat_min' => -8.00, 'lat_max' => -7.60, 'lng_min' => 110.10, 'lng_max' => 110.70];
            }

            // Generate Lat/Lng within bounds
            $lat = $faker->latitude($bounds['lat_min'], $bounds['lat_max']);
            $lng = $faker->longitude($bounds['lng_min'], $bounds['lng_max']);

            // Generate NIK (Mocking structure)
            $gender = $faker->randomElement(['L', 'P']);
            $birthDate = $faker->dateTimeBetween('-60 years', '-17 years');
            // Format NIK roughly: Prov(2) City(2) Dist(2) Date(6) Seq(4)
            // Note: This is just random string, not mathematically valid NIK logic, but good enough for dummy
            $nik = $diyProvince->id . str_pad($regency->id, 2, '0', STR_PAD_LEFT) . $faker->numerify('##########');
            // Ensure unique NIK (simple retry)
            while (Massa::where('nik', $nik)->exists()) {
                $nik = $diyProvince->id . $faker->numerify('##############');
            }

            $massa = Massa::create([
                'nik' => $nik,
                'nama_lengkap' => $faker->name($gender == 'L' ? 'male' : 'female'),
                'jenis_kelamin' => $gender,
                'tempat_lahir' => $faker->city,
                'tanggal_lahir' => $birthDate,
                'no_hp' => $faker->phoneNumber,
                'email' => $faker->unique()->safeEmail,
                'alamat' => $faker->address,
                'rt' => $faker->numerify('0##'),
                'rw' => $faker->numerify('0##'),
                'province_id' => $diyProvince->id,
                'regency_id' => $regency->id,
                // district & village skipped for simplicity unless strictly needed (would need deeper queries)
                'latitude' => $lat,
                'longitude' => $lng,
                'status' => 'active',
                'pekerjaan' => $faker->jobTitle,
            ]);

            // Register to 1-5 random events
            $eventsToJoin = $existingEvents->random(rand(1, 5));

            foreach ($eventsToJoin as $event) {
                $status = 'pending';
                $attendance = 'not_arrived';
                $checkedInAt = null;

                if ($event->event_end < $now) {
                    // Past event
                    $status = 'confirmed';
                    // 80% chance attended
                    if ($faker->boolean(80)) {
                        $attendance = 'checked_in';
                        $checkedInAt = Carbon::parse($event->event_start)->addMinutes(rand(0, 60));
                    } else {
                        $attendance = 'no_show';
                    }
                } else {
                    // Upcoming event
                    $status = $faker->randomElement(['pending', 'confirmed']);
                    $attendance = 'not_arrived';
                }

                try {
                    EventRegistration::create([
                        'event_id' => $event->id,
                        'massa_id' => $massa->id,
                        'ticket_number' => 'TKT-' . $event->code . '-' . strtoupper($faker->bothify('?????')),
                        'registration_status' => $status,
                        'confirmed_at' => $status == 'confirmed' ? $now->subDays(rand(1, 30)) : null,
                        'attendance_status' => $attendance,
                        'checked_in_at' => $checkedInAt,
                        'checkin_method' => $attendance == 'checked_in' ? 'qr_scan' : null,
                        'eligible_for_lottery' => true,
                    ]);
                } catch (\Exception $e) {
                    // Ignore duplicates if random picks same event (unlikely with random helper, but safe to catch)
                }
            }
        }

        $this->command->info('✅ 100 Massa created and registered to events!');
    }
}
