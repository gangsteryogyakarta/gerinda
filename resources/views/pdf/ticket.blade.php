<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket - {{ $registration->ticket_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            background: #f0f0f0;
            padding: 20px;
        }
        
        .ticket {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            max-width: 400px;
            margin: 0 auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .ticket-header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .ticket-header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .ticket-header .party-name {
            font-size: 12px;
            opacity: 0.9;
        }
        
        .event-name {
            background: #1f2937;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }
        
        .ticket-body {
            padding: 20px;
        }
        
        .qr-section {
            text-align: center;
            padding: 15px;
            border-bottom: 2px dashed #e5e7eb;
            margin-bottom: 15px;
        }
        
        .qr-code {
            width: 150px;
            height: 150px;
            margin: 0 auto 10px;
        }
        
        .qr-code img {
            width: 100%;
            height: 100%;
        }
        
        .ticket-number {
            font-family: monospace;
            font-size: 12px;
            color: #6b7280;
            letter-spacing: 1px;
        }
        
        .info-section {
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 11px;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #6b7280;
            font-weight: 500;
        }
        
        .info-value {
            color: #1f2937;
            font-weight: 600;
            text-align: right;
        }
        
        .attendee-name {
            text-align: center;
            padding: 15px;
            background: #fef2f2;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .attendee-name .label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .attendee-name .name {
            font-size: 16px;
            font-weight: bold;
            color: #dc2626;
        }
        
        .ticket-footer {
            background: #f9fafb;
            padding: 15px;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
        }
        
        .ticket-footer p {
            margin-bottom: 3px;
        }
        
        .important-note {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 6px;
            padding: 10px;
            margin-top: 10px;
            font-size: 10px;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="ticket-header">
            @if(isset($logoBase64) && $logoBase64)
                <img src="{{ $logoBase64 }}" alt="Gerindra Logo" style="height: 60px; margin-bottom: 10px;">
            @else
                <h1>🎫 TIKET KEHADIRAN</h1>
            @endif
            <div class="party-name">Partai Gerindra</div>
        </div>
        
        <div class="event-name">
            {{ $event->name }}
        </div>
        
        <div class="ticket-body">
            <div class="qr-section">
                <div class="qr-code">
                    @if(isset($qrCodeBase64) && $qrCodeBase64)
                        <img src="{{ $qrCodeBase64 }}" alt="QR Code">
                    @elseif(isset($qrCodeUrl))
                         <img src="{{ $qrCodeUrl }}" alt="QR Code">
                    @else
                        <div style="width:150px;height:150px;background:#e5e7eb;display:flex;align-items:center;justify-content:center;color:#6b7280;">
                            QR Code
                        </div>
                    @endif
                </div>
                <div class="ticket-number">{{ $registration->ticket_number }}</div>
            </div>
            
            <div class="attendee-name">
                <div class="label">Peserta</div>
                <div class="name">{{ $massa->nama_lengkap }}</div>
            </div>
            
            <div class="info-section">
                <div class="info-row">
                    <span class="info-label">📅 Tanggal</span>
                    <span class="info-value">{{ $event->event_start->format('d M Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">⏰ Waktu</span>
                    <span class="info-value">{{ $event->event_start->format('H:i') }} WIB</span>
                </div>
                <div class="info-row">
                    <span class="info-label">📍 Lokasi</span>
                    <span class="info-value">{{ $event->venue_name }}</span>
                </div>
            </div>
            
            <div class="important-note">
                ⚠️ <strong>Penting:</strong> Tunjukkan tiket ini (digital/cetak) beserta KTP saat check-in di lokasi acara.
            </div>
        </div>
        
        <div class="ticket-footer">
            <p>Tiket ini adalah bukti registrasi yang sah.</p>
            <p>Dicetak pada {{ now()->format('d M Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
