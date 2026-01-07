@extends('layouts.app')

@section('title', 'Check-in - ' . $event->name)

@section('content')
    <div class="checkin-container">
        <!-- Left Panel: Scanner -->
        <div class="scanner-panel">
            <div class="scanner-header">
                <h2>📱 Scan QR Code</h2>
                <p>{{ $event->name }}</p>
            </div>

            <div class="scanner-wrapper">
                <div id="qr-reader" class="qr-reader"></div>
                <div id="scan-status" class="scan-status">
                    <i class="lucide-scan"></i>
                    <span>Arahkan kamera ke QR Code</span>
                </div>
            </div>

            <!-- Manual Input -->
            <div class="manual-input">
                <div class="divider">
                    <span>atau masukkan nomor tiket</span>
                </div>
                <form id="manual-checkin-form" class="manual-form">
                    <input type="text" id="ticket-input" class="form-input" placeholder="Masukkan nomor tiket..." autocomplete="off">
                    <button type="submit" class="btn btn-primary">
                        <i class="lucide-check"></i>
                        Check-in
                    </button>
                </form>
            </div>

            <!-- Result Display -->
            <div id="checkin-result" class="checkin-result hidden">
                <div class="result-icon"></div>
                <div class="result-name"></div>
                <div class="result-message"></div>
            </div>
        </div>

        <!-- Right Panel: Stats & Recent -->
        <div class="stats-panel">
            <!-- Live Stats -->
            <div class="card live-stats-card">
                <div class="card-header">
                    <h3 class="card-title">📊 Statistik Kehadiran</h3>
                    <span class="live-indicator">
                        <span class="pulse"></span>
                        LIVE
                    </span>
                </div>
                <div class="card-body">
                    <div class="stats-row">
                        <div class="stat-box">
                            <div class="stat-value" id="stat-total">{{ $stats['total_confirmed'] }}</div>
                            <div class="stat-label">Terdaftar</div>
                        </div>
                        <div class="stat-box highlight">
                            <div class="stat-value" id="stat-checkedin">{{ $stats['total_checked_in'] }}</div>
                            <div class="stat-label">Hadir</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value" id="stat-remaining">{{ $stats['total_not_arrived'] }}</div>
                            <div class="stat-label">Belum</div>
                        </div>
                    </div>

                    <div class="attendance-progress">
                        <div class="progress-header">
                            <span>Tingkat Kehadiran</span>
                            <span id="attendance-rate">{{ $stats['attendance_rate'] }}%</span>
                        </div>
                        <div class="progress" style="height: 12px;">
                            <div class="progress-bar primary" id="attendance-bar" style="width: {{ $stats['attendance_rate'] }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Check-ins -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">🕐 Check-in Terbaru</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div id="recent-checkins" class="recent-list">
                        @forelse($recentCheckins as $log)
                            <div class="recent-item">
                                <div class="recent-avatar">{{ substr($log['nama'], 0, 1) }}</div>
                                <div class="recent-info">
                                    <div class="recent-name">{{ $log['nama'] }}</div>
                                    <div class="recent-time">{{ $log['time'] }}</div>
                                </div>
                                <div class="recent-badge">
                                    <i class="lucide-check-circle"></i>
                                </div>
                            </div>
                        @empty
                            <div class="empty-recent">
                                Belum ada check-in
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('events.registrations', $event) }}" class="btn btn-secondary btn-block">
                        <i class="lucide-users"></i>
                        Lihat Daftar Peserta
                    </a>
                    <a href="{{ route('events.show', $event) }}" class="btn btn-secondary btn-block" style="margin-top: 12px;">
                        <i class="lucide-arrow-left"></i>
                        Kembali ke Event
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Sound -->
    <audio id="success-sound" preload="auto">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj6a2teleEI7ldXvwYRWOJHR9MqMYT2N0vXLjWNAi9D1yo1jQ4rP9MqNZEWIzvPKjmVHhszyyo9mSYTL8MqQaEuBye/JkWlOfsftx5JrUXrF68aSbFR3wujEk25WeL3lwJRwWHS84r2VcVlxuN+6l3NbbbTbt5h1XWmw17SaeF9lrNOxnHphYafPrp57Y16ixqqgfWVamb2mn4BoVpS3op+CaVKPsZ6fg2xPiqubn4VuTISomo6GcEt/pJeLh3JIeZ6Ti4h0RXSYkImJdUN uKY6JinZBayCLh4t4P2UbhoSMej1gFoGBjHw8WhB9fo5/OlYNenmQgTlSCndzkII4Twh0b5KDOE0Fc2yShjdLAHFqkYg3SwBwapGJN0sAcGqRiTdLAG9qkYk3SwBvapGJN0sAb2qRiTdLAG9qkYo3SwBvapGKN0sAb2qRijdL" type="audio/wav">
    </audio>

    <!-- Error Sound -->
    <audio id="error-sound" preload="auto">
        <source src="data:audio/wav;base64,UklGRigCAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQQCAACAgICAgICAgICAgICAgICAgICAgICAgHx4eHh4eHx8fHx4eHh8gIB8eHh4eHx8fHx4eHh8gIB8eHh4eHx8fHx4eHh8gIB8eHh4eHyAgHx4eHh4fICAeHh4eHh8gICAgHh4eHyAgICAeHh4fICAgIB4eHh8gICAgHh4eHx8fHyAeHh4fHx8fIB4eHh8fHx8gHh4eHx8fHyAeHh4fHx8fIB4eHh8fHx8gHh4eHx4eHyAeHh4fHh4fIB4eHh8eHh8gHh4eHx4eHyAeHh4fHh4fICAgHx4eHh8gIB8eHh4fICAfHh4eHyAgHx4eHh8gIB8eHh4" type="audio/wav">
    </audio>
