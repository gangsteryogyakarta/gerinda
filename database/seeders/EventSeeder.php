<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Province;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        // Get DI Yogyakarta province
        $diyProvince = Province::where('name', 'like', '%Yogyakarta%')->first();
        $provinceId = $diyProvince?->id;

        // 8 Event yang sudah terlaksana (past events)
        $pastEvents = [
            [
                'name' => 'Rapat Koordinasi DPD Gerindra DIY 2025',
                'description' => 'Rapat koordinasi tahunan seluruh pengurus DPD Partai Gerindra DI Yogyakarta untuk evaluasi program kerja dan penyusunan strategi tahun 2025.',
                'event_start' => $now->copy()->subMonths(6)->setTime(9, 0),
                'event_end' => $now->copy()->subMonths(6)->setTime(15, 0),
                'venue_name' => 'Hotel Tentrem Yogyakarta',
                'venue_address' => 'Jl. P. Mangkubumi No. 72A, Yogyakarta',
                'max_participants' => 150,
                'status' => 'completed',
            ],
            [
                'name' => 'Bakti Sosial dan Pengobatan Gratis',
                'description' => 'Kegiatan bakti sosial berupa pengobatan gratis, pembagian sembako, dan penyuluhan kesehatan untuk masyarakat Bantul.',
                'event_start' => $now->copy()->subMonths(5)->setTime(8, 0),
                'event_end' => $now->copy()->subMonths(5)->setTime(14, 0),
                'venue_name' => 'Balai Desa Bantul',
                'venue_address' => 'Jl. Jend. Sudirman, Bantul',
                'max_participants' => 500,
                'status' => 'completed',
            ],
            [
                'name' => 'Pelatihan Kader Muda Gerindra',
                'description' => 'Program pelatihan kepemimpinan dan kaderisasi untuk generasi muda Partai Gerindra di wilayah DIY.',
                'event_start' => $now->copy()->subMonths(4)->setTime(8, 0),
                'event_end' => $now->copy()->subMonths(4)->addDays(2)->setTime(17, 0),
                'venue_name' => 'Wisma DPD Gerindra DIY',
                'venue_address' => 'Jl. Solo Km 8, Sleman',
                'max_participants' => 100,
                'status' => 'completed',
            ],
            [
                'name' => 'Jalan Sehat Bersama Rakyat',
                'description' => 'Kegiatan jalan sehat bersama masyarakat Sleman dalam rangka memperingati HUT Partai Gerindra.',
                'event_start' => $now->copy()->subMonths(3)->setTime(6, 0),
                'event_end' => $now->copy()->subMonths(3)->setTime(10, 0),
                'venue_name' => 'Stadion Maguwoharjo',
                'venue_address' => 'Jl. Stadion Maguwoharjo, Sleman',
                'max_participants' => 2000,
                'status' => 'completed',
                'enable_lottery' => true,
            ],
            [
                'name' => 'Sosialisasi Program Pro Rakyat',
                'description' => 'Sosialisasi program-program pro rakyat Partai Gerindra kepada masyarakat Kulon Progo.',
                'event_start' => $now->copy()->subMonths(3)->addDays(15)->setTime(13, 0),
                'event_end' => $now->copy()->subMonths(3)->addDays(15)->setTime(17, 0),
                'venue_name' => 'Gedung Kesenian Kulon Progo',
                'venue_address' => 'Jl. Sugiman, Wates, Kulon Progo',
                'max_participants' => 300,
                'status' => 'completed',
            ],
            [
                'name' => 'Turnamen Sepak Bola Antar Desa',
                'description' => 'Turnamen sepak bola antar desa se-Kabupaten Gunung Kidul yang diselenggarakan oleh DPC Gerindra.',
                'event_start' => $now->copy()->subMonths(2)->setTime(8, 0),
                'event_end' => $now->copy()->subMonths(2)->addDays(7)->setTime(17, 0),
                'venue_name' => 'Lapangan Wonosari',
                'venue_address' => 'Jl. Veteran, Wonosari, Gunung Kidul',
                'max_participants' => 500,
                'status' => 'completed',
                'enable_lottery' => true,
            ],
            [
                'name' => 'Dialog Publik: Indonesia Maju 2045',
                'description' => 'Dialog publik dengan tema Indonesia Maju 2045 bersama tokoh masyarakat dan akademisi DIY.',
                'event_start' => $now->copy()->subMonths(1)->setTime(14, 0),
                'event_end' => $now->copy()->subMonths(1)->setTime(17, 0),
                'venue_name' => 'Auditorium UGM',
                'venue_address' => 'Bulaksumur, Sleman',
                'max_participants' => 400,
                'status' => 'completed',
            ],
            [
                'name' => 'Bazar UMKM Gerindra Peduli',
                'description' => 'Bazar produk UMKM binaan Partai Gerindra dalam rangka mendukung ekonomi kerakyatan.',
                'event_start' => $now->copy()->subWeeks(2)->setTime(9, 0),
                'event_end' => $now->copy()->subWeeks(2)->addDays(2)->setTime(21, 0),
                'venue_name' => 'Malioboro Mall',
                'venue_address' => 'Jl. Malioboro, Yogyakarta',
                'max_participants' => 1000,
                'status' => 'completed',
            ],
        ];

        // 2 Event mendatang (upcoming events)
        $upcomingEvents = [
            [
                'name' => 'Konsolidasi Akbar Pemenangan 2029',
                'description' => 'Konsolidasi akbar seluruh kader dan simpatisan Partai Gerindra DIY dalam rangka persiapan Pemilu 2029. Akan dihadiri pengurus pusat dan tokoh nasional.',
                'event_start' => $now->copy()->addWeeks(2)->setTime(8, 0),
                'event_end' => $now->copy()->addWeeks(2)->setTime(17, 0),
                'venue_name' => 'Jogja Expo Center (JEC)',
                'venue_address' => 'Jl. Janti, Banguntapan, Bantul',
                'max_participants' => 5000,
                'status' => 'published',
                'enable_checkin' => true,
                'enable_lottery' => true,
                'registration_start' => $now->copy()->subDays(7),
                'registration_end' => $now->copy()->addWeeks(2)->subDays(1),
            ],
            [
                'name' => 'Gerak Jalan Sehat Merah Putih',
                'description' => 'Gerak jalan sehat dalam rangka memperingati Hari Kemerdekaan RI. Terbuka untuk umum dengan berbagai doorprize menarik.',
                'event_start' => $now->copy()->addMonths(1)->setTime(5, 30),
                'event_end' => $now->copy()->addMonths(1)->setTime(10, 0),
                'venue_name' => 'Alun-alun Utara Yogyakarta',
                'venue_address' => 'Jl. Alun-alun Utara, Yogyakarta',
                'max_participants' => 3000,
                'status' => 'published',
                'enable_checkin' => true,
                'enable_lottery' => true,
                'registration_start' => $now->copy(),
                'registration_end' => $now->copy()->addMonths(1)->subDays(3),
            ],
        ];

        $counter = 1;

        // Create past events
        foreach ($pastEvents as $eventData) {
            $eventData['code'] = 'EVT-' . $now->format('Y') . '-' . str_pad($counter++, 4, '0', STR_PAD_LEFT);
            $eventData['slug'] = Str::slug($eventData['name']) . '-' . Str::random(5);
            $eventData['province_id'] = $provinceId;
            $eventData['current_participants'] = rand(50, $eventData['max_participants']);
            
            Event::create($eventData);
        }

        // Create upcoming events
        foreach ($upcomingEvents as $eventData) {
            $eventData['code'] = 'EVT-' . $now->format('Y') . '-' . str_pad($counter++, 4, '0', STR_PAD_LEFT);
            $eventData['slug'] = Str::slug($eventData['name']) . '-' . Str::random(5);
            $eventData['province_id'] = $provinceId;
            $eventData['current_participants'] = rand(0, 100);
            
            Event::create($eventData);
        }

        $this->command->info('✅ 10 Events created (8 past, 2 upcoming)');
    }
}
