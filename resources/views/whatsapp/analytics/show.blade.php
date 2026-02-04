@extends('layouts.app')

@section('title', 'Detail Kampanye')

@section('content')
<div class="analytics-container">
    <!-- Header -->
    <div class="page-header">
        <div class="page-header-left">
            <div>
                <div class="breadcrumb">
                    <a href="{{ route('whatsapp.analytics.dashboard') }}">Analytics</a>
                    <span>/</span>
                    <span>Kampanye</span>
                </div>
                <h1>{{ $campaign->name }}</h1>
                <p>Dibuat pada {{ $campaign->created_at->format('d M Y H:i') }}</p>
            </div>
        </div>
        <div class="page-header-right">
             <a href="{{ route('whatsapp.analytics.dashboard') }}" class="btn-back">
                <i data-lucide="arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="stats-grid">
        <div class="stat-card" style="border-top: 4px solid var(--info);">
            <div>
                <h3 class="stat-value" style="color: var(--info);">{{ number_format($stats['sent']) }}</h3>
                <p class="stat-sub uppercase">Total Terkirim</p>
            </div>
        </div>
        <div class="stat-card" style="border-top: 4px solid var(--success);">
            <div>
                <h3 class="stat-value" style="color: var(--success);">{{ number_format($stats['delivered']) }}</h3>
                <p class="stat-sub uppercase">Diterima</p>
            </div>
        </div>
        <div class="stat-card" style="border-top: 4px solid #a855f7;">
            <div>
                <h3 class="stat-value" style="color: #a855f7;">{{ number_format($stats['read']) }}</h3>
                <p class="stat-sub uppercase">Dibaca</p>
            </div>
        </div>
        <div class="stat-card" style="border-top: 4px solid var(--danger);">
            <div>
                <h3 class="stat-value" style="color: var(--danger);">{{ number_format($stats['failed']) }}</h3>
                <p class="stat-sub uppercase">Gagal</p>
            </div>
        </div>
    </div>

    <div class="main-layout">
        <!-- Funnel Chart -->
        <div class="card">
            <div class="card-header">
                <h3><i data-lucide="bar-chart"></i> Funnel Konversi</h3>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 250px;">
                    <canvas id="funnelChart"></canvas>
                </div>
                <div class="chart-note">
                    Visualisasi penurunan dari pesan dikirim hingga dibaca.
                </div>
            </div>
        </div>

        <!-- Details Info -->
        <div class="card">
            <div class="card-header space-between">
                <h3><i data-lucide="list"></i> Log Pengiriman</h3>
                <span class="badge">
                    {{ $logs->total() }} Penerima
                </span>
            </div>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. HP</th>
                            <th>Status</th>
                            <th>Waktu Kirim</th>
                            <th>Waktu Baca</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="font-mono">{{ $log->phone }}</td>
                            <td>
                                @if($log->status === 'read')
                                    <span class="status-pill read">
                                        Dibaca <i data-lucide="check-check"></i>
                                    </span>
                                @elseif($log->status === 'delivered')
                                    <span class="status-pill delivered">
                                        Diterima <i data-lucide="check-check"></i>
                                    </span>
                                @elseif($log->status === 'sent')
                                    <span class="status-pill sent">
                                        Terkirim <i data-lucide="check"></i>
                                    </span>
                                @elseif($log->status === 'failed')
                                    <span class="status-pill failed">
                                        Gagal
                                    </span>
                                @else
                                    <span class="status-pill pending">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted">
                                {{ $log->sent_at ? $log->sent_at->format('d M H:i') : '-' }}
                            </td>
                            <td class="text-muted">
                                {{ $log->read_at ? $log->read_at->format('d M H:i') : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="empty-cell">
                                Belum ada log pengiriman.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(typeof lucide !== 'undefined') lucide.createIcons();

        const ctx = document.getElementById('funnelChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Terkirim', 'Diterima', 'Dibaca'],
                datasets: [{
                    label: 'Jumlah Pesan',
                    data: [{{ $stats['sent'] }}, {{ $stats['delivered'] }}, {{ $stats['read'] }}],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.6)', // Blue
                        'rgba(16, 185, 129, 0.6)', // Green
                        'rgba(168, 85, 247, 0.6)'  // Purple
                    ],
                    borderColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(168, 85, 247)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, grid: { display: false } } }
            }
        });
    });
