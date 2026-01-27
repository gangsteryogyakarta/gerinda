@extends('layouts.app')

@section('title', 'WhatsApp Dashboard')

@section('content')
<div class="flex h-screen bg-gray-50">
    <!-- Sidebar (Simplified for brevity) -->
    
    <!-- Main Content -->
    <div class="flex-1 overflow-auto">
        <div class="p-8">
            <h1 class="text-2xl font-bold mb-4">WhatsApp Dashboard</h1>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Analytics Card Link -->
                <a href="{{ route('whatsapp.analytics.dashboard') }}" class="group block bg-white border border-gray-200 rounded-xl p-6 hover:border-blue-500 hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-blue-100 text-blue-600 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <i data-lucide="bar-chart-2" class="w-6 h-6"></i>
                        </div>
                        <i data-lucide="arrow-right" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Analytics Dashboard</h3>
                    <p class="text-sm text-gray-500">Lihat statistik pengiriman dan performa kampanye.</p>
                </a>

                <!-- Existing Cards... -->
            </div>

            <!-- ... existing content ... -->
        </div>
    </div>
</div>
@endsection
