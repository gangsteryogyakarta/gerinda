@extends('layouts.app')

@section('title', $massa->nama_lengkap)

@section('content')
    <!-- Header -->
    <div class="page-header">
        <div class="page-header-left">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: var(--bg-tertiary); padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="user" style="width: 24px; height: 24px; color: var(--primary);"></i>
                </div>
                <h1 style="margin: 0;">{{ $massa->nama_lengkap }}</h1>
            </div>
        </div>
        <div class="page-header-right">
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('massa.edit', $massa) }}" class="btn btn-primary">
                    <i data-lucide="edit-2"></i>
                    Edit Data
                </a>
                <a href="{{ route('massa.index') }}" class="btn btn-secondary">
                    <i data-lucide="arrow-left"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="profile-grid">
        <!-- Left Column: Identity & Contact -->
        <div class="col-left">
            <!-- Identity Card -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">
                        <i data-lucide="user-circle" style="color: var(--primary);"></i>
                        Identitas Diri
                    </h3>
                </div>
                <div class="card-body">
                    <div class="detail-row">
                        <div class="detail-icon">
                            <i data-lucide="user"></i>
                        </div>
                        <div class="detail-content">
                            <label>Nama Lengkap</label>
                            <p>{{ $massa->nama_lengkap }}</p>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-icon">
                            <i data-lucide="credit-card"></i>
                        </div>
                        <div class="detail-content">
                            <label>NIK</label>
                            <p>{{ $massa->nik }}</p>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-icon">
                            <i data-lucide="{{ $massa->jenis_kelamin == 'L' ? 'user' : 'user-check' }}"></i>
                        </div>
                        <div class="detail-content">
                            <label>Jenis Kelamin</label>
                            <p>{{ $massa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-icon">
                            <i data-lucide="award"></i>
                        </div>
                        <div class="detail-content">
                            <label>Kategori</label>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <span class="badge badge-{{ $massa->kategori_massa === 'Pengurus' ? 'primary' : 'secondary' }}">
                                    {{ $massa->kategori_massa ?? 'Simpatisan' }}
                                </span>
                                @if($massa->sub_kategori)
                                    <span class="badge badge-info">{{ $massa->sub_kategori }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-icon">
                            <i data-lucide="calendar"></i>
                        </div>
                        <div class="detail-content">
                            <label>Tempat, Tanggal Lahir</label>
                            <p>
                                {{ $massa->tempat_lahir ?? '-' }}, {{ $massa->tanggal_lahir ? $massa->tanggal_lahir->format('d F Y') : '-' }}
                                @if($massa->age)
                                    <span class="text-muted">({{ $massa->age }} tahun)</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-icon">
                            <i data-lucide="briefcase"></i>
                        </div>
                        <div class="detail-content">
                            <label>Pekerjaan</label>
                            <p>{{ $massa->pekerjaan ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Card -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">
                        <i data-lucide="phone" style="color: var(--info);"></i>
                        Kontak
                    </h3>
                </div>
                <div class="card-body">
                    <div class="detail-row">
                        <div class="detail-icon">
                            <i data-lucide="smartphone"></i>
                        </div>
                        <div class="detail-content">
                            <label>Nomor HP / WhatsApp</label>
                            @if($massa->no_hp)
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $massa->no_hp)) }}" target="_blank" class="contact-link">
                                    {{ $massa->no_hp }} <i data-lucide="external-link" style="width: 14px;"></i>
                                </a>
                            @else
                                <p>-</p>
                            @endif
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-icon">
                            <i data-lucide="mail"></i>
                        </div>
                        <div class="detail-content">
                            <label>Email</label>
                            @if($massa->email)
                                <a href="mailto:{{ $massa->email }}" class="contact-link">
                                    {{ $massa->email }}
                                </a>
                            @else
                                <p>-</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Danger Zone for Delete -->
            <div class="card" style="border-color: rgba(239, 68, 68, 0.3);">
                <div class="card-body">
                    <button type="button" class="btn btn-danger btn-block" style="width: 100%; justify-content: center;"
                        onclick="if(confirm('Apakah anda yakin ingin menghapus data ini? Aksi ini tidak dapat dibatalkan.')) document.getElementById('delete-form').submit();">
                        <i data-lucide="trash-2"></i>
                        Hapus Data Massa
                    </button>
                    <form id="delete-form" action="{{ route('massa.destroy', $massa) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Location & Activity -->
        <div class="col-right">
            <!-- Location Card -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">
                        <i data-lucide="map-pin" style="color: var(--danger);"></i>
                        Lokasi & Alamat
                    </h3>
                </div>
                <div class="card-body">
                    <div class="detail-content" style="margin-bottom: 20px;">
                        <label>Alamat Lengkap</label>
                        <p style="font-size: 1.1em; line-height: 1.5;">{{ $massa->alamat }}</p>
                    </div>

                    <div class="location-grid">
                        <div class="location-item">
                            <label>RT/RW</label>
                            <p>{{ $massa->rt ?? '-' }} / {{ $massa->rw ?? '-' }}</p>
                        </div>
                        <div class="location-item">
                            <label>Kelurahan</label>
                            <p>{{ $massa->village->name ?? '-' }}</p>
                        </div>
                        <div class="location-item">
                            <label>Kecamatan</label>
                            <p>{{ $massa->district->name ?? '-' }}</p>
                        </div>
                        <div class="location-item">
                            <label>Kabupaten/Kota</label>
                            <p>{{ $massa->regency->name ?? '-' }}</p>
                        </div>
                        <div class="location-item">
                            <label>Provinsi</label>
                            <p>{{ $massa->province->name ?? '-' }}</p>
                        </div>
                        <div class="location-item">
                            <label>Kode Pos</label>
                            <p>{{ $massa->kode_pos ?? '-' }}</p>
                        </div>
                    </div>

                    @if($massa->latitude)
                        <div class="map-preview">
                            <div class="map-info">
                                <i data-lucide="map"></i>
                                <span>{{ $massa->latitude }}, {{ $massa->longitude }}</span>
                                <span class="badge badge-success" style="margin-left: auto;">Geocoded</span>
                            </div>
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $massa->latitude }},{{ $massa->longitude }}" target="_blank" class="btn btn-sm btn-secondary" style="width: 100%; justify-content: center; margin-top: 12px;">
                                Buka di Google Maps
                            </a>
                        </div>
                    @else
                         <div class="alert alert-warning" style="margin-top: 16px; font-size: 0.9em;">
                            <i data-lucide="alert-triangle" style="width: 16px;"></i>
                            <span>Belum ada koordinat lokasi.</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Event History Card -->
            <div class="card">
                <div class="card-header border-bottom" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="card-title">
                        <i data-lucide="history" style="color: var(--warning);"></i>
                        Riwayat Event
                    </h3>
                    @if($massa->loyalty)
                        <span class="badge" style="background: linear-gradient(135deg, #fbbf24, #dfa206); color: #000; border: none;">
                            {{ $massa->loyalty->total_events }} Event
                        </span>
                    @endif
                </div>
                <div class="card-body" style="padding: 0;">
                    @if($massa->registrations && $massa->registrations->count() > 0)
                        <div class="event-list">
                            @foreach($massa->registrations->take(5) as $reg)
                                @if($reg->event)
                                <div class="event-item">
                                    <div class="event-date">
                                        <div class="day">{{ $reg->event->event_start->format('d') }}</div>
                                        <div class="month">{{ $reg->event->event_start->format('M') }}</div>
                                    </div>
                                    <div class="event-details">
                                        <h4>{{ $reg->event->name }}</h4>
                                        <div class="event-meta">
                                            <span>
                                                <i data-lucide="map-pin" style="width: 12px;"></i> {{ $reg->event->venue_name ?? 'Lokasi tidak tersedia' }}
                                            </span>
                                            <span>|</span>
                                            <span class="{{ $reg->attendance_status == 'checked_in' ? 'text-success' : 'text-secondary' }}">
                                                {{ $reg->attendance_status == 'checked_in' ? 'Hadir' : 'Terdaftar' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="event-item" style="opacity: 0.6;">
                                    <div class="event-date" style="background: var(--bg-input);">
                                        <div class="day">-</div>
                                        <div class="month">-</div>
                                    </div>
                                    <div class="event-details">
                                        <h4 style="color: var(--text-muted); font-style: italic;">Event Telah Dihapus</h4>
                                        <div class="event-meta">
                                            <span>ID: {{ $reg->event_id }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                            <i data-lucide="calendar-off" style="width: 32px; height: 32px; margin-bottom: 8px; opacity: 0.5;"></i>
                            <p>Belum ada riwayat event.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .border-bottom {
        border-bottom: 1px solid var(--border-light);
    }

    .profile-grid {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 24px;
    }

    @media (max-width: 1024px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }

    .card {
        margin-bottom: 24px;
        height: fit-content;
    }

    .card-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .card-title i {
        width: 20px;
        height: 20px;
    }

    .detail-row {
        display: flex;
        gap: 16px;
        margin-bottom: 20px;
    }

    .detail-row:last-child {
        margin-bottom: 0;
    }

    .detail-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--bg-tertiary);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
        flex-shrink: 0;
    }

    .detail-content {
        flex: 1;
    }

    .detail-content label {
        display: block;
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 4px;
    }

    .detail-content p {
        font-size: 15px;
        color: var(--text-primary);
        font-weight: 500;
        margin: 0;
    }

    .contact-link {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: opacity 0.2s;
    }

    .contact-link:hover {
        opacity: 0.8;
        text-decoration: underline;
    }

    /* Location Grid */
    .location-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        background: var(--bg-tertiary);
        padding: 20px;
        border-radius: var(--radius);
        margin-bottom: 20px;
        border: 1px solid var(--border-light);
    }

    .location-item label {
        display: block;
        font-size: 12px;
        color: var(--text-muted);
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .location-item p {
        font-size: 14px;
        color: var(--text-primary);
        font-weight: 600;
        margin: 0;
    }

    .map-preview {
        border-radius: var(--radius);
        overflow: hidden;
        background: var(--bg-secondary);
        padding: 12px;
        border: 1px solid var(--border-color);
    }
    
    .map-info {
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: monospace;
        font-size: 13px;
    }

    /* Event List */
    .event-list {
        display: flex;
        flex-direction: column;
    }

    .event-item {
        display: flex;
        gap: 16px;
        padding: 16px;
        border-bottom: 1px solid var(--border-light);
        transition: background 0.2s;
    }

    .event-item:last-child {
        border-bottom: none;
    }

    .event-item:hover {
        background: var(--bg-tertiary);
    }

    .event-date {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        width: 50px;
        height: 50px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        line-height: 1;
    }

    .event-date .day {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .event-date .month {
        font-size: 11px;
        text-transform: uppercase;
        color: var(--text-muted);
        font-weight: 600;
        margin-top: 2px;
    }

    .event-details h4 {
        margin: 0 0 6px 0;
        font-size: 15px;
        color: var(--text-primary);
    }

    .event-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--text-muted);
    }

    .text-success { color: var(--success); }
    .text-muted { color: var(--text-muted); }
</style>
@endpush


