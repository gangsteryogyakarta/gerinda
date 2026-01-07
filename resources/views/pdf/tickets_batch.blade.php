<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Batch Tickets - {{ $event->name }}</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .page-break {
            page-break-after: always;
        }
        
        .grid-container {
            width: 100%;
            padding: 10mm;
            box-sizing: border-box;
        }

        .ticket-wrapper {
            width: 48%; /* Display 2 per row with some gap */
            display: inline-block;
            vertical-align: top;
            margin-bottom: 5mm;
            margin-right: 2%;
        }

        .ticket-wrapper:nth-child(2n) {
            margin-right: 0;
        }

        .ticket {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            page-break-inside: avoid;
        }
        
        .ticket-header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 8px;
            text-align: center;
        }
        
        .ticket-header img {
            height: 30px; 
            margin-bottom: 2px;
            vertical-align: middle;
        }
        
        .party-name {
            font-size: 8px;
            opacity: 0.9;
        }
        
        .event-name {
            background: #1f2937;
            color: white;
            padding: 5px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .ticket-body {
            padding: 10px;
            text-align: center;
        }
        
        .qr-code img {
            width: 80px;
            height: 80px;
        }

        .ticket-number {
            font-family: monospace;
            font-size: 10px;
            color: #6b7280;
            margin-top: 5px;
            letter-spacing: 1px;
            font-weight: bold;
        }
        
        .attendee-name {
            margin-top: 10px;
            font-size: 12px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 2px;
        }
        
        .attendee-nik {
            font-size: 8px;
            color: #6b7280;
        }

        .event-details {
            margin-top: 10px;
            font-size: 8px;
            color: #374151;
            border-top: 1px dashed #eee;
            padding-top: 8px;
            text-align: left;
        }

        .detail-row {
            margin-bottom: 2px;
        }
        
        .detail-row span {
            font-weight: 600;
        }
    </style>
</head>
<body>
    @foreach($registrations->chunk(8) as $chunk)
        <div class="grid-container {{ !$loop->last ? 'page-break' : '' }}">
            @foreach($chunk as $registration)
                <div class="ticket-wrapper">
                    <div class="ticket">
                        <div class="ticket-header">
                            @if(isset($logoBase64) && $logoBase64)
                                <img src="{{ $logoBase64 }}" alt="Logo">
                            @else
                                <div style="font-weight:bold; font-size:12px;">TIKET</div>
                            @endif
                            <div class="party-name">Partai Gerindra</div>
                        </div>
                        
                        <div class="event-name">{{ $event->name }}</div>
                        
                        <div class="ticket-body">
                            <div class="qr-code">
                                @if(isset($qrCodes[$registration->id]))
                                    <img src="{{ $qrCodes[$registration->id] }}" alt="QR">
                                @else
                                    <div style="width:80px;height:80px;background:#eee;margin:0 auto;"></div>
                                @endif
                            </div>
                            <div class="ticket-number">{{ $registration->ticket_number }}</div>
                            
                            <div class="attendee-name">{{ Str::limit($registration->massa->nama_lengkap, 20) }}</div>
                            <div class="attendee-nik">{{ $registration->massa->nik }}</div>

                            <div class="event-details">
                                <div class="detail-row">
                                    <span>Tgl:</span> {{ $event->event_start->format('d M Y') }}
                                </div>
                                <div class="detail-row">
                                    <span>Lok:</span> {{ Str::limit($event->venue_name, 25) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</body>
</html>
