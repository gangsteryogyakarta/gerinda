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
                         <div class="form-group">
                            <label class="form-label">Lampirkan Gambar (Optional)</label>
                            <input type="text" id="single-image-url" class="form-input" placeholder="https://example.com/image.jpg">
                            <div class="form-hint">Masukkan URL gambar yang valid</div>
                        </div>

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
                        
                         <div class="form-group">
                            <label class="form-label">Lampirkan Gambar URL (Optional)</label>
                            <input type="text" id="bulk-image-url" class="form-input" placeholder="https://...">
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

            <!-- Templates Card -->
            <div class="card mt-4">
                 <div class="card-header">
                    <h3 class="card-title">
                        <div class="card-title-icon"><i data-lucide="layout-template"></i></div>
                        Template Cepat
                    </h3>
                </div>
                <div class="card-body p-2">
                    <div class="template-list">
                        <button class="template-item" onclick="useTemplate('event-reminder')">
                            <div class="tpl-icon"><i data-lucide="calendar"></i></div>
                            <div class="tpl-info">
                                <span>Reminder Event</span>
                                <small>H-1 Acara</small>
                            </div>
                        </button>
                        <button class="template-item" onclick="useTemplate('ticket-confirm')">
                            <div class="tpl-icon"><i data-lucide="ticket"></i></div>
                            <div class="tpl-info">
                                <span>Konfirmasi Tiket</span>
                                <small>Info Registrasi</small>
                            </div>
                        </button>
                        <button class="template-item" onclick="useTemplate('thank-you')">
                            <div class="tpl-icon"><i data-lucide="heart"></i></div>
                             <div class="tpl-info">
                                <span>Ucapan Terima Kasih</span>
                                <small>Pasca Event</small>
                            </div>
                        </button>
                         <button class="template-item" onclick="useTemplate('general-blast')">
                            <div class="tpl-icon"><i data-lucide="megaphone"></i></div>
                             <div class="tpl-info">
                                <span>Info Umum</span>
                                <small>Broadcast Anggota</small>
                            </div>
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

    /* Responsive */
    @media (max-width: 1024px) {
        .wa-grid { grid-template-columns: 1fr; }
        .side-col { order: -1; } /* On mobile, status on top? Or keep bottom? Let's keep bottom or side */
    }
</style>
@endpush

