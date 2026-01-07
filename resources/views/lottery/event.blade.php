@extends('layouts.app')

@section('title', 'Undian Hadiah - ' . $event->name)

@section('content')
    <div class="lottery-container">
        <!-- Main Lottery Area -->
        <div class="lottery-main">
            <!-- Header -->
            <div class="lottery-header">
                <div>
                    <h1>🎁 Undian Hadiah</h1>
                    <p>{{ $event->name }}</p>
                </div>
                <a href="{{ route('lottery.prizes', $event) }}" class="btn btn-secondary">
                    <i class="lucide-settings"></i>
                    Kelola Hadiah
                </a>
            </div>

            <!-- Shuffle Display -->
            <div class="shuffle-container">
                <div class="shuffle-box" id="shuffle-display">
                    <span class="shuffle-text">Tekan Tombol Untuk Mulai</span>
                </div>
                
                <div class="shuffle-controls">
                    <select id="prize-select" class="prize-select">
                        <option value="">-- Pilih Hadiah --</option>
                        @foreach($prizes as $prize)
                            @if($prize->remaining_quantity > 0)
                                <option value="{{ $prize->id }}" data-remaining="{{ $prize->remaining_quantity }}">
                                    {{ $prize->name }} ({{ $prize->remaining_quantity }} tersisa)
                                </option>
                            @endif
                        @endforeach
                    </select>
                    
                    <button id="draw-btn" class="btn-draw" disabled>
                        <span class="btn-draw-icon">🎰</span>
                        <span class="btn-draw-text">MULAI UNDIAN</span>
                    </button>
                </div>

                <div class="eligible-info">
                    <i class="lucide-users"></i>
                    <span><strong>{{ $eligibleCount }}</strong> peserta memenuhi syarat undian</span>
                </div>
            </div>

            <!-- Winner Display (Hidden by default) -->
            <div id="winner-display" class="winner-display hidden">
                <div class="winner-confetti"></div>
                <div class="winner-content">
                    <div class="winner-badge">🏆 SELAMAT!</div>
                    <div class="winner-name" id="winner-name"></div>
                    <div class="winner-ticket" id="winner-ticket"></div>
                    <div class="winner-prize" id="winner-prize"></div>
                    <button onclick="hideWinner()" class="btn btn-primary" style="margin-top: 24px;">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lottery-sidebar">
            <!-- Prize List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">🎁 Daftar Hadiah</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    @forelse($prizes as $prize)
                        <div class="prize-item {{ $prize->remaining_quantity > 0 ? '' : 'depleted' }}">
                            <div class="prize-info">
                                <div class="prize-name">{{ $prize->name }}</div>
                                <div class="prize-stock">
                                    {{ $prize->quantity - $prize->remaining_quantity }}/{{ $prize->quantity }} diundi
                                </div>
                            </div>
                            <div class="prize-badge {{ $prize->remaining_quantity > 0 ? 'available' : 'empty' }}">
                                {{ $prize->remaining_quantity }}
                            </div>
                        </div>
                    @empty
                        <div class="empty-prizes">
                            <p>Belum ada hadiah</p>
                            <a href="{{ route('lottery.prizes', $event) }}" class="btn btn-primary btn-sm">
                                Tambah Hadiah
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Winners -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">🏆 Pemenang Terbaru</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div id="winners-list" class="winners-list">
                        @forelse($history as $draw)
                            <div class="winner-item">
                                <div class="winner-item-avatar">{{ substr($draw->massa->nama_lengkap, 0, 1) }}</div>
                                <div class="winner-item-info">
                                    <div class="winner-item-name">{{ $draw->massa->nama_lengkap }}</div>
                                    <div class="winner-item-prize">{{ $draw->prize->name }}</div>
                                </div>
                                <div class="winner-item-time">{{ $draw->created_at->format('H:i') }}</div>
                            </div>
                        @empty
                            <div class="empty-winners" id="empty-winners">
                                Belum ada pemenang
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="card">
                <div class="card-body">
                    <div class="lottery-stats">
                        <div class="lottery-stat">
                            <div class="lottery-stat-value">{{ $stats['total_prizes'] }}</div>
                            <div class="lottery-stat-label">Total Hadiah</div>
                        </div>
                        <div class="lottery-stat">
                            <div class="lottery-stat-value">{{ $stats['total_drawn'] }}</div>
                            <div class="lottery-stat-label">Sudah Diundi</div>
                        </div>
                        <div class="lottery-stat">
                            <div class="lottery-stat-value">{{ $stats['remaining'] }}</div>
                            <div class="lottery-stat-label">Tersisa</div>
                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('events.show', $event) }}" class="btn btn-secondary btn-block">
                <i class="lucide-arrow-left"></i>
                Kembali ke Event
            </a>
        </div>
    </div>

    <!-- Confetti Canvas -->
    <canvas id="confetti-canvas"></canvas>
