<?php

namespace App\Services;

/**
 * NIK Verification Service
 * 
 * Validasi dan parsing data dari NIK (Nomor Induk Kependudukan) Indonesia.
 * NIK terdiri dari 16 digit dengan struktur:
 * - Digit 1-2: Kode Provinsi
 * - Digit 3-4: Kode Kabupaten/Kota
 * - Digit 5-6: Kode Kecamatan
 * - Digit 7-12: Tanggal Lahir (DDMMYY, +40 untuk perempuan)
 * - Digit 13-16: Nomor Urut
 */
class NikVerificationService
{
    /**
     * Verify and parse NIK
     */
    public function verify(string $nik): array
    {
        // Basic validation
        if (!$this->isValidFormat($nik)) {
            return [
                'success' => false,
                'error' => 'Format NIK tidak valid (harus 16 digit angka)',
            ];
        }

        // Parse NIK data
        return $this->parseNik($nik);
    }

    /**
     * Check if NIK format is valid
     */
    public function isValidFormat(string $nik): bool
    {
        // Must be exactly 16 digits
        if (!preg_match('/^\d{16}$/', $nik)) {
            return false;
        }

        // Extract and validate birth date
        $birthInfo = $this->extractBirthDate($nik);
        if (!$birthInfo['valid']) {
            return false;
        }

        return true;
    }

    /**
     * Parse NIK to extract embedded information
     */
    public function parseNik(string $nik): array
    {
        $provinceCode = substr($nik, 0, 2);
        $regencyCode = substr($nik, 2, 2);
        $districtCode = substr($nik, 4, 2);
        $birthInfo = $this->extractBirthDate($nik);

        // Get province and regency names
        $provinceName = $this->getProvinceName($provinceCode);
        $regencyName = $this->getRegencyName($provinceCode, $regencyCode);

        return [
            'success' => true,
            'data' => [
                'nik' => $nik,
                'province_code' => $provinceCode,
                'province_name' => $provinceName,
                'regency_code' => $regencyCode,
                'regency_name' => $regencyName,
                'district_code' => $districtCode,
                'jenis_kelamin' => $birthInfo['gender'],
                'tanggal_lahir' => $birthInfo['birth_date'],
                'is_yogyakarta' => $provinceCode === '34',
            ],
        ];
    }

    /**
     * Extract birth date and gender from NIK
     */
    protected function extractBirthDate(string $nik): array
    {
        $day = (int) substr($nik, 6, 2);
        $month = (int) substr($nik, 8, 2);
        $year = (int) substr($nik, 10, 2);

        // Female has day + 40
        $gender = 'L';
        if ($day > 40) {
            $day -= 40;
            $gender = 'P';
        }

        // Determine century (assume 1900s for > 30, 2000s for <= 30)
        $currentYear = (int) date('y');
        $fullYear = $year > $currentYear ? 1900 + $year : 2000 + $year;

        // Validate date
        if (!checkdate($month, $day, $fullYear)) {
            return ['valid' => false, 'gender' => null, 'birth_date' => null];
        }

        return [
            'valid' => true,
            'gender' => $gender,
            'birth_date' => sprintf('%04d-%02d-%02d', $fullYear, $month, $day),
        ];
    }

    /**
     * Check if NIK is from DI Yogyakarta
     */
    public function isFromYogyakarta(string $nik): bool
    {
        return substr($nik, 0, 2) === '34';
    }

    /**
     * Get province name by code
     */
    protected function getProvinceName(string $code): ?string
    {
        $provinces = [
            '11' => 'Aceh',
            '12' => 'Sumatera Utara',
            '13' => 'Sumatera Barat',
            '14' => 'Riau',
            '15' => 'Jambi',
            '16' => 'Sumatera Selatan',
            '17' => 'Bengkulu',
            '18' => 'Lampung',
            '19' => 'Kepulauan Bangka Belitung',
            '21' => 'Kepulauan Riau',
            '31' => 'DKI Jakarta',
            '32' => 'Jawa Barat',
            '33' => 'Jawa Tengah',
            '34' => 'DI Yogyakarta',
            '35' => 'Jawa Timur',
            '36' => 'Banten',
            '51' => 'Bali',
            '52' => 'Nusa Tenggara Barat',
            '53' => 'Nusa Tenggara Timur',
            '61' => 'Kalimantan Barat',
            '62' => 'Kalimantan Tengah',
            '63' => 'Kalimantan Selatan',
            '64' => 'Kalimantan Timur',
            '65' => 'Kalimantan Utara',
            '71' => 'Sulawesi Utara',
            '72' => 'Sulawesi Tengah',
            '73' => 'Sulawesi Selatan',
            '74' => 'Sulawesi Tenggara',
            '75' => 'Gorontalo',
            '76' => 'Sulawesi Barat',
            '81' => 'Maluku',
            '82' => 'Maluku Utara',
            '91' => 'Papua',
            '92' => 'Papua Barat',
        ];

        return $provinces[$code] ?? null;
    }

    /**
     * Get regency name for DI Yogyakarta
     */
    protected function getRegencyName(string $provinceCode, string $regencyCode): ?string
    {
        // Yogyakarta regencies (code 34xx)
        if ($provinceCode === '34') {
            $regencies = [
                '01' => 'Kabupaten Kulon Progo',
                '02' => 'Kabupaten Bantul',
                '03' => 'Kabupaten Gunung Kidul',
                '04' => 'Kabupaten Sleman',
                '71' => 'Kota Yogyakarta',
            ];
            return $regencies[$regencyCode] ?? null;
        }

        // For other provinces, try to get from database
        $fullCode = $provinceCode . $regencyCode;
        $regency = \App\Models\Regency::where('id', $fullCode)->first();
        
        return $regency?->name;
    }
}