</script>

@push('styles')
<style>
     /* Variables (Shared) */
     :root {
        --primary: #c90d0d;
        --primary-light: #fce7e7;
        --success: #16a34a;
        --success-light: #dcfce7;
        --info: #3b82f6;
        --info-light: #dbeafe;
        --danger: #ef4444;
        --danger-light: #fee2e2;
        --bg-body: #f3f4f6;
        --bg-card: #ffffff;
        --text-primary: #1f2937;
        --text-secondary: #4b5563;
        --text-muted: #9ca3af;
        --border-color: #e5e7eb;
        --radius: 12px;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .analytics-container {
        padding: 24px;
        max-width: 1400px;
        margin: 0 auto;
        color: var(--text-primary);
        font-family: 'Inter', sans-serif;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        background: white;
        padding: 20px;
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
    }
    
    .breadcrumb { font-size: 13px; color: var(--text-muted); margin-bottom: 4px; display: flex; gap: 4px; }
    .breadcrumb a { color: var(--text-secondary); text-decoration: none; }
    .breadcrumb a:hover { color: var(--info); }
    
    .page-header h1 { font-size: 24px; font-weight: 700; margin: 0; }
    .page-header p { margin: 0; color: var(--text-muted); font-size: 14px; }

    .btn-back {
        display: flex; align-items: center; gap: 8px; padding: 8px 16px;
        border: 1px solid var(--border-color); border-radius: 8px;
        color: var(--text-secondary); text-decoration: none; font-weight: 500;
        transition: all 0.2s;
    }
    .btn-back:hover { background: var(--bg-body); color: var(--text-primary); }

    .stats-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; margin-bottom: 32px;
    }

    .stat-card {
        background: white; padding: 24px; border-radius: var(--radius);
        box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);
        text-align: center;
    }

     .stat-value { font-size: 32px; font-weight: 800; margin: 0; }
     .stat-sub { font-size: 11px; letter-spacing: 0.5px; color: var(--text-muted); font-weight: 600; margin-top: 4px; }
     .uppercase { text-transform: uppercase; }

     .main-layout { display: grid; grid-template-columns: 1fr 2fr; gap: 24px; }
     @media (max-width: 1024px) { .main-layout { grid-template-columns: 1fr; } }

    .card {
        background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color); overflow: hidden; display: flex; flex-direction: column;
    }

    .card-header { padding: 20px; border-bottom: 1px solid var(--border-color); }
    .card-header.space-between { display: flex; justify-content: space-between; align-items: center; }
    .card-header h3 { margin: 0; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
    
    .card-body { padding: 20px; }
    .card-footer { padding: 16px; border-top: 1px solid var(--border-color); background: #f9fafb; }
    
    .chart-note { text-align: center; font-size: 13px; color: var(--text-muted); margin-top: 16px; }

    .badge { background: var(--bg-body); padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; color: var(--text-secondary); }

    /* Table */
    .table-responsive { overflow-x: auto; }
    .data-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .data-table th { text-align: left; padding: 12px 20px; color: var(--text-muted); font-weight: 500; border-bottom: 1px solid var(--border-color); background: #f9fafb; }
    .data-table td { padding: 12px 20px; border-bottom: 1px solid var(--border-color); color: var(--text-secondary); }
    .data-table tr:hover { background: #f9fafb; }
    
    .font-mono { font-family: monospace; color: var(--text-primary); }
    .text-muted { color: var(--text-muted); }
    
    .status-pill { display: inline-flex; align-items: center; gap: 4px; padding: 2px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
    .status-pill.read { background: #f3e8ff; color: #a855f7; }
    .status-pill.delivered { background: var(--success-light); color: var(--success); }
    .status-pill.sent { background: var(--info-light); color: var(--info); }
    .status-pill.failed { background: var(--danger-light); color: var(--danger); }
    .status-pill.pending { background: var(--bg-body); color: var(--text-secondary); }
    
    .empty-cell { text-align: center; padding: 32px; color: var(--text-muted); font-style: italic; }
</style>
@endpush
@endsection
