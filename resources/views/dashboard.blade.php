@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-avatar">
                {{ substr(auth()->user()?->name ?? 'A', 0, 1) }}
            </div>
            <div>
                <h1>Halo, {{ auth()->user()?->name ?? 'Admin' }}!</h1>
                <p>{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>
        <div class="page-header-right">
            <div class="search-box">
                <i data-lucide="search"></i>
                <input type="text" placeholder="Cari event, massa, atau tiket...">
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-row">
        <div class="stat-card animate-fade-in delay-1">
            <div class="stat-card-header">
                <div class="stat-icon primary">
                    <i data-lucide="calendar-days"></i>
                </div>
                <div class="stat-trend up">
                    <i data-lucide="trending-up" style="width: 14px; height: 14px;"></i>
                    +12%
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_events'] ?? 0 }}</div>
            <div class="stat-label">Total Event</div>
            <div class="stat-progress">
                <div class="stat-progress-bar">
                    <div class="stat-progress-fill" style="width: {{ min(100, ($stats['active_events'] ?? 0) / max(1, $stats['total_events'] ?? 1) * 100) }}%"></div>
                </div>
            </div>
        </div>

        <div class="stat-card animate-fade-in delay-2">
            <div class="stat-card-header">
                <div class="stat-icon success">
                    <i data-lucide="users"></i>
                </div>
                <div class="stat-trend up">
                    <i data-lucide="trending-up" style="width: 14px; height: 14px;"></i>
                    +24%
                </div>
            </div>
            <div class="stat-value">{{ number_format($stats['total_massa'] ?? 0) }}</div>
            <div class="stat-label">Total Massa</div>
            <div class="stat-progress">
                <div class="stat-progress-bar">
                    <div class="stat-progress-fill success" style="width: 75%"></div>
                </div>
            </div>
        </div>

        <div class="stat-card animate-fade-in delay-3">
            <div class="stat-card-header">
                <div class="stat-icon warning">
                    <i data-lucide="ticket"></i>
                </div>
                <div class="stat-trend up">
                    <i data-lucide="trending-up" style="width: 14px; height: 14px;"></i>
                    +8%
                </div>
            </div>
            <div class="stat-value">{{ number_format($stats['total_registrations'] ?? 0) }}</div>
            <div class="stat-label">Total Registrasi</div>
            <div class="stat-progress">
                <div class="stat-progress-bar">
                    <div class="stat-progress-fill warning" style="width: 60%"></div>
                </div>
            </div>
        </div>

        <div class="stat-card animate-fade-in delay-4">
            <div class="stat-card-header">
                <div class="stat-icon info">
                    <i data-lucide="check-circle"></i>
                </div>
                <div class="stat-trend up">
                    <i data-lucide="trending-up" style="width: 14px; height: 14px;"></i>
                    +15%
                </div>
            </div>
            <div class="stat-value">{{ number_format($stats['total_checkins'] ?? 0) }}</div>
            <div class="stat-label">Total Check-in</div>
            <div class="stat-progress">
                <div class="stat-progress-bar">
                    <div class="stat-progress-fill" style="width: {{ $stats['checkin_rate'] ?? 0 }}%; background: linear-gradient(90deg, #3B82F6, #2563EB);"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid-3-1" style="margin-bottom: 24px;">
        <!-- Registration Trend Chart -->
        <div class="card animate-fade-in">
            <div class="card-header">
                <div class="card-title">
                    <div class="card-title-icon">
                        <i data-lucide="trending-up" style="width: 16px; height: 16px;"></i>
                    </div>
                    Tren Registrasi
                </div>
                <div style="display: flex; gap: 16px;">
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 12px;">
                        <span style="width: 10px; height: 10px; background: var(--primary); border-radius: 50%;"></span>
                        Registrasi
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 12px;">
                        <span style="width: 10px; height: 10px; background: var(--success); border-radius: 50%;"></span>
                        Check-in
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="card animate-fade-in">
            <div class="card-header">
                <div class="card-title">
                    <div class="card-title-icon">
                        <i data-lucide="activity" style="width: 16px; height: 16px;"></i>
                    </div>
                    Aktivitas Terbaru
                </div>
            </div>
            <div class="card-body" style="padding: 12px 24px;">
                <div class="activity-list">
                    @forelse($recentActivities ?? [] as $activity)
                        <div class="activity-item">
                            <div class="activity-avatar">
                                {{ substr($activity['name'] ?? 'U', 0, 1) }}
                            </div>
                            <div class="activity-info">
                                <div class="activity-name">{{ $activity['name'] ?? 'Unknown' }}</div>
                                <div class="activity-desc">{{ $activity['action'] ?? '' }}</div>
                            </div>
                            <div class="activity-value">
                                <div class="activity-value-main">{{ $activity['time'] ?? '' }}</div>
                            </div>
                        </div>
                    @empty
                        @for($i = 0; $i < 5; $i++)
                            <div class="activity-item">
                                <div class="activity-avatar" style="background: linear-gradient(135deg, #{{ dechex(rand(100, 255)) . dechex(rand(100, 200)) . dechex(rand(100, 200)) }}, #{{ dechex(rand(100, 200)) . dechex(rand(100, 200)) . dechex(rand(100, 255)) }}); color: white;">
                                    {{ chr(65 + $i) }}
                                </div>
                                <div class="activity-info">
                                    <div class="activity-name">Peserta {{ $i + 1 }}</div>
                                    <div class="activity-desc">Check-in berhasil</div>
                                </div>
                                <div class="activity-value">
                                    <div class="activity-value-main">{{ now()->subMinutes(rand(5, 60))->format('H:i') }}</div>
                                </div>
                            </div>
                        @endfor
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <!-- Upcoming Events -->
        <div class="card animate-fade-in">
            <div class="card-header">
                <div class="card-title">
                    <div class="card-title-icon">
                        <i data-lucide="calendar" style="width: 16px; height: 16px;"></i>
                    </div>
                    Event Mendatang
                </div>
                <a href="{{ route('events.index') }}" class="btn btn-sm btn-secondary">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Tanggal</th>
                            <th>Peserta</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingEvents ?? [] as $event)
                            <tr>
                                <td>
                                    <strong>{{ $event->name }}</strong>
                                    <div style="font-size: 12px; color: var(--text-muted);">{{ Str::limit($event->venue_name, 25) }}</div>
                                </td>
                                <td>{{ $event->event_start->format('d M Y') }}</td>
                                <td>
                                    <span class="badge badge-primary">{{ $event->registrations_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $event->status === 'ongoing' ? 'success' : 'warning' }}">
                                        {{ ucfirst($event->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                    Belum ada event mendatang
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Distribution Chart -->
        <div class="card animate-fade-in">
            <div class="card-header">
                <div class="card-title">
                    <div class="card-title-icon">
                        <i data-lucide="pie-chart" style="width: 16px; height: 16px;"></i>
                    </div>
                    Sebaran Peserta
                </div>
                <select class="form-input" style="width: auto; padding: 8px 12px; font-size: 12px;">
                    <option>Bulan Ini</option>
                    <option>Tahun Ini</option>
                </select>
            </div>
            <div class="card-body">
                <div style="height: 240px;">
                    <canvas id="distributionChart"></canvas>
                </div>
                <div style="display: flex; justify-content: center; gap: 24px; margin-top: 20px;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 13px;">
                        <span style="width: 12px; height: 12px; background: var(--primary); border-radius: 3px;"></span>
                        Terkonfirmasi
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 13px;">
                        <span style="width: 12px; height: 12px; background: var(--success); border-radius: 3px;"></span>
                        Hadir
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 13px;">
                        <span style="width: 12px; height: 12px; background: var(--warning); border-radius: 3px;"></span>
                        Pending
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card animate-fade-in" style="margin-top: 24px;">
        <div class="card-header">
            <div class="card-title">
                <div class="card-title-icon">
                    <i data-lucide="zap" style="width: 16px; height: 16px;"></i>
                </div>
                Aksi Cepat
            </div>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <a href="{{ route('events.create') }}" class="btn btn-primary" style="justify-content: flex-start;">
                    <i data-lucide="plus-circle"></i>
                    Buat Event Baru
                </a>
                <a href="{{ route('massa.create') }}" class="btn btn-secondary" style="justify-content: flex-start;">
                    <i data-lucide="user-plus"></i>
                    Tambah Massa
                </a>
                <a href="{{ route('checkin.index') }}" class="btn btn-success" style="justify-content: flex-start;">
                    <i data-lucide="scan"></i>
                    Mulai Check-in
                </a>
                <a href="{{ route('maps.index') }}" class="btn btn-secondary" style="justify-content: flex-start;">
                    <i data-lucide="map"></i>
                    Lihat Peta Sebaran
                </a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Registration Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const trendGradient = trendCtx.createLinearGradient(0, 0, 0, 300);
    trendGradient.addColorStop(0, 'rgba(220, 38, 38, 0.3)');
    trendGradient.addColorStop(1, 'rgba(220, 38, 38, 0)');

    const successGradient = trendCtx.createLinearGradient(0, 0, 0, 300);
    successGradient.addColorStop(0, 'rgba(16, 185, 129, 0.3)');
    successGradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [
                {
                    label: 'Registrasi',
                    data: [150, 230, 180, 320, 280, 450, 520],
                    borderColor: '#DC2626',
                    backgroundColor: trendGradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#DC2626',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                },
                {
                    label: 'Check-in',
                    data: [120, 180, 150, 280, 240, 380, 450],
                    borderColor: '#10B981',
                    backgroundColor: successGradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#10B981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1F2937',
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true,
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#9CA3AF',
                        font: { size: 12 }
                    }
                },
                y: {
                    grid: {
                        color: '#F3F4F6'
                    },
                    ticks: {
                        color: '#9CA3AF',
                        font: { size: 12 }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });

    // Distribution Chart
    const distCtx = document.getElementById('distributionChart').getContext('2d');
    new Chart(distCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [
                {
                    label: 'Terkonfirmasi',
                    data: [120, 150, 180, 220, 280, 320],
                    backgroundColor: '#DC2626',
                    borderRadius: 6,
                    barThickness: 16,
                },
                {
                    label: 'Hadir',
                    data: [100, 130, 160, 200, 250, 290],
                    backgroundColor: '#10B981',
                    borderRadius: 6,
                    barThickness: 16,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#9CA3AF',
                        font: { size: 12 }
                    }
                },
                y: {
                    grid: {
                        color: '#F3F4F6'
                    },
                    ticks: {
                        color: '#9CA3AF',
                        font: { size: 12 }
                    }
                }
            },
        }
    });

    // Re-init icons
    lucide.createIcons();
</script>
@endpush
