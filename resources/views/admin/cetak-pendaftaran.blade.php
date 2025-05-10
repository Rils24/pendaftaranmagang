<!DOCTYPE html>
<html>
<head>
    <title>Cetak Data Pendaftaran Magang</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
            background-color: #f9f9f9;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 15px;
            position: relative;
        }
        .header h2 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .header p {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 5px;
        }
        .container {
            max-width: 850px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h3 {
            color: #2980b9;
            border-left: 4px solid #3498db;
            padding-left: 10px;
            margin-top: 30px;
            margin-bottom: 20px;
            font-size: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
        }
        table, th, td {
            border: 1px solid #e0e0e0;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
        }
        th {
            background-color: #3498db;
            color: #fff;
            font-weight: 500;
            font-size: 15px;
        }
        td {
            font-size: 14px;
        }
        tr:nth-child(even) {
            background-color: #f2f7fb;
        }
        .status-pending {
            color: #f39c12;
            font-weight: 600;
        }
        .status-diterima {
            color: #27ae60;
            font-weight: 600;
        }
        .status-ditolak {
            color: #e74c3c;
            font-weight: 600;
        }
        .footer {
            margin-top: 40px;
            text-align: right;
            color: #7f8c8d;
            font-size: 14px;
            border-top: 1px dashed #bdc3c7;
            padding-top: 15px;
        }
        .button-container {
            margin-top: 30px;
            text-align: center;
        }
        .print-btn, .close-btn {
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .print-btn {
            background-color: #3498db;
            color: white;
        }
        .print-btn:hover {
            background-color: #2980b9;
        }
        .close-btn {
            background-color: #e74c3c;
            color: white;
        }
        .close-btn:hover {
            background-color: #c0392b;
        }
        .print-header {
            display: none;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
                margin: 0;
                background-color: white;
            }
            .container {
                box-shadow: none;
                padding: 15px;
                max-width: 100%;
            }
            .print-header {
                display: block;
                text-align: center;
                margin-bottom: 20px;
            }
            .print-header img {
                height: 80px;
                margin-bottom: 10px;
            }
            .header h2 {
                font-size: 24px;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="print-header">
            <!-- Tambahkan logo instansi jika diperlukan -->
            <!-- <img src="/path/to/logo.png" alt="Logo Instansi"> -->
            <h3>BADAN PUSAT STATISTIK</h3>
        </div>

        <div class="header">
            <h2>DATA PENDAFTARAN MAGANG</h2>
            <p>Tanggal Cetak: {{ date('d-m-Y H:i:s') }}</p>
        </div>
        
        <h3>Data Pendaftar Utama</h3>
        <table>
            <tr>
                <th width="30%">Nama</th>
                <td>{{ $pendaftaran->user->name }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $pendaftaran->user->email }}</td>
            </tr>
            <tr>
                <th>Asal Kampus</th>
                <td>{{ $pendaftaran->asal_kampus }}</td>
            </tr>
            <tr>
                <th>Jurusan</th>
                <td>{{ $pendaftaran->jurusan }}</td>
            </tr>
            <tr>
                <th>Periode Magang</th>
                <td>{{ date('d-m-Y', strtotime($pendaftaran->tanggal_mulai)) }} s/d {{ date('d-m-Y', strtotime($pendaftaran->tanggal_selesai)) }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    @if($pendaftaran->status == 'pending')
                        <span class="status-pending">Menunggu</span>
                    @elseif($pendaftaran->status == 'diterima')
                        <span class="status-diterima">Diterima</span>
                    @elseif($pendaftaran->status == 'ditolak')
                        <span class="status-ditolak">Ditolak</span>
                    @endif
                </td>
            </tr>
        </table>
        
        @if(count($pendaftaran->anggota) > 0)
        <h3>Data Anggota</h3>
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="25%">Nama</th>
                    <th width="15%">NIM</th>
                    <th width="30%">Email</th>
                    <th width="25%">No HP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendaftaran->anggota as $key => $anggota)
                <tr>
                    <td style="text-align: center;">{{ $key + 1 }}</td>
                    <td>{{ $anggota->nama_anggota }}</td>
                    <td>{{ $anggota->nim_anggota }}</td>
                    <td>{{ $anggota->email_anggota }}</td>
                    <td>{{ $anggota->no_hp_anggota }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        
        <div class="footer">
            <p>Dicetak oleh: {{ Auth::user()->name }} | {{ date('d F Y') }}</p>
        </div>
        
        <div class="no-print button-container">
            <button class="print-btn" onclick="window.print()">Cetak Dokumen</button>
            <button class="close-btn" onclick="window.history.back()">Kembali</button>
        </div>
    </div>
    
    <script>
        window.onload = function() {
            // Auto print saat halaman selesai dimuat
            // window.print();
        }
    </script>
</body>
</html>