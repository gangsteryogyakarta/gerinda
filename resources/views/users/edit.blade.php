@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
            <i data-lucide="arrow-left"></i>
            Kembali
        </a>
        <div>
            <h1>Edit User</h1>
            <p>Perbarui informasi akun pengguna dan hak akses.</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div style="display: grid; grid-template-columns: 1fr; gap: 24px; max-width: 800px;">
                <!-- Name -->
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span style="color:red">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="form-input" placeholder="Masukkan nama lengkap">
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label">Email Address <span style="color:red">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="form-input" placeholder="contoh@gerindradiy.com">
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); padding: 12px 16px; border-radius: 8px;">
                    <div style="display: flex; gap: 10px; align-items: flex-start;">
                        <i data-lucide="info" style="color: #F59E0B; width: 18px; height: 18px; margin-top: 2px;"></i>
                        <p style="font-size: 13px; color: #FCD34D; margin: 0;">Isi kolom password hanya jika ingin menggantinya. Biarkan kosong jika tidak.</p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password"
                               class="form-input" placeholder="••••••••">
                        @error('password')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation"
                               class="form-input" placeholder="••••••••">
                    </div>
                </div>

                <!-- Roles -->
                <div class="form-group">
                    <label class="form-label">Role Akses <span style="color:red">*</span></label>
                    <div style="background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px;">
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            @foreach($roles as $role)
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                                       @if($user->hasRole($role->name)) checked @endif
                                       style="width: 16px; height: 16px; accent-color: var(--primary);">
                                <span style="font-size: 14px; color: white;">{{ $role->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @error('roles')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div style="padding-top: 20px; border-top: 1px solid var(--border-light); display: flex; justify-content: flex-end; gap: 12px;">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="save"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    @media (max-width: 768px) {
        div[style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush
@endsection
