<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = \App\Models\Event::all();
        $faker = \Faker\Factory::create('id_ID');

        $genericCopywriting = [
            "Hadiri dan ramaikan acara ini! Dapatkan wawasan baru dan perluas jaringan Anda bersama para kader dan simpatisan Partai Gerindra. Acara ini akan menghadirkan berbagai narasumber inspiratif yang siap berbagi pengalaman dan strategi pemenangan.",
            "Mari bersatu untuk Indonesia Raya! Kegiatan ini merupakan wadah konsolidasi dan silaturahmi antar anggota. Jangan lewatkan kesempatan untuk bertemu dengan tokoh-tokoh penting dan mendengarkan arahan langsung dari pimpinan.",
            "Siapkan diri Anda untuk perubahan! Bergabunglah dalam agenda penting ini demi masa depan bangsa yang lebih baik. Kami mengundang seluruh elemen masyarakat untuk berpartisipasi aktif dan menyuarakan aspirasi.",
            "Ajang silaturahmi akbar tahun ini! Pastikan nama Anda terdaftar dalam sejarah pergerakan ini. Nikmati berbagai hiburan, doorprize menarik, dan diskusi yang mencerahkan pikiran dan semangat juang.",
        ];

        // Ensure directory exists
        if (!file_exists(storage_path('app/public/events/banners'))) {
            mkdir(storage_path('app/public/events/banners'), 0755, true);
        }

        foreach ($events as $event) {
            $updated = [];

            // Add Copywriting if missing
            if (empty($event->copywriting)) {
                $baseText = $faker->randomElement($genericCopywriting);
                $details = "\n\nAgenda Acara:\n";
                for ($i = 0; $i < 3; $i++) {
                    $details .= "- " . $faker->sentence(4) . "\n";
                }
                $details .= "\nBenefit Peserta:\n";
                $details .= "- Sertifikat Keikutsertaan\n- Snack & Makan Siang\n- Relasi Baru\n- Souvenir Eksklusif";

                $updated['copywriting'] = $baseText . $details;
            }

            // Add Banner if missing
            if (empty($event->banner_image)) {
                $imageId = rand(10, 100); // Random image ID from picsum
                $imageUrl = "https://picsum.photos/id/{$imageId}/800/400";
                $filename = 'events/banners/dummy_' . $event->id . '_' . time() . '.jpg';
                $path = storage_path('app/public/' . $filename);

                try {
                    $content = file_get_contents($imageUrl);
                    if ($content) {
                        file_put_contents($path, $content);
                        $updated['banner_image'] = $filename;
                    }
                } catch (\Exception $e) {
                    echo "Failed to download image for event {$event->id}: " . $e->getMessage() . "\n";
                }
            }

            if (!empty($updated)) {
                $event->update($updated);
                echo "Updated event: {$event->name}\n";
            }
        }
    }
}
