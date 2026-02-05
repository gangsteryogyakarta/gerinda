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

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            background-color: #ffffff;
            color: #333;
        }

        /* 
           Layout Grid: 2x2 
           A4 is 210mm x 297mm
           Each Cell: ~105mm x ~148.5mm
        */
        .page {
            width: 210mm;
            height: 296mm; /* slightly less than 297 to avoid overflow page break */
            position: relative;
            background: white;
            overflow: hidden;
        }

        .page-break {
            page-break-after: always;
        }

        .ticket-cell {
            position: absolute;
            width: 105mm;
            height: 148mm;
            padding: 4mm; /* Gap for the card inside */
        }
        
        /* Positioning logic without float to be safer in some pdf renderers, 
           or use float if preferred. DomPDF handles absolute well if precise.
        */
        .pos-1 { top: 0; left: 0; }
        .pos-2 { top: 0; left: 105mm; }
        .pos-3 { top: 148mm; left: 0; }
        .pos-4 { top: 148mm; left: 105mm; }

        /* Cutting Guides */
        .cut-line-horizontal {
            position: absolute;
            top: 148mm;
            left: 0;
            width: 100%;
            height: 0;
            border-top: 1px dashed #ccc;
            z-index: 10;
        }

        .cut-line-vertical {
            position: absolute;
            top: 0;
            left: 105mm;
            width: 0;
            height: 100%;
            border-left: 1px dashed #ccc;
            z-index: 10;
        }
        
        /* Scissor Icon approx positions */
        .scissor {
            position: absolute;
            font-size: 14px;
            color: #666;
            z-index: 11;
        }
        .scissor-h { top: 147mm; left: 5mm; } /* near horizontal line */
        .scissor-v { top: 290mm; left: 103.5mm; transform: rotate(90deg); } 

        /* 
           Ticket Card Design 
           Mimicking the reference image
        */
        .ticket-card {
            width: 100%;
            height: 100%;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
            position: relative;
        }

        /* Background texture/pattern logic could go here */

        /* Header */
        .header {
            background-color: #B91C1C; /* Deep Red */
            background: linear-gradient(135deg, #B91C1C 0%, #991B1B 100%);
            height: 45px;
            width: 100%;
            padding: 0 15px;
            display: table; /* Vertical align trick */
        }

        .header-content {
            display: table-cell;
            vertical-align: middle;
        }
        
        .header-logo {
            width: 24px;
            height: 24px;
            background: #fff;
            border-radius: 4px;
            padding: 2px;
            vertical-align: middle;
            display: inline-block;
            margin-right: 8px;
        }
        
        .header-text {
            color: #fff;
            font-size: 10px; 
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
            vertical-align: middle;
            letter-spacing: 0.5px;
        }

        /* Body Content */
        .card-body {
            padding: 15px 15px 10px 15px;
            text-align: center;
        }

        .role-badge {
            display: inline-block;
            background-color: #FEF2F2;
            color: #B91C1C;
            font-size: 8px;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .participant-name {
            font-size: 18px;
            font-weight: 800;
            color: #B91C1C;
            margin: 5px 0 2px 0;
            line-height: 1.2;
            text-transform: capitalize;
            /* clamp name length visually */
            max-height: 44px;
            overflow: hidden;
        }

        .ticket-code {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            color: #6B7280;
            letter-spacing: 1px;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        /* Info Grid */
        .info-grid {
            border-top: 1px solid #F3F4F6;
            margin-top: 5px;
            padding-top: 12px;
            text-align: left;
            position: relative;
        }

        .info-row {
            margin-bottom: 8px;
            font-size: 10px;
            color: #374151;
            /* Use table for alignment */
            display: table; 
            width: 100%;
        }

        .icon-cell {
            display: table-cell;
            width: 16px;
            vertical-align: top;
            padding-top: 1px;
        }
        
        .icon-img {
            width: 12px;
            height: 12px;
            opacity: 0.7;
            /* DomPDF supports SVG well, or we use unicode chars as fallback */
        }
        
        .text-cell {
            display: table-cell;
            font-weight: 600;
            vertical-align: top;
        }

        /* QR Code Area - Absolute positioning to right */
        .qr-section {
            position: absolute;
            top: 12px; /* aligns with padding-top of info-grid */
            right: 0;
            width: 75px; 
            text-align: center;
        }

        .qr-box {
            width: 75px;
            height: 75px;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            padding: 2px;
            margin-bottom: 2px;
        }
        
        .qr-img {
            width: 100%;
            height: 100%;
        }

        .qr-caption {
            font-size: 6px;
            color: #9CA3AF;
        }

        /* Warning Area */
        .warning-box {
            background-color: #FFFBEB;
            border: 1px solid #FCD34D;
            border-radius: 6px;
            padding: 6px 8px;
            margin-top: 15px; /* space after info/qr */
            font-size: 9px;
            color: #92400E;
            text-align: left;
            line-height: 1.3;
        }
        
        .warning-icon {
            display: inline-block;
            margin-right: 3px;
            font-weight: bold;
        }

        /* Footer */
        .card-footer {
            margin-top: 10px;
            text-align: center;
            font-size: 7px;
            color: #9CA3AF;
        }

        /* Utility classes for red/bold */
        .text-red { color: #B91C1C; }
        .font-bold { font-weight: 700; }
        .text-sm { font-size: 9px; color: #6B7280; font-weight: normal; }

    </style>
</head>
<body>
    @php
        $logoPath = public_path('img/logo-gerindra.png');
        $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
        $logoSrc = $logoData ? 'data:image/png;base64,'.$logoData : '';
        
        // Simple SVG Icons as base64 or inline logic (using minimal SVG paths for DomPDF)
        // Calendar
        $iconDate = '<svg width="12" height="12" viewBox="0 0 24 24" fill="#B91C1C"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>';
        // Clock
        $iconTime = '<svg width="12" height="12" viewBox="0 0 24 24" fill="#B91C1C"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm4.2 14.2L11 13V7h1.5v5.2l4.5 2.7-.8 1.3z"/></svg>';
        // Pin
        $iconLoc = '<svg width="12" height="12" viewBox="0 0 24 24" fill="#B91C1C"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>';
    @endphp

    @foreach($registrations->chunk(4) as $chunk)
        <div class="page {{ !$loop->last ? 'page-break' : '' }}">
            
            <!-- Cut Lines -->
            <div class="cut-line-horizontal"></div>
            <div class="cut-line-vertical"></div>
            <div class="scissor scissor-h">✂</div>
            {{-- <div class="scissor scissor-v">✂</div> --}}

            @foreach($chunk as $index => $reg)
            @php
                 // Map index 0-3 to position classes
                 $posClass = 'pos-' . ($loop->iteration);
            @endphp

            <div class="ticket-cell {{ $posClass }}">
                <div class="ticket-card">
                    <!-- Header -->
                    <div class="header">
                        <div class="header-content">
                            @if($logoSrc)
                                <div class="header-logo">
                                    <img src="{{ $logoSrc }}" style="width:100%; height:100%; object-fit:contain;">
                                </div>
                            @endif
                            <div class="header-text">{{ Str::limit($event->name, 35) }}</div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                        <!-- Name Section -->
                        <div class="participant-name">{{ $reg->massa->nama_lengkap }}</div>
                        <div class="ticket-code">{{ $reg->ticket_number }}</div>

                        <!-- Ticket Details -->
                        <div class="info-grid">
                            
                            <!-- Detail List -->
                            <div style="width: 65%; display: inline-block; vertical-align: top;">
                                <div class="info-row">
                                    <div class="icon-cell">{!! $iconDate !!}</div>
                                    <div class="text-cell">{{ $event->event_start->translatedFormat('d M Y') }}</div>
                                </div>
                                <div class="info-row">
                                    <div class="icon-cell">{!! $iconTime !!}</div>
                                    <div class="text-cell">{{ $event->event_start->format('H:i') }} WIB</div>
                                </div>
                                <div class="info-row">
                                    <div class="icon-cell">{!! $iconLoc !!}</div>
                                    <div class="text-cell">{{ Str::limit($event->venue_name, 30) }}</div>
                                </div>
                            </div>

                            <!-- QR Code (Right Side) -->
                            <div class="qr-section">
                                <div class="qr-box">
                                    @if(isset($qrCodes[$reg->id]))
                                        <img src="{{ $qrCodes[$reg->id] }}" class="qr-img">
                                    @endif
                                </div>
                                <div class="qr-caption">{{ $reg->ticket_number }}</div>
                            </div>

                        </div>

                        <!-- Divider Line -->
                        <div style="border-top: 1px dashed #E5E7EB; margin: 10px 0;"></div>

                        <!-- Warning / Instructions -->
                        <div class="warning-box">
                            <span class="warning-icon">⚠</span> 
                            Tunjukkan tiket ini <span class="text-red font-bold">(digital/cetak)</span> beserta KTP saat check-in di lokasi acara.
                        </div>

                        <div class="card-footer">
                            Tiket ini adalah bukti registrasi yang sah.<br>
                            Dicetak pada {{ now()->format('d M Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

        </div>
    @endforeach
</body>
</html>
