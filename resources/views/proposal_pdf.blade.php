<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Proposal Sistem Management Event Gerindra</title>
    <style>
        body {
            font-family: sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #b91d19;
            text-align: center;
            border-bottom: 2px solid #b91d19;
            padding-bottom: 10px;
        }
        h2 {
            color: #b91d19;
            margin-top: 30px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            page-break-after: avoid; /* Prevent heading being at bottom of page */
        }
        h3 {
            color: #555;
            margin-top: 20px;
        }
        ul {
            list-style-type: disc;
            margin-left: 20px;
        }
        li {
            margin-bottom: 5px;
        }
        .footer {
            margin-top: 50px;
            font-size: 12px;
            text-align: center;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        
        /* Flowchart Styles */
        .flow-container {
            width: 100%;
            margin: 20px 0;
            text-align: center;
        }
        .step-box {
            display: inline-block;
            background-color: #f8f9fa;
            border: 1px solid #b91d19;
            border-radius: 8px;
            padding: 10px;
            width: 140px;
            vertical-align: top;
            margin: 0 10px;
            position: relative;
            min-height: 80px;
        }
        .step-title {
            font-weight: bold;
            font-size: 12px;
            color: #b91d19;
            display: block;
            margin-bottom: 5px;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
        }
        .step-desc {
            font-size: 10px;
            color: #555;
        }
        .arrow {
            display: inline-block;
            width: 30px;
            vertical-align: top;
            margin-top: 40px;
            color: #b91d19;
            font-weight: bold;
            font-size: 20px;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <h1>Sistem Management Event Gerindra</h1>
    <p style="text-align: center;"><strong>Proposal Teknis & Detail Fitur</strong></p>

    <h2>1. Deskripsi Singkat</h2>
    <p>
        Sistem ini adalah platform manajemen event terpadu yang dirancang untuk Partai Gerindra, mencakup seluruh siklus operasional event mulai dari pendaftaran massa, manajemen tiket, check-in di lokasi, hingga undian doorprize dan pelaporan analitik berbasis geografis (GIS). Sistem ini dibangun untuk menangani skala besar dengan fokus pada kecepatan (QR Scan), akurasi data (NIK Validasi), dan visualisasi data real-time.
    </p>

    <h2>2. Alur Penggunaan Aplikasi (Workflow)</h2>
    
    <h3>A. Tahap Pra-Event (Persiapan & Pendaftaran)</h3>
    <p>Alur ini menjelaskan proses dari persiapan data hingga peserta mendapatkan tiket.</p>
    
    <div class="flow-container">
        <div class="step-box">
            <span class="step-title">1. Admin</span>
            <span class="step-desc">Membuat Event, setup kuota, & generate Link Pendaftaran.</span>
        </div>
        <div class="arrow">&#8594;</div>
        <div class="step-box">
            <span class="step-title">2. Distribusi</span>
            <span class="step-desc">Link disebar ke Korcam/Kordes atau Publik.</span>
        </div>
        <div class="arrow">&#8594;</div>
        <div class="step-box">
            <span class="step-title">3. Pendaftaran</span>
            <span class="step-desc">Peserta input data diri & NIK (Validasi otomatis).</span>
        </div>
        <div class="arrow">&#8594;</div>
        <div class="step-box">
            <span class="step-title">4. Tiket</span>
            <span class="step-desc">System generate QR Tiket & kirim via WA (opsional).</span>
        </div>
    </div>

    <h3>B. Tahap Event (Pelaksanaan & Check-in)</h3>
    <p>Alur operasional saat hari-H acara untuk memastikan data kehadiran akurat.</p>

    <div class="flow-container">
        <div class="step-box">
            <span class="step-title">1. Kedatangan</span>
            <span class="step-desc">Peserta datang membawa QR Tiket (HP/Cetak).</span>
        </div>
        <div class="arrow">&#8594;</div>
        <div class="step-box">
            <span class="step-title">2. Scanning</span>
            <span class="step-desc">Panitia scan QR menggunakan HP/Laptop via App.</span>
        </div>
        <div class="arrow">&#8594;</div>
        <div class="step-box">
            <span class="step-title">3. Validasi</span>
            <span class="step-desc">System cek validitas tiket & mencatat jam hadir.</span>
        </div>
        <div class="arrow">&#8594;</div>
        <div class="step-box">
            <span class="step-title">4. Real-time</span>
            <span class="step-desc">Angka kehadiran di Dashboard bertambah otomatis.</span>
        </div>
    </div>

    <!-- Page Break for cleaner layout -->
    <div class="page-break"></div>

    <h2>3. Fitur-Fitur Utama</h2>

    <h3>A. Manajemen Event & Operasional</h3>
    <ul>
        <li><strong>Event Dashboard:</strong> Monitor performa event secara real-time.</li>
        <li><strong>Manajemen Event:</strong> Pembuatan event dengan form kustom & kuota.</li>
        <li><strong>Sistem Tiketing:</strong> Generate tiket masal QR Code unik & aman.</li>
        <li><strong>Check-in System:</strong>
            <ul>
                <li><strong>QR Scanner Web-based:</strong> Scan tanpa alat khusus.</li>
                <li><strong>Real-time Stats:</strong> Data terupdate split-second.</li>
            </ul>
        </li>
    </ul>

    <h3>B. Manajemen Massa & GIS</h3>
    <ul>
        <li><strong>Database Terpusat:</strong> Validasi NIK (Deduplikasi).</li>
        <li><strong>WebGIS Heatmap:</strong> Visualisasi kepadatan massa di peta.</li>
        <li><strong>Clustering:</strong> Analisis sebaran per wilayah.</li>
    </ul>

    <h3>C. Engagement (Doorprize)</h3>
    <ul>
        <li><strong>Undian Digital:</strong> Sistem acak transparan.</li>
        <li><strong>Visualisasi:</strong> Animasi shuffle & confetti untuk kemeriahan.</li>
    </ul>

    <h2>4. Teknologi yang Digunakan</h2>
    <p>Stack teknologi modern untuk performa & maintainability.</p>
    
    <table width="100%" style="border-collapse: collapse; margin-top: 10px;">
        <tr style="background-color: #f2f2f2;">
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Komponen</td>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Teknologi</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd;">Backend</td>
            <td style="padding: 8px; border: 1px solid #ddd;">Laravel 11, PHP 8.2, MySQL 8</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd;">Performance</td>
            <td style="padding: 8px; border: 1px solid #ddd;">Redis (Queue & Cache)</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd;">Frontend</td>
            <td style="padding: 8px; border: 1px solid #ddd;">Blade, TailwindCSS, Alpine.js</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd;">Maps / GIS</td>
            <td style="padding: 8px; border: 1px solid #ddd;">Leaflet.js (Open Source)</td>
        </tr>
    </table>

    <div class="footer">
        <p>Generated by Sistem Management Event Gerindra | {{ date('Y') }}</p>
    </div>

</body>
</html>
