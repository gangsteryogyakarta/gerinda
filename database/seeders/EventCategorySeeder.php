<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Seeder;

class EventCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Konsolidasi',
                'slug' => 'konsolidasi',
                'icon' => 'users',
                'color' => '#dc2626',
                'description' => 'Kegiatan konsolidasi dan rapat koordinasi partai',
                'sort_order' => 1,
            ],
            [
                'name' => 'Senam Sehat',
                'slug' => 'senam-sehat',
                'icon' => 'heart',
                'color' => '#16a34a',
                'description' => 'Kegiatan senam bersama dan olahraga untuk kesehatan masyarakat',
                'sort_order' => 2,
            ],
            [
                'name' => 'Tebus Sembako',
                'slug' => 'tebus-sembako',
                'icon' => 'shopping-bag',
                'color' => '#2563eb',
                'description' => 'Program bantuan sembako untuk masyarakat',
                'sort_order' => 3,
            ],
            [
                'name' => 'Bakti Sosial',
                'slug' => 'bakti-sosial',
                'icon' => 'gift',
                'color' => '#9333ea',
                'description' => 'Kegiatan bakti sosial dan pelayanan masyarakat',
                'sort_order' => 4,
            ],
            [
                'name' => 'Bazar Murah',
                'slug' => 'bazar-murah',
                'icon' => 'shopping-cart',
                'color' => '#ea580c',
                'description' => 'Bazar dengan harga terjangkau untuk masyarakat',
                'sort_order' => 5,
            ],
            [
                'name' => 'Pengobatan Gratis',
                'slug' => 'pengobatan-gratis',
                'icon' => 'medical',
                'color' => '#0891b2',
                'description' => 'Layanan kesehatan dan pengobatan gratis',
                'sort_order' => 6,
            ],
            [
                'name' => 'Pelatihan',
                'slug' => 'pelatihan',
                'icon' => 'book',
                'color' => '#4f46e5',
                'description' => 'Pelatihan dan pendidikan keterampilan',
                'sort_order' => 7,
            ],
            [
                'name' => 'Kampanye',
                'slug' => 'kampanye',
                'icon' => 'megaphone',
                'color' => '#db2777',
                'description' => 'Kegiatan kampanye dan sosialisasi',
                'sort_order' => 8,
            ],
            [
                'name' => 'Lainnya',
                'slug' => 'lainnya',
                'icon' => 'calendar',
                'color' => '#64748b',
                'description' => 'Kegiatan lainnya',
                'sort_order' => 99,
            ],
        ];

        foreach ($categories as $category) {
            EventCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
