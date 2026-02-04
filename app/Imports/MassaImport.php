<?php

namespace App\Imports;

use App\Models\Massa;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class MassaImport implements ToModel, WithHeadingRow, WithValidation
{
    private $overwrite;
    private $provinces;
    private $regencies;
    private $districts;
    private $villages;

    public function __construct($overwrite = false)
    {
        $this->overwrite = $overwrite;
    }

    public function model(array $row)
    {
        // Skip if NIK exists and overwrite is false
        if (!$this->overwrite && Massa::where('nik', $row['nik'])->exists()) {
            return null;
        }

        // Clean NIK (remove single quote if exists)
        $nik = ltrim($row['nik'], "'");

        // Map Locations (Hierarchical & Fuzzy)
        $provinceId = null;
        $regencyId = null;
        $districtId = null;
        $villageId = null;

        // Flexible key lookup
        $pKey = $this->findKey($row, ['provinsi', 'province', 'propinsi']);
        
        if ($pKey && !empty($row[$pKey])) {
            $provName = strtoupper(trim($row[$pKey]));
            $province = Province::where('name', $provName)
                ->orWhere('name', 'LIKE', "%{$provName}%")
                ->first();
            
            if ($province) {
                $provinceId = $province->id;

                $rKey = $this->findKey($row, ['kabupaten', 'kabupaten_kota', 'kabupatenkota', 'kota', 'regency']);
                if ($rKey && !empty($row[$rKey])) {
                    $regencyName = strtoupper(trim($row[$rKey]));
                    
                    $regency = Regency::where('province_id', $provinceId)
                        ->where(function($q) use ($regencyName) {
                            $q->where('name', $regencyName)
                              ->orWhere('name', 'LIKE', "%{$regencyName}%");
                        })->first();

                    if ($regency) {
                        $regencyId = $regency->id;

                        $dKey = $this->findKey($row, ['kecamatan', 'district']);
                        if ($dKey && !empty($row[$dKey])) {
                            $districtName = strtoupper(trim($row[$dKey]));
                            $district = District::where('regency_id', $regencyId)
                                ->where('name', $districtName)->first();

                            if ($district) {
                                $districtId = $district->id;

                                $vKey = $this->findKey($row, ['desa', 'desa_kelurahan', 'desakelurahan', 'kelurahan', 'village']);
                                if ($vKey && !empty($row[$vKey])) {
                                    $villageName = strtoupper(trim($row[$vKey]));
                                    $village = Village::where('district_id', $districtId)
                                        ->where('name', $villageName)->first();

                                    if ($village) {
                                        $villageId = $village->id;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $data = [
            'nik' => $nik,
            'nama_lengkap' => $row['nama_lengkap'],
            'kategori_massa' => in_array($row['kategori_massa'] ?? '', ['Pengurus', 'Simpatisan']) ? $row['kategori_massa'] : 'Simpatisan',
            'sub_kategori' => in_array(strtoupper($row['sub_kategori'] ?? ''), ['DPD DIY', 'DPC KABUPATEN', 'PAC']) ? strtoupper($row['sub_kategori']) : null,
            'jenis_kelamin' => in_array(strtoupper($row['jenis_kelamin'] ?? ''), ['L', 'P']) ? strtoupper($row['jenis_kelamin']) : 'L',
            'no_hp' => $row['no_hp'] ?? null,
            'alamat' => $row['alamat'] ?? null,
            'rt' => $row['rt'] ?? null,
            'rw' => $row['rw'] ?? null,
            'tempat_lahir' => $row['tempat_lahir'] ?? null,
            'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? null),
            'email' => $row['email'] ?? null,
            'pekerjaan' => $row['pekerjaan'] ?? null,
            'province_id' => $provinceId,
            'regency_id' => $regencyId,
            'district_id' => $districtId,
            'village_id' => $villageId,
        ];

        return Massa::updateOrCreate(
            ['nik' => $nik],
            $data
        );
    }

    public function rules(): array
    {
        return [
            'nik' => 'required',
            'nama_lengkap' => 'required',
        ];
    }
    
    private function parseDate($date)
    {
        if (!$date) return null;
        try {
            // Excel dates are usually numeric (e.g. 45000)
            if (is_numeric($date)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)->format('Y-m-d');
            }
            
            // If string (e.g. '2025-01-01'), parse directly
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function findKey(array $row, array $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            if (array_key_exists($key, $row)) {
                return $key;
            }
        }
        return null;
    }
}
