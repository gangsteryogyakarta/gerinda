@extends('layouts.app')

@section('title', 'Import Data Massa')

@section('content')
<div class="import-container">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="page-header">
            <div>
                <h1>Import Data Excel</h1>
                <p>Upload data massa dalam jumlah banyak sekaligus.</p>
            </div>
            <a href="{{ route('massa.index') }}" class="btn-back">
                <i data-lucide="arrow-left"></i>
                Kembali
            </a>
        </div>

        <!-- Main Card -->
        <div class="import-card">
            <!-- Progress Steps -->
            <div class="steps-header">
                <div class="step active">
                    <span class="step-num">1</span>
                    <span class="step-text">Download Template</span>
                </div>
                <div class="step-divider"></div>
                <div class="step active">
                    <span class="step-num">2</span>
                    <span class="step-text">Upload File</span>
                </div>
                <div class="step-divider"></div>
                <div class="step">
                    <span class="step-num disabled">3</span>
                    <span class="step-text disabled">Selesai</span>
                </div>
            </div>

            <div class="card-content">
                @if(session('success'))
                    <div class="alert alert-success">
                        <div class="alert-icon"><i data-lucide="check-circle"></i></div>
                        <div>
                            <h3>Import Berhasil</h3>
                            <p>{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        <div class="alert-icon"><i data-lucide="alert-triangle"></i></div>
                        <div>
                            <h3>Terjadi Kesalahan</h3>
                            <p>{{ session('error') }}</p>
                        </div>
                    </div>
                @endif
                
                @if(session('import_errors'))
                    <div class="alert alert-danger">
                        <div class="error-header">
                            <i data-lucide="x-circle"></i>
                            <span>{{ count(session('import_errors')) }} Data Gagal Diimport</span>
                        </div>
                        <div class="error-list-container">
                            <ul class="error-list">
                                @foreach(session('import_errors') as $err)
                                    <li><span class="bullet"></span> {{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="info-box">
                    <div>
                        <h3>Belum punya format file?</h3>
                        <p>Download template Excel standar agar import berjalan lancar.</p>
                    </div>
                    <a href="{{ route('massa.template') }}" class="btn-download">
                        <i data-lucide="download"></i>
                        Download Template
                    </a>
                </div>

                <form action="{{ route('massa.import.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Drag and Drop Zone -->
                    <div class="form-group">
                        <label class="form-label">Upload File Excel</label>
                        <div class="dropzone-wrapper">
                            <input id="file-upload" name="file" type="file" class="file-input" accept=".xlsx, .xls">
                            <div class="dropzone-content" id="dropzone-content">
                                <div class="upload-icon">
                                    <i data-lucide="cloud-upload"></i>
                                </div>
                                <div class="upload-text">
                                    <span class="highlight">Klik untuk upload</span> atau drag and drop file ke sini
                                </div>
                                <p class="file-hint">Excel (.xlsx), Maks 10MB</p>
                            </div>
                            
                            <!-- Selected File State -->
                            <div id="file-selected" class="file-selected hidden">
                                <div class="success-icon">
                                    <i data-lucide="file-spreadsheet"></i>
                                </div>
                                <p id="filename-display" class="filename"></p>
                                <p class="file-status">File siap diproses</p>
                                <button type="button" id="reset-file" class="btn-reset">Ganti File</button>
                            </div>
                        </div>
                        @error('file')
                            <p class="error-msg"><i data-lucide="alert-circle"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Options -->
                    <div class="form-group">
                        <label class="checkbox-wrapper">
                            <input id="overwrite" name="overwrite" type="checkbox" value="1">
                            <div class="checkbox-text">
                                <span class="checkbox-title">Update data jika NIK sudah ada</span>
                                <span class="checkbox-desc">Jika dicentang, data lama dengan NIK yang sama akan <strong>ditimpa (overwrite)</strong> dengan data baru dari file Excel. Jika tidak, data tersebut akan dilewati (skip).</span>
                            </div>
                        </label>
                    </div>

                    <!-- Action Button -->
                    <button type="submit" class="btn-submit">
                        <i data-lucide="play"></i>
                        Mulai Proses Import
                    </button>
                </form>
            </div>
            
            <!-- Footer Hints -->
            <div class="card-footer">
                <h4>Ketentuan File & Format</h4>
                <div class="hints-grid">
                    <div class="hint-item">
                        <i data-lucide="check"></i>
                        <span>Kolom <strong>NIK</strong> (16 digit) & <strong>Nama</strong> wajib diisi.</span>
                    </div>
                    <div class="hint-item">
                        <i data-lucide="check"></i>
                        <span>Kolom <strong>Kategori Massa</strong> bisa diisi 'Pengurus' atau 'Simpatisan'.</span>
                    </div>
                    <div class="hint-item">
                        <i data-lucide="check"></i>
                        <span>Isi kolom <strong>Provinsi, Kabupaten, Kecamatan, Desa</strong> agar terpetakan di GIS.</span>
                    </div>
                    <div class="hint-item">
                        <i data-lucide="check"></i>
                        <span>Format Tanggal Lahir: <strong>YYYY-MM-DD</strong>.</span>
                    </div>
                    <div class="hint-item">
                        <i data-lucide="check"></i>
                        <span>Gunakan file template terbaru untuk hasil terbaik.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(typeof lucide !== 'undefined') lucide.createIcons();
    });

    const input = document.getElementById('file-upload');
    const dropzoneContent = document.getElementById('dropzone-content');
    const fileSelected = document.getElementById('file-selected');
    const filenameDisplay = document.getElementById('filename-display');
    const resetFileBtn = document.getElementById('reset-file');
    const dropzoneWrapper = document.querySelector('.dropzone-wrapper');
    
    input.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            showFile(e.target.files[0].name);
        }
    });

    resetFileBtn.addEventListener('click', (e) => {
        e.preventDefault(); 
        input.value = '';
        hideFile();
    });

    function showFile(name) {
        dropzoneContent.style.display = 'none';
        fileSelected.classList.remove('hidden');
        fileSelected.style.display = 'block';
        filenameDisplay.textContent = name;
        dropzoneWrapper.classList.add('has-file');
    }

    function hideFile() {
        dropzoneContent.style.display = 'block';
        fileSelected.classList.add('hidden');
        fileSelected.style.display = 'none';
        filenameDisplay.textContent = '';
        dropzoneWrapper.classList.remove('has-file');
    }

    // Drag and drop visual feedback
    const dropzone = input.parentElement;
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, highlight, false)
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, unhighlight, false)
    });

    function highlight(e) {
        dropzone.classList.add('highlighted');
    }

    function unhighlight(e) {
        dropzone.classList.remove('highlighted');
    }
