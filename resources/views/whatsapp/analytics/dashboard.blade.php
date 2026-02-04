@extends('layouts.app')

@section('title', 'WhatsApp Analytics')

@section('content')
<div class="analytics-container">
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-avatar">
                <i data-lucide="bar-chart-2" style="width: 24px; height: 24px;"></i>
            </div>
            <div>
                <h1>Analytics Dashboard</h1>
                <p>Monitor performa kampanye WhatsApp Anda</p>
            </div>
        </div>
        <div class="page-header-right">
            <a href="{{ route('whatsapp.index') }}" class="btn-back">
                <i data-lucide="arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <!-- Total Sent -->
        <div class="stat-card">
            <div class="stat-icon primary">
                <i data-lucide="send"></i>
            </div>
            <div>
                <p class="stat-label">Total Pesan</p>
                <h3 class="stat-value">{{ number_format($totalSent) }}</h3>
                <p class="stat-sub">Terkirim ke server</p>
            </div>
        </div>

        <!-- Success Rate -->
        <div class="stat-card">
            <div class="stat-icon success">
                <i data-lucide="check-circle"></i>
            </div>
            <div>
                <p class="stat-label">Delivery Rate</p>
                <h3 class="stat-value">{{ $deliveryRate }}%</h3>
                <p class="stat-sub">Pesan diterima HP</p>
            </div>
        </div>

        <!-- Read Rate -->
        <div class="stat-card">
             <div class="stat-icon info">
                <i data-lucide="eye"></i>
            </div>
            <div>
                <p class="stat-label">Read Rate</p>
                <h3 class="stat-value">{{ $readRate }}%</h3>
                <p class="stat-sub">Pesan dibaca</p>
            </div>
        </div>

        <!-- Failed -->
        <div class="stat-card">
            <div class="stat-icon danger">
                <i data-lucide="alert-circle"></i>
            </div>
            <div>
                <p class="stat-label">Gagal</p>
                <h3 class="stat-value">{{ number_format($totalFailed) }}</h3>
                <p class="stat-sub">Gagal terkirim</p>
            </div>
        </div>
    </div>

    <div class="main-layout">
        <!-- Main Chart -->
        <div class="card chart-card">
            <div class="card-header">
                <h3><i data-lucide="trending-up"></i> Tren Pengiriman (30 Hari)</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="messageTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Campaigns -->
        <div class="card campaigns-card">
            <div class="card-header">
                <h3><i data-lucide="history"></i> Kampanye Terakhir</h3>
            </div>
            <div class="card-body p-0">
                <div class="campaign-list">
                    @forelse($recentCampaigns as $campaign)
                        <div class="campaign-item">
                            <div class="campaign-info">
                                <a href="{{ route('whatsapp.analytics.show', $campaign->id) }}" class="campaign-name">
                                    {{ $campaign->name }}
                                </a>
                                <span class="campaign-date">{{ $campaign->created_at->format('d M H:i') }}</span>
                            </div>
                            <div class="campaign-stats">
                                <div class="stat-badges">
                                    <span>{{ $campaign->log_count }} Pesan</span>
                                    <span>Read: {{ $campaign->read_rate }}%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ $campaign->read_rate }}%"></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">Belum ada kampanye.</div>
                    @endforelse
                </div>
            </div>
            @if(count($recentCampaigns) > 0)
            <div class="card-footer">
                <a href="{{ route('whatsapp.index') }}">Lihat Semua Kampanye <i data-lucide="arrow-right"></i></a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Re-initialize Lucide
        if(typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        const ctx = document.getElementById('messageTrendChart').getContext('2d');
        
        // Gradient for Fill
        const gradientSent = ctx.createLinearGradient(0, 0, 0, 400);
        gradientSent.addColorStop(0, 'rgba(100, 116, 139, 0.2)'); // Slate 500
        gradientSent.addColorStop(1, 'rgba(100, 116, 139, 0)');

        const gradientRead = ctx.createLinearGradient(0, 0, 0, 400);
        gradientRead.addColorStop(0, 'rgba(22, 163, 74, 0.2)'); // Green 600
        gradientRead.addColorStop(1, 'rgba(22, 163, 74, 0)');
        
        fetch("{{ route('whatsapp.analytics.data') }}")
            .then(response => response.json())
            .then(data => {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [
                            {
                                label: 'Terkirim',
                                data: data.sent,
                                borderColor: '#64748b', // Slate 500
                                backgroundColor: gradientSent,
                                pointBackgroundColor: '#64748b',
                                borderWidth: 2,
                                tension: 0.4,
                                fill: true
                            },
                            {
                                label: 'Dibaca',
                                data: data.read,
                                borderColor: '#16a34a', // Green 600
                                backgroundColor: gradientRead,
                                pointBackgroundColor: '#16a34a',
                                borderWidth: 2,
                                tension: 0.4,
                                fill: true,
                                borderDash: [] // Solid line for stronger emphasis
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: { 
                            legend: { 
                                position: 'top',
                                align: 'end',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8,
                                    font: { family: "'Inter', sans-serif", size: 12 }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0,0,0,0.8)',
                                titleFont: { family: "'Inter', sans-serif", size: 13 },
                                bodyFont: { family: "'Inter', sans-serif", size: 12 },
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: true,
                                usePointStyle: true
                            }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                grid: { color: '#f1f5f9', borderDash: [] },
                                ticks: { font: { family: "'Inter', sans-serif", size: 11 }, color: '#94a3b8' },
                                border: { display: false }
                            },
                            x: { 
                                grid: { display: false },
                                ticks: { font: { family: "'Inter', sans-serif", size: 11 }, color: '#94a3b8' },
                                border: { display: false }
                            }
                        }
                    }
                });
            })
            .catch(error => console.error("Error loading chart data:", error));
    });
