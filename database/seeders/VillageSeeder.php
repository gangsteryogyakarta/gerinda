<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VillageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $villages = [
            // KOTAGEDE (347113)
            ['id' => '3471131001', 'district_id' => '347113', 'code' => '3471131001', 'name' => 'PRENGGAN'],
            ['id' => '3471131002', 'district_id' => '347113', 'code' => '3471131002', 'name' => 'PURBAYAN'],
            ['id' => '3471131003', 'district_id' => '347113', 'code' => '3471131003', 'name' => 'REJOWINANGUN'],
            
            // TEGALREJO (347101)
            ['id' => '3471011001', 'district_id' => '347101', 'code' => '3471011001', 'name' => 'BENER'],
            ['id' => '3471011002', 'district_id' => '347101', 'code' => '3471011002', 'name' => 'KARANGWARU'],
            ['id' => '3471011003', 'district_id' => '347101', 'code' => '3471011003', 'name' => 'KRICAK'],
            ['id' => '3471011004', 'district_id' => '347101', 'code' => '3471011004', 'name' => 'TEGALREJO'],

            // DEPOK (340403)
            ['id' => '3404032001', 'district_id' => '340403', 'code' => '3404032001', 'name' => 'CATURTUNGGAL'],
            ['id' => '3404032002', 'district_id' => '340403', 'code' => '3404032002', 'name' => 'CONDONGCATUR'],
            ['id' => '3404032003', 'district_id' => '340403', 'code' => '3404032003', 'name' => 'MAGUWOHARJO'],

            // GAMPING (340401)
            ['id' => '3404012001', 'district_id' => '340401', 'code' => '3404012001', 'name' => 'BALECATUR'],
            ['id' => '3404012002', 'district_id' => '340401', 'code' => '3404012002', 'name' => 'AMBARKETAWANG'],
            ['id' => '3404012003', 'district_id' => '340401', 'code' => '3404012003', 'name' => 'BANYURADEN'],
            ['id' => '3404012004', 'district_id' => '340401', 'code' => '3404012004', 'name' => 'NOGOTIRTO'],
            ['id' => '3404012005', 'district_id' => '340401', 'code' => '3404012005', 'name' => 'TRIHANGGO'],

            // GODEAN (340402)
            ['id' => '3404022001', 'district_id' => '340402', 'code' => '3404022001', 'name' => 'SIDOARUM'],
            ['id' => '3404022002', 'district_id' => '340402', 'code' => '3404022002', 'name' => 'SIDOMULYO'],
            ['id' => '3404022003', 'district_id' => '340402', 'code' => '3404022003', 'name' => 'SIDOKARTO'],
            
            // SANDEN (340202)
            ['id' => '3402022001', 'district_id' => '340202', 'code' => '3402022001', 'name' => 'GADINGSARI'],
            ['id' => '3402022002', 'district_id' => '340202', 'code' => '3402022002', 'name' => 'GADINGHARJO'],
            ['id' => '3402022003', 'district_id' => '340202', 'code' => '3402022003', 'name' => 'SRIGADING'],
            ['id' => '3402022004', 'district_id' => '340202', 'code' => '3402022004', 'name' => 'MURTIGADING'],
        ];

        foreach ($villages as $village) {
            \Illuminate\Support\Facades\DB::table('villages')->updateOrInsert(
                ['id' => $village['id']],
                array_merge($village, ['created_at' => now(), 'updated_at' => now()])
            );
        }
        
        $this->command->info('✅ Sample Villages seeded.');
    }
}
