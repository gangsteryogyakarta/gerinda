@extends('layouts.app')

@section('title', 'Print Dashboard - ' . $event->name)

@section('content')
    <!-- Page Header -->
    <div class="page-header" style="flex-direction: column; align-items: flex-start; gap: 20px;">
        <div class="page-header-left">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 8px;">
                <span class="badge badge-info" style="font-size: 14px; padding: 6px 12px;">
                    Print Dashboard
                </span>
            </div>
            <h1>Riwayat Cetak Tiket: {{ $event->name }}</h1>
            <p>Cetak tiket dalam batch untuk menghindari timeout server</p>
        </div>
        <div class="page-header-right" style="width: 100%; display: flex; gap: 12px; border-top: 1px solid var(--border-light); padding-top: 20px; justify-content: flex-start;">
            <form action="{{ route('events.batch-tickets', $event) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="download"></i>
                    Generate Semua PDF
                </button>
            </form>
            <a href="{{ route('events.show', $event) }}" class="btn btn-secondary">
                <i data-lucide="arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="card" style="margin-bottom: 20px; background: rgba(16, 185, 129, 0.15); border-color: var(--success);">
            <div class="card-body" style="padding: 16px 20px;">
                <span style="color: var(--success); font-weight: 600;">✓ {{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Info Box -->
    <div class="card" style="margin-bottom: 24px; background: rgba(59, 130, 246, 0.1); border-color: var(--info);">
        <div class="card-body" style="padding: 16px 20px; display: flex; align-items: center; gap: 12px;">
            <i data-lucide="info" style="color: var(--info); width: 20px; height: 20px;"></i>
            <span style="color: var(--text-secondary); font-size: 14px;">
                Halaman ini akan otomatis diperbarui setiap 10 detik untuk mengecek status job.
            </span>
        </div>
    </div>

    <!-- Jobs Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <div class="card-title-icon">
                    <i data-lucide="printer"></i>
                </div>
                Daftar Print Job
            </h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Batch</th>
                        <th>Range Tiket</th>
                        <th>Status</th>
                        <th>Waktu Request</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobs as $job)
                        <tr>
                            <td><strong>#{{ $job->batch_no }}</strong></td>
                            <td>{{ $job->ticket_range }}</td>
                            <td>
                                @if($job->status == 'completed')
                                    <span class="badge badge-success">Selesai</span>
                                @elseif($job->status == 'failed')
                                    <span class="badge badge-danger" title="{{ $job->error_message }}">Gagal</span>
                                @elseif($job->status == 'processing')
                                    <span class="badge badge-warning">Memproses...</span>
                                @else
                                    <span class="badge" style="background: rgba(255,255,255,0.1); color: var(--text-muted);">Antri</span>
                                @endif
                            </td>
                            <td style="color: var(--text-muted);">
                                {{ $job->created_at->diffForHumans() }}
                            </td>
                            <td style="text-align: right;">
                                @if($job->status == 'completed' && $job->file_path)
                                    <a href="{{ Storage::url($job->file_path) }}" target="_blank" class="btn btn-success btn-sm">
                                        <i data-lucide="download"></i>
                                        Download
                                    </a>
                                @elseif($job->status == 'failed')
                                    <span style="color: var(--danger); font-size: 12px;">{{ Str::limit($job->error_message, 30) }}</span>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                                <i data-lucide="inbox" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                                <p style="font-size: 16px; margin-bottom: 8px;">Belum ada riwayat cetak tiket</p>
                                <p style="font-size: 14px;">Klik "Generate Semua PDF" untuk memulai proses cetak batch.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
<script>
    // Auto refresh every 10 seconds if there are pending jobs
    @if($jobs->whereIn('status', ['pending', 'processing'])->isNotEmpty())
        setTimeout(function() {
            window.location.reload();
        }, 10000);
    @endif
</script>
@endpush
