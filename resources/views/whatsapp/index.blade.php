@extends('layouts.app')

@section('title', 'WhatsApp Blast')

@section('content')
<div class="wa-container">
    <!-- Header Section -->
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-avatar">
                <i data-lucide="message-circle" style="width: 24px; height: 24px;"></i>
            </div>
            <div>
                <h1>WhatsApp Blast</h1>
                <p>Kirim pesan massal dan notifikasi event secara otomatis</p>
            </div>
        </div>
        <div class="page-header-right">
            <a href="{{ route('whatsapp.analytics.dashboard') }}" class="btn-analytics" title="Lihat Analytics">
                <i data-lucide="bar-chart-2"></i>
                <span class="hidden md:inline">Analytics</span>
            </a>
            <div id="connection-status-pill" class="connection-pill disconnected">
                <span class="pill-dot"></span>
                <span class="pill-text" id="connection-text">Memuat...</span>
            </div>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon success">
                    <i data-lucide="check-check"></i>
                </div>
                <div class="stat-trend up">
                    <i data-lucide="trending-up" style="width: 14px; height: 14px;"></i>
                    <span>Terkirim</span>
                </div>
            </div>
            <div class="stat-value" id="stat-sent">0</div>
            <div class="stat-label">Pesan Terkirim</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon warning">
                    <i data-lucide="clock"></i>
                </div>
                <div class="stat-trend">
                    <span>Pending</span>
                </div>
            </div>
            <div class="stat-value" id="stat-queue">0</div>
            <div class="stat-label">Dalam Antrean</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon primary">
                    <i data-lucide="alert-circle"></i>
                </div>
                <div class="stat-trend down">
                    <span>Gagal</span>
                </div>
            </div>
            <div class="stat-value" id="stat-failed">0</div>
            <div class="stat-label">Gagal Terkirim</div>
        </div>
        <div class="stat-card">
             <div class="stat-card-header">
                <div class="stat-icon info">
                    <i data-lucide="smartphone"></i>
                </div>
                <div class="stat-trend">
                     <span>Device</span>
                </div>
            </div>
             <div class="stat-value" style="font-size: 20px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" id="connected-phone-stat">--</div>
            <div class="stat-label">Status Perangkat</div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="wa-grid">
        <!-- Message Composer (Left, Large) -->
        <div class="card composer-card">
            <div class="card-header">
                <div class="tab-options">
                    <button class="tab-btn active" data-tab="single">
                        <i data-lucide="message-square"></i> Pesan Tunggal
                    </button>
                    <button class="tab-btn" data-tab="bulk">
                        <i data-lucide="users"></i> Blast Massa
                    </button>
                    <button class="tab-btn" data-tab="event">
                        <i data-lucide="calendar"></i> Event
                    </button>
                    <button class="tab-btn" data-tab="templates">
                        <i data-lucide="file-text"></i> Template
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Single Message -->
                <div class="tab-content active" id="tab-single">
                    <form id="single-message-form">
                        <div class="form-group">
                            <label class="form-label">Nomor WhatsApp <span class="required">*</span></label>
                            <input type="text" id="single-phone" class="form-input" placeholder="08xxxxxxxxxx" required>
                            <div class="form-hint">Gunakan format 08xx atau 628xx</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Isi Pesan <span class="required">*</span></label>
                            <textarea id="single-message" class="form-input" rows="6" placeholder="Ketik pesan Anda disini..." required maxlength="4096"></textarea>
                            <div class="char-counter"><span id="single-char-count">0</span>/4096</div>
                        </div>
                         <!-- Add Image Upload for Single Message -->


                        <div class="form-action right">
                            <button type="submit" class="btn btn-primary" id="btn-send-single">
                                <i data-lucide="send"></i> Kirim Pesan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Bulk Blast -->
                <div class="tab-content" id="tab-bulk">
                    <div class="alert alert-info mb-4">
                        <div class="alert-icon"><i data-lucide="info"></i></div>
                        <div class="alert-content">
                            <strong>Broadcast Cerdas</strong>
                            <p>Pesan dikirim dengan jeda waktu acak (3-8 detik) untuk menghindari blokir WhatsApp.</p>
                        </div>
                    </div>
                    <form id="bulk-message-form">
                        <div class="grid-2">
                            <div class="form-group">
                                <label class="form-label">Target Penerima</label>
                                <select id="bulk-filter" class="form-input">
                                    <option value="all">Semua Data Massa</option>
                                    <option value="active">Hanya Anggota Aktif</option>
                                    <option value="province">Filter Wilayah</option>
                                </select>
                            </div>
                            <div class="form-group" id="province-select-container" style="display:none;">
                                <label class="form-label">Provinsi</label>
                                <select id="bulk-province" class="form-input">
                                    <option value="">-- Pilih Provinsi --</option>
                                </select>
                            </div>
                        </div>

                        <!-- Advanced Filters -->
                        <div class="advanced-filters">
                            <div class="advanced-header" onclick="toggleAdvanced()">
                                <span><i data-lucide="sliders"></i> Filter Lanjutan (Demografi)</span>
                                <i data-lucide="chevron-down" id="advanced-icon"></i>
                            </div>
                            <div class="advanced-body" id="advanced-body" style="display:none;">
                                <div class="grid-2">
                                     <div class="form-group">
                                        <label class="form-label">Jenis Kelamin</label>
                                        <select id="bulk-gender" class="form-input">
                                            <option value="">Semua</option>
                                            <option value="L">Laki-laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Kabupaten/Kota</label>
                                        <select id="bulk-regency" class="form-input">
                                            <option value="">Semua</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Usia Minimal</label>
                                        <input type="number" id="bulk-age-min" class="form-input" placeholder="17">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Usia Maksimal</label>
                                        <input type="number" id="bulk-age-max" class="form-input" placeholder="60">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                             <label class="form-label">Limit Pengiriman (max 1000)</label>
                             <input type="number" id="bulk-limit" class="form-input" placeholder="1000" min="1" max="1000">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Pesan Broadcast <span class="required">*</span></label>
                            <textarea id="bulk-message" class="form-input" rows="5" placeholder="Gunakan {nama} untuk menyebut nama penerima..." required></textarea>
                            <div class="char-counter"><span id="bulk-char-count">0</span>/4096</div>
                        </div>
                        


                        <div class="form-action right">
                            <button type="submit" class="btn btn-primary" id="btn-send-bulk">
                                <i data-lucide="rocket"></i> Mulai Broadcast
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Event Notification -->
                <div class="tab-content" id="tab-event">
                    <form id="event-message-form">
                        <div class="grid-2">
                            <div class="form-group">
                                <label class="form-label">Pilih Event <span class="required">*</span></label>
                                <select id="event-select" class="form-input" required>
                                    <option value="">-- Pilih Event --</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}">{{ $event->name }} ({{ $event->event_start ? $event->event_start->format('d M Y') : 'TBA' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Status Peserta</label>
                                <select id="event-status" class="form-input">
                                    <option value="all">Semua Pendaftar</option>
                                    <option value="confirmed">Sudah Konfirmasi</option>
                                    <option value="pending">Belum Konfirmasi</option>
                                    <option value="checked_in">Sudah Check-in (Hadir)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pesan Notifikasi <span class="required">*</span></label>
                            <textarea id="event-message" class="form-input" rows="5" placeholder="Informasi seputar event..." required></textarea>
                            <div class="char-counter"><span id="event-char-count">0</span>/4096</div>
                        </div>
                        <div class="form-action right">
                            <button type="submit" class="btn btn-primary" id="btn-send-event">
                                <i data-lucide="bell"></i> Kirim Notifikasi
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Template Builder -->
                <div class="tab-content" id="tab-templates">
                    <div class="template-builder-container">
                        <!-- Top Row: Variables + Editor -->
                        <div class="template-builder-top">
                            <!-- Left: Variables Panel -->
                            <div class="variables-panel">
                                <h4><i data-lucide="code"></i> Variabel</h4>
                                <div id="variables-list" class="variables-list">
                                    <!-- Loaded via JS -->
                                </div>
                                <div class="condition-hint">
                                    <small><i data-lucide="info"></i> Klik untuk insert</small>
                                </div>
                            </div>

                            <!-- Right: Editor -->
                            <div class="editor-panel">
                                <div class="editor-header">
                                    <div class="form-group" style="margin-bottom: 8px;">
                                        <input type="text" id="template-name" class="form-input" placeholder="Nama Template" required>
                                    </div>
                                    <div class="editor-meta">
                                        <select id="template-category" class="form-input" style="width: auto;">
                                            <option value="umum">Pesan Umum</option>
                                            <option value="promosi">Promosi & Undangan</option>
                                            <option value="event">Notifikasi Event</option>
                                            <option value="birthday">Ucapan Ulang Tahun</option>
                                            <option value="survey">Survey & Feedback</option>
                                            <option value="transaksi">Konfirmasi Transaksi</option>
                                        </select>
                                    </div>
                                </div>
                                <textarea id="template-content" class="form-input template-textarea" rows="8" placeholder="Tulis template pesan di sini...

