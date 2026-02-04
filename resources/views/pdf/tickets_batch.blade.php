<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tickets - {{ $event->name }}</title>
    <style>
        @page {
            margin: 0;
            size: A4 portrait;
        }
        
        body {
            font-family: 'Calibri', sans-serif;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #D80000;
        }

        .page-container {
            width: 200mm;
            height: 285mm;
            position: relative;
            margin: 6mm auto;
        }
        
        .page-break {
            page-break-after: always;
        }

        .ticket-wrapper {
            width: 100%;
            height: 57mm;
            position: relative;
            box-sizing: border-box;
        }

        /* Cutting guide - Red Dashed line */
        .cut-line {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            border-bottom: 2px dashed #D80000;
            opacity: 0.3;
            z-index: 10;
        }
        
        .ticket-wrapper:last-child .cut-line {
            display: none;
        }

        .ticket {
            width: 100%;
            height: 55mm;
            display: table;
            background: #fff;
            position: relative;
            overflow: hidden;
        }
        
        /* Main Section (Left - 72%) - PASTEL RED BACKGROUND */
        .main-section {
            display: table-cell;
            width: 72%;
            vertical-align: middle;
            background-color: #ef4444; /* Force Solid Red */
            color: white;
            position: relative;
            padding: 10px 15px;
            border-right: 4px dotted #f3f4f6;
        }
        
        /* Stub Section (Right - 28%) - LIGHT GRAY BACKGROUND */
        .stub-section {
            display: table-cell;
            width: 28%;
            vertical-align: middle;
            background-color: #f3f4f6 !important; /* Force Light Gray */
            padding: 10px;
            text-align: center;
            color: #D80000;
        }
        
        /* Large Semi-Circle Cutouts for separation */
        .tear-circle-top {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 20px;
            height: 20px;
            background-color: #fff;
            border-radius: 50%;
            z-index: 5;
        }
        
        .tear-circle-bottom {
            position: absolute;
            bottom: -10px;
            right: -10px;
            width: 20px;
            height: 20px;
            background-color: #fff;
            border-radius: 50%;
            z-index: 5;
        }

        /* LOGO STYLING - EXTRA LARGE */
        .header-logo {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .logo-box {
            background: white;
            padding: 8px;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .logo-img {
            height: 60px;
            width: auto;
            vertical-align: middle;
        }
        
        .party-title {
            font-size: 26px; /* Slightly larger */
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-left: 15px;
            line-height: 1;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            font-family: 'Calibri', 'Arial Black', sans-serif;
        }

        /* EVENT CONTENT */
        .event-title {
            font-size: 30px; /* Larger for better visibility */
            font-weight: 900;
            text-transform: uppercase;
            line-height: 0.9;
            margin-top: 5px;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            font-family: 'Calibri', 'Arial Black', sans-serif;
        }
        
        .event-info-grid {
            width: 100%;
            border-collapse: collapse;
        }
        
        .event-info-grid td {
            vertical-align: top;
            padding-right: 15px;
        }
        
        .info-label {
            font-size: 10px;
            text-transform: uppercase;
            opacity: 0.9;
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
            color: rgba(255,255,255,0.9);
        }
        
        .info-value {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.1;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        /* STUB CONTENT */
        .qr-box {
            border: 4px solid #D80000;
            padding: 5px;
            display: inline-block;
            margin-bottom: 5px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .qr-img {
            width: 85px;
            height: 85px;
            display: block;
        }
        
        .stub-label {
            font-size: 9px;
            font-weight: bold;
            color: #D80000;
            opacity: 0.8;
            margin-top: 5px;
        }
        
        .stub-value {
            font-size: 13px;
            font-weight: 800;
            color: #D80000;
            text-transform: uppercase;
            line-height: 1.1;
        }

        /* Watermark - ADJUSTED FOR BETTER VISIBILITY */
        .watermark {
            position: absolute;
            right: 10px;
            bottom: 5px;
            font-size: 110px;
            font-weight: 900;
            color: white;
            opacity: 0.15; /* Increased opacity */
            z-index: 0;
            pointer-events: none;
            line-height: 0.8;
            transform: rotate(-5deg);
            font-family: 'Arial Black', sans-serif; /* Keep heavy font for watermark */
        }
    </style>
</head>
<body>
    @foreach($registrations->chunk(5) as $chunk)
        <div class="page-container {{ !$loop->last ? 'page-break' : '' }}">
            @foreach($chunk as $registration)
                <div class="ticket-wrapper">
                    <div class="cut-line"></div>
                    <div class="ticket">
                        <!-- LEFT SIDE (PASTEL RED) -->
                        <div class="main-section">
                            <div class="tear-circle-top"></div>
                            <div class="tear-circle-bottom"></div>
                            
                            <!-- Watermark Background -->
                            <div class="watermark">GERINDRA</div>
                            
                            <table style="width: 100%; position: relative; z-index: 2;">
                                <tr>
                                    <td width="70px" style="vertical-align: middle;">
                                        <div class="logo-box">
                                            @if(isset($logoBase64) && $logoBase64)
                                                <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo">
                                            @else
                                                <div style="width:60px; height:60px; background:#D80000;"></div>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div class="party-title">PARTAI GERINDRA</div>
                                    </td>
                                </tr>
                            </table>
                            
                            <div style="margin-top: 10px; position: relative; z-index: 2;">
                                <div class="event-title">{{ Str::limit($event->name, 35) }}</div>
                                
                                <table class="event-info-grid">
                                    <tr>
                                        <td width="30%">
                                            <span class="info-label">TANGGAL</span>
                                            <div class="info-value">{{ $event->event_start->format('d M Y') }}</div>
                                        </td>
                                        <td width="20%">
                                            <span class="info-label">WAKTU</span>
                                            <div class="info-value">{{ $event->event_start->format('H:i') }} WIB</div>
                                        </td>
                                        <td width="50%">
                                            <span class="info-label">LOKASI</span>
                                            <div class="info-value">{{ Str::limit($event->venue_name, 25) }}</div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <!-- RIGHT SIDE (LIGHT GRAY) -->
                        <div class="stub-section">
                            <div class="qr-box">
                                @if(isset($qrCodes[$registration->id]))
                                    <img src="{{ $qrCodes[$registration->id] }}" class="qr-img" alt="QR">
                                @endif
                            </div>
                            
                            <div>
                                <span class="stub-label">NAMA PESERTA</span>
                                <div class="stub-value" style="font-size: 14px;">{{ Str::limit($registration->massa->nama_lengkap, 15) }}</div>
                            </div>
                            
                            <div style="margin-top: 5px;">
                                <span class="stub-label">NO. TIKET</span>
                                <div class="stub-value" style="font-family: 'Courier New', monospace;">{{ $registration->ticket_number }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</body>
</html>