@endsection

@push('styles')
<style>
    .lottery-container {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 24px;
        min-height: calc(100vh - 48px);
    }

    @media (max-width: 1024px) {
        .lottery-container {
            grid-template-columns: 1fr;
        }
    }

    .lottery-main {
        display: flex;
        flex-direction: column;
    }

    .lottery-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .lottery-header h1 {
        font-size: 28px;
        margin-bottom: 4px;
    }

    .lottery-header p {
        color: var(--text-secondary);
    }

    .shuffle-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-xl);
        padding: 48px;
    }

    .shuffle-box {
        width: 100%;
        max-width: 600px;
        aspect-ratio: 2/1;
        background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 32px;
        overflow: hidden;
        position: relative;
    }

    .shuffle-box::before {
        content: '';
        position: absolute;
        inset: 0;
        border: 3px solid transparent;
        border-radius: var(--radius-lg);
        background: linear-gradient(135deg, var(--primary), var(--accent)) border-box;
        -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
        mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
    }

    .shuffle-text {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-muted);
        text-align: center;
        padding: 20px;
    }

    .shuffle-box.shuffling .shuffle-text {
        color: var(--text-primary);
        animation: shuffle 0.1s linear infinite;
    }

    @keyframes shuffle {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    .shuffle-controls {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
    }

    .prize-select {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 16px 24px;
        color: var(--text-primary);
        font-size: 16px;
        min-width: 250px;
        cursor: pointer;
    }

    .btn-draw {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 48px;
        font-size: 18px;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        border: none;
        border-radius: var(--radius);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-draw:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-draw:not(:disabled):hover {
        transform: scale(1.05);
        box-shadow: 0 10px 30px -10px rgba(220, 38, 38, 0.5);
    }

    .btn-draw-icon {
        font-size: 24px;
    }

    .eligible-info {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-secondary);
        font-size: 14px;
    }

    /* Winner Display */
    .winner-display {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .winner-display.hidden {
        display: none;
    }

    .winner-content {
        text-align: center;
        z-index: 10;
    }

    .winner-badge {
        display: inline-block;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #000;
        font-size: 24px;
        font-weight: 800;
        padding: 12px 32px;
        border-radius: 9999px;
        margin-bottom: 24px;
        animation: bounce 0.5s ease;
    }

    @keyframes bounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .winner-name {
        font-size: 64px;
        font-weight: 800;
        color: white;
        margin-bottom: 16px;
        text-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
    }

    .winner-ticket {
        font-size: 20px;
        color: var(--text-secondary);
        margin-bottom: 8px;
    }

    .winner-prize {
        font-size: 24px;
        color: var(--accent);
        font-weight: 600;
    }

    /* Sidebar */
    .lottery-sidebar .card {
        margin-bottom: 24px;
    }

    .prize-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
    }

    .prize-item.depleted {
        opacity: 0.5;
    }

    .prize-name {
        font-weight: 600;
    }

    .prize-stock {
        font-size: 12px;
        color: var(--text-muted);
    }

    .prize-badge {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }

    .prize-badge.available {
        background: rgba(16, 185, 129, 0.15);
        color: var(--success);
    }

    .prize-badge.empty {
        background: var(--bg-tertiary);
        color: var(--text-muted);
    }

    .empty-prizes {
        padding: 40px 20px;
        text-align: center;
        color: var(--text-muted);
    }

    .winners-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .winner-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        border-bottom: 1px solid var(--border-color);
    }

    .winner-item-avatar {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, var(--accent) 0%, #d97706 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: #000;
    }

    .winner-item-info {
        flex: 1;
    }

    .winner-item-name {
        font-weight: 600;
        font-size: 14px;
    }

    .winner-item-prize {
        font-size: 12px;
        color: var(--text-muted);
    }

    .winner-item-time {
        font-size: 12px;
        color: var(--text-muted);
    }

    .empty-winners {
        padding: 40px 20px;
        text-align: center;
        color: var(--text-muted);
    }

    .lottery-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    .lottery-stat {
        text-align: center;
    }

    .lottery-stat-value {
        font-size: 28px;
        font-weight: 800;
        color: var(--primary);
    }

    .lottery-stat-label {
        font-size: 12px;
        color: var(--text-muted);
    }

    .btn-block {
        width: 100%;
    }

    /* Confetti */
    #confetti-canvas {
        position: fixed;
        inset: 0;
        pointer-events: none;
        z-index: 1001;
    }
</style>
@endpush