@push('scripts')
<script>
    // Templates
    const textTemplates = {
        'event-reminder': `Halo Kader Gerindra! 🇮🇩\n\nJangan lupa hadir di acara:\n📅 {nama_event}\n📍 {lokasi}\n\nPastikan membawa KTP dan tiket.\n\nSalam Perjuangan! ✊`,
        'ticket-confirm': `Selamat! 🎉\nRegistrasi Anda terkonfirmasi:\n📌 Event: {nama_event}\n🎫 Tiket: {no_tiket}\n\nTunjukkan pesan ini saat registrasi.\nDPD Gerindra DIY`,
        'thank-you': `Terima kasih atas kehadiran Anda di {nama_event}! 🙏\nKawal terus perjuangan Partai Gerindra.\n\nSalam Indonesia Raya!`,
        'general-blast': `Salam Perjuangan! 🇮🇩\n\nKepada Yth. Kader Gerindra,\n\n{isi_pesan}\n\nTerima kasih.\nDPD Gerindra DIY`
    };

    let qrInterval;
    let statusInterval;

    document.addEventListener('DOMContentLoaded', () => {
        setupTabs();
        setupCharCounters();
        setupAdvancedFilter();
        loadResources();
        
        // Initial check
        checkHealth();
        // Poll every 5s
        statusInterval = setInterval(checkHealth, 5000);
        
        setupForms();
    });

    function setupTabs() {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active classes
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active
                btn.classList.add('active');
                document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
            });
        });
    }

    function setupCharCounters() {
        ['single', 'bulk', 'event'].forEach(id => {
            const el = document.getElementById(id + '-message');
            const cnt = document.getElementById(id + '-char-count');
            if(el && cnt) {
                el.addEventListener('input', () => cnt.innerText = el.value.length);
            }
        });
    }

    function setupAdvancedFilter() {
        // Toggle logic if needed, already inline in HTML via onclick
        document.getElementById('bulk-filter').addEventListener('change', function() {
            const isProvince = this.value === 'province';
            document.getElementById('province-select-container').style.display = isProvince ? 'block' : 'none';
        });
    }
    
    function toggleAdvanced() {
        const body = document.getElementById('advanced-body');
        const icon = document.getElementById('advanced-icon');
        if (body.style.display === 'none') {
            body.style.display = 'block';
            icon.style.transform = 'rotate(180deg)';
        } else {
            body.style.display = 'none';
            icon.style.transform = 'rotate(0deg)';
        }
    }

    function loadResources() {
        // Load Provinces
        fetch('/api/v1/locations/provinces')
            .then(r => r.json())
            .then(data => {
                const select = document.getElementById('bulk-province');
                (data.data || data).forEach(p => select.innerHTML += `<option value="${p.id}">${p.name}</option>`);
            });

        // Load Events
        fetch('/api/v1/events')
            .then(r => r.json())
            .then(data => {
                const select = document.getElementById('event-select');
                (data.data || data).forEach(e => select.innerHTML += `<option value="${e.id}">${e.name}</option>`);
            });
            
        // Load Regencies for DIY (ID 34)
         fetch('/api/v1/locations/regencies?province_id=34')
            .then(r => r.json())
            .then(data => {
                const select = document.getElementById('bulk-regency');
                (data.data || data).forEach(r => select.innerHTML += `<option value="${r.id}">${r.name}</option>`);
            });
    }

    function useTemplate(key) {
        const tmpl = textTemplates[key];
        // Find visible textarea
        const activeTab = document.querySelector('.tab-content.active');
        const textarea = activeTab.querySelector('textarea');
        if(textarea && tmpl) {
            textarea.value = tmpl;
            textarea.dispatchEvent(new Event('input')); // Update counter
        }
    }

    // --- WhatsApp Logic ---
    function checkHealth() {
        fetch('/whatsapp/health')
            .then(r => r.json())
            .then(updateStatus)
            .catch(() => updateStatus({ connected: false, status: 'offline' }));
    }

    function updateStatus(data) {
        const loading = document.getElementById('session-loading');
        const start = document.getElementById('start-container');
        const qr = document.getElementById('qr-container');
        const connected = document.getElementById('connected-container');
        
        const pillText = document.getElementById('connection-text');
        const pill = document.getElementById('connection-status-pill');

        // Hide all
        loading.style.display = 'none';
        start.style.display = 'none';
        qr.style.display = 'none';
        connected.style.display = 'none';

        const status = data.status || {};
        const state = status.status || 'disconnected';
        const isConnected = status.connected || false;

        if (isConnected) {
            connected.style.display = 'flex';
            document.getElementById('connected-detail').innerText = 'Terhubung: ' + (status.user?.id?.split(':')[0] || 'Unknown');
            document.getElementById('connected-phone-stat').innerText = status.user?.id?.split(':')[0] || 'Online';
            
            pill.className = 'connection-pill connected';
            pillText.innerText = 'Terhubung';
            
            if (qrInterval) clearInterval(qrInterval);
        } else if (state === 'qr' || state === 'scan_qr_code') {
            qr.style.display = 'flex';
            pill.className = 'connection-pill disconnected';
            pillText.innerText = 'Scan QR';
            
            // Auto refresh logic handled by interval or manual button
             refreshQR();
        } else {
            start.style.display = 'flex';
            pill.className = 'connection-pill disconnected';
            pillText.innerText = 'Terputus';
        }
    }

    function startSession() {
        const btn = document.getElementById('btn-start-session');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Memulai...';

        fetch('/whatsapp/session/start', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.json())
        .then(() => {
            checkHealth();
            // Start QR polling
             if (!qrInterval) qrInterval = setInterval(refreshQR, 4000);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = original;
        });
    }

    function refreshQR() {
        fetch('/whatsapp/qr')
            .then(r => r.json())
            .then(data => {
                if(data.success && data.qr) {
                     document.getElementById('qr-image').src = data.qr;
                }
            });
    }
    
    function logout() {
        if(!confirm('Putuskan koneksi WhatsApp?')) return;
        fetch('/whatsapp/logout', {
             method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(checkHealth);
    }
    
    function setupForms() {
        // Generic form submit handler
        const handle = (formId, url, btnId, payloadFn) => {
            const form = document.getElementById(formId);
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                if(!confirm('Kirim pesan ini?')) return;
                
                const btn = document.getElementById(btnId);
                const original = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = 'Mengirim...';
                
                const payload = payloadFn();
                
                // Add Image URL if exists in form
                const imgInput = form.querySelector('input[placeholder*="image"]');
                if(imgInput && imgInput.value) {
                     // Check if backend supports mixed text+image or separate endpoints.
                     // For now, assuming standard text endpoint might not handle it, 
                     // but we updated the service to handle context. 
                     // Ideally we should use separate endpoint or update the controller to handle optional image_url
                     // Let's pass it in payload
                     payload.image_url = imgInput.value;
                }

                // Determine URL based on payload or default
                let targetUrl = url;
                if(formId === 'event-message-form') {
                    // For event, we use a specific route format: /whatsapp/event/{id}/notify
                    targetUrl = `/whatsapp/event/${payload.event_id || document.getElementById('event-select').value}/notify`;
                }

                fetch(targetUrl, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json())
                .then(d => {
                    if(d.success) {
                        alert('Berhasil: ' + (d.message || 'Pesan dikirim/antre.'));
                        form.reset();
                    } else {
                        alert('Gagal: ' + (d.error || d.message));
                    }
                })
                .catch(e => alert('Error: ' + e))
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = original;
                });
            });
        };

        handle('single-message-form', '/whatsapp/send', 'btn-send-single', () => ({
            phone: document.getElementById('single-phone').value,
            message: document.getElementById('single-message').value
        }));

        handle('bulk-message-form', '/whatsapp/blast', 'btn-send-bulk', () => ({
            filter: document.getElementById('bulk-filter').value,
            province_id: document.getElementById('bulk-province').value,
             regency_id: document.getElementById('bulk-regency').value,
             gender: document.getElementById('bulk-gender').value,
             age_min: document.getElementById('bulk-age-min').value,
            age_max: document.getElementById('bulk-age-max').value,
            limit: document.getElementById('bulk-limit').value,
            message: document.getElementById('bulk-message').value
        }));
        
        handle('event-message-form', '', 'btn-send-event', () => ({
             status: document.getElementById('event-status').value,
             message: document.getElementById('event-message').value
             // event_id injected in url construction
        }));
    }
</script>
@endpush
@endsection
