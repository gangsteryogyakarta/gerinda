@extends('layouts.app')

@section('title', 'Undian Hadiah')

@section('content')
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <h1>🎁 Undian Hadiah</h1>
            <p>Pilih event untuk memulai undian hadiah</p>
        </div>
    </div>

    <!-- Events with Lottery -->
    <div class="events-grid">
        @forelse($events as $event)
            <div class="event-card animate-fadeIn">
                <div class="event-card-header">
                    <div class="event-card-date">
                        <span class="day">{{ $event->event_start->format('d') }}</span>
                        <span class="month">{{ $event->event_start->format('M') }}</span>
                    </div>
                    <div class="event-card-status">
                        <span class="badge badge-{{ $event->status === 'ongoing' ? 'success' : ($event->status === 'completed' ? 'info' : 'warning') }}">
                            {{ ucfirst($event->status) }}
                        </span>
                    </div>
                </div>
                
                <div class="event-card-body">
                    <h3 class="event-card-title">{{ $event->name }}</h3>
                    <div class="event-card-meta">
                        <div class="meta-item">
                            <i data-lucide="map-pin"></i>
                            {{ Str::limit($event->venue_name, 30) }}
                        </div>
                    </div>
                </div>
                
                <div class="event-card-stats">
                    <div class="stat-item">
                        <span class="stat-value">{{ $event->lotteryPrizes->sum('quantity') }}</span>
                        <span class="stat-label">Total Hadiah</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ $event->lotteryPrizes->sum('remaining_quantity') }}</span>
                        <span class="stat-label">Tersisa</span>
                    </div>
                </div>
                
                <div class="event-card-actions">
                    <a href="{{ route('lottery.event', $event) }}" class="btn btn-primary" style="flex: 1;">
                        <i data-lucide="gift"></i>
                        Mulai Undian
                    </a>
                    <a href="{{ route('lottery.prizes', $event) }}" class="btn btn-secondary">
                        <i data-lucide="settings"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-icon">🎁</div>
                <h3>Tidak Ada Event dengan Undian</h3>
                <p>Belum ada event yang mengaktifkan fitur undian hadiah</p>
                <a href="{{ route('events.index') }}" class="btn btn-primary">
                    Lihat Semua Event
                </a>
            </div>
        @endforelse
    </div>
@endsection

@push('styles')
<style>
    .events-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 24px;
    }

    .event-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .event-card:hover {
        transform: translateY(-4px);
        border-color: var(--accent);
    }

    .event-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 20px;
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, transparent 100%);
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
        font-size: 24px;
        font-weight: 800;
        color: var(--text-primary);
    }

    .event-card-date .month {
        font-size: 12px;
        color: var(--text-secondary);
    }

    .event-card-body {
        padding: 0 20px 16px;
    }

    .event-card-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--text-secondary);
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
        font-size: 24px;
        font-weight: 700;
    }

    .stat-item .stat-label {
        font-size: 11px;
        color: var(--text-muted);
    }

    .event-card-actions {
        display: flex;
        gap: 8px;
        padding: 16px 20px;
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
        margin-bottom: 8px;
    }

    .empty-state p {
        color: var(--text-secondary);
        margin-bottom: 24px;
    }
</style>
@endpush
