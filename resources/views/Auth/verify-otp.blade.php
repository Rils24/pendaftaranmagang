<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="{{ asset('bootslander/assets/img/Logo.png') }}" rel="icon">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --light-color: #f8f9fa;
            --border-radius: 12px;
            --box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
        }
        
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            max-width: 420px;
            width: 100%;
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .logo {
            width: 70px;
            height: 70px;
            margin-bottom: 15px;
            background-color: var(--light-color);
            border-radius: 50%;
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }
        
        h3 {
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 500;
            color: #495057;
        }
        
        .form-control {
            height: 50px;
            border-radius: 8px;
            padding-left: 15px;
            font-size: 1rem;
            border: 1px solid #dce1e8;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }
        
        .otp-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        
        .otp-input {
            width: 50px;
            height: 60px;
            font-size: 1.5rem;
            text-align: center;
            border-radius: 8px;
            border: 1px solid #dce1e8;
            background-color: white;
            font-weight: 600;
        }
        
        .otp-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            height: 50px;
            font-weight: 500;
            font-size: 1rem;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .timer {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .resend-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .resend-link:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            padding: 12px 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .alert-icon {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .alert-danger {
            background-color: #ffe3e3;
            color: #cf0000;
        }
        
        .alert-success {
            background-color: #e3ffe3;
            color: #00a300;
        }
        
        .footer-text {
            font-size: 0.85rem;
            color: #6c757d;
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center p-3">

<div class="card p-4 p-md-5">
    <div class="logo-container">
        <div class="logo mx-auto">
            <img src="{{ asset('bootslander/assets/img/Logo.png') }}" alt="Logo" class="img-fluid" onerror="this.src='/api/placeholder/70/70'">
        </div>
        <h3>Verifikasi OTP</h3>
    </div>
    
    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle alert-icon"></i>
            {{ session('error') }}
        </div>
    @endif
    
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle alert-icon"></i>
            {{ session('success') }}
        </div>
    @endif
    
    <form action="{{ route('otp.verify') }}" method="POST" id="otpForm">
        @csrf
        <div class="mb-4">
            <label for="otp" class="form-label">Masukkan Kode OTP</label>
            <div class="otp-container">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            </div>
            <input type="hidden" name="otp" id="otpValue">
            
            <div class="timer text-center">
                <i class="far fa-clock me-1"></i> Kode berlaku selama <span id="countdown">10:00</span>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="fas fa-check-circle me-2"></i>Verifikasi
        </button>
        
        <div class="text-center">
            <p class="mb-0">
                Tidak menerima kode? <a href="{{ route('otp.resend') }}" class="resend-link">Kirim ulang OTP</a>
            </p>
            <p class="footer-text mt-3">
                <i class="fas fa-shield-alt me-1"></i> Jangan bagikan kode OTP Anda kepada siapapun
            </p>
        </div>
    </form>
</div>

<script>
    // Handle OTP input fields
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.otp-input');
        const form = document.getElementById('otpForm');
        const otpValue = document.getElementById('otpValue');
        
        // Auto-focus and auto-tab functionality
        inputs.forEach((input, index) => {
            input.addEventListener('keyup', function(e) {
                // Move to next input if current is filled
                if (this.value.length === this.maxLength && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                
                // Allow backspace to go to previous field
                if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                    inputs[index - 1].focus();
                }
                
                // Update hidden field with combined OTP
                updateOTPValue();
            });
            
            // Handle paste event
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text').trim();
                
                if (/^\d+$/.test(pasteData)) {
                    for (let i = 0; i < inputs.length; i++) {
                        if (pasteData[i]) {
                            inputs[i].value = pasteData[i];
                            if (i < inputs.length - 1 && pasteData[i+1]) {
                                inputs[i+1].focus();
                            }
                        }
                    }
                    updateOTPValue();
                }
            });
        });
        
        function updateOTPValue() {
            let otp = '';
            inputs.forEach(input => {
                otp += input.value;
            });
            otpValue.value = otp;
        }
        
        // Form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            updateOTPValue();
            if (otpValue.value.length === inputs.length) {
                this.submit();
            }
        });
        
        // Timer functionality
        let timeLeft = 600; // 10 minutes in seconds
        const countdownEl = document.getElementById('countdown');
        
        const timer = setInterval(function() {
            const minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            
            countdownEl.innerHTML = minutes + ':' + seconds;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                countdownEl.innerHTML = 'Waktu habis';
            }
            timeLeft -= 1;
        }, 1000);
    });
</script>

</body>
</html>