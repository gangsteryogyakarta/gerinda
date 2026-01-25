@extends('layouts.app')

@section('content')
<div class="header mb-5">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Manajemen Pengguna</h1>
            <p class="text-slate-500 mt-2">Kelola pengguna dan hak akses sistem.</p>
        </div>
        <a href="{{ route('users.create') }}" class="px-5 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors shadow-lg shadow-red-600/20 flex items-center gap-2">
            <i data-lucide="plus-circle" class="w-5 h-5"></i>
            <span>Tambah User</span>
        </a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Nama</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Email</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Role</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Dibuat</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach ($users as $user)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-medium text-slate-900">{{ $user->name }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-slate-600">{{ $user->email }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            @foreach($user->roles as $role)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-6 py-4 text-slate-500 text-sm">
                        {{ $user->created_at->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('users.edit', $user) }}" class="p-2 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                <i data-lucide="edit-2" class="w-5 h-5"></i>
                            </a>
                            @if(auth()->id() !== $user->id)
                                <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                        <i data-lucide="trash-2" class="w-5 h-5"></i>
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
    <div class="px-6 py-4 border-t border-slate-200">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
