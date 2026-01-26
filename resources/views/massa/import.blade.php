@extends('layouts.app')

@section('title', 'Import Data Massa')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                Import Data Massa (CSV)
            </h1>
            <a href="{{ route('massa.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">
                &larr; Kembali
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6">
                
                @if(session('success'))
                    <div class="mb-4 bg-green-50 text-green-700 p-4 rounded-lg border border-green-100 flex items-start gap-3">
                        <svg class="w-5 h-5 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <p class="font-bold">Import Berhasil</p>
                            <p>{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-50 text-red-700 p-4 rounded-lg border border-red-100 flex items-start gap-3">
                        <svg class="w-5 h-5 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <p class="font-bold">Terjadi Kesalahan</p>
                            <p>{{ session('error') }}</p>
                        </div>
                    </div>
                @endif
                
                @if(session('import_errors'))
                    <div class="mb-6 bg-red-50 text-red-800 p-4 rounded-lg border border-red-100">
                        <div class="flex items-center gap-2 mb-2 font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <span>Laporan Error ({{ count(session('import_errors')) }} baris gagal)</span>
                        </div>
                        <ul class="list-disc list-inside text-sm space-y-1 max-h-60 overflow-y-auto">
                            @foreach(session('import_errors') as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('massa.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    <!-- File Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">File CSV</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload file CSV</span>
                                        <input id="file-upload" name="file" type="file" class="sr-only" accept=".csv, .txt">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">
                                    CSV (UTF-8 Separated by Comma) hingga 10MB
                                </p>
                            </div>
                        </div>
                        @error('file')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Options -->
                    <div class="flex items-center">
                        <input id="overwrite" name="overwrite" type="checkbox" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="overwrite" class="ml-2 block text-sm text-gray-900">
                            Update data jika NIK sudah ada (Overwrite)
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                        <a href="{{ route('massa.template') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Download Template CSV
                        </a>

                        <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Proses Import
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-8 bg-blue-50 rounded-xl p-6 border border-blue-100">
            <h3 class="text-blue-800 font-bold mb-2">Panduan Import:</h3>
            <ul class="list-disc list-inside text-sm text-blue-700 space-y-1">
                <li>Gunakan <strong>Template CSV</strong> yang disediakan agar format kolom sesuai.</li>
                <li>Kolom <strong>NIK</strong> dan <strong>Nama Lengkap</strong> wajib diisi.</li>
                <li>Format Tanggal Lahir: <strong>YYYY-MM-DD</strong> (contoh: 1990-12-31).</li>
                <li>Jenis Kelamin: Gunakan <strong>L</strong> atau <strong>P</strong>.</li>
                <li>Jika "Update data" dicentang, data lama dengan NIK yang sama akan ditimpa data baru dari CSV.</li>
            </ul>
        </div>
    </div>
</div>

<script>
    // Simple file name display script
    const input = document.getElementById('file-upload');
    const label = input.parentElement.parentElement;
    
    input.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            label.innerHTML = `
                <div class="text-green-600 font-bold flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    File Terpilih: ${e.target.files[0].name}
                </div>
            `;
        }
    });
</script>
@endsection
