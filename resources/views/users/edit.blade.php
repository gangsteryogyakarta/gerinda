@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="header mb-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('users.index') }}" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <h1 class="text-3xl font-bold text-slate-800">Edit User</h1>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <form action="{{ route('users.update', $user) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border-slate-200 focus:border-red-500 focus:ring-red-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border-slate-200 focus:border-red-500 focus:ring-red-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="p-4 bg-yellow-50 rounded-xl border border-yellow-200">
                    <div class="flex items-start gap-3">
                        <i data-lucide="info" class="w-5 h-5 text-yellow-600 mt-0.5"></i>
                        <p class="text-sm text-yellow-700">Kosongkan jika tidak ingin mengubah password.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Password Baru</label>
                        <input type="password" name="password" 
                               class="w-full px-4 py-2.5 rounded-xl border-slate-200 focus:border-red-500 focus:ring-red-500">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" 
                               class="w-full px-4 py-2.5 rounded-xl border-slate-200 focus:border-red-500 focus:ring-red-500">
                    </div>
                </div>

                <!-- Roles -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-3">Role Akses</label>
                    <div class="space-y-3 p-4 bg-slate-50 rounded-xl border border-slate-200">
                        @foreach($roles as $role)
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                                   @if($user->hasRole($role->name)) checked @endif
                                   class="w-5 h-5 rounded border-slate-300 text-red-600 focus:ring-red-500">
                            <span class="text-sm font-medium text-slate-700">{{ $role->name }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('roles')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                    <a href="{{ route('users.index') }}" class="px-5 py-2.5 text-slate-600 font-medium hover:bg-slate-50 rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-red-600 text-white font-medium rounded-xl hover:bg-red-700 transition-colors shadow-lg shadow-red-600/20">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
