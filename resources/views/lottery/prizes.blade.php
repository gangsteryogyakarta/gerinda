@extends('layouts.app')

@section('title', 'Kelola Hadiah - ' . $event->name)

@section('content')
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <h1>🎁 Kelola Hadiah</h1>
            <p>{{ $event->name }}</p>
        </div>
        <div class="header-right">
            <a href="{{ route('lottery.event', $event) }}" class="btn btn-primary">
                <i class="lucide-arrow-left"></i>
                Kembali ke Undian
            </a>
        </div>
    </div>

    <div class="grid-2">
        <!-- Add Prize Form -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">➕ Tambah Hadiah</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('lottery.store-prize', $event) }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">Nama Hadiah <span class="required">*</span></label>
                        <input type="text" name="name" class="form-input" placeholder="Contoh: Sepeda Motor" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-input" rows="3" placeholder="Deskripsi hadiah (opsional)"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jumlah <span class="required">*</span></label>
                        <input type="number" name="quantity" class="form-input" value="1" min="1" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="lucide-plus"></i>
                        Tambah Hadiah
                    </button>
                </form>
            </div>
        </div>

        <!-- Prize List -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">📋 Daftar Hadiah</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                @forelse($prizes as $prize)
                    <div class="prize-item">
                        <div class="prize-info">
                            <div class="prize-name">{{ $prize->name }}</div>
                            <div class="prize-desc">{{ $prize->description ?? '-' }}</div>
                            <div class="prize-stock">
                                <span class="badge badge-{{ $prize->remaining_quantity > 0 ? 'success' : 'danger' }}">
                                    {{ $prize->remaining_quantity }}/{{ $prize->quantity }} tersisa
                                </span>
                            </div>
                        </div>
                        <div class="prize-actions">
                            @if(!$prize->draws()->exists())
                                <form action="{{ route('lottery.delete-prize', $prize) }}" method="POST" 
                                      onsubmit="return confirm('Yakin ingin menghapus hadiah ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="lucide-trash-2"></i>
                                    </button>
                                </form>
                            @else
                                <span class="badge badge-info">Sudah diundi</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-prizes">
                        <p>Belum ada hadiah. Tambahkan hadiah di form sebelah.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .form-group {
        margin-bottom: 20px;
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

    textarea.form-input {
        resize: vertical;
    }

    .prize-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
    }

    .prize-item:last-child {
        border-bottom: none;
    }

    .prize-name {
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 4px;
    }

    .prize-desc {
        color: var(--text-muted);
        font-size: 13px;
        margin-bottom: 8px;
    }

    .empty-prizes {
        padding: 60px 20px;
        text-align: center;
        color: var(--text-muted);
    }

    .btn-danger {
        background: var(--danger);
        color: white;
    }
</style>
@endpush
