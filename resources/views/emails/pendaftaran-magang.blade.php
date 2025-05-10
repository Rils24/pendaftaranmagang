<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pendaftaran Magang</title>
    <style>
        /* Reset dan Font dasar */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f7fa;
            padding: 20px;
        }
        
        /* Container utama */
        .container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        }
        
        /* Header */
        .header {
            background-color: #2563eb;
            color: white;
            padding: 25px 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        /* Content */
        .content {
            padding: 35px 30px;
        }
        
        .greeting {
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 25px;
            color: #1e293b;
        }
        
        .status-card {
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .status-card.accepted {
            background-color: #ecfdf5;
            border-left: 5px solid #10b981;
        }
        
        .status-card.rejected {
            background-color: #fef2f2;
            border-left: 5px solid #ef4444;
        }
        
        .status-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 16px;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }
        
        .status-badge.accepted {
            background-color: #10b981;
            color: white;
        }
        
        .status-badge.rejected {
            background-color: #ef4444;
            color: white;
        }
        
        .next-steps {
            background-color: #f8fafc;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .next-steps h3 {
            font-size: 17px;
            margin-bottom: 10px;
            color: #334155;
        }
        
        .next-steps ul {
            padding-left: 20px;
        }
        
        .next-steps li {
            margin-bottom: 8px;
        }
        
        .reason-box {
            background-color: #fff0f0;
            border: 1px dashed #ef4444;
            padding: 15px;
            margin-top: 15px;
            border-radius: 8px;
        }
        
        /* Team members section */
        .team-members {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .team-members h3 {
            font-size: 17px;
            color: #0369a1;
            margin-bottom: 10px;
        }
        
        .team-members ul {
            list-style-type: none;
        }
        
        .team-members li {
            padding: 5px 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        
        .team-members li:last-child {
            border-bottom: none;
        }
        
        /* Footer */
        .footer {
            background-color: #f1f5f9;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer p {
            font-size: 14px;
            color: #64748b;
        }
        
        .contact-info {
            margin-top: 10px;
        }
        
        .contact-info a {
            color: #2563eb;
            text-decoration: none;
        }
        
        .contact-info a:hover {
            text-decoration: underline;
        }
        
        /* Icon styling */
        .icon {
            display: block;
            width: 64px;
            height: 64px;
            margin: 0 auto 15px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }
        
        .icon.accepted {
            background-color: #d1fae5;
            color: #10b981;
        }
        
        .icon.rejected {
            background-color: #fee2e2;
            color: #ef4444;
        }
        
        /* Responsif */
        @media screen and (max-width: 600px) {
            .header {
                padding: 20px 15px;
            }
            
            .content {
                padding: 25px 20px;
            }
            
            .greeting {
                font-size: 18px;
            }
            
            .status-card {
                padding: 20px 15px;
            }
            
            .status-badge {
                padding: 6px 15px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Status Pendaftaran Magang</h1>
            <p>Informasi terkini tentang aplikasi magang Anda</p>
        </div>
        
        <div class="content">
            <p class="greeting">Halo {{ $nama ?? 'Calon Peserta Magang' }},</p>
            
            @if ($status == 'diterima')
                <div class="status-card accepted">
                    <div class="icon accepted">✓</div>
                    <p class="status-title">Status Pendaftaran:</p>
                    <div class="status-badge accepted">DITERIMA</div>
                    <p>Selamat! Anda telah <b>diterima</b> untuk mengikuti program magang kami. Kami sangat senang dapat bekerja sama dengan Anda dalam perjalanan pengembangan karir Anda.</p>
                    
                    @if (!empty($anggota) && count($anggota) > 0)
                    <div class="team-members">
                        <h3>Anggota Tim:</h3>
                        <ul>
                            @foreach ($anggota as $member)
                            <li>{{ $member }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    <div class="next-steps">
                        <h3>Langkah Selanjutnya:</h3>
                        <ul>
                            <li>Persiapkan dokumen yang diperlukan sebelum hari pertama magang.</li>
                            <li>Hadiri orientasi pada tanggal yang akan diinformasikan.</li>
                        </ul>
                    </div>
                </div>
            @else
                <div class="status-card rejected">
                    <div class="icon rejected">✕</div>
                    <p class="status-title">Status Pendaftaran:</p>
                    <div class="status-badge rejected">DITOLAK</div>
                    <p>Mohon maaf, pendaftaran magang Anda <b>tidak dapat kami terima</b> pada kesempatan kali ini.</p>
                    
                    @if (!empty($anggota) && count($anggota) > 0)
                    <div class="team-members">
                        <h3>Anggota Tim:</h3>
                        <ul>
                            @foreach ($anggota as $member)
                            <li>{{ $member }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    <div class="reason-box">
                        <p><strong>Alasan:</strong> {{ $alasan ?? 'Tidak memenuhi kriteria yang diperlukan saat ini' }}</p>
                    </div>
                    
                    <div class="next-steps">
                        <h3>Kami Sarankan:</h3>
                        <ul>
                            <li>Jangan berkecil hati. Kesempatan lain menunggu Anda.</li>
                            <li>Tingkatkan keterampilan dan pengalaman Anda.</li>
                            <li>Anda dapat mencoba mendaftar kembali pada periode selanjutnya.</li>
                        </ul>
                    </div>
                </div>
            @endif
            
            <p>Terima kasih atas minat Anda terhadap program magang kami. Kami menghargai waktu dan usaha yang telah Anda berikan dalam proses pendaftaran ini.</p>
        </div>
        
        <div class="footer">
            <p>© 2025 Komdigi. Semua hak dilindungi.</p>
            <div class="contact-info">
                <p>Jika Anda memiliki pertanyaan, silakan hubungi kami di <a href="mailto:info@komdigi.com">info@komdigi.com</a></p>
            </div>
        </div>
    </div>
</body>
</html>