@push('scripts')
<script>
    const eventId = {{ $event->id }};
    let shuffleNames = [];
    let isDrawing = false;
    let shuffleInterval;

    document.addEventListener('DOMContentLoaded', function() {
        // Load shuffle names
        loadShuffleNames();

        // Prize select handler
        document.getElementById('prize-select').addEventListener('change', function() {
            document.getElementById('draw-btn').disabled = !this.value;
        });

        // Draw button handler
        document.getElementById('draw-btn').addEventListener('click', startDraw);
    });

    async function loadShuffleNames() {
        try {
            const response = await fetch(`/lottery/${eventId}/shuffle`);
            const data = await response.json();
            shuffleNames = data.names;
        } catch (error) {
            console.error('Failed to load names:', error);
        }
    }

    function startDraw() {
        if (isDrawing) return;
        
        const prizeId = document.getElementById('prize-select').value;
        if (!prizeId) return;

        isDrawing = true;
        document.getElementById('draw-btn').disabled = true;

        // Start shuffle animation
        const shuffleBox = document.getElementById('shuffle-display');
        shuffleBox.classList.add('shuffling');
        
        let counter = 0;
        shuffleInterval = setInterval(() => {
            const randomName = shuffleNames[Math.floor(Math.random() * shuffleNames.length)] || 'Loading...';
            document.querySelector('.shuffle-text').textContent = randomName;
            counter++;
        }, 80);

        // Draw winner after animation
        setTimeout(async () => {
            clearInterval(shuffleInterval);
            
            try {
                const response = await fetch(`/lottery/${eventId}/draw`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ prize_id: prizeId }),
                });

                const data = await response.json();

                if (data.success) {
                    showWinner(data.winner, data.prize);
                    updatePrizeList(prizeId, data.prize.remaining);
                    addWinnerToList(data.winner, data.prize);
                } else {
                    alert(data.message);
                    shuffleBox.classList.remove('shuffling');
                    document.querySelector('.shuffle-text').textContent = 'Tekan Tombol Untuk Mulai';
                }
            } catch (error) {
                alert('Terjadi kesalahan');
                console.error(error);
            }

            isDrawing = false;
            document.getElementById('draw-btn').disabled = false;
        }, 3000);
    }

    function showWinner(winner, prize) {
        document.getElementById('winner-name').textContent = winner.nama;
        document.getElementById('winner-ticket').textContent = winner.ticket;
        document.getElementById('winner-prize').textContent = '🎁 ' + prize.name;
        
        document.getElementById('winner-display').classList.remove('hidden');
        
        // Trigger confetti
        launchConfetti();
        
        // Reset shuffle display
        document.getElementById('shuffle-display').classList.remove('shuffling');
        document.querySelector('.shuffle-text').textContent = winner.nama;
    }

    function hideWinner() {
        document.getElementById('winner-display').classList.add('hidden');
        document.querySelector('.shuffle-text').textContent = 'Tekan Tombol Untuk Mulai';
    }

    function updatePrizeList(prizeId, remaining) {
        const select = document.getElementById('prize-select');
        const option = select.querySelector(`option[value="${prizeId}"]`);
        
        if (remaining <= 0) {
            option.remove();
        } else {
            option.textContent = option.textContent.replace(/\(\d+ tersisa\)/, `(${remaining} tersisa)`);
        }
    }

    function addWinnerToList(winner, prize) {
        const list = document.getElementById('winners-list');
        const empty = document.getElementById('empty-winners');
        if (empty) empty.remove();

        const item = document.createElement('div');
        item.className = 'winner-item';
        item.innerHTML = `
            <div class="winner-item-avatar">${winner.nama.charAt(0)}</div>
            <div class="winner-item-info">
                <div class="winner-item-name">${winner.nama}</div>
                <div class="winner-item-prize">${prize.name}</div>
            </div>
            <div class="winner-item-time">Baru</div>
        `;
        
        list.insertBefore(item, list.firstChild);
    }

    // Simple confetti implementation
    function launchConfetti() {
        const canvas = document.getElementById('confetti-canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        
        const particles = [];
        const colors = ['#dc2626', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6'];
        
        for (let i = 0; i < 150; i++) {
            particles.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height - canvas.height,
                vx: (Math.random() - 0.5) * 10,
                vy: Math.random() * 3 + 2,
                color: colors[Math.floor(Math.random() * colors.length)],
                size: Math.random() * 10 + 5,
                rotation: Math.random() * Math.PI * 2,
                rotationSpeed: (Math.random() - 0.5) * 0.2,
            });
        }

        let frame = 0;
        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            particles.forEach(p => {
                p.x += p.vx;
                p.y += p.vy;
                p.vy += 0.1;
                p.rotation += p.rotationSpeed;
                
                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate(p.rotation);
                ctx.fillStyle = p.color;
                ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size / 2);
                ctx.restore();
            });

            frame++;
            if (frame < 200) {
                requestAnimationFrame(animate);
            } else {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        }

        animate();
    }
</script>
@endpush
