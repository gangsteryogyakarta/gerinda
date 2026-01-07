@extends('layouts.app')

@section('title', 'Daftar Event')

@section('content')
    <!-- Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1>📅 Daftar Event</h1>
            <p>Kelola semua event partai Gerindra</p>
        </div>
        <div class="page-header-right">
            <a href="{{ route('events.create') }}" class="btn btn-primary">
                <i data-lucide="plus"></i>
                Buat Event Baru
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-body" style="padding: 16px;">
            <form method="GET" action="{{ route('events.index') }}" class="filter-form">
                <div style="display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <input type="text" name="search" class="form-input" placeholder="Cari event..." 
                               value="{{ request('search') }}">
                    </div>
                    
                    <div class="form-group" style="min-width: 150px;">
                        <select name="status" class="form-input">
                            <option value="">Semua Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="min-width: 150px;">
                        <select name="category" class="form-input">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-secondary">
                        <i data-lucide="search"></i>
                        Filter
                    </button>
                    
                    @if(request()->hasAny(['search', 'status', 'category']))
                        <a href="{{ route('events.index') }}" class="btn btn-secondary">
                            <i data-lucide="x"></i>
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Events Grid -->
    <div class="events-grid">
        @forelse($events as $event)
            <div class="event-card animate-fadeIn">
                <div class="event-card-header">
                    <div class="event-card-date">
                        <span class="day">{{ $event->event_start->format('d') }}</span>
                        <span class="month">{{ $event->event_start->format('M Y') }}</span>
                    </div>
                    <div class="event-card-status">
                        <span class="badge badge-{{ $event->status === 'published' ? 'success' : ($event->status === 'ongoing' ? 'warning' : ($event->status === 'draft' ? 'info' : 'secondary')) }}">
                            {{ ucfirst($event->status) }}
                        </span>
                    </div>
                </div>
                
                <div class="event-card-body">
                    <div class="event-card-category">
                        @if($event->category)
                            <span style="color: {{ $event->category->color }};">{{ $event->category->name }}</span>
                        @endif
                    </div>
                    <h3 class="event-card-title">{{ $event->name }}</h3>
                    <div class="event-card-meta">
                        <div class="meta-item">
                            <i data-lucide="map-pin"></i>
                            {{ Str::limit($event->venue_name, 30) }}
                        </div>
                        <div class="meta-item">
                            <i data-lucide="clock"></i>
                            {{ $event->event_start->format('H:i') }} WIB
                        </div>
                    </div>
                </div>
                
                <div class="event-card-stats">
                    <div class="stat-item">
                        <span class="stat-value">{{ $event->confirmed_count }}</span>
                        <span class="stat-label">Terdaftar</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ $event->checkedin_count }}</span>
                        <span class="stat-label">Hadir</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ $event->max_participants ?? '∞' }}</span>
                        <span class="stat-label">Kuota</span>
                    </div>
                </div>
                
                <div class="event-card-actions">
                    <a href="{{ route('events.show', $event) }}" class="btn btn-primary btn-sm">
                        <i data-lucide="eye"></i>
                        Detail
                    </a>
                    <a href="{{ route('events.registrations', $event) }}" class="btn btn-secondary btn-sm">
                        <i data-lucide="users"></i>
                        Peserta
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-secondary btn-sm dropdown-toggle">
                            <i data-lucide="more-horizontal"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a href="{{ route('events.edit', $event) }}" class="dropdown-item">
                                <i data-lucide="edit"></i> Edit
                            </a>
                            @if($event->enable_checkin)
                                <a href="{{ route('checkin.event', $event) }}" class="dropdown-item">
                                    <i data-lucide="scan"></i> Check-in
                                </a>
                            @endif
                            @if($event->enable_lottery)
                                <a href="{{ route('lottery.event', $event) }}" class="dropdown-item">
                                    <i data-lucide="gift"></i> Undian
                                </a>
                            @endif
                            <hr style="margin: 8px 0; border-color: var(--border-color);">
                            <form action="{{ route('events.destroy', $event) }}" method="POST" 
                                  onsubmit="return confirm('Yakin ingin menghapus event ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger">
                                    <i data-lucide="trash-2"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-icon">📅</div>
                <h3>Belum Ada Event</h3>
                <p>Mulai dengan membuat event pertama Anda</p>
                <a href="{{ route('events.create') }}" class="btn btn-primary">
                    <i data-lucide="plus"></i>
                    Buat Event Baru
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($events->hasPages())
        <div style="margin-top: 24px; display: flex; justify-content: center;">
            {{ $events->links() }}
        </div>
    @endif
@endsection

@push('styles')
<style>
    .events-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 24px;
    }

    .event-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        transition: all 0.3s ease;
    }

    .event-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--primary);
    }

    .event-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 20px;
        background: linear-gradient(135deg, rgba(220, 38, 38, 0.1) 0%, transparent 100%);
    }

    .event-card-date {
        display: flex;
        flex-direction: column;
        align-items: center;
        background: var(--bg-secondary);
        padding: 12px 16px;
        border-radius: var(--radius);
    }

    .event-card-date .day {
        font-size: 28px;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
    }

    .event-card-date .month {
        font-size: 12px;
        color: var(--text-secondary);
        text-transform: uppercase;
    }

    .event-card-body {
        padding: 0 20px 16px;
    }

    .event-card-category {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .event-card-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 12px;
        line-height: 1.3;
    }

    .event-card-meta {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--text-secondary);
    }

    .meta-item i {
        font-size: 14px;
        color: var(--text-muted);
    }

    .event-card-stats {
        display: flex;
        justify-content: space-around;
        padding: 16px 20px;
        background: var(--bg-tertiary);
        border-top: 1px solid var(--border-color);
        border-bottom: 1px solid var(--border-color);
    }

    .stat-item {
        text-align: center;
    }

    .stat-item .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .stat-item .stat-label {
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
    }

    .event-card-actions {
        display: flex;
        gap: 8px;
        padding: 16px 20px;
    }

    .event-card-actions .btn {
        flex: 1;
    }

    .event-card-actions .dropdown {
        position: relative;
    }

    .event-card-actions .dropdown .btn {
        flex: none;
        padding: 8px 12px;
    }

    .dropdown-menu {
        position: absolute;
        right: 0;
        top: 100%;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        min-width: 160px;
        z-index: 1000;
        display: none;
        box-shadow: var(--shadow-lg);
    }

    .dropdown:hover .dropdown-menu,
    .dropdown-menu:hover {
        display: block;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        color: var(--text-primary);
        text-decoration: none;
        font-size: 14px;
        transition: background 0.2s;
        border: none;
        background: none;
        width: 100%;
        cursor: pointer;
    }

    .dropdown-item:hover {
        background: var(--bg-tertiary);
    }

    .dropdown-item.text-danger {
        color: var(--danger);
    }

    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 80px 40px;
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        border: 2px dashed var(--border-color);
    }

    .empty-icon {
        font-size: 64px;
        margin-bottom: 16px;
    }

    .empty-state h3 {
        font-size: 20px;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: var(--text-secondary);
        margin-bottom: 24px;
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
        transition: all 0.2s ease;
    }

    .form-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
    }

    .form-input::placeholder {
        color: var(--text-muted);
    }

    .badge-secondary {
        background: var(--bg-tertiary);
        color: var(--text-secondary);
    }
</style>
@endpush
