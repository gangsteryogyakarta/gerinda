@extends('layouts.app')

@section('title', 'WhatsApp Analytics')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Analytics Dashboard</h1>
            <p class="text-gray-500 mt-1">Monitor performa kampanye WhatsApp Anda.</p>
        </div>
        <a href="{{ route('whatsapp.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Kembali ke WhatsApp
        </a>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Sent -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-start gap-4">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                <i data-lucide="send" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Total Pesan</p>
                <h3 class="text-2xl font-bold text-gray-900">{{ number_format($totalSent) }}</h3>
                <p class="text-xs text-blue-600 mt-1">Terikirim ke server</p>
            </div>
        </div>

        <!-- Success Rate -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-start gap-4">
            <div class="p-3 bg-green-50 text-green-600 rounded-lg">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Delivery Rate</p>
                <h3 class="text-2xl font-bold text-gray-900">{{ $deliveryRate }}%</h3>
                <p class="text-xs text-green-600 mt-1">Pesan diterima HP tujuan</p>
            </div>
        </div>

        <!-- Read Rate -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-start gap-4">
            <div class="p-3 bg-purple-50 text-purple-600 rounded-lg">
                <i data-lucide="eye" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Read Rate</p>
                <h3 class="text-2xl font-bold text-gray-900">{{ $readRate }}%</h3>
                <p class="text-xs text-purple-600 mt-1">Pesan dibaca</p>
            </div>
        </div>

        <!-- Failed -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-start gap-4">
            <div class="p-3 bg-red-50 text-red-600 rounded-lg">
                <i data-lucide="alert-circle" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Gagal</p>
                <h3 class="text-2xl font-bold text-gray-900">{{ number_format($totalFailed) }}</h3>
                <p class="text-xs text-red-600 mt-1">Gagal terkirim</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Chart -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <i data-lucide="bar-chart-2" class="w-5 h-5 text-gray-400"></i>
                Tren Pengiriman (30 Hari Terakhir)
            </h3>
            <div class="h-80 w-full relative">
                <canvas id="messageTrendChart"></canvas>
            </div>
        </div>

        <!-- Recent Campaigns -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <i data-lucide="history" class="w-5 h-5 text-gray-400"></i>
                Kampanye Terakhir
            </h3>
            <div class="space-y-6">
                @forelse($recentCampaigns as $campaign)
                    <div class="group">
                        <div class="flex items-center justify-between mb-2">
                            <a href="{{ route('whatsapp.analytics.show', $campaign->id) }}" class="text-sm font-medium text-gray-900 hover:text-blue-600 transition truncate w-2/3">
                                {{ $campaign->name }}
                            </a>
                            <span class="text-xs text-gray-500">{{ $campaign->created_at->format('d M') }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                            <span>Read Rate: {{ $campaign->read_rate }}%</span>
                            <span class="text-gray-300">•</span>
                            <span>{{ $campaign->log_count }} Pesan</span>
                        </div>
                        <!-- Mini Progress Bar -->
                        <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full" style="width: {{ $campaign->read_rate }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500 text-sm">
                        Belum ada kampanye.
                    </div>
                @endforelse
            </div>
            
            <div class="mt-6 pt-6 border-t border-gray-100">
                <a href="{{ route('whatsapp.index') }}" class="text-sm text-blue-600 font-medium hover:text-blue-700 flex items-center justify-center">
                    Lihat Semua Kampanye <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('messageTrendChart').getContext('2d');
        
        // Fetch Data
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
                                borderColor: '#3b82f6', // Blue 500
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.3,
                                fill: true
                            },
                            {
                                label: 'Dibaca',
                                data: data.read,
                                borderColor: '#a855f7', // Purple 500
                                backgroundColor: 'transparent',
                                tension: 0.3,
                                borderDash: [5, 5]
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { borderDash: [2, 2] }
                            },
                            x: {
                                grid: { display: false }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            })
            .catch(error => console.error("Error loading chart data:", error));
    });
</script>
@endsection