Gunakan {nama} untuk nama penerima
Gunakan {if:kategori=Pengurus}...{endif} untuk kondisi"></textarea>
                                <div class="editor-footer">
                                    <span class="char-counter"><span id="template-char-count">0</span>/4096</span>
                                    <div class="editor-actions">
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="loadExistingTemplates()">
                                            <i data-lucide="folder-open"></i> Muat Template
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="saveTemplate()">
                                            <i data-lucide="save"></i> Simpan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Row: Preview -->
                        <div class="preview-panel preview-panel-bottom">
                            <div class="preview-header">
                                <h4><i data-lucide="eye"></i> Preview Pesan</h4>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <small>Sample: <span id="sample-name">Budi Santoso</span></small>
                                    <button type="button" class="btn-icon-sm" onclick="refreshPreview()" title="Refresh">
                                        <i data-lucide="refresh-cw"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="template-preview" class="preview-content">
                                <div class="preview-placeholder">
                                    <i data-lucide="message-circle"></i>
                                    <p>Preview akan muncul di sini setelah Anda menulis template</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Templates Library -->
                    <div class="templates-library" style="margin-top: 16px;">
                        <div class="library-header" onclick="toggleTemplatesLibrary()">
                            <span><i data-lucide="library"></i> Template Library</span>
                            <i data-lucide="chevron-down" id="library-icon"></i>
                        </div>
                        <div id="templates-library-body" class="library-body" style="display: none;">
                            <div id="templates-grid" class="templates-grid">
                                <!-- Loaded via JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Connection & Templates -->
        <div class="side-col">
            <!-- Connection Card -->
            <div class="card connection-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <div class="card-title-icon"><i data-lucide="link"></i></div>
                        Koneksi
                    </h3>
                    <div class="header-action">
                        <button class="btn-icon-sm" onclick="checkHealth()" title="Refresh Status">
                            <i data-lucide="refresh-cw"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body centered">
                    <!-- Loading -->
                    <div id="session-loading" class="conn-state">
                        <div class="spinner"></div>
                        <p>Memeriksa status...</p>
                    </div>

                    <!-- QR Code -->
                    <div id="qr-container" class="conn-state" style="display:none;">
                        <div class="qr-wrapper">
                            <img id="qr-image" src="" alt="Scan QR">
                            <div class="scan-line"></div>
                        </div>
                        <p class="mt-3 text-muted">Scan QR Code dengan WhatsApp Anda</p>
                        <div class="countdown">Refresh otomatis dalam <span id="qr-timer">5</span>s</div>
                    </div>

                    <!-- Connected -->
                    <div id="connected-container" class="conn-state" style="display:none;">
                        <div class="success-ring">
                            <i data-lucide="check" style="width: 40px; height: 40px; stroke-width: 3;"></i>
                        </div>
                        <h4>Terhubung</h4>
                        <p class="text-muted" id="connected-detail">WhatsApp Siap</p>
                        
                         <button class="btn btn-sm btn-danger mt-3 w-100" onclick="logout()">
                            <i data-lucide="log-out"></i> Putuskan Koneksi
                        </button>
                    </div>

                    <!-- Disconnected / Start -->
                    <div id="start-container" class="conn-state" style="display:none;">
                        <div class="off-icon">
                            <i data-lucide="power-off"></i>
                        </div>
                        <h4>Tidak Terhubung</h4>
                        <p class="text-muted mb-3">Sesi WhatsApp belum aktif</p>
                        <button class="btn btn-primary w-100" id="btn-start-session" onclick="startSession()">
                            <i data-lucide="play"></i> Mulai Sesi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts & Styles -->
