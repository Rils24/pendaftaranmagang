<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .logo {
            width: 120px;
            height: 40px;
            background-color: #4e73df;
            color: white;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-weight: bold;
        }
        h2 {
            color: #2c3e50;
            margin-top: 0;
            font-size: 24px;
        }
        .content {
            padding: 0 10px;
        }
        .otp-container {
            background-color: #f1f7ff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
            border-left: 4px solid #4e73df;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 4px;
            color: #4e73df;
            margin: 10px 0;
        }
        .timer {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #7f8c8d;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background-color: #4e73df;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #3a5fc8;
        }
        .note {
            background-color: #fff9e6;
            border-left: 4px solid #f1c40f;
            padding: 10px 15px;
            margin-top: 20px;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Sistem Pendaftaran Magang</div>
            <h2>Verifikasi OTP</h2>
        </div>
        
        <div class="content">
            <p>Halo, <strong>{{ $user }}</strong></p>
            <p>Terima kasih telah mendaftar. Untuk melanjutkan proses registrasi, masukkan kode OTP di bawah ini:</p>
            
            <div class="otp-container">
                <p>Kode Verifikasi Anda</p>
                <div class="otp-code">{{ $otp }}</div>
                <div class="timer">Berlaku selama 10 menit</div>
            </div>
            
            <p>Jika Anda tidak merasa melakukan pendaftaran, abaikan email ini atau hubungi tim support kami.</p>
            
            <div style="text-align: center;">
                <a href="#" class="button">Verifikasi Akun</a>
            </div>
            
            <div class="note">
                <strong>Catatan Keamanan:</strong> Jangan pernah membagikan kode OTP Anda kepada siapapun, termasuk pihak yang mengaku sebagai staf kami.
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; 2025 Sistem Pendaftaran magang. Seluruh hak cipta dilindungi.</p>
            <p>Jika Anda mengalami kesulitan, silakan hubungi <a href="mailto:support@company.com">support@company.com</a></p>
        </div>
    </div>
</body>
</html>