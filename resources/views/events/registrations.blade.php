@extends('layouts.app')

@section('title', 'Peserta - ' . $event->name)

@section('content')
    <!-- Header -->
    <div class="header" style="margin-bottom: 32px;">
        <div class="header-content" style="display: flex; flex-direction: column; gap: 20px;">
            <div class="header-left">
                <h1>👥 Daftar Peserta</h1>
                <p>{{ $event->name }}</p>
            </div>
            <div class="header-right" style="display: flex; gap: 12px;">
                <a href="{{ route('events.show', $event) }}" class="btn btn-secondary">
                    <i data-lucide="arrow-left"></i>
                    Kembali
                </a>
                <form action="{{ route('events.batch-tickets', $event) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="ticket"></i>
                        Generate Semua Tiket
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
        <div class="stat-card" style="background: var(--bg-card); border: 1px solid var(--border-light); border-radius: 12px; padding: 1.5rem; box-shadow: var(--shadow); backdrop-filter: blur(12px);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <span class="stat-label" style="color: var(--text-secondary); font-size: 0.875rem; font-weight: 600; letter-spacing: 0.5px;">TOTAL PESERTA</span>
                <i data-lucide="users" style="width: 1.5rem; height: 1.5rem; color: #EF4444;"></i>
            </div>
            <div class="stat-value" style="font-size: 1.875rem; font-weight: 800; color: var(--text-primary);">{{ $registrations->total() }}</div>
        </div>
        <div class="stat-card" style="background: var(--bg-card); border: 1px solid var(--border-light); border-radius: 12px; padding: 1.5rem; box-shadow: var(--shadow); backdrop-filter: blur(12px);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <span class="stat-label" style="color: var(--text-secondary); font-size: 0.875rem; font-weight: 600; letter-spacing: 0.5px;">TOTAL PESERTA</span>
                <i data-lucide="users" style="width: 1.5rem; height: 1.5rem; color: #EF4444;"></i>
            </div>
            <div class="stat-value" style="font-size: 1.875rem; font-weight: 800; color: var(--text-primary);">{{ $registrations->total() }}</div>
        </div>
        <div class="stat-card" style="background: var(--bg-card); border: 1px solid var(--border-light); border-radius: 12px; padding: 1.5rem; box-shadow: var(--shadow); backdrop-filter: blur(12px);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <span class="stat-label" style="color: var(--text-secondary); font-size: 0.875rem; font-weight: 600; letter-spacing: 0.5px;">CONFIRMED</span>
                <i data-lucide="check-circle" style="width: 1.5rem; height: 1.5rem; color: #10B981;"></i>
            </div>
            <div class="stat-value" style="font-size: 1.875rem; font-weight: 800; color: var(--text-primary);">{{ $stats['confirmed'] }}</div>
        </div>
        <div class="stat-card" style="background: var(--bg-card); border: 1px solid var(--border-light); border-radius: 12px; padding: 1.5rem; box-shadow: var(--shadow); backdrop-filter: blur(12px);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <span class="stat-label" style="color: var(--text-secondary); font-size: 0.875rem; font-weight: 600; letter-spacing: 0.5px;">SUDAH HADIR</span>
                <i data-lucide="user-check" style="width: 1.5rem; height: 1.5rem; color: #3B82F6;"></i>
            </div>
            <div class="stat-value" style="font-size: 1.875rem; font-weight: 800; color: var(--text-primary);">{{ $stats['checked_in'] }}</div>
        </div>
        <div class="stat-card" style="background: var(--bg-card); border: 1px solid var(--border-light); border-radius: 12px; padding: 1.5rem; box-shadow: var(--shadow); backdrop-filter: blur(12px);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <span class="stat-label" style="color: var(--text-secondary); font-size: 0.875rem; font-weight: 600; letter-spacing: 0.5px;">WAITING LIST</span>
                <i data-lucide="clock" style="width: 1.5rem; height: 1.5rem; color: #F59E0B;"></i>
            </div>
            <div class="stat-value" style="font-size: 1.875rem; font-weight: 800; color: var(--text-primary);">{{ $stats['waitlist'] }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-body" style="padding: 16px;">
            <form method="GET" action="{{ route('events.registrations', $event) }}">
                <div style="display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
                    <div class="form-group" style="flex: 1; min-width: 200px; margin: 0;">
                        <input type="text" name="search" class="form-input" placeholder="Cari nama, NIK, atau tiket..." 
                               value="{{ request('search') }}">
                    </div>
                    
                    <div class="form-group" style="min-width: 150px; margin: 0;">
                        <select name="status" class="form-input">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="waitlist" {{ request('status') == 'waitlist' ? 'selected' : '' }}>Waitlist</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="min-width: 150px; margin: 0;">
                        <select name="attendance" class="form-input">
                            <option value="">Semua Kehadiran</option>
                            <option value="not_arrived" {{ request('attendance') == 'not_arrived' ? 'selected' : '' }}>Belum Hadir</option>
                            <option value="checked_in" {{ request('attendance') == 'checked_in' ? 'selected' : '' }}>Sudah Hadir</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-secondary">
                        <i data-lucide="search"></i>
                        Filter
                    </button>
                    
                    @if(request()->hasAny(['search', 'status', 'attendance']))
                        <a href="{{ route('events.registrations', $event) }}" class="btn btn-secondary">
                            <i data-lucide="x"></i>
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Registrations Table -->
    <div class="card">
        <div class="card-body" style="padding: 0; overflow-x: auto;">
            <table class="table" style="width: 100%; white-space: nowrap;">
                <thead>
                    <tr>
                        <th>Peserta</th>
                        <th>NIK</th>
                        <th>No. Tiket</th>
                        <th>Status</th>
                        <th>Kehadiran</th>
                        <th>Waktu Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                        <tr>
                            <td>
                                @if($reg->massa)
                                    <strong>{{ $reg->massa->nama_lengkap }}</strong>
                                    <br><span style="color: var(--text-muted); font-size: 12px;">{{ $reg->massa->no_hp ?? '-' }}</span>
                                @else
                                    <strong style="color: var(--text-muted); font-style: italic;">(Massa Terhapus)</strong>
                                    <br><span style="color: var(--text-muted); font-size: 12px;">ID: {{ $reg->massa_id }}</span>
                                @endif
                            </td>
                            <td>
                                @if($reg->massa)
                                    <code>{{ $reg->massa->nik }}</code>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td><code>{{ $reg->ticket_number }}</code></td>
                            <td>
                                <span class="badge badge-{{ $reg->registration_status === 'confirmed' ? 'success' : ($reg->registration_status === 'pending' ? 'warning' : ($reg->registration_status === 'waitlist' ? 'info' : 'danger')) }}">
                                    {{ ucfirst($reg->registration_status) }}
                                </span>
                            </td>
                            <td>
                                @if($reg->attendance_status === 'checked_in')
                                    <span class="badge badge-success">
                                        ✓ {{ $reg->checked_in_at?->format('H:i') }}
                                    </span>
                                @else
                                    <span class="badge badge-secondary">Belum</span>
                                @endif
                            </td>
                            <td>{{ $reg->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    @if($reg->qr_code_path)
                                        <a href="{{ route('registrations.download-ticket', $reg) }}" class="btn btn-sm btn-secondary" title="Download Tiket">
                                            <i data-lucide="download"></i>
                                        </a>
                                    @endif
                                    
                                    @if($reg->attendance_status !== 'checked_in' && $reg->registration_status === 'confirmed')
                                        <form action="{{ route('registrations.checkin', $reg) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Manual Check-in">
                                                <i data-lucide="check"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 60px; color: var(--text-muted);">
                                <div style="font-size: 48px; margin-bottom: 16px;">👥</div>
                                <p>Belum ada peserta terdaftar</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($registrations->hasPages())
        <div style="margin-top: 24px; display: flex; justify-content: center;">
            {{ $registrations->links() }}
        </div>
    @endif
@endsection

@push('styles')
<style>
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

    .form-input:focus {
        border-color: var(--primary);
    }

    /* Force proper dropdown styling */
    select option {
        background-color: #ffffff !important;
        color: #000000 !important;
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
