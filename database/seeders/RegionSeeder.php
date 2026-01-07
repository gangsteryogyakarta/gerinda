<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Provinces
        // Ensure DIY exists or create it
        $diyId = DB::table('provinces')->where('name', 'DI Yogyakarta')->value('id');
        
        if (!$diyId) {
            $diyId = DB::table('provinces')->insertGetId([
                'id' => 34, // Standard code for DIY
                'code' => '34',
                'name' => 'DI Yogyakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info('Created Province: DI Yogyakarta');
        } else {
            $this->command->info('Province DI Yogyakarta already exists.');
        }

        // 2. Regencies (Kabupaten/Kota di DIY)
        $regencies = [
            ['id' => '3401', 'code' => '3401', 'name' => 'KABUPATEN KULON PROGO'],
            ['id' => '3402', 'code' => '3402', 'name' => 'KABUPATEN BANTUL'],
            ['id' => '3403', 'code' => '3403', 'name' => 'KABUPATEN GUNUNG KIDUL'],
            ['id' => '3404', 'code' => '3404', 'name' => 'KABUPATEN SLEMAN'],
            ['id' => '3471', 'code' => '3471', 'name' => 'KOTA YOGYAKARTA'],
        ];

        foreach ($regencies as $regency) {
            $exists = DB::table('regencies')->where('id', $regency['id'])->exists();
            if (!$exists) {
                DB::table('regencies')->insert([
                    'id' => $regency['id'],
                    'province_id' => $diyId,
                    'code' => $regency['code'],
                    'name' => $regency['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $this->command->info('✅ Regencies for DIY seeded.');

        // 3. Districts (Kecamatan - Sample only, 1 per regency for testing)
        // We actually need data for dropdowns, let's add a few key ones
        $districts = [
            // Sleman
            ['id' => '340401', 'regency_id' => '3404', 'code' => '340401', 'name' => 'GAMPING'],
            ['id' => '340402', 'regency_id' => '3404', 'code' => '340402', 'name' => 'GODEAN'],
            ['id' => '340403', 'regency_id' => '3404', 'code' => '340403', 'name' => 'DEPOK'],
            // Bantul
            ['id' => '340201', 'regency_id' => '3402', 'code' => '340201', 'name' => 'SRANDAKAN'],
            ['id' => '340202', 'regency_id' => '3402', 'code' => '340202', 'name' => 'SANDEN'],
            // Kota
            ['id' => '347101', 'regency_id' => '3471', 'code' => '347101', 'name' => 'TEGALREJO'],
            ['id' => '347113', 'regency_id' => '3471', 'code' => '347113', 'name' => 'KOTAGEDE'],
        ];

        foreach ($districts as $district) {
            $exists = DB::table('districts')->where('id', $district['id'])->exists();
            if (!$exists) {
                DB::table('districts')->insert([
                    'id' => $district['id'],
                    'regency_id' => $district['regency_id'],
                    'code' => $district['code'],
                    'name' => $district['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $this->command->info('✅ Sample Districts seeded.');
    }
}
