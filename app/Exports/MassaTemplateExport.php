<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MassaTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                "'3404012010900001", // NIK Example
                "Budi Santoso", 
                "Pengurus", 
                "L", 
                "081234567890", 
                "Jl. Kaliurang km 5", 
                "001", "002", 
                "Sleman", "1990-01-01", 
                "budi@jogja.com", 
                "Wiraswasta", 
                "DI YOGYAKARTA", "SLEMAN", "DEPOK", "CATURTUNGGAL"
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'nik', 'nama_lengkap', 'kategori_massa', 'jenis_kelamin', 'no_hp', 
            'alamat', 'rt', 'rw', 'tempat_lahir', 'tanggal_lahir', 
            'email', 'pekerjaan', 'provinsi', 'kabupaten', 'kecamatan', 'desa'
        ];
    }
}
