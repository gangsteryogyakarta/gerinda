@extends('layouts.app')

@section('title', $event->name)

@section('content')
    <!-- Header -->
    <div class="page-header">
        <div class="page-header-left">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 8px;">
                <span class="badge badge-{{ $event->status === 'published' ? 'success' : ($event->status === 'ongoing' ? 'warning' : ($event->status === 'draft' ? 'info' : 'secondary')) }}" style="font-size: 14px; padding: 6px 12px;">
                    {{ ucfirst($event->status) }}
                </span>
                <span style="color: var(--text-muted); font-size: 14px;">{{ $event->code }}</span>
            </div>
            <h1>{{ $event->name }}</h1>
            <p style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                @if($event->category)
                    <span style="color: {{ $event->category->color }}; font-weight: 600;">{{ $event->category->name }}</span>
                @endif
                <span>📍 {{ $event->venue_name }}</span>
                <span>📅 {{ $event->event_start->format('d M Y, H:i') }} WIB</span>
            </p>
        </div>
        <div class="page-header-right" style="display: flex; gap: 12px;">
            <a href="{{ route('events.edit', $event) }}" class="btn btn-secondary">
                <i data-lucide="edit"></i>
                Edit
            </a>
            <a href="{{ route('events.print-tickets', $event) }}" class="btn btn-secondary" target="_blank">
                <i data-lucide="printer"></i>
                Cetak Tiket
            </a>
            <a href="{{ route('events.registrations', $event) }}" class="btn btn-primary">
                <i class="lucide-users"></i>
                Lihat Peserta
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card" style="border-left: 4px solid var(--info);">
            <div class="stat-icon-wrapper" style="background: rgba(59, 130, 246, 0.1); color: var(--info);">
                <i data-lucide="users"></i>
            </div>
            <div>
                <div class="stat-value">{{ $event->confirmed_count }}</div>
                <div class="stat-label">Terdaftar</div>
                @if($event->max_participants)
                    <div class="progress small" style="margin-top: 8px;">
                        <div class="progress-bar primary" style="width: {{ min(($event->confirmed_count / $event->max_participants) * 100, 100) }}%;"></div>
                    </div>
                    <span class="stat-subtext">{{ $event->confirmed_count }}/{{ $event->max_participants }}</span>
                @endif
            </div>
        </div>

        <div class="stat-card" style="border-left: 4px solid var(--success);">
            <div class="stat-icon-wrapper" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                <i data-lucide="check-circle"></i>
            </div>
            <div>
                <div class="stat-value">{{ $event->checkedin_count }}</div>
                <div class="stat-label">Sudah Hadir</div>
                @if($event->confirmed_count > 0)
                    <div class="stat-subtext success">
                        {{ round(($event->checkedin_count / $event->confirmed_count) * 100, 1) }}% attendance
                    </div>
                @endif
            </div>
        </div>

        <div class="stat-card" style="border-left: 4px solid var(--warning);">
            <div class="stat-icon-wrapper" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                <i data-lucide="clock"></i>
            </div>
            <div>
                <div class="stat-value">{{ $event->pending_count }}</div>
                <div class="stat-label">Pending</div>
            </div>
        </div>

        <div class="stat-card" style="border-left: 4px solid var(--text-muted);">
            <div class="stat-icon-wrapper" style="background: rgba(255, 255, 255, 0.1); color: var(--text-muted);">
                <i data-lucide="list"></i>
            </div>
            <div>
                <div class="stat-value">{{ $event->waitlist_count }}</div>
                <div class="stat-label">Waiting List</div>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <!-- Event Details -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">📋 Detail Event</h3>
            </div>
            <div class="card-body">
                <div class="detail-list">
                    <div class="detail-item">
                        <span class="detail-label">Lokasi</span>
                        <span class="detail-value">
                            <strong>{{ $event->venue_name }}</strong><br>
                            {{ $event->venue_address }}
                            @if($event->province)
                                <br>{{ $event->district?->name }}, {{ $event->regency?->name }}, {{ $event->province->name }}
                            @endif
                        </span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Jadwal</span>
                        <span class="detail-value">
                            <strong>{{ $event->event_start->format('d M Y') }}</strong><br>
                            {{ $event->event_start->format('H:i') }} - {{ $event->event_end->format('H:i') }} WIB
                        </span>
                    </div>

                    @if($event->registration_start || $event->registration_end)
                        <div class="detail-item">
                            <span class="detail-label">Periode Registrasi</span>
                            <span class="detail-value">
                                {{ $event->registration_start?->format('d M Y H:i') ?? 'Tidak ditentukan' }}
                                s/d
                                {{ $event->registration_end?->format('d M Y H:i') ?? 'Tidak ditentukan' }}
                            </span>
                        </div>
                    @endif

                    <div class="detail-item">
                        <span class="detail-label">Kuota</span>
                        <span class="detail-value">
                            @if($event->max_participants)
                                {{ $event->max_participants }} peserta
                                @if($event->enable_waitlist)
                                    <span class="badge badge-info">+ Waiting List</span>
                                @endif
                            @else
                                <span class="badge badge-success">Unlimited</span>
                            @endif
                        </span>
                    </div>

                    @if($event->description)
                        <div class="detail-item">
                            <span class="detail-label">Deskripsi</span>
                            <span class="detail-value">{{ $event->description }}</span>
                        </div>
                    @endif
                    @if($event->copywriting)
                        <div class="detail-item">
                            <span class="detail-label">Copywriting</span>
                            <span class="detail-value" style="white-space: pre-line;">{{ $event->copywriting }}</span>
                        </div>
                    @endif

                    @if($event->banner_image)
                        <div class="detail-item">
                            <span class="detail-label">Banner</span>
                            <span class="detail-value">
                                <img src="{{ asset('storage/' . $event->banner_image) }}" alt="Banner Event" style="max-width: 100%; border-radius: var(--radius); max-height: 300px; object-fit: cover;">
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions & Features -->
        <div>
            <!-- Status Management -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <h3 class="card-title">⚡ Aksi Cepat</h3>
                </div>
                <div class="card-body">
                    <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
                        @if($event->enable_checkin)
                            <a href="{{ route('checkin.event', $event) }}" class="btn btn-success">
                                <i class="lucide-scan"></i>
                                Mulai Check-in
                            </a>
                        @endif
                        
                        @if($event->enable_lottery)
                            <a href="{{ route('lottery.event', $event) }}" class="btn btn-secondary">
                                <i class="lucide-gift"></i>
                                Undian Hadiah
                            </a>
                        @endif

                        <form action="{{ route('events.batch-tickets', $event) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-secondary">
                                <i class="lucide-ticket"></i>
                                Generate Tiket
                            </button>
                        </form>
                    </div>

                    <!-- Share Link -->
                    <div style="background: var(--bg-tertiary); padding: 12px; border-radius: var(--radius); margin-bottom: 20px;">
                        <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">🔗 Link Pendaftaran Publik</label>
                        <div style="display: flex; gap: 8px;">
                            <input type="text" value="{{ route('public.register', $event) }}" class="form-input" id="shareLink" readonly style="flex: 1; font-size: 13px; padding: 8px;">
                            <button onclick="copyLink()" class="btn btn-secondary btn-sm" title="Salin Link">
                                <i data-lucide="copy"></i>
                            </button>
                            <a href="https://wa.me/?text={{ urlencode('Ayo daftar event ' . $event->name . '! Klik link ini: ' . route('public.register', $event)) }}" target="_blank" class="btn btn-success btn-sm" title="Bagikan ke WhatsApp">
                                <i data-lucide="share-2"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Status Change -->
                    <div class="status-buttons">
                        <span style="font-size: 14px; color: var(--text-secondary); margin-right: 12px;">Ubah Status:</span>
                        @foreach(['draft', 'published', 'ongoing', 'completed', 'cancelled'] as $status)
                            @if($status !== $event->status)
                                <form action="{{ route('events.update-status', $event) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $status }}">
                                    <button type="submit" class="btn btn-sm 
                                        {{ $status === 'published' ? 'btn-success' : '' }}
                                        {{ $status === 'ongoing' ? 'btn-warning' : '' }}
                                        {{ $status === 'completed' ? 'btn-info' : '' }}
                                        {{ $status === 'cancelled' ? 'btn-danger' : '' }}
                                        {{ $status === 'draft' ? 'btn-secondary' : '' }}
                                    ">
                                        {{ ucfirst($status) }}
                                    </button>
                                </form>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <h3 class="card-title">⚙️ Fitur Aktif</h3>
                </div>
                <div class="card-body">
                    <div class="feature-list">
                        <div class="feature-item {{ $event->require_ticket ? 'active' : '' }}">
                            <i class="lucide-ticket"></i>
                            Tiket
                        </div>
                        <div class="feature-item {{ $event->enable_checkin ? 'active' : '' }}">
                            <i class="lucide-scan"></i>
                            Check-in
                        </div>
                        <div class="feature-item {{ $event->enable_lottery ? 'active' : '' }}">
                            <i class="lucide-gift"></i>
                            Undian
                        </div>
                        <div class="feature-item {{ $event->send_wa_notification ? 'active' : '' }}">
                            <i class="lucide-message-circle"></i>
                            WhatsApp
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lottery Prizes -->
            @if($event->enable_lottery && $event->lotteryPrizes->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🎁 Hadiah Undian</h3>
                    </div>
                    <div class="card-body" style="padding: 12px;">
                        @foreach($event->lotteryPrizes as $prize)
                            <div class="prize-item">
                                <div class="prize-info">
                                    <span class="prize-name">{{ $prize->name }}</span>
                                    <span class="prize-qty">{{ $prize->remaining_quantity }}/{{ $prize->quantity }} tersisa</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar success" style="width: {{ ($prize->remaining_quantity / $prize->quantity) * 100 }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Registrations -->
    <div class="card" style="margin-top: 24px;">
        <div class="card-header">
            <h3 class="card-title">👥 Registrasi Terbaru</h3>
            <a href="{{ route('events.registrations', $event) }}" class="btn btn-secondary btn-sm">
                Lihat Semua
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>No. Tiket</th>
                        <th>Status</th>
                        <th>Kehadiran</th>
                        <th>Waktu Daftar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentRegistrations as $reg)
                        <tr>
                            <td>
                                <strong>{{ $reg->massa->nama_lengkap }}</strong>
                                <br><span style="color: var(--text-muted); font-size: 12px;">{{ $reg->massa->no_hp }}</span>
                            </td>
                            <td><code>{{ $reg->ticket_number }}</code></td>
                            <td>
                                <span class="badge badge-{{ $reg->registration_status === 'confirmed' ? 'success' : ($reg->registration_status === 'pending' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($reg->registration_status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $reg->attendance_status === 'checked_in' ? 'success' : 'secondary' }}">
                                    {{ $reg->attendance_status === 'checked_in' ? 'Hadir' : 'Belum' }}
                                </span>
                            </td>
                            <td>{{ $reg->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                Belum ada registrasi
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
    .detail-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .detail-item {
        display: flex;
        gap: 16px;
    }

    .detail-label {
        min-width: 140px;
        font-size: 14px;
        color: var(--text-muted);
        font-weight: 500;
    }

    .detail-value {
        flex: 1;
        font-size: 14px;
        color: var(--text-primary);
    }

    .feature-list {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background: var(--bg-tertiary);
        border-radius: var(--radius);
        font-size: 14px;
        color: var(--text-muted);
        opacity: 0.5;
    }

    .feature-item.active {
        background: rgba(16, 185, 129, 0.15);
        color: var(--success);
        opacity: 1;
    }

    .status-buttons {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
        color: #000;
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
        color: white;
    }

    .btn-info {
        background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%);
        color: white;
    }

    .prize-item {
        padding: 12px;
        background: var(--bg-tertiary);
        border-radius: var(--radius);
        margin-bottom: 8px;
    }

    .prize-item:last-child {
        margin-bottom: 0;
    }

    .prize-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .prize-name {
        font-weight: 600;
    }

    .prize-qty {
        font-size: 12px;
        color: var(--text-secondary);
    }

    code {
        background: var(--bg-tertiary);
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-family: monospace;
    }

    .form-hint {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 4px;
    }
    
    .form-input {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        color: var(--text-primary);
        outline: none;
    }

    /* Enhanced Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
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

    .stat-subtext {
        font-size: 11px;
        color: var(--text-muted);
        display: block;
        margin-top: 4px;
    }

    .stat-subtext.success {
        color: var(--success);
    }

    .progress.small {
        height: 4px;
        background: rgba(255,255,255,0.1);
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
</style>
<script>
    function copyLink() {
        var copyText = document.getElementById("shareLink");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices
        navigator.clipboard.writeText(copyText.value);
        
        // Optional: Show tooltip or toast
        alert("Link berhasil disalin!");
    }
</script>
@endpush
