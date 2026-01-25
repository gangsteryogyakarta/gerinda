@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
            <i data-lucide="arrow-left"></i>
            Kembali
        </a>
        <div>
            <h1>Tambah User Baru</h1>
            <p>Buat akun pengguna baru dengan hak akses tertentu.</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            
            <div style="display: grid; grid-template-columns: 1fr; gap: 24px; max-width: 800px;">
                <!-- Name -->
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span style="color:red">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="form-input" placeholder="Masukkan nama lengkap">
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label">Email Address <span style="color:red">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="form-input" placeholder="contoh@gerindradiy.com">
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Password <span style="color:red">*</span></label>
                        <input type="password" name="password" required
                               class="form-input" placeholder="••••••••">
                        @error('password')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password <span style="color:red">*</span></label>
                        <input type="password" name="password_confirmation" required
                               class="form-input" placeholder="••••••••">
                    </div>
                </div>

                <!-- Roles -->
                <div class="form-group">
                    <label class="form-label">Role Akses <span style="color:red">*</span></label>
                    <div style="background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px;">
                        <p style="font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 12px;">Pilih satu atau lebih role:</p>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            @foreach($roles as $role)
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
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
                        Simpan User
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
