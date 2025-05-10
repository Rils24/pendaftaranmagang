<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pendaftaran Magang</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('bootslander/assets/img/Logo.png') }}" rel="icon">
    <style>
        :root {
            /* 
            Customize these colors to match your logo 
            You can use a color picker tool to extract exact colors
            */
            --primary-color: #005A8D;       /* Main brand color */
            --secondary-color: #00304E;     /* Darker shade for depth */
            --accent-color: #6BA5C1;        /* Lighter complementary color */
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

        .anggota-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .anggota-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
        }

        .anggota-table td {
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
            font-size: 15px;
        }

        .anggota-table tr:last-child td {
            border-bottom: none;
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

        .print-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
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

        .print-button:hover {
            background-color: var(--primary-color);
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
            .print-button {
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
                <h1>Bukti Pendaftaran Magang</h1>
                <p>Sistem Pendaftaran Magang</p>
            </div>
        </div>

        <div class="certificate-section">
            <h2 class="section-title">Informasi Pendaftaran</h2>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Asal Kampus</span>
                    <span class="detail-value">{{ $pendaftaran->asal_kampus }}</span>
                </div>
                {{-- <div class="detail-item">
                    <span class="detail-label">Jurusan</span>
                    <span class="detail-value">{{ $pendaftaran->jurusan }}</span>
                </div> --}}
                <div class="detail-item">
                    <span class="detail-label">Tanggal Mulai</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($pendaftaran->tanggal_mulai)->format('d F Y') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Tanggal Selesai</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($pendaftaran->tanggal_selesai)->format('d F Y') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status Pendaftaran</span>
                    <span class="detail-value">
                        <span class="status-badge 
                            @switch($pendaftaran->status)
                                @case('pending')
                                    status-pending
                                    @break
                                @case('diterima')
                                    status-diterima
                                    @break
                                @case('ditolak')
                                    status-ditolak
                                    @break
                            @endswitch
                        ">
                            {{ ucfirst($pendaftaran->status) }}
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

        @if($pendaftaran->anggota->count() > 0)
        <div class="certificate-section">
            <h2 class="section-title">Daftar Anggota Magang</h2>
            <table class="anggota-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Jurusan</th>
                        <th>Email</th>
                        <th>No. Telepon</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendaftaran->anggota as $anggota)
                    <tr>
                        <td>{{ $anggota->nama_anggota }}</td>
                        <td>{{ $anggota->nim_anggota ?? '-' }}</td>
                        <td>{{ $anggota->jurusan }}</td>
                        <td>{{ $anggota->email_anggota ?? '-' }}</td>
                        <td>{{ $anggota->no_hp_anggota ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="footer">
            Dokumen ini dicetak pada {{ now()->format('d F Y H:i:s') }} | Sistem Informasi Manajemen Magang
        </div>
    </div>

    <button class="print-button" onclick="window.print()">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 9l6 6 6-6"/>
        </svg>
        Cetak Bukti
    </button>
</body>
</html>