@push('styles')
<style>
    /* WhatsApp Page Specific Styles */
    .wa-container {
        padding-bottom: 2rem;
    }

    .wa-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 24px;
    }



    /* Custom Card Styles for WA Page to match Premium Theme */
    .composer-card {
        min-height: 500px;
    }

    /* Tabs */
    .tab-options {
        display: flex;
        gap: 8px;
        background: var(--bg-input);
        padding: 4px;
        border-radius: var(--radius);
    }

    .tab-btn {
        flex: 1;
        border: none;
        background: transparent;
        color: var(--text-secondary);
        padding: 10px 16px;
        border-radius: var(--radius-sm);
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .tab-btn:hover {
        color: var(--text-primary);
        background: rgba(255,255,255,0.05);
    }
    
    /* Fix for Select Option Colors */
    select.form-input option {
        color: #000000;
        background-color: #ffffff;
    }

    .tab-btn.active {
        background: white;
        color: var(--primary);
        box-shadow: var(--shadow-sm);
    }
    
    .tab-content {
        display: none;
        animation: fadeIn 0.3s ease;
    }
    .tab-content.active {
        display: block;
    }

    /* Utils */
    .char-counter {
        text-align: right;
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .form-action.right {
        display: flex;
        justify-content: flex-end;
        margin-top: 24px;
    }

    /* Connection Card */
    .conn-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 20px 0;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid var(--primary-light);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s infinite linear;
        margin-bottom: 16px;
    }

    .qr-wrapper {
        width: 200px;
        height: 200px;
        background: white;
        padding: 10px;
        border-radius: var(--radius);
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow);
    }
    
    .qr-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    
    .scan-line {
        position: absolute;
        width: 100%;
        height: 2px;
        background: var(--primary);
        top: 0;
        left: 0;
        animation: scan 2s infinite linear;
        box-shadow: 0 0 4px var(--primary);
    }
    
    @keyframes scan {
        0% { top: 0; }
        50% { top: 100%; }
        100% { top: 0; }
    }

    .success-ring {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: var(--success-light);
        color: var(--success);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .off-icon {
        width: 60px;
        height: 60px;
        background: var(--bg-input);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        margin-bottom: 16px;
    }

    /* Template Lists */
    .template-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .template-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: transparent;
        border: 1px solid transparent;
        border-radius: var(--radius);
        cursor: pointer;
        text-align: left;
        transition: all 0.2s;
        width: 100%;
    }

    .template-item:hover {
        background: var(--bg-input);
        border-color: var(--border-color);
    }

    .tpl-icon {
        width: 36px;
        height: 36px;
        background: var(--primary-light);
        color: var(--primary);
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .tpl-info span {
        display: block;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 13px;
    }

    .tpl-info small {
        color: var(--text-muted);
        font-size: 11px;
    }
    
    /* Connection Pill in Header */
    .connection-pill {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        background: white;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        box-shadow: var(--shadow-sm);
    }
    
    .connection-pill.connected { color: var(--success); }
    .connection-pill.disconnected { color: var(--danger); }
    .connection-pill .pill-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
    }

    .btn-analytics {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        background: #facc15; /* Yellow */
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        color: #000000; /* Black text */
        box-shadow: var(--shadow-sm);
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        margin-right: 12px;
    }

    .btn-analytics:hover {
        background: #eab308; /* Darker yellow */
        color: #000000;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .btn-analytics i {
        width: 16px;
        height: 16px;
        stroke-width: 2.5px;
    }

    /* Advanced Filters */
    .advanced-filters {
        margin-top: 1rem;
        background: var(--bg-input);
        border-radius: var(--radius);
        overflow: hidden;
    }
    
    .advanced-header {
        padding: 12px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        color: var(--text-secondary);
    }
    
    .advanced-body {
        padding: 16px;
        border-top: 1px solid var(--border-light);
    }

    /* Alert */
    .alert {
        padding: 12px 16px;
        border-radius: var(--radius);
        display: flex;
        gap: 12px;
        font-size: 13px;
    }
    
    .alert-info {
        background: var(--info-light);
        color: var(--info);
    }

    /* Button Spinner for Loading States */
    .btn-spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin 0.8s infinite linear;
        vertical-align: middle;
        margin-right: 6px;
    }

    /* ========== TEMPLATE BUILDER STYLES ========== */
    .template-builder-container {
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-height: 400px;
    }

    .template-builder-top {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 16px;
    }

    /* Variables Panel */
    .variables-panel {
        background: var(--bg-input);
        border-radius: var(--radius);
        padding: 12px;
        overflow-y: auto;
        max-height: 350px;
    }

    .variables-panel h4 {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--text-secondary);
    }

    .variables-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .var-group-label {
        font-size: 10px;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-top: 8px;
        letter-spacing: 0.5px;
    }

    .var-chip {
        display: inline-block;
        padding: 4px 8px;
        background: var(--primary-light);
        color: var(--primary);
        border-radius: 4px;
        font-size: 11px;
        font-family: monospace;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        text-align: left;
    }

    .var-chip:hover {
        background: var(--primary);
        color: white;
        transform: scale(1.02);
    }

    .condition-hint {
        margin-top: 16px;
        padding-top: 12px;
        border-top: 1px solid var(--border-light);
    }

    .condition-hint small {
        color: var(--text-muted);
        font-size: 11px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Editor Panel */
    .editor-panel {
        display: flex;
        flex-direction: column;
    }

    .editor-header {
        margin-bottom: 8px;
    }

    .editor-meta {
        display: flex;
        gap: 8px;
    }

    .template-textarea {
        flex: 1;
        font-family: 'Consolas', 'Monaco', monospace;
        font-size: 13px;
        line-height: 1.6;
        resize: none;
        min-height: 300px;
    }

    .editor-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 8px;
    }

    .editor-actions {
        display: flex;
        gap: 8px;
    }

    /* Preview Panel */
    .preview-panel {
        background: var(--bg-input);
        border-radius: var(--radius);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .preview-panel-bottom {
        flex-direction: row;
        min-height: 120px;
        max-height: 150px;
    }

    .preview-panel-bottom .preview-header {
        writing-mode: horizontal-tb;
        flex-shrink: 0;
        border-bottom: none;
        border-right: 1px solid var(--border-light);
        padding: 12px 16px;
        flex-direction: column;
        gap: 8px;
        min-width: 180px;
    }

    .preview-panel-bottom .preview-content {
        flex: 1;
        min-height: 100px;
    }

    .preview-header {
        padding: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border-light);
    }

    .preview-header h4 {
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--text-secondary);
        margin: 0;
    }

    .preview-content {
        flex: 1;
        padding: 12px;
        overflow-y: auto;
        background: #1a1a1a;
        font-size: 13px;
        line-height: 1.5;
        white-space: pre-wrap;
        color: #e0e0e0;
    }

    .preview-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--text-muted);
        text-align: center;
    }

    .preview-placeholder i {
        width: 32px;
        height: 32px;
        margin-bottom: 8px;
        opacity: 0.5;
    }

    .preview-sample {
        padding: 8px 12px;
        background: rgba(0,0,0,0.2);
        border-top: 1px solid var(--border-light);
    }

    .preview-sample small {
        color: var(--text-muted);
    }

    /* Templates Library */
    .templates-library {
        background: var(--bg-input);
        border-radius: var(--radius);
        overflow: hidden;
    }

    .library-header {
        padding: 12px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        color: var(--text-secondary);
    }

    .library-header:hover {
        background: rgba(255,255,255,0.05);
    }

    .library-body {
        padding: 16px;
        border-top: 1px solid var(--border-light);
    }

    .templates-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 12px;
    }

    .template-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .template-card:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    .template-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
    }

    .template-card-title {
        font-weight: 600;
        font-size: 13px;
        color: var(--text-primary);
    }

    .template-card-category {
        font-size: 10px;
        padding: 2px 6px;
        background: var(--primary-light);
        color: var(--primary);
        border-radius: 4px;
    }

    .template-card-preview {
        font-size: 11px;
        color: var(--text-muted);
        line-height: 1.4;
        max-height: 60px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .template-card-actions {
        display: flex;
        gap: 8px;
        margin-top: 8px;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .wa-grid { grid-template-columns: 1fr; }
        .side-col { order: -1; }
        .template-builder-container {
            grid-template-columns: 1fr;
        }
        .variables-panel {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            max-height: none;
        }
        .variables-panel h4 { width: 100%; }
        .variables-list { flex-direction: row; flex-wrap: wrap; }
        .preview-panel { min-height: 200px; }
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/whatsapp-page.js') }}?v=4.1"></script>
@endpush
@endsection
