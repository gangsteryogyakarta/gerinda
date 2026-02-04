@extends('layouts.app')

@section('content')
<div class="container py-8">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-4">Cetak Tiket Bertahap: {{ $event->name }}</h2>
        
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Total tiket ({{ $total }}) terlalu banyak untuk dicetak sekaligus. 
                        Silakan unduh per bagian (batch) agar server tidak overload.
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            @for ($i = 0; $i < $batches; $i++)
                @php
                    $start = ($i * $perPage) + 1;
                    $end = min(($i + 1) * $perPage, $total);
                @endphp
                <div class="flex items-center justify-between p-4 border rounded hover:bg-gray-50">
                    <div>
                        <span class="font-bold text-lg">Batch {{ $i + 1 }}</span>
                        <span class="text-gray-500 ml-2">(Tiket #{{ $start }} - #{{ $end }})</span>
                    </div>
                    <a href="{{ route('events.print-tickets', ['event' => $event->id, 'batch' => $i + 1]) }}" 
                       class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition duration-150 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download PDF
                    </a>
                </div>
            @endfor
        </div>

        <div class="mt-6 border-t pt-4">
            <a href="{{ route('events.show', $event) }}" class="text-gray-600 hover:text-gray-900 underline">
                &larr; Kembali ke Detail Event
            </a>
        </div>
    </div>
</div>
@endsection
