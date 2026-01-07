@extends('layouts.app')

@section('title', 'Statistik & Laporan')

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-avatar" style="background: linear-gradient(135deg, #8B5CF6, #6D28D9);">
                <i data-lucide="bar-chart-3" style="width: 24px; height: 24px; color: white;"></i>
            </div>
            <div>
                <h1>Statistik & Laporan</h1>
                <p>Analisis data event dan massa secara komprehensif</p>
            </div>
        </div>
        <div class="page-header-right">
            <form action="{{ route('reports.export') }}" method="GET" style="display: flex; gap: 12px;">
                <select name="event_id" class="form-input" style="width: auto;">
                    <option value="">Semua Event</option>
                    @foreach(\App\Models\Event::orderByDesc('event_start')->get() as $event)
                        <option value="{{ $event->id }}">{{ $event->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="download"></i>
                    Export CSV
                </button>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-row" style="grid-template-columns: repeat(6, 1fr);">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon primary">
                    <i data-lucide="calendar-days"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_events'] }}</div>
            <div class="stat-label">Total Event</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon success">
                    <i data-lucide="users"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($stats['total_massa']) }}</div>
            <div class="stat-label">Total Massa</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon warning">
                    <i data-lucide="ticket"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($stats['total_registrations']) }}</div>
            <div class="stat-label">Registrasi</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon info">
                    <i data-lucide="check-circle"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($stats['total_checkins']) }}</div>
            <div class="stat-label">Check-in</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon" style="background: #EDE9FE; color: #7C3AED;">
                    <i data-lucide="trending-up"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['avg_attendance_rate'] }}%</div>
            <div class="stat-label">Rata-rata Kehadiran</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon" style="background: #FCE7F3; color: #DB2777;">
                    <i data-lucide="repeat"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['conversion_rate'] }}%</div>
            <div class="stat-label">Konversi Massa</div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid-2" style="margin-bottom: 24px;">
        <!-- Monthly Trend -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <div class="card-title-icon">
                        <i data-lucide="line-chart" style="width: 16px; height: 16px;"></i>
                    </div>
                    Tren Bulanan {{ now()->year }}
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Weekly Checkins -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <div class="card-title-icon" style="background: var(--success-light); color: var(--success);">
                        <i data-lucide="calendar-check" style="width: 16px; height: 16px;"></i>
                    </div>
                    Check-in Minggu Ini
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="grid-3-1">
        <!-- Event Performance -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <div class="card-title-icon" style="background: var(--warning-light); color: var(--warning);">
                        <i data-lucide="trophy" style="width: 16px; height: 16px;"></i>
                    </div>
                    Performa Event
                </div>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Registrasi</th>
                            <th>Konfirmasi</th>
                            <th>Kehadiran</th>
                            <th>Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($eventPerformance as $event)
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">{{ Str::limit($event->name, 30) }}</div>
                                    <div style="font-size: 12px; color: var(--text-muted);">{{ $event->event_start->format('d M Y') }}</div>
                                </td>
                                <td><span class="badge badge-primary">{{ $event->registrations_count }}</span></td>
                                <td><span class="badge badge-success">{{ $event->confirmed_count }}</span></td>
                                <td><span class="badge badge-info">{{ $event->checkedin_count }}</span></td>
                                <td>
                                    @php
                                        $rate = $event->confirmed_count > 0 
                                            ? round(($event->checkedin_count / $event->confirmed_count) * 100) 
                                            : 0;
                                    @endphp
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div class="progress" style="width: 60px;">
                                            <div class="progress-bar {{ $rate >= 80 ? 'success' : 'primary' }}" style="width: {{ $rate }}%"></div>
                                        </div>
                                        <span style="font-weight: 600; font-size: 12px;">{{ $rate }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Province Distribution -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <div class="card-title-icon" style="background: var(--info-light); color: var(--info);">
                        <i data-lucide="map" style="width: 16px; height: 16px;"></i>
                    </div>
                    Sebaran Provinsi
                </div>
            </div>
            <div class="card-body" style="padding: 12px 20px;">
                @foreach($provinceStats as $stat)
                    <div style="margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="font-size: 13px; font-weight: 500;">{{ $stat->province?->name ?? 'Unknown' }}</span>
                            <span style="font-size: 13px; font-weight: 700; color: var(--primary);">{{ number_format($stat->total) }}</span>
                        </div>
                        <div class="progress">
                            @php
                                $maxTotal = $provinceStats->first()->total ?? 1;
                                $percentage = ($stat->total / $maxTotal) * 100;
                            @endphp
                            <div class="progress-bar primary" style="width: {{ $percentage }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Monthly Trend Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyData = @json($monthlyStats);
    
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const registrations = new Array(12).fill(0);
    const checkins = new Array(12).fill(0);
    
    monthlyData.forEach(item => {
        registrations[item.month - 1] = item.registrations;
        checkins[item.month - 1] = item.checkins;
    });

    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Registrasi',
                    data: registrations,
                    borderColor: '#DC2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#DC2626',
                },
                {
                    label: 'Check-in',
                    data: checkins,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#10B981',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { usePointStyle: true, padding: 20 }
                }
            },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { color: '#F3F4F6' }, beginAtZero: true }
            }
        }
    });

    // Weekly Checkins Chart
    const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
    const weeklyData = @json($weeklyCheckins);
    
    const days = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    const dailyCheckins = new Array(7).fill(0);
    
    weeklyData.forEach(item => {
        const dayOfWeek = new Date(item.date).getDay();
        const adjustedDay = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
        dailyCheckins[adjustedDay] = item.total;
    });

    new Chart(weeklyCtx, {
        type: 'bar',
        data: {
            labels: days,
            datasets: [{
                label: 'Check-in',
                data: dailyCheckins,
                backgroundColor: [
                    '#DC2626', '#F59E0B', '#10B981', '#3B82F6', 
                    '#8B5CF6', '#EC4899', '#6B7280'
                ],
                borderRadius: 8,
                barThickness: 40,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { color: '#F3F4F6' }, beginAtZero: true }
            }
        }
    });

    lucide.createIcons();
</script>
@endpush
