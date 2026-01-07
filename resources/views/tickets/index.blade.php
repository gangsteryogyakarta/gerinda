@extends('layouts.app')

@section('title', 'Manajemen Tiket')

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-avatar" style="background: linear-gradient(135deg, #F59E0B, #D97706);">
                <i data-lucide="ticket" style="width: 24px; height: 24px; color: white;"></i>
            </div>
            <div>
                <h1>Manajemen Tiket</h1>
                <p>Kelola tiket peserta event</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-body" style="padding: 16px;">
            <form method="GET" action="{{ route('tickets.index') }}">
                <div style="display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
                    <div class="form-group" style="flex: 1; min-width: 250px; margin: 0;">
                        <input type="text" name="search" class="form-input" placeholder="Cari No Tiket, Nama, atau NIK..." 
                               value="{{ request('search') }}">
                    </div>
                    
                    <div class="form-group" style="min-width: 250px; margin: 0;">
                        <select name="event_id" class="form-input">
                            <option value="">Semua Event</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                                    {{ Str::limit($event->name, 40) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group" style="min-width: 150px; margin: 0;">
                        <select name="status" class="form-input">
                            <option value="">Status</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-secondary">
                        <i data-lucide="search"></i>
                        Filter
                    </button>
                    
                    @if(request()->hasAny(['search', 'event_id', 'status']))
                        <a href="{{ route('tickets.index') }}" class="btn btn-secondary">
                            <i data-lucide="x"></i>
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>No Tiket</th>
                        <th>Peserta</th>
                        <th>Event</th>
                        <th>Tanggal Daftar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <i data-lucide="ticket" style="width: 16px; height: 16px; color: var(--text-muted);"></i>
                                    <code>{{ $ticket->ticket_number }}</code>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div class="avatar-sm">{{ substr($ticket->massa->nama_lengkap ?? '?', 0, 1) }}</div>
                                    <div>
                                        <strong>{{ $ticket->massa->nama_lengkap ?? 'Unknown' }}</strong>
                                        <div style="font-size: 12px; color: var(--text-muted);">
                                            {{ $ticket->massa->nik ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $ticket->event->name ?? '-' }}</div>
                                <div style="font-size: 12px; color: var(--text-muted);">
                                    {{ $ticket->event->event_start ? $ticket->event->event_start->format('d M Y') : '-' }}
                                </div>
                            </td>
                            <td>
                                {{ $ticket->created_at->format('d M Y H:i') }}
                            </td>
                            <td>
                                @if($ticket->attendance_status === 'checked_in')
                                    <span class="badge badge-success">
                                        <i data-lucide="check-circle" style="width: 12px; height: 12px; margin-right: 4px;"></i>
                                        Checked In
                                    </span>
                                @elseif($ticket->registration_status === 'confirmed')
                                    <span class="badge badge-primary">Confirmed</span>
                                @elseif($ticket->registration_status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($ticket->registration_status) }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="{{ route('registrations.download-ticket', $ticket->id) }}" class="btn btn-sm btn-secondary" target="_blank" title="Download PDF">
                                        <i data-lucide="download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 60px; color: var(--text-muted);">
                                <div style="font-size: 48px; margin-bottom: 16px;">🎟️</div>
                                <p>Belum ada tiket yang ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($tickets->hasPages())
        <div style="margin-top: 24px; display: flex; justify-content: center;">
            {{ $tickets->links('vendor.pagination.default') }}
        </div>
    @endif
@endsection

@push('styles')
<style>
    .avatar-sm {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: white;
        font-size: 12px;
    }
    
    code {
        background: var(--bg-tertiary);
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-family: monospace;
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

    /* Fix dropdown option visibility */
    select.form-input option {
        background-color: #1f2937; /* Dark background for options */
        color: #f3f4f6; /* Light text for options */
        padding: 8px;
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
</style>
@endpush