</script>

@push('styles')
<style>
    /* Variables */
    :root {
        --primary: #c90d0d;
        --primary-hover: #a50b0b;
        --primary-light: #fce7e7;
        --secondary: #4b5563;
        --success: #16a34a;
        --success-bg: #dcfce7;
        --danger: #ef4444;
        --danger-bg: #fee2e2;
        --info: #3b82f6;
        --info-bg: #dbeafe;
        --border-color: #e5e7eb;
        --radius: 12px;
        --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .import-container {
        padding: 40px 20px;
        font-family: 'Inter', sans-serif;
        color: #1f2937;
    }

    .max-w-2xl { max-width: 42rem; margin: 0 auto; }

    /* Header */
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
    .page-header h1 { font-size: 30px; font-weight: 700; margin: 0; color: #111827; }
    .page-header p { margin: 4px 0 0; color: #6b7280; }

    .btn-back {
        display: inline-flex; align-items: center; padding: 8px 16px;
        background: white; border: 1px solid #d1d5db; border-radius: 8px;
        color: #374151; font-weight: 500; text-decoration: none; font-size: 14px;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: all 0.2s;
        gap: 8px;
    }
    .btn-back:hover { background: #f9fafb; }
    .btn-back i { width: 16px; height: 16px; }

    /* Card */
    .import-card {
        background: white; border-radius: 16px; box-shadow: var(--shadow);
        border: 1px solid var(--border-color); overflow: hidden;
    }

    /* Steps */
    .steps-header {
        background: #f9fafb; border-bottom: 1px solid var(--border-color);
        padding: 16px 24px; display: flex; align-items: center; justify-content: space-between;
    }
    
    .step { display: flex; align-items: center; gap: 12px; }
    .step-num {
        width: 32px; height: 32px; border-radius: 50%;
        background: var(--info-bg); color: var(--info);
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 14px;
    }
    .step-num.disabled { background: #f3f4f6; color: #9ca3af; }
    .step-text { font-size: 14px; font-weight: 600; color: #111827; }
    .step-text.disabled { color: #9ca3af; }
    .step-divider { height: 1px; background: #d1d5db; width: 48px; }

    /* Content */
    .card-content { padding: 32px; }

    /* Alerts */
    .alert {
        padding: 16px; border-radius: 12px; margin-bottom: 24px;
        display: flex; gap: 16px; align-items: flex-start;
    }
    .alert-success { background: var(--success-bg); color: #15803d; border: 1px solid #bbf7d0; }
    .alert-danger { background: var(--danger-bg); color: #b91c1c; border: 1px solid #fecaca; }
    
    .alert-icon {
        padding: 8px; background: rgba(255,255,255,0.5); border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
    }
    .alert h3 { margin: 0 0 4px; font-weight: 700; font-size: 16px; }
    .alert p { margin: 0; font-size: 14px; }
    
    .error-header { display: flex; align-items: center; gap: 8px; font-weight: 700; margin-bottom: 12px; }
    .error-list-container { background: white; border-radius: 8px; padding: 12px; border: 1px solid #fecaca; max-height: 192px; overflow-y: auto; }
    .error-list { list-style: none; padding: 0; margin: 0; font-size: 13px; }
    .error-list li { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 8px; }
    .bullet { width: 6px; height: 6px; background: #f87171; border-radius: 50%; margin-top: 6px; flex-shrink: 0; }

    /* Info Box */
    .info-box {
        background: var(--info-bg); border: 1px solid #bfdbfe; border-radius: 12px;
        padding: 20px; margin-bottom: 32px; display: flex; align-items: center; justify-content: space-between;
    }
    .info-box h3 { margin: 0 0 4px; font-weight: 700; color: #1e3a8a; font-size: 16px; }
    .info-box p { margin: 0; font-size: 14px; color: #1d4ed8; }

    .btn-download {
        background: #2563eb; color: white; padding: 8px 16px; border-radius: 8px;
        text-decoration: none; font-size: 14px; font-weight: 500; display: inline-flex;
        align-items: center; gap: 8px; transition: bg 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        white-space: nowrap;
    }
    .btn-download:hover { background: #1d4ed8; }
    .btn-download i { width: 16px; height: 16px; }

    /* Form */
    .form-group { margin-bottom: 24px; }
    .form-label { display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px; }
    
    .dropzone-wrapper {
        position: relative; border: 2px dashed #d1d5db; border-radius: 16px; padding: 40px;
        text-align: center; transition: all 0.2s; cursor: pointer;
    }
    .dropzone-wrapper:hover, .dropzone-wrapper.highlighted { background: #f9fafb; border-color: #60a5fa; }
    .dropzone-wrapper.has-file { border-color: #10b981; background: #f0fdf4; border-style: solid; }
    
    .file-input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10; }
    
    .upload-icon {
        width: 64px; height: 64px; background: #eff6ff; color: #3b82f6; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;
    }
    .upload-icon i { width: 32px; height: 32px; } /* CRITICAL: Fixed icon size */

    .upload-text { font-size: 16px; color: #4b5563; margin-bottom: 8px; }
    .highlight { font-weight: 500; color: #2563eb; }
    .file-hint { font-size: 12px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin: 0; }

    /* File Selected State */
    .file-selected { display: none; }
    .success-icon {
        width: 64px; height: 64px; background: #ecfccb; color: #65a30d; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;
    }
    .success-icon i { width: 32px; height: 32px; }

    .filename { font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 4px; }
    .file-status { font-size: 14px; color: #16a34a; font-weight: 500; margin: 0; }
    .btn-reset { margin-top: 16px; background: none; border: none; font-size: 12px; color: #6b7280; text-decoration: underline; cursor: pointer; position: relative; z-index: 20; }
    .btn-reset:hover { color: #ef4444; }

    .error-msg { margin-top: 8px; font-size: 14px; color: #dc2626; display: flex; align-items: center; gap: 4px; }
    .error-msg i { width: 16px; height: 16px; }

    /* Checkbox */
    .checkbox-wrapper {
        display: flex; align-items: flex-start; padding: 16px; border: 1px solid #e5e7eb;
        border-radius: 12px; cursor: pointer; transition: bg 0.2s;
    }
    .checkbox-wrapper:hover { background: #f9fafb; }
    
    .checkbox-wrapper input { width: 20px; height: 20px; margin-top: 2px; }
    .checkbox-text { margin-left: 12px; }
    .checkbox-title { display: block; font-weight: 700; color: #111827; margin-bottom: 4px; font-size: 14px; }
    .checkbox-desc { font-size: 14px; color: #6b7280; line-height: 1.5; }

    /* Submit Button */
    .btn-submit {
        width: 100%; display: flex; align-items: center; justify-content: center; padding: 14px 24px;
        background: linear-gradient(to right, #2563eb, #1d4ed8); color: white; border: none;
        border-radius: 12px; font-size: 16px; font-weight: 700; cursor: pointer;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: transform 0.1s;
        gap: 8px;
    }
    .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    .btn-submit:active { transform: translateY(0); }
    .btn-submit i { width: 20px; height: 20px; }

    /* Footer */
    .card-footer { background: #f9fafb; padding: 24px 32px; border-top: 1px solid #e5e7eb; }
    .card-footer h4 { margin: 0 0 16px; font-size: 12px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; }
    
    .hints-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .hint-item { display: flex; align-items: flex-start; gap: 8px; font-size: 14px; color: #4b5563; }
    .hint-item i { width: 16px; height: 16px; color: #22c55e; margin-top: 2px; }

    @media (max-width: 640px) {
        .hints-grid { grid-template-columns: 1fr; }
        .steps-header { display: none; }
    }
</style>
@endpush
@endsection
