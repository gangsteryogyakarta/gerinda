@extends('layouts.app')

@section('title', $massa->nama_lengkap)

@section('content')
    <!-- Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1>👤 {{ $massa->nama_lengkap }}</h1>
            <p>
                <code style="margin-right: 12px;">{{ $massa->nik }}</code>
                @if($massa->latitude)
                    <span class="badge badge-success">Geocoded</span>
                @endif
            </p>
        </div>
        <div class="page-header-right" style="display: flex; gap: 12px;">
            <a href="{{ route('massa.edit', $massa) }}" class="btn btn-secondary">
                <i class="lucide-edit"></i>
                Edit
            </a>
            <a href="{{ route('massa.index') }}" class="btn btn-secondary">
                <i class="lucide-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    <div class="grid-2">
        <!-- Profile Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">🪪 Data Pribadi</h3>
            </div>
            <div class="card-body">
                <div class="profile-header">
                    <div class="profile-avatar">
                        {{ substr($massa->nama_lengkap, 0, 2) }}
                    </div>
                    <div class="profile-info">
                        <h2>{{ $massa->nama_lengkap }}</h2>
                        <p>{{ $massa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                    </div>
                </div>

                <div class="detail-list">
                    <div class="detail-item">
                        <span class="detail-label">NIK</span>
                        <span class="detail-value"><code>{{ $massa->nik }}</code></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Tempat/Tgl Lahir</span>
                        <span class="detail-value">
                            {{ $massa->tempat_lahir ?? '-' }}{{ $massa->tanggal_lahir ? ', ' . $massa->tanggal_lahir->format('d M Y') : '' }}
                            @if($massa->age)
                                <span style="color: var(--text-muted);">({{ $massa->age }} tahun)</span>
                            @endif
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Pekerjaan</span>
                        <span class="detail-value">{{ $massa->pekerjaan ?? '-' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">No HP</span>
                        <span class="detail-value">{{ $massa->no_hp ?? '-' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email</span>
                        <span class="detail-value">{{ $massa->email ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address & Map -->
        <div>
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <h3 class="card-title">📍 Alamat</h3>
                </div>
                <div class="card-body">
                    <p style="margin-bottom: 16px;">{{ $massa->alamat }}</p>
                    
                    @if($massa->rt || $massa->rw)
                        <p style="color: var(--text-secondary);">RT {{ $massa->rt ?? '-' }} / RW {{ $massa->rw ?? '-' }}</p>
                    @endif
                    
                    <div class="address-hierarchy">
                        @if($massa->village)
                            <span>{{ $massa->village->name }}</span>
                        @endif
                        @if($massa->district)
                            <span>{{ $massa->district->name }}</span>
                        @endif
                        @if($massa->regency)
                            <span>{{ $massa->regency->name }}</span>
                        @endif
                        @if($massa->province)
                            <span>{{ $massa->province->name }}</span>
                        @endif
                        @if($massa->kode_pos)
                            <span>{{ $massa->kode_pos }}</span>
                        @endif
                    </div>

                    @if($massa->latitude && $massa->longitude)
                        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                            <strong style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                <i class="lucide-map" style="color: var(--success);"></i>
                                Koordinat
                            </strong>
                            <code>{{ $massa->latitude }}, {{ $massa->longitude }}</code>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Loyalty Stats -->
            @if($massa->loyalty)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">⭐ Loyalitas</h3>
                    </div>
                    <div class="card-body">
                        <div class="loyalty-stats">
                            <div class="loyalty-stat">
                                <div class="loyalty-value">{{ $massa->loyalty->total_events }}</div>
                                <div class="loyalty-label">Event Dihadiri</div>
                            </div>
                            <div class="loyalty-stat">
                                <div class="loyalty-value">{{ $massa->loyalty->total_checkins }}</div>
                                <div class="loyalty-label">Total Check-in</div>
                            </div>
                            <div class="loyalty-stat">
                                <div class="loyalty-value">{{ $massa->loyalty->prizes_won }}</div>
                                <div class="loyalty-label">Hadiah Dimenangkan</div>
                            </div>
                        </div>
                        <div style="text-align: center; margin-top: 16px;">
                            <span class="badge" style="font-size: 16px; padding: 8px 20px; 
                                background: {{ $massa->loyalty->tier === 'platinum' ? 'linear-gradient(135deg, #94a3b8, #475569)' : 
                                    ($massa->loyalty->tier === 'gold' ? 'linear-gradient(135deg, #fbbf24, #f59e0b)' : 
                                    ($massa->loyalty->tier === 'silver' ? 'linear-gradient(135deg, #9ca3af, #6b7280)' : 'var(--bg-tertiary)')) }}; 
                                color: {{ in_array($massa->loyalty->tier, ['gold', 'platinum']) ? '#000' : 'var(--text-primary)' }};">
                                {{ strtoupper($massa->loyalty->tier) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Event History -->
    <div class="card" style="margin-top: 24px;">
        <div class="card-header">
            <h3 class="card-title">📅 Riwayat Event</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Tanggal</th>
                        <th>No Tiket</th>
                        <th>Status</th>
                        <th>Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($massa->registrations as $reg)
                        <tr>
                            <td>
                                <a href="{{ route('events.show', $reg->event) }}" style="color: var(--primary);">
                                    <strong>{{ $reg->event->name }}</strong>
                                </a>
                                <br><span style="font-size: 12px; color: var(--text-muted);">{{ $reg->event->venue_name }}</span>
                            </td>
                            <td>{{ $reg->event->event_start->format('d M Y') }}</td>
                            <td><code>{{ $reg->ticket_number }}</code></td>
                            <td>
                                <span class="badge badge-{{ $reg->registration_status === 'confirmed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($reg->registration_status) }}
                                </span>
                            </td>
                            <td>
                                @if($reg->attendance_status === 'checked_in')
                                    <span class="badge badge-success">
                                        ✓ {{ $reg->checked_in_at?->format('H:i') }}
                                    </span>
                                @else
                                    <span class="badge badge-secondary">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                Belum ada riwayat event
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .profile-header {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 24px;
        padding-bottom: 24px;
        border-bottom: 1px solid var(--border-color);
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 700;
        color: white;
        text-transform: uppercase;
    }

    .profile-info h2 {
        font-size: 24px;
        margin-bottom: 4px;
    }

    .profile-info p {
        color: var(--text-secondary);
    }

    .detail-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .detail-item {
        display: flex;
        gap: 16px;
    }

    .detail-label {
        min-width: 140px;
        font-size: 14px;
        color: var(--text-muted);
    }

    .detail-value {
        flex: 1;
        font-size: 14px;
    }

    .address-hierarchy {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .address-hierarchy span {
        background: var(--bg-tertiary);
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 13px;
    }

    .loyalty-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    .loyalty-stat {
        text-align: center;
        padding: 16px;
        background: var(--bg-tertiary);
        border-radius: var(--radius);
    }

    .loyalty-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--primary);
    }

    .loyalty-label {
        font-size: 12px;
        color: var(--text-muted);
    }

    code {
        background: var(--bg-tertiary);
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-family: monospace;
    }
</style>
@endpush
