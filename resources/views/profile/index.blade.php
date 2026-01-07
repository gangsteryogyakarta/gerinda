@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-avatar" style="background: linear-gradient(135deg, #4B5563, #374151);">
                <i data-lucide="user" style="width: 24px; height: 24px; color: white;"></i>
            </div>
            <div>
                <h1>Profil Saya</h1>
                <p>Kelola informasi akun Anda</p>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <!-- Profile Form -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <div class="card-title-icon" style="background: var(--primary-light); color: var(--primary);">
                        <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i>
                    </div>
                    Informasi Akun
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-input" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="divider" style="margin: 32px 0 24px;"></div>
                    <div style="margin-bottom: 24px;">
                        <h4 style="margin-bottom: 8px;">Ganti Password</h4>
                        <p style="color: var(--text-muted); font-size: 14px;">Kosongkan jika tidak ingin mengubah password.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password Saat Ini</label>
                        <div class="input-with-icon-right">
                            <input type="password" name="current_password" class="form-input" placeholder="Masukkan password saat ini">
                            <i data-lucide="lock" class="input-icon"></i>
                        </div>
                        @error('current_password')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password Baru</label>
                        <div class="input-with-icon-right">
                            <input type="password" name="new_password" class="form-input" placeholder="Minimal 8 karakter">
                            <i data-lucide="key" class="input-icon"></i>
                        </div>
                        @error('new_password')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <div class="input-with-icon-right">
                            <input type="password" name="new_password_confirmation" class="form-input" placeholder="Ulangi password baru">
                            <i data-lucide="check" class="input-icon"></i>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 32px;">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="save"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Role Info -->
        <div class="card" style="height: fit-content;">
            <div class="card-header">
                <div class="card-title">
                    <div class="card-title-icon" style="background: var(--info-light); color: var(--info);">
                        <i data-lucide="shield" style="width: 16px; height: 16px;"></i>
                    </div>
                    Informasi Role
                </div>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Role Aktif</div>
                        <div style="font-size: 18px; font-weight: 700;">
                            {{ $user->roles->first()->name ?? 'Guest' }}
                        </div>
                    </div>
                    
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Bergabung Sejak</div>
                        <div>{{ $user->created_at->translatedFormat('d F Y') }}</div>
                    </div>

                    <div style="margin-top: 12px; padding-top: 24px; border-top: 1px solid var(--border-light);">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-block">
                                <i data-lucide="log-out"></i>
                                Logout dari Aplikasi
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .input-with-icon-right {
        position: relative;
    }

    .input-with-icon-right .form-input {
        padding-right: 40px;
    }

    .input-with-icon-right .input-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        color: var(--text-muted);
    }
</style>
@endpush
