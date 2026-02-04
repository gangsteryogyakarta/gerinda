@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-avatar">
            <i data-lucide="users"></i>
        </div>
        <div>
            <h1>Manajemen User</h1>
            <p>Kelola pengguna dan hak akses sistem.</p>
        </div>
    </div>
    <div class="page-header-right">
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i data-lucide="plus-circle"></i>
            Tambah User
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <div class="card-title-icon">
                <i data-lucide="list"></i>
            </div>
            Daftar Pengguna
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: white;">{{ $user->name }}</div>
                        </td>
                        <td>
                            <div style="color: rgba(255,255,255,0.7);">{{ $user->email }}</div>
                        </td>
                        <td>
                            <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                @foreach($user->roles as $role)
                                    <span class="badge badge-info">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-secondary">Non-Aktif</span>
                            @endif
                        </td>
                        <td>
                            <span style="color: rgba(255,255,255,0.6); font-size: 13px;">
                                {{ $user->created_at->format('d M Y') }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                @if(auth()->id() !== $user->id)
                                    <form action="{{ route('users.toggle-status', $user) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-secondary' : 'btn-success' }}" 
                                                title="{{ $user->is_active ? 'Non-aktifkan' : 'Aktifkan' }}">
                                            <i data-lucide="{{ $user->is_active ? 'lock' : 'unlock' }}"></i>
                                        </button>
                                    </form>
                                @endif
                                
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-secondary" title="Edit">
                                    <i data-lucide="edit-2"></i>
                                </a>
                                @if(auth()->id() !== $user->id)
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-secondary" style="color: #EF4444;" title="Hapus">
                                            <i data-lucide="trash-2"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
        <div style="padding: 20px; border-top: 1px solid var(--border-light);">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .table-responsive {
        overflow-x: auto;
    }
    .p-0 {
        padding: 0 !important;
    }
    .text-right {
        text-align: right !important;
    }
</style>
@endpush
@endsection
