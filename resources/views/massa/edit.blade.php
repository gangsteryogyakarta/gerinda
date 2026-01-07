@extends('layouts.app')

@section('title', 'Edit - ' . $massa->nama_lengkap)

@section('content')
    <!-- Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1>✏️ Edit Data Massa</h1>
            <p>{{ $massa->nama_lengkap }}</p>
        </div>
        <div class="page-header-right">
            <a href="{{ route('massa.show', $massa) }}" class="btn btn-secondary">
                <i data-lucide="arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    <form action="{{ route('massa.update', $massa) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-grid">
            <!-- Main Form -->
            <div class="form-main">
                <!-- Identity -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🪪 Data Identitas</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">NIK</label>
                            <input type="text" class="form-input" value="{{ $massa->nik }}" disabled>
                            <span class="form-hint">NIK tidak dapat diubah</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-input" 
                                   value="{{ old('nama_lengkap', $massa->nama_lengkap) }}" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Jenis Kelamin <span class="required">*</span></label>
                                <select name="jenis_kelamin" class="form-input" required>
                                    <option value="L" {{ old('jenis_kelamin', $massa->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin', $massa->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-input" 
                                       value="{{ old('tanggal_lahir', $massa->tanggal_lahir?->format('Y-m-d')) }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-input" 
                                   value="{{ old('tempat_lahir', $massa->tempat_lahir) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Pekerjaan</label>
                            <input type="text" name="pekerjaan" class="form-input" 
                                   value="{{ old('pekerjaan', $massa->pekerjaan) }}">
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📍 Alamat</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">Alamat Lengkap <span class="required">*</span></label>
                            <textarea name="alamat" class="form-input" rows="3" required>{{ old('alamat', $massa->alamat) }}</textarea>
                        </div>

                        <div class="form-row" style="grid-template-columns: 1fr 1fr 1fr;">
                            <div class="form-group">
                                <label class="form-label">RT</label>
                                <input type="text" name="rt" class="form-input" value="{{ old('rt', $massa->rt) }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">RW</label>
                                <input type="text" name="rw" class="form-input" value="{{ old('rw', $massa->rw) }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kode Pos</label>
                                <input type="text" name="kode_pos" class="form-input" value="{{ old('kode_pos', $massa->kode_pos) }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Provinsi</label>
                                <select name="province_id" class="form-input">
                                    <option value="">-- Pilih Provinsi --</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}" {{ old('province_id', $massa->province_id) == $province->id ? 'selected' : '' }}>
                                            {{ $province->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kabupaten/Kota</label>
                                <select name="regency_id" class="form-input">
                                    <option value="">-- Pilih Kabupaten --</option>
                                    @if($massa->regency)
                                        <option value="{{ $massa->regency_id }}" selected>{{ $massa->regency->name }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="form-sidebar">
                <!-- Contact -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📞 Kontak</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">Nomor HP</label>
                            <input type="tel" name="no_hp" class="form-input" value="{{ old('no_hp', $massa->no_hp) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-input" value="{{ old('email', $massa->email) }}">
                        </div>
                    </div>
                </div>

                <!-- Current Location -->
                @if($massa->latitude)
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">📍 Koordinat Saat Ini</h3>
                        </div>
                        <div class="card-body">
                            <code style="font-size: 12px;">{{ $massa->latitude }}, {{ $massa->longitude }}</code>
                            <p style="font-size: 12px; color: var(--text-muted); margin-top: 8px;">
                                Koordinat akan diperbarui otomatis jika alamat berubah.
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i data-lucide="save"></i>
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('massa.show', $massa) }}" class="btn btn-secondary btn-block" style="margin-top: 12px;">
                            Batal
                        </a>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="card" style="border-color: var(--danger);">
                    <div class="card-header">
                        <h3 class="card-title" style="color: var(--danger);">⚠️ Zona Berbahaya</h3>
                    </div>
                    <div class="card-body">
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
                            Menghapus data massa akan menghilangkan semua riwayat terkait.
                        </p>
                        <form action="{{ route('massa.destroy', $massa) }}" method="POST" 
                              onsubmit="return confirm('Yakin ingin menghapus data massa ini? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block">
                                <i data-lucide="trash-2"></i>
                                Hapus Data Massa
                            </button>
                        </form>
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

    .form-input {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 12px 16px;
        color: var(--text-primary);
        font-size: 14px;
        width: 100%;
        outline: none;
    }

    .form-input:focus {
        border-color: var(--primary);
    }

    .form-input:disabled {
        opacity: 0.6;
        cursor: not-allowed;
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

    .btn-block {
        width: 100%;
    }

    .btn-danger {
        background: var(--danger);
        color: white;
    }

    textarea.form-input {
        resize: vertical;
    }

    code {
        background: var(--bg-tertiary);
        padding: 4px 8px;
        border-radius: 4px;
        font-family: monospace;
    }
</style>
@endpush
