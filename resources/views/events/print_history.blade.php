@extends('layouts.app')

@section('content')
<div class="container py-8">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Riwayat Cetak Tiket: {{ $event->name }}</h2>
            <div class="space-x-2">
                <form action="{{ route('events.batch-tickets', $event) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-150">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                        Generate Semua PDF (Background)
                    </button>
                </form>
                <a href="{{ route('events.show', $event) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 transition duration-150">
                    Kembali
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Halaman ini akan otomatis diperbarui setiap 10 detik untuk mengecek status.
                    </p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr class="bg-gray-100 border-b">
                        <th class="py-3 px-4 text-left font-medium text-gray-600">Batch</th>
                        <th class="py-3 px-4 text-left font-medium text-gray-600">Range Tiket</th>
                        <th class="py-3 px-4 text-left font-medium text-gray-600">Status</th>
                        <th class="py-3 px-4 text-left font-medium text-gray-600">Waktu Request</th>
                        <th class="py-3 px-4 text-right font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($jobs as $job)
                        <tr>
                            <td class="py-3 px-4">#{{ $job->batch_no }}</td>
                            <td class="py-3 px-4">{{ $job->ticket_range }}</td>
                            <td class="py-3 px-4">
                                @if($job->status == 'completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Selesai
                                    </span>
                                @elseif($job->status == 'failed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800" title="{{ $job->error_message }}">
                                        Gagal
                                    </span>
                                @elseif($job->status == 'processing')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Memproses...
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Antri
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-500">
                                {{ $job->created_at->diffForHumans() }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if($job->status == 'completed' && $job->file_path)
                                    <a href="{{ Storage::url($job->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-900 font-medium">
                                        Download PDF
                                    </a>
                                @elseif($job->status == 'failed')
                                    <span class="text-red-500 text-sm">Error</span>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500">
                                Belum ada riwayat cetak tiket. Klik "Generate Semua PDF" untuk memulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Auto refresh every 10 seconds if there represent pending jobs
    @if($jobs->whereIn('status', ['pending', 'processing'])->isNotEmpty())
        setTimeout(function() {
            window.location.reload();
        }, 10000);
    @endif
</script>
@endsection
