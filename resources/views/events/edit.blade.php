@extends('layouts.app')

@section('title', 'Edit Event - ' . $event->name)

@section('content')
    <!-- Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1>✏️ Edit Event</h1>
            <p>{{ $event->name }}</p>
        </div>
        <div class="page-header-right">
            <a href="{{ route('events.show', $event) }}" class="btn btn-secondary">
                <i data-lucide="arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    <form action="{{ route('events.update', $event) }}" method="POST" class="event-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
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
                                   value="{{ old('name', $event->name) }}" required>
                            @error('name')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Kategori Event</label>
                            <select name="event_category_id" class="form-input">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('event_category_id', $event->event_category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-input" rows="4">{{ old('description', $event->description) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Copywriting (Detail Lengkap)</label>
                            <textarea name="copywriting" class="form-input" rows="6" 
                                      placeholder="Tuliskan detail lengkap event untuk materi promosi...">{{ old('copywriting', $event->copywriting) }}</textarea>
                            <span class="form-hint">Gunakan ini untuk detail panjang, ajakan bertindak, benefit, dll.</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Banner Event</label>
                            <div class="image-upload-area" id="banner-upload-area">
                                <input type="file" name="banner_image" id="banner_image" class="hidden-input" accept="image/*" onchange="previewImage(this)">
                                
                                <div class="upload-placeholder" id="upload-placeholder" style="{{ $event->banner_image ? 'display: none;' : '' }}">
                                    <i data-lucide="image"></i>
                                    <span>Klik untuk ganti banner</span>
                                    <span class="file-info">JPG, PNG, max 2MB</span>
                                </div>
                                
                                <img id="banner-preview" class="image-preview" 
                                     src="{{ $event->banner_image ? asset('storage/' . $event->banner_image) : '' }}" 
                                     style="{{ $event->banner_image ? '' : 'display: none;' }}">
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
                            <input type="text" name="venue_name" class="form-input" 
                                   value="{{ old('venue_name', $event->venue_name) }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Alamat Lengkap <span class="required">*</span></label>
                            <textarea name="venue_address" class="form-input" rows="2" required>{{ old('venue_address', $event->venue_address) }}</textarea>
                        </div>

                        <x-location-selector 
                            :province-id="old('province_id', $event->province_id)"
                            :regency-id="old('regency_id', $event->regency_id)"
                            :district-id="old('district_id', $event->district_id)"
                            :village-id="old('village_id', $event->village_id)"
                            :postal-code="old('postal_code', $event->postal_code)"
                        />
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
                                <input type="datetime-local" name="event_start" class="form-input" 
                                       value="{{ old('event_start', $event->event_start->format('Y-m-d\TH:i')) }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Selesai Event <span class="required">*</span></label>
                                <input type="datetime-local" name="event_end" class="form-input" 
                                       value="{{ old('event_end', $event->event_end->format('Y-m-d\TH:i')) }}" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Buka Registrasi</label>
                                <input type="datetime-local" name="registration_start" class="form-input" 
                                       value="{{ old('registration_start', $event->registration_start?->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tutup Registrasi</label>
                                <input type="datetime-local" name="registration_end" class="form-input" 
                                       value="{{ old('registration_end', $event->registration_end?->format('Y-m-d\TH:i')) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="form-sidebar">
                <!-- Status -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📊 Status</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">Status Event</label>
                            <select name="status" class="form-input">
                                <option value="draft" {{ $event->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ $event->status === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="ongoing" {{ $event->status === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                <option value="completed" {{ $event->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $event->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Quota -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">👥 Kuota Peserta</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">Maksimal Peserta</label>
                            <input type="number" name="max_participants" class="form-input" 
                                   value="{{ old('max_participants', $event->max_participants) }}" min="1">
                            <span class="form-hint">Biarkan kosong jika tidak ada batasan</span>
                        </div>

                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" name="enable_waitlist" value="1" {{ old('enable_waitlist', $event->enable_waitlist) ? 'checked' : '' }}>
                                <span class="checkmark"></span>
                                Aktifkan Waiting List
                            </label>
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
                                <input type="checkbox" name="require_ticket" value="1" {{ old('require_ticket', $event->require_ticket) ? 'checked' : '' }}>
                                Memerlukan Tiket
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" name="enable_checkin" value="1" {{ old('enable_checkin', $event->enable_checkin) ? 'checked' : '' }}>
                                Aktifkan Check-in
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" name="enable_lottery" value="1" {{ old('enable_lottery', $event->enable_lottery) ? 'checked' : '' }}>
                                Aktifkan Undian Hadiah
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" name="send_wa_notification" value="1" {{ old('send_wa_notification', $event->send_wa_notification) ? 'checked' : '' }}>
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
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('events.show', $event) }}" class="btn btn-secondary btn-block" style="margin-top: 12px;">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
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

    .form-main .card,
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
    }

    .form-label .required {
        color: var(--primary);
    }

    /* Unified Input Styling */
    .form-input, 
    select.form-input {
        width: 100%;
        background-color: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 12px 16px;
        color: var(--text-primary);
        font-size: 14px;
        outline: none;
        transition: all 0.2s ease;
        
        /* Fix for select elements */
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 16px center;
        background-size: 16px;
    }

    /* Force consistent height for select to match inputs */
    select.form-input {
        height: 46px;
    }

    .form-input:focus,
    select.form-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
    }

    .form-input:disabled,
    select.form-input:disabled {
        background-color: var(--bg-tertiary);
        cursor: not-allowed;
        opacity: 0.7;
    }

    /* Placeholder color */
    .form-input::placeholder {
        color: var(--text-muted);
    }

    /* Validation states */
    .form-input.is-invalid {
        border-color: var(--danger);
    }

    /* Dark mode specific override */
    @media (prefers-color-scheme: dark) {
        .form-input,
        select.form-input {
            background-color: #1e293b;
            border-color: #334155;
            color: #f8fafc;
        }
        
        select.form-input option {
            background-color: #1e293b;
            color: #f8fafc;
        }
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
    }

    .btn-block {
        width: 100%;
    }

    textarea.form-input {
        resize: vertical;
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
</script>
@endpush
