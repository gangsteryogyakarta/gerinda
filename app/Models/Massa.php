<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Massa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'massa';

    protected $fillable = [
        'nik',
        'nama_lengkap',
        'kategori_massa',
        'sub_kategori',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'no_hp',
        'email',
        'alamat',
        'rt',
        'rw',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'kode_pos',
        'latitude',
        'longitude',
        'geocode_source',
        'geocoded_at',
        'pekerjaan',
        'foto',
        'catatan',
        'status',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'geocoded_at' => 'datetime',
    ];

    // Relationships
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function loyalty(): HasOne
    {
        return $this->hasOne(MassaLoyalty::class);
    }

    public function lotteryDraws(): HasMany
    {
        return $this->hasMany(LotteryDraw::class);
    }

    // Accessors
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->alamat,
            $this->rt ? "RT {$this->rt}" : null,
            $this->rw ? "RW {$this->rw}" : null,
            $this->village?->name,
            $this->district?->name,
            $this->regency?->name,
            $this->province?->name,
            $this->kode_pos,
        ]);
        
        return implode(', ', $parts);
    }

    public function getAgeAttribute(): ?int
    {
        if (!$this->tanggal_lahir) {
            return null;
        }
        return $this->tanggal_lahir->age;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByNik($query, string $nik)
    {
        return $query->where('nik', $nik);
    }

    public function scopeInProvince($query, int $provinceId)
    {
        return $query->where('province_id', $provinceId);
    }

    public function scopeInRegency($query, int $regencyId)
    {
        return $query->where('regency_id', $regencyId);
    }

    // Check if massa exists by NIK (for deduplication)
    public static function findByNik(string $nik): ?self
    {
        return static::byNik($nik)->first();
    }
}
