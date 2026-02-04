<?php

namespace App\Exports;

use App\Models\Massa;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MassaExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Massa::with(['province', 'regency', 'district', 'village'])
            ->orderByDesc('created_at');

        // Search
        if ($search = $this->request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        // Filter by province
        if ($provinceId = $this->request->input('province')) {
            $query->where('province_id', $provinceId);
        }

        // Filter by kategori_massa
        if ($kategori = $this->request->input('kategori_massa')) {
            $query->where('kategori_massa', $kategori);
        }

        // Filter by geocoding status
        if ($this->request->input('geocoded') === 'yes') {
            $query->whereNotNull('latitude');
        } elseif ($this->request->input('geocoded') === 'no') {
            $query->whereNull('latitude');
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'NIK', 'Nama Lengkap', 'Kategori Massa', 'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir',
            'No HP', 'Email', 'Alamat', 'RT', 'RW', 
            'Provinsi', 'Kabupaten/Kota', 'Kecamatan', 'Desa/Kelurahan',
            'Kode Pos', 'Pekerjaan', 'Terdaftar Pada'
        ];
    }

    public function map($massa): array
    {
        return [
            "'" . $massa->nik, // Force string to prevent scientific notation
            $massa->nama_lengkap,
            $massa->kategori_massa,
            $massa->jenis_kelamin,
            $massa->tempat_lahir,
            $massa->tanggal_lahir?->format('Y-m-d'),
            $massa->no_hp,
            $massa->email,
            $massa->alamat,
            $massa->rt,
            $massa->rw,
            $massa->province?->name,
            $massa->regency?->name,
            $massa->district?->name,
            $massa->village?->name,
            $massa->kode_pos,
            $massa->pekerjaan,
            $massa->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
