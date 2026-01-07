@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-avatar" style="background: linear-gradient(135deg, #6B7280, #4B5563);">
                <i data-lucide="settings" style="width: 24px; height: 24px; color: white;"></i>
            </div>
            <div>
                <h1>Pengaturan Sistem</h1>
                <p>Konfigurasi dan pemeliharaan aplikasi</p>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <!-- System Status -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <div class="card-title-icon" style="background: var(--success-light); color: var(--success);">
                        <i data-lucide="activity" style="width: 16px; height: 16px;"></i>
                    </div>
                    Status Sistem
                </div>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="settings-list">
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Queue Driver</div>
                            <div class="settings-item-desc">Driver untuk background jobs</div>
                        </div>
                        <div class="settings-item-value">
                            <span class="badge badge-{{ $settings['queue_driver'] === 'redis' ? 'success' : 'warning' }}">
                                {{ strtoupper($settings['queue_driver']) }}
                            </span>
                        </div>
                    </div>
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Cache Driver</div>
                            <div class="settings-item-desc">Driver untuk caching data</div>
                        </div>
                        <div class="settings-item-value">
                            <span class="badge badge-{{ in_array($settings['cache_driver'], ['redis', 'memcached']) ? 'success' : 'warning' }}">
                                {{ strtoupper($settings['cache_driver']) }}
                            </span>
                        </div>
                    </div>
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Geocoding Provider</div>
                            <div class="settings-item-desc">Layanan untuk konversi alamat ke koordinat</div>
                        </div>
                        <div class="settings-item-value">
                            <span class="badge badge-info">
                                {{ strtoupper($settings['geocoding_provider']) }}
                            </span>
                        </div>
                    </div>
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Google Maps API</div>
                            <div class="settings-item-desc">Integrasi dengan Google Maps</div>
                        </div>
                        <div class="settings-item-value">
                            <span class="badge badge-{{ $settings['google_maps_configured'] ? 'success' : 'danger' }}">
                                {{ $settings['google_maps_configured'] ? 'Configured' : 'Not Configured' }}
                            </span>
                        </div>
                    </div>
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">WhatsApp Gateway</div>
                            <div class="settings-item-desc">Integrasi pengiriman notifikasi WhatsApp</div>
                        </div>
                        <div class="settings-item-value">
                            <span class="badge badge-{{ $settings['wa_gateway_configured'] ? 'success' : 'danger' }}">
                                {{ $settings['wa_gateway_configured'] ? 'Configured' : 'Not Configured' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <div class="card-title-icon" style="background: var(--warning-light); color: var(--warning);">
                        <i data-lucide="wrench" style="width: 16px; height: 16px;"></i>
                    </div>
                    Pemeliharaan
                </div>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <form action="{{ route('settings.clear-cache') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-block" style="justify-content: flex-start;">
                            <i data-lucide="trash-2"></i>
                            Bersihkan Cache
                        </button>
                    </form>
                    
                    <form action="{{ route('settings.migrate') }}" method="POST" 
                          onsubmit="return confirm('Yakin ingin menjalankan migrasi database?')">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-block" style="justify-content: flex-start;">
                            <i data-lucide="database"></i>
                            Jalankan Migrasi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- WhatsApp Test -->
    <div class="card" style="margin-top: 24px;">
        <div class="card-header">
            <div class="card-title">
                <div class="card-title-icon" style="background: #D1FAE5; color: #059669;">
                    <i data-lucide="message-circle" style="width: 16px; height: 16px;"></i>
                </div>
                Test WhatsApp Gateway
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('settings.test-wa') }}" method="POST" style="max-width: 500px;">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nomor WhatsApp</label>
                    <input type="text" name="phone" class="form-input" placeholder="628xxxxxxxxxx" required>
                    <div class="form-hint">Format: 628xxxxxxxxxx (tanpa + atau spasi)</div>
                </div>
                <button type="submit" class="btn btn-success">
                    <i data-lucide="send"></i>
                    Kirim Pesan Test
                </button>
            </form>
        </div>
    </div>

    <!-- Environment Info removed -->
@endsection

@push('styles')
<style>
    .settings-list {
        display: flex;
        flex-direction: column;
    }

    .settings-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 24px;
        border-bottom: 1px solid var(--border-light);
    }

    .settings-item:last-child {
        border-bottom: none;
    }

    .settings-item-label {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 2px;
    }

    .settings-item-desc {
        font-size: 12px;
        color: var(--text-muted);
    }
</style>
@endpush

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
