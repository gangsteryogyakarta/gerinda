<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class WhatsappTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'content',
        'variables',
        'conditions',
        'image_url',
        'is_system',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'variables' => 'array',
        'conditions' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Available placeholder variables
    public static array $availableVariables = [
        'penerima' => [
            ['key' => 'nama', 'label' => 'Nama Lengkap', 'example' => 'Budi Santoso'],
            ['key' => 'nama_depan', 'label' => 'Nama Depan', 'example' => 'Budi'],
            ['key' => 'alamat', 'label' => 'Alamat', 'example' => 'Jl. Mawar No. 5'],
            ['key' => 'alamat_lengkap', 'label' => 'Alamat Lengkap', 'example' => 'Jl. Mawar No. 5, RT 01/RW 02, Condongcatur, Sleman, DIY'],
            ['key' => 'kecamatan', 'label' => 'Kecamatan', 'example' => 'Sleman'],
            ['key' => 'kelurahan', 'label' => 'Kelurahan', 'example' => 'Condongcatur'],
            ['key' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'example' => '15 Agustus 1990'],
            ['key' => 'umur', 'label' => 'Umur', 'example' => '34'],
            ['key' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'example' => 'Laki-laki'],
            ['key' => 'kategori', 'label' => 'Kategori Massa', 'example' => 'Pengurus'],
            ['key' => 'no_hp', 'label' => 'Nomor HP', 'example' => '08123456789'],
        ],
        'waktu' => [
            ['key' => 'tanggal_hari_ini', 'label' => 'Tanggal Hari Ini', 'example' => '28 Januari 2026'],
            ['key' => 'waktu', 'label' => 'Waktu Sekarang', 'example' => '14:30 WIB'],
            ['key' => 'hari', 'label' => 'Hari', 'example' => 'Selasa'],
        ],
        'event' => [
            ['key' => 'nama_event', 'label' => 'Nama Event', 'example' => 'Rakernas 2026'],
            ['key' => 'tanggal_event', 'label' => 'Tanggal Event', 'example' => '1 Februari 2026'],
            ['key' => 'lokasi_event', 'label' => 'Lokasi Event', 'example' => 'Gedung DPD DIY'],
            ['key' => 'no_tiket', 'label' => 'Nomor Tiket', 'example' => 'TKT-2026-00123'],
            ['key' => 'status_tiket', 'label' => 'Status Tiket', 'example' => 'confirmed'],
        ],
    ];

    // Categories
    public static array $categories = [
        'promosi' => 'Promosi & Undangan',
        'event' => 'Notifikasi Event',
        'birthday' => 'Ucapan Ulang Tahun',
        'survey' => 'Survey & Feedback',
        'transaksi' => 'Konfirmasi Transaksi',
        'umum' => 'Pesan Umum',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Auto-generate slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Parse template content and replace placeholders with actual data
     */
    public function parseContent($massa, ?array $eventContext = null): string
    {
        $content = $this->content;

        // Build replacement map
        $replacements = $this->buildReplacements($massa, $eventContext);

        // First, process conditional blocks
        $content = $this->processConditions($content, $replacements);

        // Then, replace simple placeholders
        foreach ($replacements as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
        }

        return $content;
    }

    /**
     * Build replacement map from massa and context
     */
    protected function buildReplacements($massa, ?array $eventContext = null): array
    {
        $replacements = [];

        // Penerima data
        if ($massa) {
            $replacements['nama'] = $massa->nama_lengkap ?? '';
            $replacements['nama_depan'] = explode(' ', $massa->nama_lengkap ?? '')[0];
            $replacements['alamat'] = $massa->alamat ?? '';
            $replacements['alamat_lengkap'] = $massa->full_address ?? '';
            $replacements['kecamatan'] = $massa->district->name ?? '';
            $replacements['kelurahan'] = $massa->village->name ?? '';
            $replacements['tanggal_lahir'] = $massa->tanggal_lahir ? $massa->tanggal_lahir->translatedFormat('d F Y') : '';
            $replacements['umur'] = $massa->age ?? '';
            $replacements['jenis_kelamin'] = $massa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
            $replacements['kategori'] = $massa->kategori_massa ?? '';
            $replacements['kategori_massa'] = $massa->kategori_massa ?? '';
            $replacements['no_hp'] = $massa->no_hp ?? '';
        }

        // Time data
        $now = now()->setTimezone('Asia/Jakarta');
        $replacements['tanggal_hari_ini'] = $now->translatedFormat('d F Y');
        $replacements['waktu'] = $now->format('H:i') . ' WIB';
        $replacements['hari'] = $now->translatedFormat('l');

        // Event context
        if ($eventContext) {
            $replacements['nama_event'] = $eventContext['nama_event'] ?? '';
            $replacements['tanggal_event'] = $eventContext['tanggal_event'] ?? '';
            $replacements['lokasi_event'] = $eventContext['lokasi_event'] ?? '';
            $replacements['no_tiket'] = $eventContext['no_tiket'] ?? '';
            $replacements['status_tiket'] = $eventContext['status_tiket'] ?? '';
            $replacements['link_survey'] = $eventContext['link_survey'] ?? '';
        }

        return $replacements;
    }

    /**
     * Process conditional blocks in content
     * Supports: {if:field=value}...{else}...{endif}
     */
    protected function processConditions(string $content, array $replacements): string
    {
        // Pattern: {if:field=value}content{else}alt_content{endif} or {if:field=value}content{endif}
        $pattern = '/\{if:(\w+)([=<>!]+)([^\}]+)\}(.*?)(?:\{else\}(.*?))?\{endif\}/s';

        return preg_replace_callback($pattern, function ($matches) use ($replacements) {
            $field = $matches[1];
            $operator = $matches[2];
            $compareValue = trim($matches[3]);
            $trueContent = $matches[4];
            $falseContent = $matches[5] ?? '';

            $actualValue = $replacements[$field] ?? '';

            $conditionMet = $this->evaluateCondition($actualValue, $operator, $compareValue);

            return $conditionMet ? $trueContent : $falseContent;
        }, $content);
    }

    /**
     * Evaluate a single condition
     */
    protected function evaluateCondition($actual, string $operator, $compare): bool
    {
        switch ($operator) {
            case '=':
            case '==':
                return strtolower($actual) === strtolower($compare);
            case '!=':
            case '<>':
                return strtolower($actual) !== strtolower($compare);
            case '>':
                return (float) $actual > (float) $compare;
            case '<':
                return (float) $actual < (float) $compare;
            case '>=':
                return (float) $actual >= (float) $compare;
            case '<=':
                return (float) $actual <= (float) $compare;
            default:
                return false;
        }
    }

    /**
     * Get preview with sample data
     */
    public function getPreview(?array $sampleData = null): string
    {
        // Create sample massa object
        $sampleMassa = (object) [
            'nama_lengkap' => $sampleData['nama'] ?? 'Budi Santoso',
            'alamat' => $sampleData['alamat'] ?? 'Jl. Mawar No. 5',
            'full_address' => 'Jl. Mawar No. 5, RT 01/RW 02, Condongcatur, Sleman, DIY',
            'tanggal_lahir' => now()->subYears(34),
            'age' => 34,
            'jenis_kelamin' => $sampleData['jenis_kelamin'] ?? 'L',
            'kategori_massa' => $sampleData['kategori'] ?? 'Pengurus',
            'no_hp' => '08123456789',
            'district' => (object) ['name' => 'Sleman'],
            'village' => (object) ['name' => 'Condongcatur'],
        ];

        $eventContext = [
            'nama_event' => 'Rakernas 2026',
            'tanggal_event' => '1 Februari 2026',
            'lokasi_event' => 'Gedung DPD DIY',
            'no_tiket' => 'TKT-2026-00123',
            'status_tiket' => 'confirmed',
            'link_survey' => 'https://survey.gerindradiy.com/feedback',
        ];

        return $this->parseContent($sampleMassa, $eventContext);
    }

    /**
     * Extract used variables from content
     */
    public function extractVariables(): array
    {
        preg_match_all('/\{(\w+)\}/', $this->content, $matches);
        return array_unique($matches[1] ?? []);
    }
}
