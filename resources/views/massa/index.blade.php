@extends('layouts.app')

@section('title', 'Data Massa')

@section('content')
    <!-- Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1>👥 Data Massa</h1>
            <p>Database peserta dan pendukung partai</p>
        </div>
        <div class="page-header-right">
            <a href="{{ route('massa.create') }}" class="btn btn-primary">
                <i data-lucide="user-plus"></i>
                Tambah Massa
            </a>
        </div>
    </div>

    <!-- Stats -->
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card" style="border-left: 4px solid var(--primary);">
            <div class="stat-icon-wrapper" style="background: rgba(220, 38, 38, 0.1); color: var(--primary);">
                <i data-lucide="users"></i>
            </div>
            <div>
                <div class="stat-value">{{ number_format($stats['total']) }}</div>
                <div class="stat-label">Total Massa</div>
            </div>
        </div>
        <div class="stat-card" style="border-left: 4px solid var(--success);">
            <div class="stat-icon-wrapper" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                <i data-lucide="map-pin"></i>
            </div>
            <div>
                <div class="stat-value">{{ number_format($stats['geocoded']) }}</div>
                <div class="stat-label">Sudah Geocoded</div>
            </div>
        </div>
        <div class="stat-card" style="border-left: 4px solid var(--info);">
            <div class="stat-icon-wrapper" style="background: rgba(59, 130, 246, 0.1); color: var(--info);">
                <i data-lucide="calendar"></i>
            </div>
            <div>
                <div class="stat-value">{{ number_format($stats['this_month']) }}</div>
                <div class="stat-label">Bulan Ini</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-body" style="padding: 16px;">
            <form method="GET" action="{{ route('massa.index') }}">
                <div style="display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
                    <div class="form-group" style="flex: 1; min-width: 200px; margin: 0;">
                        <input type="text" name="search" class="form-input" placeholder="Cari nama, NIK, atau no HP..." 
                               value="{{ request('search') }}">
                    </div>
                    
                    <div class="form-group" style="min-width: 180px; margin: 0;">
                        <select name="district" id="district_filter" class="form-input">
                            <option value="">Semua Kecamatan</option>
                            @foreach(\App\Models\District::whereHas('regency', function($q){ $q->where('province_id', 34); })->orderBy('name')->get() as $district)
                                <option value="{{ $district->id }}" {{ request('district') == $district->id ? 'selected' : '' }}>
                                    {{ $district->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="min-width: 180px; margin: 0;">
                        <select name="village" id="village_filter" class="form-input">
                            <option value="">Semua Kelurahan</option>
                            @if(request('district') && $villages->isNotEmpty())
                                @foreach($villages as $village)
                                    <option value="{{ $village->id }}" {{ request('village') == $village->id ? 'selected' : '' }}>
                                        {{ $village->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    
                    <div class="form-group" style="min-width: 150px; margin: 0;">
                        <select name="kategori_massa" class="form-input">
                            <option value="">Semua Kategori</option>
                            <option value="Pengurus" {{ request('kategori_massa') == 'Pengurus' ? 'selected' : '' }}>Pengurus</option>
                            <option value="Simpatisan" {{ request('kategori_massa') == 'Simpatisan' ? 'selected' : '' }}>Simpatisan</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-secondary">
                        <i data-lucide="search"></i>
                        Filter
                    </button>
                    
                    @if(request()->hasAny(['search', 'province', 'geocoded']))
                        <a href="{{ route('massa.index') }}" class="btn btn-secondary">
                            <i data-lucide="x"></i>
                            Reset
                        </a>
                    @endif

                    <div style="flex: 1;"></div> 

                    <a href="{{ route('massa.export', request()->all()) }}" class="btn btn-secondary" title="Export Excel sesuai filter">
                        <i data-lucide="download"></i>
                        Export Excel
                    </a>
                    
                    <a href="{{ route('massa.import') }}" class="btn btn-secondary" title="Import Data Excel">
                        <i data-lucide="upload"></i>
                        Import Excel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Massa Table -->
    <div class="card">
        <div style="padding: 12px 16px; border-bottom: 1px solid var(--border-light); background: var(--bg-tertiary); display: flex; align-items: center; gap: 8px; font-size: 0.875rem; color: var(--text-secondary);">
            <i data-lucide="arrow-left-right" style="width: 16px; height: 16px;"></i>
            <span>Geser tabel ke kanan untuk melihat informasi selengkapnya</span>
        </div>
        <div class="card-body" style="padding: 0; overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>Kategori</th>
                        <th>Sub Kategori</th>
                        <th>No HP</th>
                        <th>Lokasi</th>
                        <th>Event</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($massa as $item)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div class="avatar">{{ substr($item->nama_lengkap, 0, 1) }}</div>
                                    <div>
                                        <strong>{{ $item->nama_lengkap }}</strong>
                                        <div style="font-size: 12px; color: var(--text-muted);">
                                            {{ $item->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                            @if($item->tanggal_lahir)
                                                • {{ $item->age }} tahun
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><span style="font-family: monospace; font-size: 14px;">{{ $item->nik }}</span></td>
                            <td>
                                <span class="badge badge-{{ $item->kategori_massa === 'Pengurus' ? 'primary' : 'secondary' }}" style="font-size: 14px;">
                                    {{ $item->kategori_massa ?: 'Simpatisan' }}
                                </span>
                            </td>
                            <td>
                                @if(!empty(trim($item->sub_kategori)))
                                    <span class="badge badge-info" style="font-size: 14px;">{{ $item->sub_kategori }}</span>
                                @elseif($item->kategori_massa === 'Pengurus')
                                    <span class="badge badge-warning" style="font-size: 12px;">(Data Kosong)</span>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>

                            <td>{{ $item->no_hp ?? '-' }}</td>
                            <td>
                                @if($item->regency)
                                    {{ $item->regency->name }}
                                    <br><span style="font-size: 12px; color: var(--text-muted);">{{ $item->province->name ?? '' }}</span>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $item->registrations_count }}</span>
                            </td>
                            <td>
                                @if($item->latitude)
                                    <span class="badge badge-success">Geocoded</span>
                                @else
                                    <span class="badge badge-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="{{ route('massa.show', $item) }}" class="btn btn-sm btn-secondary" title="Detail">
                                        <i data-lucide="eye"></i>
                                    </a>
                                    <a href="{{ route('massa.edit', $item) }}" class="btn btn-sm btn-secondary" title="Edit">
                                        <i data-lucide="edit"></i>
                                    </a>
                                    <form action="{{ route('massa.destroy', $item) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data massa ini?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-secondary" style="color: #EF4444;" title="Hapus">
                                            <i data-lucide="trash-2"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 60px; color: var(--text-muted);">
                                <div style="font-size: 48px; margin-bottom: 16px;">👥</div>
                                <p>Belum ada data massa</p>
                                <a href="{{ route('massa.create') }}" class="btn btn-primary" style="margin-top: 16px;">
                                    <i data-lucide="user-plus"></i>
                                    Tambah Massa
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($massa->hasPages())
        <div style="margin-top: 24px; display: flex; justify-content: center;">
            {{ $massa->links('vendor.pagination.default') }}
        </div>
    @endif
@endsection

    @push('styles')
<style>
    /* Enhanced Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 20px;
        display: flex;
        align-items: flex-start;
        gap: 16px;
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 13px;
        color: var(--text-secondary);
        font-weight: 500;
    }

    .avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: white;
        font-size: 16px;
    }

    .form-input {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 10px 16px;
        color: var(--text-primary);
        font-size: 14px;
        width: 100%;
        outline: none;
    }

    /* Pagination Styles */
    .pagination-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        margin-top: 24px;
    }

    .pagination {
        display: flex;
        gap: 4px;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .page-item {
        display: inline-flex;
    }

    .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 12px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.2s;
        cursor: pointer;
    }

    .page-link:hover {
        background: var(--bg-tertiary);
        color: var(--text-primary);
        border-color: var(--border-light);
    }

    .page-item.active .page-link {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .page-item.disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
        background: transparent;
    }

    .pagination-info {
        font-size: 12px;
        color: var(--text-muted);
    }

    /* Modern Select Styling */
    select.form-input {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%239CA3AF' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 16px;
        padding-right: 40px;
    }

    @media (max-width: 1024px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Fix dropdown option visibility - Override dark theme */
    select.form-input option {
        background-color: #ffffff !important;
        color: #000000 !important;
        padding: 8px;
    }

    .form-input:focus {
        border-color: var(--primary);
    }

    code {
        background: var(--bg-tertiary);
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-family: monospace;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const districtSelect = document.getElementById('district_filter');
        const villageSelect = document.getElementById('village_filter');

        districtSelect.addEventListener('change', function() {
            const districtId = this.value;
            
            // Clear current options
            villageSelect.innerHTML = '<option value="">Semua Kelurahan</option>';
            
            if (districtId) {
                // Disable while loading
                villageSelect.disabled = true;
                
                // Fetch villages
                fetch(`/api/v1/locations/villages/${districtId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(village => {
                            const option = document.createElement('option');
                            option.value = village.id;
                            option.textContent = village.name;
                            villageSelect.appendChild(option);
                        });
                        villageSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error fetching villages:', error);
                        villageSelect.disabled = false;
                    });
            }
        });
    });
</script>
@endpush
    }
</style>
@endpush
