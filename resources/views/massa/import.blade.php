@extends('layouts.app')

@section('title', 'Import Data Massa')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Import Data CSV</h1>
                <p class="text-gray-500 mt-1">Upload data massa dalam jumlah banyak sekaligus.</p>
            </div>
            <a href="{{ route('massa.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Kembali
            </a>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <!-- Progress Steps (Visual only) -->
            <div class="bg-gray-50 border-b border-gray-100 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold text-sm">1</span>
                    <span class="text-sm font-medium text-gray-900">Download Template</span>
                </div>
                <div class="h-px bg-gray-300 w-12"></div>
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold text-sm">2</span>
                    <span class="text-sm font-medium text-gray-900">Upload File</span>
                </div>
                <div class="h-px bg-gray-300 w-12"></div>
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-400 font-bold text-sm">3</span>
                    <span class="text-sm font-medium text-gray-400">Selesai</span>
                </div>
            </div>

            <div class="p-8">
                @if(session('success'))
                    <div class="mb-6 bg-green-50 text-green-700 p-4 rounded-xl border border-green-100 flex items-start gap-4">
                        <div class="p-2 bg-green-100 rounded-lg text-green-600">
                            <i data-lucide="check-circle" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg mb-1">Import Berhasil</h3>
                            <p>{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-50 text-red-700 p-4 rounded-xl border border-red-100 flex items-start gap-4">
                        <div class="p-2 bg-red-100 rounded-lg text-red-600">
                            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg mb-1">Terjadi Kesalahan</h3>
                            <p>{{ session('error') }}</p>
                        </div>
                    </div>
                @endif
                
                @if(session('import_errors'))
                    <div class="mb-6 bg-red-50 p-4 rounded-xl border border-red-100">
                        <div class="flex items-center gap-2 mb-3 font-bold text-red-800">
                            <i data-lucide="x-circle" class="w-5 h-5"></i>
                            <span>{{ count(session('import_errors')) }} Data Gagal Diimport</span>
                        </div>
                        <div class="bg-white rounded-lg border border-red-100 p-3 max-h-48 overflow-y-auto">
                            <ul class="text-sm text-red-600 space-y-2">
                                @foreach(session('import_errors') as $err)
                                    <li class="flex items-start gap-2">
                                        <span class="mt-1 w-1.5 h-1.5 bg-red-400 rounded-full flex-shrink-0"></span>
                                        {{ $err }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="mb-8 p-5 bg-blue-50 rounded-xl border border-blue-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-blue-900 mb-1">Belum punya format file?</h3>
                        <p class="text-sm text-blue-700">Download template CSV standar agar import berjalan lancar.</p>
                    </div>
                    <a href="{{ route('massa.template') }}" class="flex-shrink-0 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm shadow-md">
                        <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                        Download Template
                    </a>
                </div>

                <form action="{{ route('massa.import.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Drag and Drop Zone -->
                    <div class="mb-6 group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Upload File CSV</label>
                        <div class="relative border-2 border-dashed border-gray-300 rounded-2xl p-10 text-center hover:bg-gray-50 hover:border-blue-400 transition-all cursor-pointer group-focus-within:ring-2 group-focus-within:ring-blue-500 group-focus-within:ring-offset-2">
                            <input id="file-upload" name="file" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".csv, .txt">
                            <div class="space-y-3" id="dropzone-content">
                                <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                                    <i data-lucide="upload-cloud" class="w-8 h-8"></i>
                                </div>
                                <div class="text-gray-600">
                                    <span class="font-medium text-blue-600">Klik untuk upload</span> atau drag and drop file ke sini
                                </div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">CSV (UTF-8), Maks 10MB</p>
                            </div>
                            <!-- Selected File State -->
                            <div id="file-selected" class="hidden">
                                <div class="w-16 h-16 bg-green-50 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i data-lucide="file-spreadsheet" class="w-8 h-8"></i>
                                </div>
                                <p id="filename-display" class="text-lg font-medium text-gray-900 mb-1"></p>
                                <p class="text-sm text-green-600 font-medium">File siap diproses</p>
                                <button type="button" id="reset-file" class="mt-4 text-xs text-gray-500 underline hover:text-red-500">Ganti File</button>
                            </div>
                        </div>
                        @error('file')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-4 h-4"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Options -->
                    <div class="mb-8">
                        <label class="flex items-start p-4 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                            <div class="flex items-center h-5">
                                <input id="overwrite" name="overwrite" type="checkbox" value="1" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            </div>
                            <div class="ml-3 text-sm">
                                <span class="font-bold text-gray-900 block mb-1">Update data jika NIK sudah ada</span>
                                <span class="text-gray-500">Jika dicentang, data lama dengan NIK yang sama akan <strong>ditimpa (overwrite)</strong> dengan data baru dari file CSV. Jika tidak, data tersebut akan dilewati (skip).</span>
                            </div>
                        </label>
                    </div>

                    <!-- Action Button -->
                    <button type="submit" class="w-full flex items-center justify-center py-3.5 px-6 border border-transparent rounded-xl text-base font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                        <i data-lucide="play" class="w-5 h-5 mr-2"></i>
                        Mulai Proses Import
                    </button>
                </form>
            </div>
            
            <!-- Footer Hints -->
            <div class="bg-gray-50 px-8 py-6 border-t border-gray-100">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Ketentuan File & Format</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                    <div class="flex items-start gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-green-500 mt-0.5"></i>
                        <span>Kolom <strong>NIK</strong> (16 digit) & <strong>Nama</strong> wajib diisi.</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-green-500 mt-0.5"></i>
                        <span>Format Tanggal Lahir: <strong>YYYY-MM-DD</strong>.</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-green-500 mt-0.5"></i>
                        <span>Jenis Kelamin: <strong>L</strong> atau <strong>P</strong>.</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-green-500 mt-0.5"></i>
                        <span>Gunakan file template untuk hasil terbaik.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const input = document.getElementById('file-upload');
    const dropzoneContent = document.getElementById('dropzone-content');
    const fileSelected = document.getElementById('file-selected');
    const filenameDisplay = document.getElementById('filename-display');
    const resetFileBtn = document.getElementById('reset-file');
    
    input.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            showFile(e.target.files[0].name);
        }
    });

    resetFileBtn.addEventListener('click', (e) => {
        e.preventDefault(); // Stop label click propagation
        input.value = '';
        hideFile();
    });

    function showFile(name) {
        dropzoneContent.classList.add('hidden');
        fileSelected.classList.remove('hidden');
        filenameDisplay.textContent = name;
    }

    function hideFile() {
        dropzoneContent.classList.remove('hidden');
        fileSelected.classList.add('hidden');
        filenameDisplay.textContent = '';
    }

    // Optional: Drag and drop styling feedback
    const dropzone = input.parentElement;
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, highlight, false)
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, unhighlight, false)
    });

    function highlight(e) {
        dropzone.classList.add('bg-blue-50', 'border-blue-400');
    }

    function unhighlight(e) {
        dropzone.classList.remove('bg-blue-50', 'border-blue-400');
    }
</script>
@endsection
