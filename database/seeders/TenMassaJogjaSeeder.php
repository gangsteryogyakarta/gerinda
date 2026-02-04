<?php

namespace Database\Seeders;

use App\Models\Massa;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;
use Illuminate\Database\Seeder;

class TenMassaJogjaSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure Province DIY exists
        $prov = Province::firstOrCreate(['name' => 'DI YOGYAKARTA'], ['code' => '34']);

        $data = [
            // 1. Sleman - Depok - Caturtunggal (Village Level)
            [
                'nik' => '3404010101900001', 'nama' => 'Andi Catur', 'hp' => '081200000001',
                'reg' => 'SLEMAN', 'dist' => 'DEPOK', 'vill' => 'CATURTUNGGAL'
            ],
            // 2. Sleman - Depok - Condongcatur (Village Level)
            [
                'nik' => '3404010101900002', 'nama' => 'Budi Condong', 'hp' => '081200000002',
                'reg' => 'SLEMAN', 'dist' => 'DEPOK', 'vill' => 'CONDONGCATUR'
            ],
            // 3. Sleman - Mlati - Sinduadi (Village Level)
            [
                'nik' => '3404010101900003', 'nama' => 'Citra Mlati', 'hp' => '081200000003',
                'reg' => 'SLEMAN', 'dist' => 'MLATI', 'vill' => 'SINDUADI'
            ],
            // 4. Bantul - Kasihan - Tamantirto (Village Level)
            [
                'nik' => '3402010101900004', 'nama' => 'Dedi Kasihan', 'hp' => '081200000004',
                'reg' => 'BANTUL', 'dist' => 'KASIHAN', 'vill' => 'TAMANTIRTO'
            ],
            // 5. Kota - Gondokusuman - Klitren (Village Level)
            [
                'nik' => '3471010101900005', 'nama' => 'Eka Kota', 'hp' => '081200000005',
                'reg' => 'YOGYAKARTA', 'dist' => 'GONDOKUSUMAN', 'vill' => 'KLITREN'
            ],
            
            // 6. Sleman - Godean (District Fallback - Village left empty/random)
            [
                'nik' => '3404010101900006', 'nama' => 'Fajar Godean', 'hp' => '081200000006',
                'reg' => 'SLEMAN', 'dist' => 'GODEAN', 'vill' => null
            ],
            // 7. Bantul - Banguntapan (District Fallback)
            [
                'nik' => '3402010101900007', 'nama' => 'Gita Banguntapan', 'hp' => '081200000007',
                'reg' => 'BANTUL', 'dist' => 'BANGUNTAPAN', 'vill' => null
            ],
            // 8. Kota - Umbulharjo (District Fallback)
            [
                'nik' => '3471010101900008', 'nama' => 'Hadi Umbul', 'hp' => '081200000008',
                'reg' => 'YOGYAKARTA', 'dist' => 'UMBULHARJO', 'vill' => null
            ],

            // 9. Kulon Progo (Regency Fallback)
            [
                'nik' => '3401010101900009', 'nama' => 'Indra Kulon', 'hp' => '081200000009',
                'reg' => 'KULON PROGO', 'dist' => null, 'vill' => null
            ],
            // 10. Gunung Kidul (Regency Fallback)
            [
                'nik' => '3403010101900010', 'nama' => 'Joko Gunung', 'hp' => '081200000010',
                'reg' => 'GUNUNG KIDUL', 'dist' => null, 'vill' => null
            ],
        ];

        foreach ($data as $d) {
            // Find IDs
            $regId = $this->findId(Regency::class, $d['reg'], 'province_id', $prov->id);
            $distId = $d['dist'] && $regId ? $this->findId(District::class, $d['dist'], 'regency_id', $regId) : null;
            $villId = $d['vill'] && $distId ? $this->findId(Village::class, $d['vill'], 'district_id', $distId) : null;

            Massa::updateOrCreate(
                ['nik' => $d['nik']],
                [
                    'nama_lengkap' => $d['nama'],
                    'jenis_kelamin' => 'L',
                    'no_hp' => $d['hp'],
                    'alamat' => 'Alamat Contoh ' . $d['nama'],
                    'province_id' => $prov->id,
                    'regency_id' => $regId,
                    'district_id' => $distId,
                    'village_id' => $villId,
                    'status' => 'active',
                    'created_at' => now(),
                ]
            );
        }
    }

    private function findId($modelClass, $name, $foreignKey, $foreignId)
    {
        $record = $modelClass::where($foreignKey, $foreignId)
            ->where(function($q) use ($name) {
                $q->where('name', $name)
                  ->orWhere('name', 'LIKE', "%$name%");
            })->first();
            
        return $record ? $record->id : null;
    }
}
