<?php

namespace Database\Seeders;

use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;
use Illuminate\Database\Seeder;

class WilayahSeeder extends Seeder
{
    /**
     * Seeder untuk beberapa provinsi contoh.
     * Untuk data lengkap, import dari database wilayah Indonesia resmi.
     */
    public function run(): void
    {
        $provinces = [
            ['code' => '31', 'name' => 'DKI Jakarta'],
            ['code' => '32', 'name' => 'Jawa Barat'],
            ['code' => '33', 'name' => 'Jawa Tengah'],
            ['code' => '34', 'name' => 'DI Yogyakarta'],
            ['code' => '35', 'name' => 'Jawa Timur'],
            ['code' => '36', 'name' => 'Banten'],
            ['code' => '11', 'name' => 'Aceh'],
            ['code' => '12', 'name' => 'Sumatera Utara'],
            ['code' => '13', 'name' => 'Sumatera Barat'],
            ['code' => '14', 'name' => 'Riau'],
            ['code' => '15', 'name' => 'Jambi'],
            ['code' => '16', 'name' => 'Sumatera Selatan'],
            ['code' => '17', 'name' => 'Bengkulu'],
            ['code' => '18', 'name' => 'Lampung'],
            ['code' => '19', 'name' => 'Kepulauan Bangka Belitung'],
            ['code' => '21', 'name' => 'Kepulauan Riau'],
            ['code' => '51', 'name' => 'Bali'],
            ['code' => '52', 'name' => 'Nusa Tenggara Barat'],
            ['code' => '53', 'name' => 'Nusa Tenggara Timur'],
            ['code' => '61', 'name' => 'Kalimantan Barat'],
            ['code' => '62', 'name' => 'Kalimantan Tengah'],
            ['code' => '63', 'name' => 'Kalimantan Selatan'],
            ['code' => '64', 'name' => 'Kalimantan Timur'],
            ['code' => '65', 'name' => 'Kalimantan Utara'],
            ['code' => '71', 'name' => 'Sulawesi Utara'],
            ['code' => '72', 'name' => 'Sulawesi Tengah'],
            ['code' => '73', 'name' => 'Sulawesi Selatan'],
            ['code' => '74', 'name' => 'Sulawesi Tenggara'],
            ['code' => '75', 'name' => 'Gorontalo'],
            ['code' => '76', 'name' => 'Sulawesi Barat'],
            ['code' => '81', 'name' => 'Maluku'],
            ['code' => '82', 'name' => 'Maluku Utara'],
            ['code' => '91', 'name' => 'Papua'],
            ['code' => '92', 'name' => 'Papua Barat'],
        ];

        foreach ($provinces as $province) {
            Province::updateOrCreate(
                ['code' => $province['code']],
                $province
            );
        }

        // Sample regencies for DKI Jakarta
        $jakarta = Province::where('code', '31')->first();
        if ($jakarta) {
            $jakartaRegencies = [
                ['code' => '3171', 'name' => 'Kota Jakarta Pusat'],
                ['code' => '3172', 'name' => 'Kota Jakarta Utara'],
                ['code' => '3173', 'name' => 'Kota Jakarta Barat'],
                ['code' => '3174', 'name' => 'Kota Jakarta Selatan'],
                ['code' => '3175', 'name' => 'Kota Jakarta Timur'],
                ['code' => '3101', 'name' => 'Kabupaten Kepulauan Seribu'],
            ];

            foreach ($jakartaRegencies as $regency) {
                Regency::updateOrCreate(
                    ['code' => $regency['code']],
                    array_merge($regency, ['province_id' => $jakarta->id])
                );
            }
        }

        // Sample regencies for Jawa Barat
        $jabar = Province::where('code', '32')->first();
        if ($jabar) {
            $jabarRegencies = [
                ['code' => '3201', 'name' => 'Kabupaten Bogor'],
                ['code' => '3202', 'name' => 'Kabupaten Sukabumi'],
                ['code' => '3203', 'name' => 'Kabupaten Cianjur'],
                ['code' => '3204', 'name' => 'Kabupaten Bandung'],
                ['code' => '3205', 'name' => 'Kabupaten Garut'],
                ['code' => '3206', 'name' => 'Kabupaten Tasikmalaya'],
                ['code' => '3207', 'name' => 'Kabupaten Ciamis'],
                ['code' => '3208', 'name' => 'Kabupaten Kuningan'],
                ['code' => '3209', 'name' => 'Kabupaten Cirebon'],
                ['code' => '3210', 'name' => 'Kabupaten Majalengka'],
                ['code' => '3211', 'name' => 'Kabupaten Sumedang'],
                ['code' => '3212', 'name' => 'Kabupaten Indramayu'],
                ['code' => '3213', 'name' => 'Kabupaten Subang'],
                ['code' => '3214', 'name' => 'Kabupaten Purwakarta'],
                ['code' => '3215', 'name' => 'Kabupaten Karawang'],
                ['code' => '3216', 'name' => 'Kabupaten Bekasi'],
                ['code' => '3217', 'name' => 'Kabupaten Bandung Barat'],
                ['code' => '3218', 'name' => 'Kabupaten Pangandaran'],
                ['code' => '3271', 'name' => 'Kota Bogor'],
                ['code' => '3272', 'name' => 'Kota Sukabumi'],
                ['code' => '3273', 'name' => 'Kota Bandung'],
                ['code' => '3274', 'name' => 'Kota Cirebon'],
                ['code' => '3275', 'name' => 'Kota Bekasi'],
                ['code' => '3276', 'name' => 'Kota Depok'],
                ['code' => '3277', 'name' => 'Kota Cimahi'],
                ['code' => '3278', 'name' => 'Kota Tasikmalaya'],
                ['code' => '3279', 'name' => 'Kota Banjar'],
            ];

            foreach ($jabarRegencies as $regency) {
                Regency::updateOrCreate(
                    ['code' => $regency['code']],
                    array_merge($regency, ['province_id' => $jabar->id])
                );
            }
        }
    }
}