</script>

@push('styles')
<style>
    /* Glassmorphism Theme Vars */
    :root {
        --primary: #c90d0d;
        --primary-light: rgba(201, 13, 13, 0.1);
        --success: #16a34a;
        --success-light: rgba(22, 163, 74, 0.1);
        --info: #0ea5e9;
        --info-light: rgba(14, 165, 233, 0.1);
        --danger: #ef4444;
        --danger-light: rgba(239, 68, 68, 0.1);
        
        /* More transparent for strong glass effect */
        --glass-bg: rgba(255, 255, 255, 0.55); 
        --glass-border: rgba(255, 255, 255, 0.5);
        --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
        
        --text-primary: #0f172a;
        --text-secondary: #334155;
        --text-muted: #64748b;
        
        --radius: 20px;
    }

    body {
        background: url('{{ asset("img/bg.jpg") }}') no-repeat center center fixed;
        background-size: cover;
        position: relative;
        min-height: 100vh;
    }
    
    body::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        /* Much significantly reduced overlay opacity to let background show through */
        background: linear-gradient(135deg, rgba(248,250,252,0.4), rgba(248,250,252,0.2)); 
        z-index: -1;
        backdrop-filter: blur(5px);
    }
    
    .analytics-container {
        padding: 40px;
        max-width: 1400px;
        margin: 0 auto;
        font-family: 'Inter', sans-serif;
    }

    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
        background: rgba(255, 255, 255, 0.4); /* More transparent */
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        padding: 24px 32px;
        border-radius: var(--radius);
        border: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.05);
    }

    .page-header-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .page-header-avatar {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, var(--primary), #ef4444);
        color: white;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 6px -1px rgba(201, 13, 13, 0.3);
    }

    .page-header h1 {
        font-size: 28px;
        font-weight: 800;
        margin: 0;
        color: var(--text-primary);
        letter-spacing: -0.5px;
    }

    .page-header p {
        margin: 4px 0 0;
        color: var(--text-secondary);
        font-size: 15px;
        font-weight: 500;
    }

    .btn-back {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid var(--glass-border);
        border-radius: 10px;
        color: var(--text-secondary);
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .btn-back:hover {
        background: white;
        color: var(--text-primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-bottom: 32px;
    }
    
    @media (max-width: 1200px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .stats-grid { grid-template-columns: 1fr; }
        .page-header { flex-direction: column; align-items: flex-start; gap: 16px; }
        .page-header-right { width: 100%; }
        .btn-back { width: 100%; justify-content: center; }
    }

    .stat-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 24px;
        border-radius: var(--radius);
        box-shadow: var(--glass-shadow);
        border: 1px solid var(--glass-border);
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        background: rgba(255, 255, 255, 0.95);
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.4);
    }

    .stat-icon.primary { background: rgba(241, 245, 249, 0.8); color: #64748b; }
    .stat-icon.success { background: rgba(220, 252, 231, 0.8); color: var(--success); }
    .stat-icon.info { background: rgba(224, 242, 254, 0.8); color: var(--info); }
    .stat-icon.danger { background: rgba(254, 226, 226, 0.8); color: var(--danger); }

    .stat-label { font-size: 14px; color: var(--text-secondary); font-weight: 600; margin: 0; }
    .stat-value { font-size: 32px; font-weight: 800; margin: 0; color: var(--text-primary); letter-spacing: -1px; line-height: 1.2; text-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .stat-sub { font-size: 13px; color: var(--text-muted); margin: 4px 0 0; }

    /* Main Layout */
    .main-layout {
        display: grid;
        grid-template-columns: 2fr 1.2fr;
        gap: 24px;
    }

    @media (max-width: 1024px) {
        .main-layout { grid-template-columns: 1fr; }
    }

    .card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: var(--radius);
        box-shadow: var(--glass-shadow);
        border: 1px solid var(--glass-border);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }

    .card-header {
        padding: 24px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        background: rgba(255,255,255,0.4);
    }

    .card-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .card-header h3 i {
        color: var(--primary);
        width: 20px;
        height: 20px;
    }

    .card-body { padding: 24px; flex: 1; }
    .card-body.p-0 { padding: 0; }

    .chart-container {
        height: 380px;
        width: 100%;
    }

    /* Campaign List */
    .campaign-list {
        display: flex;
        flex-direction: column;
    }

    .campaign-item {
        padding: 20px 24px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        transition: background-color 0.2s;
    }

    .campaign-item:last-child { border-bottom: none; }
    .campaign-item:hover { background: rgba(255,255,255,0.5); }

    .campaign-info {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .campaign-name {
        font-weight: 700;
        color: var(--text-primary);
        text-decoration: none;
        font-size: 15px;
        display: block;
        margin-bottom: 4px;
    }
    .campaign-name:hover { color: var(--primary); }
    
    .campaign-date { font-size: 12px; color: var(--text-muted); font-weight: 500; background: rgba(0,0,0,0.05); padding: 2px 8px; border-radius: 99px; }

    .campaign-stats {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .stat-badges {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .progress-bar {
        height: 8px;
        background: rgba(0,0,0,0.05);
        border-radius: 99px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--success), #22c55e);
        border-radius: 99px;
        box-shadow: 0 2px 4px rgba(22, 163, 74, 0.2);
    }
    
    .card-footer {
        padding: 20px 24px;
        border-top: 1px solid rgba(0,0,0,0.05);
        background: rgba(255,255,255,0.4);
        text-align: center;
    }
    
    .card-footer a {
        font-size: 14px;
        font-weight: 600;
        color: var(--primary);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        padding: 8px 16px;
        border-radius: 8px;
    }
    
    .card-footer a:hover {
        gap: 8px;
        background: rgba(201, 13, 13, 0.05);
    }
    
    .empty-state {
        padding: 40px;
        text-align: center;
        color: var(--text-muted);
        font-size: 14px;
        background: transparent;
    }
</style>
@endpush
@endsection
