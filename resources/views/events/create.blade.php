@extends('layouts.app')

@section('title', 'Buat Event Baru')

@section('content')
    <!-- Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1>➕ Buat Event Baru</h1>
            <p>Isi form di bawah untuk membuat event baru</p>
        </div>
        <div class="page-header-right">
            <a href="{{ route('events.index') }}" class="btn btn-secondary">
                <i data-lucide="arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    <form action="{{ route('events.store') }}" method="POST" class="event-form" enctype="multipart/form-data">
        @csrf
        
        <div class="form-grid">
            <!-- Main Form -->
            <div class="form-main">
                <!-- Basic Info -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📋 Informasi Dasar</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">Nama Event <span class="required">*</span></label>
                            <input type="text" name="name" class="form-input @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" placeholder="Contoh: Konsolidasi DPD Jawa Barat" required>
                            @error('name')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Kategori Event</label>
                            <select name="event_category_id" class="form-input">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('event_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-input" rows="4" 
                                      placeholder="Deskripsi singkat tentang event ini...">{{ old('description') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Copywriting (Detail Lengkap)</label>
                            <textarea name="copywriting" class="form-input" rows="6" 
                                      placeholder="Tuliskan detail lengkap event untuk materi promosi...">{{ old('copywriting') }}</textarea>
                            <span class="form-hint">Gunakan ini untuk detail panjang, ajakan bertindak, benefit, dll.</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Banner Event</label>
                            <div class="image-upload-area" id="banner-upload-area">
                                <input type="file" name="banner_image" id="banner_image" class="hidden-input" accept="image/*" onchange="previewImage(this)">
                                <div class="upload-placeholder" id="upload-placeholder">
                                    <i data-lucide="image"></i>
                                    <span>Klik untuk upload banner</span>
                                    <span class="file-info">JPG, PNG, max 2MB</span>
                                </div>
                                <img id="banner-preview" class="image-preview" style="display: none;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📍 Lokasi</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">Nama Tempat <span class="required">*</span></label>
                            <input type="text" name="venue_name" class="form-input @error('venue_name') is-invalid @enderror" 
                                   value="{{ old('venue_name') }}" placeholder="Contoh: Gedung Juang 45" required>
                            @error('venue_name')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Alamat Lengkap <span class="required">*</span></label>
                            <textarea name="venue_address" class="form-input @error('venue_address') is-invalid @enderror" 
                                      rows="2" placeholder="Alamat lengkap lokasi event..." required>{{ old('venue_address') }}</textarea>
                            @error('venue_address')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Provinsi</label>
                                <select name="province_id" id="province_id" class="form-input">
                                    <option value="">-- Pilih Provinsi --</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>
                                            {{ $province->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kabupaten/Kota</label>
                                <select name="regency_id" id="regency_id" class="form-input">
                                    <option value="">-- Pilih Kabupaten --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Schedule -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📆 Jadwal</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Mulai Event <span class="required">*</span></label>
                                <input type="datetime-local" name="event_start" 
                                       class="form-input @error('event_start') is-invalid @enderror" 
                                       value="{{ old('event_start') }}" required>
                                @error('event_start')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Selesai Event <span class="required">*</span></label>
                                <input type="datetime-local" name="event_end" 
                                       class="form-input @error('event_end') is-invalid @enderror" 
                                       value="{{ old('event_end') }}" required>
                                @error('event_end')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Buka Registrasi</label>
                                <input type="datetime-local" name="registration_start" class="form-input" 
                                       value="{{ old('registration_start') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tutup Registrasi</label>
                                <input type="datetime-local" name="registration_end" class="form-input" 
                                       value="{{ old('registration_end') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="form-sidebar">
                <!-- Quota -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">👥 Kuota Peserta</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">Maksimal Peserta</label>
                            <input type="number" name="max_participants" class="form-input" 
                                   value="{{ old('max_participants') }}" placeholder="Kosongkan jika unlimited" min="1">
                            <span class="form-hint">Biarkan kosong jika tidak ada batasan</span>
                        </div>

                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" name="enable_waitlist" value="1" {{ old('enable_waitlist') ? 'checked' : '' }}>
                                <span class="checkmark"></span>
                                Aktifkan Waiting List
                            </label>
                            <span class="form-hint">Peserta bisa mendaftar meski kuota penuh</span>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">⚙️ Fitur Event</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" name="require_ticket" value="1" {{ old('require_ticket', true) ? 'checked' : '' }}>
                                <span class="checkmark"></span>
                                Memerlukan Tiket
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" name="enable_checkin" value="1" {{ old('enable_checkin', true) ? 'checked' : '' }}>
                                <span class="checkmark"></span>
                                Aktifkan Check-in
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" name="enable_lottery" value="1" {{ old('enable_lottery') ? 'checked' : '' }}>
                                <span class="checkmark"></span>
                                Aktifkan Undian Hadiah
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" name="send_wa_notification" value="1" {{ old('send_wa_notification') ? 'checked' : '' }}>
                                <span class="checkmark"></span>
                                Kirim Notifikasi WhatsApp
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i data-lucide="save"></i>
                            Simpan Event
                        </button>
                        <a href="{{ route('events.index') }}" class="btn btn-secondary btn-block" style="margin-top: 12px;">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
    /* Dropdown Styling */
    select.form-input option {
        background-color: var(--bg-card);
        color: var(--text-primary);
        padding: 8px;
    }

    /* Dark mode specific override if needed */
    @media (prefers-color-scheme: dark) {
        select.form-input option {
            background-color: #1a1a1a;
            color: #ffffff;
        }
    }
@endsection

@push('styles')
<style>
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 24px;
    }

    @media (max-width: 1024px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    .form-main .card {
        margin-bottom: 24px;
    }

    .form-sidebar .card {
        margin-bottom: 24px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--text-primary);
    }

    .form-label .required {
        color: var(--primary);
    }

    .form-input {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 12px 16px;
        color: var(--text-primary);
        font-size: 14px;
        width: 100%;
        outline: none;
        transition: all 0.2s ease;
    }

    .form-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
    }

    .form-input::placeholder {
        color: var(--text-muted);
    }

    .form-input.is-invalid {
        border-color: var(--danger);
    }

    .form-error {
        display: block;
        color: var(--danger);
        font-size: 12px;
        margin-top: 6px;
    }

    .form-hint {
        display: block;
        color: var(--text-muted);
        font-size: 12px;
        margin-top: 6px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    @media (max-width: 640px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .form-checkbox {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        font-size: 14px;
    }

    .form-checkbox input[type="checkbox"] {
        width: 20px;
        height: 20px;
        accent-color: var(--primary);
        cursor: pointer;
    }

    .btn-block {
        width: 100%;
    }

    textarea.form-input {
        resize: vertical;
        min-height: 80px;
    }

    .image-upload-area {
        border: 2px dashed var(--border-color);
        border-radius: var(--radius);
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        background: var(--bg-tertiary);
        min-height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .image-upload-area:hover {
        border-color: var(--primary);
        background: rgba(220, 38, 38, 0.05);
    }

    .hidden-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .upload-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        color: var(--text-muted);
    }

    .upload-placeholder i {
        width: 32px;
        height: 32px;
        color: var(--text-secondary);
    }

    .file-info {
        font-size: 12px;
    }

    .image-preview {
        max-width: 100%;
        max-height: 200px;
        border-radius: var(--radius);
        object-fit: cover;
    }
</style>
@endpush

@push('scripts')
<script>
    function previewImage(input) {
        const preview = document.getElementById('banner-preview');
        const placeholder = document.getElementById('upload-placeholder');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Province-Regency cascade
    document.getElementById('province_id').addEventListener('change', async function() {
        const provinceId = this.value;
        const regencySelect = document.getElementById('regency_id');
        
        regencySelect.innerHTML = '<option value="">-- Memuat... --</option>';
        
        if (!provinceId) {
            regencySelect.innerHTML = '<option value="">-- Pilih Kabupaten --</option>';
            return;
        }
        
        try {
            const response = await fetch(`/api/v1/regencies?province_id=${provinceId}`);
            const data = await response.json();
            
            regencySelect.innerHTML = '<option value="">-- Pilih Kabupaten --</option>';
            
            if (data.data) {
                data.data.forEach(regency => {
                    regencySelect.innerHTML += `<option value="${regency.id}">${regency.name}</option>`;
                });
            }
        } catch (error) {
            console.error('Error loading regencies:', error);
            regencySelect.innerHTML = '<option value="">-- Error memuat data --</option>';
        }
    });
</script>
@endpush
