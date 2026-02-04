<?php

namespace Database\Seeders;

use App\Models\WhatsappTemplate;
use Illuminate\Database\Seeder;

class WhatsappTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Undangan Event',
                'slug' => 'undangan-event',
                'category' => 'promosi',
                'is_system' => true,
                'content' => '🎉 *UNDANGAN SPESIAL*

Halo {nama_depan}! 👋

Anda diundang hadir di:
📌 *{nama_event}*
📅 {tanggal_event}
📍 {lokasi_event}

{if:kategori_massa=Pengurus}
⭐ Sebagai Pengurus, Anda mendapat kursi VIP!
{endif}

Konfirmasi kehadiran dengan membalas pesan ini.

Salam Perjuangan! ✊🇮🇩
_DPD Gerindra DIY_',
            ],
            [
                'name' => 'Ucapan Ulang Tahun',
                'slug' => 'ulang-tahun',
                'category' => 'birthday',
                'is_system' => true,
                'content' => '🎂 *SELAMAT ULANG TAHUN!* 🎂

Halo {if:jenis_kelamin=L}Bapak{else}Ibu{endif} *{nama}*! 🎉

Hari ini {tanggal_hari_ini}, Anda genap berusia *{umur} tahun*!

Semoga sehat selalu dan terus berkontribusi untuk bangsa.

🎁 Sebagai hadiah, Anda mendapat poin loyalty tambahan!

Salam hangat,
_Keluarga Besar Gerindra DIY_ 🇮🇩',
            ],
            [
                'name' => 'Pengingat H-1 Event',
                'slug' => 'reminder-event',
                'category' => 'event',
                'is_system' => true,
                'content' => '⏰ *REMINDER: BESOK!*

Halo {nama_depan}! 📢

Jangan lupa hadir di:
📌 *{nama_event}*
📅 BESOK, {tanggal_event}
📍 {lokasi_event}

{if:status_tiket=confirmed}
✅ Tiket Anda sudah terkonfirmasi.
Tunjukkan QR Code saat registrasi.
{else}
⚠️ Tiket belum dikonfirmasi. Segera konfirmasi!
{endif}

Pastikan datang tepat waktu! 🕐',
            ],
            [
                'name' => 'Survey Feedback',
                'slug' => 'survey-feedback',
                'category' => 'survey',
                'is_system' => true,
                'content' => '📋 *KAMI BUTUH MASUKAN ANDA*

Halo {nama_depan}! 🙏

Terima kasih telah hadir di *{nama_event}*.

Mohon luangkan 2 menit untuk memberikan feedback:
👉 {link_survey}

Masukan Anda sangat berarti untuk perbaikan kami.

Terima kasih!
_Tim Gerindra DIY_',
            ],
            [
                'name' => 'Konfirmasi Registrasi',
                'slug' => 'konfirmasi-registrasi',
                'category' => 'transaksi',
                'is_system' => true,
                'content' => '✅ *KONFIRMASI REGISTRASI*

Halo {nama}!

Registrasi Anda berhasil:
━━━━━━━━━━━━━━━
📌 Event: *{nama_event}*
🎫 No. Tiket: *{no_tiket}*
📅 Tanggal: {tanggal_event}
📍 Lokasi: {lokasi_event}
━━━━━━━━━━━━━━━

Simpan pesan ini sebagai bukti.

_DPD Gerindra DIY_',
            ],
            [
                'name' => 'Broadcast Umum',
                'slug' => 'broadcast-umum',
                'category' => 'umum',
                'is_system' => true,
                'content' => 'Salam Perjuangan! 🇮🇩

Kepada Yth. {if:jenis_kelamin=L}Bapak{else}Ibu{endif} *{nama}*,

[Isi pesan Anda di sini]

Terima kasih atas perhatiannya.

Salam,
_DPD Gerindra DIY_',
            ],
        ];

        foreach ($templates as $template) {
            WhatsappTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
