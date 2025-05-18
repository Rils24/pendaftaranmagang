<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Data Pendaftaran Magang</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('bootslander/assets/img/Logo.png') }}" rel="icon">
    <style>
        :root {
            --primary-color: #005A8D;
            --secondary-color: #00304E;
            --accent-color: #6BA5C1;
            --text-primary: #2c3333;
            --text-secondary: #495057;
            --background-light: #f8f9fa;
            --border-color: #dee2e6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            line-height: 1.6;
            color: var(--text-primary);
            background-color: var(--background-light);
            -webkit-print-color-adjust: exact;
        }

        .certificate-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            padding: 30mm;
        }

        .certificate-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 4px solid var(--primary-color);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header-logo {
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .header-title {
            text-align: right;
        }

        .header-title h1 {
            color: var(--primary-color);
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .header-title p {
            color: var(--secondary-color);
            font-size: 16px;
            font-weight: 500;
        }

        .certificate-section {
            background-color: var(--background-light);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .section-title {
            color: var(--primary-color);
            border-bottom: 3px solid var(--accent-color);
            padding-bottom: 8px;
            margin-bottom: 15px;
            font-size: 20px;
            font-weight: 600;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 4px;
            font-size: 14px;
            text-transform: uppercase;
        }

        .detail-value {
            color: var(--text-primary);
            font-weight: 500;
            font-size: 16px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }

        .status-diterima {
            background-color: var(--primary-color);
            color: white;
        }

        .status-ditolak {
            background-color: #dc3545;
            color: white;
        }

        /* Tabel anggota style */
        .anggota-section {
            margin-top: 20px;
        }
        
        .anggota-title {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 5px;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 600;
        }
        
        .anggota-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        .anggota-table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            padding: 8px;
            text-align: center;
            border: 1px solid #ccc;
        }
        
        .anggota-table td {
            padding: 8px;
            border: 1px solid #ccc;
            max-width: 150px;
            word-wrap: break-word;
        }
        
        .anggota-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .anggota-table tr:hover {
            background-color: #e9e9e9;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 13px;
            color: var(--text-secondary);
            border-top: 2px solid var(--border-color);
            padding-top: 15px;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.1;
            font-size: 120px;
            font-weight: 700;
            color: var(--primary-color);
            z-index: 1;
        }

        .button-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }

        .print-btn, .close-btn {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 3px 6px rgba(0,0,0,0.2);
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        .print-btn {
            background-color: var(--primary-color);
        }

        .print-btn:hover {
            background-color: var(--secondary-color);
        }

        .close-btn {
            background-color: #dc3545;
        }

        .close-btn:hover {
            background-color: #bd2130;
        }

        @media print {
            body {
                background-color: white;
            }
            .certificate-container {
                width: 100%;
                margin: 0;
                padding: 20mm;
                box-shadow: none;
            }
            .button-container {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="watermark">KOMDIGI</div>
        
        <div class="certificate-header">
            <div class="header-logo">
                <img src="{{ asset('bootslander/assets/img/Logo.png') }}" alt="Logo Instansi">
            </div>
            <div class="header-title">
                <h1>Data Pendaftaran Magang</h1>
                <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</p>
            </div>
        </div>

        <div class="certificate-section">
            <h2 class="section-title">Informasi Pendaftar</h2>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Email</span>
                    <span class="detail-value">{{ $pendaftaran->user->email }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Asal Kampus</span>
                    <span class="detail-value">{{ $pendaftaran->asal_kampus }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Jurusan</span>
                    <span class="detail-value">{{ $pendaftaran->jurusan }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Periode Magang</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($pendaftaran->tanggal_mulai)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($pendaftaran->tanggal_selesai)->format('d F Y') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">
                        <span class="status-badge 
                            @if($pendaftaran->status == 'pending')
                                status-pending
                            @elseif($pendaftaran->status == 'diterima')
                                status-diterima
                            @elseif($pendaftaran->status == 'ditolak')
                                status-ditolak
                            @endif
                        ">
                            @if($pendaftaran->status == 'pending')
                                Menunggu
                            @elseif($pendaftaran->status == 'diterima')
                                Diterima
                            @elseif($pendaftaran->status == 'ditolak')
                                Ditolak
                            @endif
                        </span>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Durasi Magang</span>
                    <span class="detail-value">
                        {{ \Carbon\Carbon::parse($pendaftaran->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($pendaftaran->tanggal_selesai)) }} Hari
                    </span>
                </div>
            </div>
        </div>

        @if(count($pendaftaran->anggota) > 0)
        <div class="certificate-section">
            <h2 class="section-title">Data Anggota</h2>
            <table class="anggota-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">NO</th>
                        <th style="width: 25%;">NAMA</th>
                        <th style="width: 15%;">NIM</th>
                        <th style="width: 20%;">JURUSAN</th>
                        <th style="width: 20%;">EMAIL</th>
                        <th style="width: 15%;">NO HP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendaftaran->anggota as $key => $anggota)
                    <tr>
                        <td style="text-align: center;">{{ $key + 1 }}</td>
                        <td>{{ $anggota->nama_anggota }}</td>
                        <td>{{ $anggota->nim_anggota }}</td>
                        <td>{{ $anggota->jurusan }}</td>
                        <td>{{ $anggota->email_anggota }}</td>
                        <td>{{ $anggota->no_hp_anggota }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        
        <div class="footer">
            Dicetak oleh: {{ Auth::user()->name }} | {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }} | Sistem Informasi Manajemen Magang
        </div>
    </div>

    <div class="button-container">
        <button class="print-btn" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Cetak Dokumen
        </button>
        <button class="close-btn" onclick="window.history.back()">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
            Kembali
        </button>
    </div>
    
    <script>
        window.onload = function() {
            // Auto print saat halaman selesai dimuat
            // window.print();
        }
    </script>
</body>
</html>