@endsection

@push('styles')
<style>
    .checkin-container {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 24px;
        min-height: calc(100vh - 48px);
    }

    @media (max-width: 1024px) {
        .checkin-container {
            grid-template-columns: 1fr;
        }
    }

    .scanner-panel {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 32px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .scanner-header {
        text-align: center;
        margin-bottom: 24px;
    }

    .scanner-header h2 {
        font-size: 24px;
        margin-bottom: 8px;
    }

    .scanner-header p {
        color: var(--text-secondary);
    }

    .scanner-wrapper {
        position: relative;
        width: 100%;
        max-width: 400px;
        aspect-ratio: 1;
        background: var(--bg-secondary);
        border-radius: var(--radius-lg);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .qr-reader {
        width: 100%;
        height: 100%;
    }

    .qr-reader video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .scan-status {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.7);
        padding: 12px 24px;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: white;
    }

    .manual-input {
        width: 100%;
        max-width: 400px;
    }

    .divider {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 16px;
        color: var(--text-muted);
        font-size: 14px;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border-color);
    }

    .manual-form {
        display: flex;
        gap: 12px;
    }

    .manual-form .form-input {
        flex: 1;
        padding: 14px 20px;
        font-size: 16px;
    }

    .checkin-result {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: var(--bg-secondary);
        border-radius: var(--radius-xl);
        padding: 48px 64px;
        text-align: center;
        z-index: 1000;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        animation: popIn 0.3s ease;
    }

    .checkin-result.hidden {
        display: none;
    }

    .checkin-result.success .result-icon {
        width: 80px;
        height: 80px;
        background: rgba(16, 185, 129, 0.15);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        font-size: 40px;
        color: var(--success);
    }

    .checkin-result.success .result-icon::before {
        content: '✓';
    }

    .checkin-result.error .result-icon {
        width: 80px;
        height: 80px;
        background: rgba(239, 68, 68, 0.15);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        font-size: 40px;
        color: var(--danger);
    }

    .checkin-result.error .result-icon::before {
        content: '✗';
    }

    .result-name {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .result-message {
        color: var(--text-secondary);
        font-size: 16px;
    }

    @keyframes popIn {
        0% { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
        100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
    }

    /* Stats Panel */
    .stats-panel .card {
        margin-bottom: 24px;
    }

    .live-indicator {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 600;
        color: var(--success);
    }

    .pulse {
        width: 8px;
        height: 8px;
        background: var(--success);
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-box {
        text-align: center;
        padding: 20px;
        background: var(--bg-tertiary);
        border-radius: var(--radius);
    }

    .stat-box.highlight {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(16, 185, 129, 0.1) 100%);
    }

    .stat-box .stat-value {
        font-size: 32px;
        font-weight: 800;
    }

    .stat-box.highlight .stat-value {
        color: var(--success);
    }

    .attendance-progress .progress-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 14px;
    }

    /* Recent List */
    .recent-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .recent-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        border-bottom: 1px solid var(--border-color);
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .recent-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: white;
    }

    .recent-info {
        flex: 1;
    }

    .recent-name {
        font-weight: 600;
        font-size: 14px;
    }

    .recent-time {
        font-size: 12px;
        color: var(--text-muted);
    }

    .recent-badge {
        color: var(--success);
    }

    .empty-recent {
        padding: 40px 20px;
        text-align: center;
        color: var(--text-muted);
    }

    .btn-block {
        width: 100%;
    }

    /* Overlay for result */
    .result-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }

    .form-input {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 12px 16px;
        color: var(--text-primary);
        font-size: 14px;
        outline: none;
    }

    .form-input:focus {
        border-color: var(--primary);
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    const eventId = {{ $event->id }};
    let html5QrCode;
    let isProcessing = false;

    // Initialize QR Scanner
    document.addEventListener('DOMContentLoaded', function() {
        html5QrCode = new Html5Qrcode("qr-reader");
        
        Html5Qrcode.getCameras().then(devices => {
            if (devices && devices.length) {
                const cameraId = devices[devices.length - 1].id; // Use last camera (usually back camera)
                
                html5QrCode.start(
                    cameraId,
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                    },
                    onScanSuccess,
                    onScanFailure
                );
            }
        }).catch(err => {
            console.error('Error getting cameras:', err);
            document.getElementById('scan-status').innerHTML = '<i class="lucide-camera-off"></i><span>Kamera tidak tersedia</span>';
        });

        // Manual input form
        document.getElementById('manual-checkin-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const ticketNumber = document.getElementById('ticket-input').value.trim();
            if (ticketNumber) {
                processCheckin(ticketNumber);
            }
        });

        // Auto-refresh stats
        setInterval(refreshStats, 10000);
    });

    function onScanSuccess(decodedText, decodedResult) {
        if (isProcessing) return;
        
        // Extract ticket number from QR data
        let ticketNumber = decodedText;
        try {
            const qrData = JSON.parse(decodedText);
            ticketNumber = qrData.ticket || qrData.t || decodedText;
        } catch (e) {
            // Not JSON, use as-is
        }

        processCheckin(ticketNumber);
    }

    function onScanFailure(error) {
        // Ignore scan failures
    }

    async function processCheckin(ticketNumber) {
        if (isProcessing) return;
        isProcessing = true;

        document.getElementById('scan-status').innerHTML = '<i class="lucide-loader-2" style="animation: spin 1s linear infinite;"></i><span>Memproses...</span>';

        try {
            const response = await fetch('/checkin/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ ticket_number: ticketNumber }),
            });

            const data = await response.json();

            if (data.success) {
                showResult('success', data.data.nama, 'Check-in berhasil pada ' + data.data.checked_in_at);
                document.getElementById('success-sound').play();
                addRecentCheckin(data.data.nama, data.data.checked_in_at);
                refreshStats();
            } else {
                showResult('error', 'Gagal', data.message);
                document.getElementById('error-sound').play();
            }
        } catch (error) {
            showResult('error', 'Error', 'Terjadi kesalahan koneksi');
            console.error(error);
        }

        document.getElementById('ticket-input').value = '';
        
        setTimeout(() => {
            isProcessing = false;
            document.getElementById('scan-status').innerHTML = '<i class="lucide-scan"></i><span>Arahkan kamera ke QR Code</span>';
        }, 1000);
    }

    function showResult(type, name, message) {
        const result = document.getElementById('checkin-result');
        result.className = 'checkin-result ' + type;
        result.querySelector('.result-name').textContent = name;
        result.querySelector('.result-message').textContent = message;

        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'result-overlay';
        overlay.onclick = hideResult;
        document.body.appendChild(overlay);

        // Auto hide after 3 seconds
        setTimeout(hideResult, 3000);
    }

    function hideResult() {
        document.getElementById('checkin-result').classList.add('hidden');
        const overlay = document.querySelector('.result-overlay');
        if (overlay) overlay.remove();
    }

    function addRecentCheckin(nama, time) {
        const list = document.getElementById('recent-checkins');
        const item = document.createElement('div');
        item.className = 'recent-item';
        item.innerHTML = `
            <div class="recent-avatar">${nama.charAt(0)}</div>
            <div class="recent-info">
                <div class="recent-name">${nama}</div>
                <div class="recent-time">${time}</div>
            </div>
            <div class="recent-badge">
                <i class="lucide-check-circle"></i>
            </div>
        `;
        
        const empty = list.querySelector('.empty-recent');
        if (empty) empty.remove();
        
        list.insertBefore(item, list.firstChild);
        
        // Keep only 10 items
        while (list.children.length > 10) {
            list.removeChild(list.lastChild);
        }
    }

    async function refreshStats() {
        try {
            const response = await fetch(`/checkin/${eventId}/stats`);
            const data = await response.json();

            document.getElementById('stat-total').textContent = data.stats.total_confirmed;
            document.getElementById('stat-checkedin').textContent = data.stats.total_checked_in;
            document.getElementById('stat-remaining').textContent = data.stats.total_not_arrived;
            document.getElementById('attendance-rate').textContent = data.stats.attendance_rate + '%';
            document.getElementById('attendance-bar').style.width = data.stats.attendance_rate + '%';
        } catch (error) {
            console.error('Failed to refresh stats:', error);
        }
    }

    // Add spin animation
    const style = document.createElement('style');
    style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
    document.head.appendChild(style);
</script>
@endpush
