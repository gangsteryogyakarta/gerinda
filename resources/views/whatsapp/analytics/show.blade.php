@extends('layouts.app')

@section('title', 'Detail Kampanye')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="{{ route('whatsapp.analytics.dashboard') }}" class="hover:text-blue-600">Analytics</a>
                <span>/</span>
                <span>Kampanye</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">{{ $campaign->name }}</h1>
            <p class="text-gray-500 mt-1">Dibuat pada {{ $campaign->created_at->format('d M Y H:i') }}</p>
        </div>
        <div class="flex gap-3">
             <a href="{{ route('whatsapp.analytics.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-6 text-center">
            <h3 class="text-3xl font-bold text-blue-700 mb-1">{{ number_format($stats['sent']) }}</h3>
            <p class="text-sm font-medium text-blue-600 uppercase tracking-wide">Total Terkirim</p>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-xl p-6 text-center">
            <h3 class="text-3xl font-bold text-green-700 mb-1">{{ number_format($stats['delivered']) }}</h3>
            <p class="text-sm font-medium text-green-600 uppercase tracking-wide">Diterima</p>
        </div>
        <div class="bg-purple-50 border border-purple-100 rounded-xl p-6 text-center">
            <h3 class="text-3xl font-bold text-purple-700 mb-1">{{ number_format($stats['read']) }}</h3>
            <p class="text-sm font-medium text-purple-600 uppercase tracking-wide">Dibaca</p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-6 text-center">
            <h3 class="text-3xl font-bold text-red-700 mb-1">{{ number_format($stats['failed']) }}</h3>
            <p class="text-sm font-medium text-red-600 uppercase tracking-wide">Gagal</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Funnel Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Funnel Konversi</h3>
            <div class="h-64 relative">
                <canvas id="funnelChart"></canvas>
            </div>
            <div class="mt-6 text-sm text-gray-500 text-center">
                Visualisasi penurunan dari pesan dikirim hingga dibaca.
            </div>
        </div>

        <!-- Details Info -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900">Log Pengiriman</h3>
                <span class="text-xs bg-gray-100 text-gray-600 py-1 px-3 rounded-full font-medium">
                    {{ $logs->total() }} Penerima
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500 font-medium border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3">No. HP</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Waktu Kirim</th>
                            <th class="px-6 py-3">Waktu Baca</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 font-mono text-gray-600">{{ $log->phone }}</td>
                            <td class="px-6 py-4">
                                @if($log->status === 'read')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Dibaca <i data-lucide="check-check" class="w-3 h-3 ml-1"></i>
                                    </span>
                                @elseif($log->status === 'delivered')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Diterima <i data-lucide="check-check" class="w-3 h-3 ml-1"></i>
                                    </span>
                                @elseif($log->status === 'sent')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Terkirim <i data-lucide="check" class="w-3 h-3 ml-1"></i>
                                    </span>
                                @elseif($log->status === 'failed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Gagal
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $log->sent_at ? $log->sent_at->format('d M H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $log->read_at ? $log->read_at->format('d M H:i') : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400 italic">
                                Belum ada log pengiriman.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
                indexAxis: 'y', // Horizontal Bar Chart for Funnel-like look
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>
@endsection
