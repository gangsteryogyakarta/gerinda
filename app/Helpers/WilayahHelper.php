<?php

namespace App\Helpers;

use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class WilayahHelper
{
    /**
     * Cache TTL in seconds (24 hours)
     */
    protected const CACHE_TTL = 86400;

    /**
     * Get all provinces (cached)
     */
    public static function provinces(): Collection
    {
        return Cache::remember('wilayah_provinces', self::CACHE_TTL, function () {
            return Province::select(['id', 'name'])
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get regencies by province (cached)
     */
    public static function regencies(int $provinceId): Collection
    {
        return Cache::remember("wilayah_regencies_{$provinceId}", self::CACHE_TTL, function () use ($provinceId) {
            return Regency::select(['id', 'name'])
                ->where('province_id', $provinceId)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get districts by regency (cached)
     */
    public static function districts(int $regencyId): Collection
    {
        return Cache::remember("wilayah_districts_{$regencyId}", self::CACHE_TTL, function () use ($regencyId) {
            return District::select(['id', 'name'])
                ->where('regency_id', $regencyId)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get villages by district (cached)
     */
    public static function villages(int $districtId): Collection
    {
        return Cache::remember("wilayah_villages_{$districtId}", self::CACHE_TTL, function () use ($districtId) {
            return Village::select(['id', 'name'])
                ->where('district_id', $districtId)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get Yogyakarta province (commonly used default)
     */
    public static function yogyakartaProvince(): ?Province
    {
        return Cache::remember('wilayah_yogyakarta', self::CACHE_TTL, function () {
            return Province::where('name', 'like', '%Yogyakarta%')->first();
        });
    }

    /**
     * Clear all wilayah caches
     */
    public static function clearCache(): void
    {
        Cache::forget('wilayah_provinces');
        Cache::forget('wilayah_yogyakarta');
        
        // Note: Regency, district, village caches will expire naturally
        // or can be cleared with Cache::flush() for Redis
    }
}
