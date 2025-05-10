<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Sistem Pendaftaran Magang</title>
    <link href="{{ asset('bootslander/assets/img/Logo.png') }}" rel="icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #0a0a0a;
            overflow-x: hidden;
            position: relative;
        }

        /* Enhanced Background Animation */
        .background {
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(-45deg, #3a0ca3, #4361ee, #4cc9f0, #2d3748);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            z-index: -2;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Improved Particles Effect */
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            animation: floatParticles linear infinite;
        }

        @keyframes floatParticles {
            from { transform: translateY(100vh) rotate(0deg); }
            to { transform: translateY(-10vh) rotate(360deg); }
        }

        /* White Navbar */
        .navbar {
            background-color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 15px 50px;
            margin-bottom: 40px;
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .navbar a {
            color: #333;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .navbar a:hover {
            color: #4361ee;
        }

        .navbar-brand {
            font-size: 1.4rem;
            color: #333;
        }

        .navbar .btn-primary {
            background-color: #4361ee;
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 30px;
            transition: all 0.3s ease;
        }

        .navbar .btn-primary:hover {
            background-color: #3a0ca3;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        /* Modern Register Card */
        .card-register {
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 1;
            border: 1px solid rgba(255, 255, 255, 0.18);
            transition: all 0.3s ease;
            margin-bottom: 40px;
        }

        .card-register:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .card-register h3 {
            color: #3a0ca3;
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 28px;
            text-align: center;
        }

        .card-register p.subtitle {
            color: #718096;
            margin-bottom: 30px;
            text-align: center;
        }

        /* Stylish Form Controls */
        .form-control {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 20px;
            color: #2d3748;
            font-size: 1rem;
            transition: all 0.3s ease;
            margin-bottom: 5px;
        }

        .form-control:focus {
            background: white;
            border-color: #4cc9f0;
            box-shadow: 0 0 0 3px rgba(76, 201, 240, 0.25);
        }

        .form-control::placeholder {
            color: #a0aec0;
        }

        .form-label {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group .form-control {
            padding-left: 45px;
            margin-bottom: 0;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #718096;
            z-index: 10;
        }

        /* Enhanced Button */
        .btn-register {
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            padding: 12px 20px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 10px 15px -3px rgba(58, 12, 163, 0.3);
        }

        .btn-register:hover {
            background: linear-gradient(135deg, #3a0ca3, #4361ee);
            transform: translateY(-3px);
            box-shadow: 0 15px 20px -3px rgba(58, 12, 163, 0.4);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        /* Login Link */
        .login-link {
            margin-top: 25px;
            text-align: center;
            color: #718096;
            font-size: 0.95rem;
        }

        .login-link a {
            color: #4361ee;
            font-weight: 600;
            text-decoration: none;
            padding-bottom: 2px;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            border-bottom: 2px solid #4361ee;
        }

        /* Improved Notification */
        .alert {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform: translateY(-10px);
        }

        .alert.show {
            opacity: 1;
            transform: translateY(0);
        }

        .alert-success {
            background: rgba(39, 174, 96, 0.2);
            border-left: 4px solid #27ae60;
            color: #27ae60;
        }

        .alert-danger {
            background: rgba(255, 59, 48, 0.2);
            border-left: 4px solid #ff3b30;
            color: #e53e3e;
        }

        .invalid-feedback {
            color: #e53e3e;
            font-size: 0.8rem;
            margin-bottom: 10px;
        }

        /* Responsive Styling */
        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }
            
            .card-register {
                padding: 30px;
                max-width: 90%;
                margin: 0 auto 40px;
            }
            
            .navbar-brand {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 480px) {
            .navbar {
                padding: 12px 15px;
            }
            
            .navbar-brand img {
                height: 30px;
            }
            
            .navbar-brand {
                font-size: 0.9rem;
            }
            
            .card-register {
                padding: 25px;
            }
            
            .card-register h3 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

    <!-- Background Animasi -->
    <div class="background"></div>

    <!-- Efek Partikel -->
    <div class="particles">
        <script>
            for (let i = 0; i < 50; i++) {
                let particle = document.createElement("div");
                particle.classList.add("particle");
                
                // Random size between 2-6px
                const size = Math.random() * 4 + 2;
                particle.style.width = size + "px";
                particle.style.height = size + "px";
                
                // Random position
                particle.style.left = Math.random() * 100 + "vw";
                
                // Random animation duration
                particle.style.animationDuration = (Math.random() * 10 + 10) + "s";
                
                // Random opacity
                particle.style.opacity = Math.random() * 0.5 + 0.1;
                
                document.querySelector(".particles").appendChild(particle);
            }
        </script>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <img src="{{ asset('bootslander/assets/img/Logo.png') }}" alt="Logo" height="45" class="me-3">
                <strong>Sistem Pendaftaran Magang</strong>
            </a>
            <div>
                <a href="{{ route('login') }}" class="me-4">Login</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
            </div>
        </div>
    </nav>

    <!-- Register Form -->
    <div class="container d-flex justify-content-center align-items-center flex-grow-1">
        <div class="card-register">
            <h3>Registrasi</h3>
            <p class="subtitle">Buat akun baru untuk mulai mendaftar program magang</p>
            
            @if(session('success'))
                <div class="alert alert-success show">
                    {{ session('success') }}
                    <button type="button" class="btn-close float-end" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <script>
                    setTimeout(() => {
                        const alert = document.querySelector('.alert-success');
                        if (alert) alert.classList.remove('show');
                    }, 5000);
                </script>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger show">
                    {{ session('error') }}
                    <button type="button" class="btn-close float-end" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <script>
                    setTimeout(() => {
                        const alert = document.querySelector('.alert-danger');
                        if (alert) alert.classList.remove('show');
                    }, 5000);
                </script>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="input-group">
                    <span class="input-icon">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required placeholder="Nama Lengkap">
                </div>
                @error('name')<div class="invalid-feedback mb-3">{{ $message }}</div>@enderror
                
                <div class="input-group">
                    <span class="input-icon">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required placeholder="Email">
                </div>
                @error('email')<div class="invalid-feedback mb-3">{{ $message }}</div>@enderror
                
                <div class="input-group">
                    <span class="input-icon">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" required placeholder="Password">
                </div>
                @error('password')<div class="invalid-feedback mb-3">{{ $message }}</div>@enderror
                
                <div class="input-group">
                    <span class="input-icon">
                        <i class="fas fa-check-circle"></i>
                    </span>
                    <input type="password" class="form-control" name="password_confirmation" required placeholder="Konfirmasi Password">
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn-register">
                        <i class="fas fa-user-plus me-2"></i> Daftar Sekarang
                    </button>
                </div>
            </form>
            
            <p class="login-link">Sudah punya akun? <a href="{{ route('login') }}">Login</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Langsung tampilkan notifikasi jika ada
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.classList.add('show');
            });
        });
    </script>
</body>
</html>