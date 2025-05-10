<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pendaftaran Magang</title>
    <link href="{{ asset('bootslander/assets/img/Logo.png') }}" rel="icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Slick Carousel -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
    <!-- CSS Utama -->
    <link rel="stylesheet" href="{{ asset('css/magang-style.css') }}">
    <!-- CSS untuk Dropdown Kampus -->
    <link rel="stylesheet" href="{{ asset('css/dropdown-kampus.css') }}">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Slick Carousel JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <!-- JavaScript files -->
    <script src="{{ asset('js/pendaftaran.js') }}"></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a class="navbar-brand" href="/">
                <img src="{{ asset('bootslander/assets/img/Logo.png') }}" alt="Logo">
                <span>Sistem Pendaftaran Magang</span>
            </a>
            <div class="user-dropdown">
                <button class="dropdown-button">
                    <i class="fas fa-user-circle"></i> {{ Auth::user()->name }}
                </button>
                <div class="dropdown-menu">
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="dashboard-header">
            <h2>Selamat Datang, {{ Auth::user()->name }}</h2>
            <p>Kelola pendaftaran magang Anda di sini.</p>
        </div>

        @php
            use App\Models\Setting;
            use App\Models\InternshipRequirement;
            $settings = Setting::first();
            $pendaftaran = \App\Models\PendaftaranMagang::where('user_id', Auth::id())->first();
            $requirements = InternshipRequirement::where('is_active', true)->first();
        @endphp
        
        @if ($settings && !$settings->status_pendaftaran)
            <div class="card pendaftaran-tertutup">
                <div class="alert alert-info">
                    <h4>Pendaftaran Magang Periode Ini Telah Ditutup</h4>
                    <p>Pendaftaran selanjutnya akan dibuka dalam:</p>
                </div>
                
                <!-- Countdown Timer -->
                @if(isset($requirements) && $requirements->deadline)
                    <div id="countdown-timer" class="countdown-container" data-target="{{ $requirements->deadline }}">
                        <div class="countdown-item"><span>0</span>Hari</div>
                        <div class="countdown-item"><span>0</span>Jam</div>
                        <div class="countdown-item"><span>0</span>Menit</div>
                        <div class="countdown-item"><span>0</span>Detik</div>
                    </div>
                @endif
                
                <!-- Informasi Pendaftaran -->
                <div class="info-section">
                    <div class="info-header">
                        <i class="fas fa-info-circle"></i>
                        <h4>Informasi Periode Magang Selanjutnya</h4>
                    </div>
                    
                    @if(isset($requirements))
                    <div class="requirement-details">
                        <div class="requirement-item">
                            <div class="icon-box">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="info-box">
                                <h5>Periode Magang</h5>
                                <p>{{ $requirements->period }}</p>
                            </div>
                        </div>
                        
                        <div class="requirement-item">
                            <div class="icon-box">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="info-box">
                                <h5>Lokasi</h5>
                                <p>{{ $requirements->location }}</p>
                            </div>
                        </div>
                        
                        <div class="requirement-item">
                            <div class="icon-box">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="info-box">
                                <h5>Kuota</h5>
                                <p>{{ $requirements->quota }} orang</p>
                            </div>
                        </div>
                        
                        <div class="requirement-item">
                            <div class="icon-box">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="info-box">
                                <h5>Dokumen Persyaratan</h5>
                                <div>{!! $requirements->documents !!}</div>
                            </div>
                        </div>
                        
                        @if($requirements->additional_info)
                        <div class="requirement-item full-width">
                            <div class="icon-box">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div class="info-box">
                                <h5>Informasi Tambahan</h5>
                                <div>{!! $requirements->additional_info !!}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                    
                    <!-- Carousel Testimoni/Galeri -->
                    <div class="carousel-section">
                        <h5><i class="fas fa-image"></i> Galeri Magang Periode Sebelumnya</h5>
                        <div class="carousel-container">
                            <div class="carousel-item">
                                <img src="{{ asset(path: 'bootslander/assets/img/gallery/gallery-1.jpg') }}" alt="Magang Periode Sebelumnya">
                                <div class="carousel-caption">Presentasi Proyek Magang</div>
                            </div>
                            <div class="carousel-item">
                                <img src="{{ asset('bootslander/assets/img/gallery/gallery-2.jpg') }}" alt="Magang Periode Sebelumnya">
                                <div class="carousel-caption">Workshop Pengembangan</div>
                            </div>
                            <div class="carousel-item">
                                <img src="{{ asset('bootslander/assets/img/gallery/gallery-3.jpg') }}" alt="Magang Periode Sebelumnya">
                                <div class="carousel-caption">Team Building Activity</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kontak Informasi -->
                    <div class="contact-info">
                        <h5><i class="fas fa-envelope"></i> Hubungi Kami</h5>
                        <p>Jika ada pertanyaan lebih lanjut, silakan hubungi tim kami melalui:</p>
                        <div class="contact-methods">
                            <div class="contact-method">
                                <i class="fas fa-envelope-open-text"></i>
                                <span>komdigi@gmail.com</span>
                            </div>
                            <div class="contact-method">
                                <i class="fas fa-phone-alt"></i>
                                <span>(021) 123-4567</span>
                            </div>
                            <div class="contact-method">
                                <i class="fab fa-whatsapp"></i>
                                <span>0812-3456-7890</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notifikasi Email -->
                    <div class="notification-box">
                        <h5><i class="fas fa-bell"></i> Dapatkan Notifikasi</h5>
                        <p>Kami akan mengirimkan pemberitahuan ke email Anda saat pendaftaran dibuka kembali.</p>
                        <div class="notification-form">
                            <input type="text" value="{{ Auth::user()->email }}" disabled class="form-control">
                            <button class="btn btn-primary" onclick="confirmNotification()">Aktifkan Notifikasi</button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            @if ($pendaftaran)
                <!-- Card Status Pendaftaran -->
                <div class="card">
                    <h3>Status Pendaftaran Magang</h3>
                    
                    <!-- Informasi progress status dengan progress bar -->
                    <div class="status-progress">
                        <div class="progress-container">
                            <div class="progress-step {{ $pendaftaran->status == 'pending' || $pendaftaran->status == 'diterima' || $pendaftaran->status == 'ditolak' ? 'active' : '' }}">
                                <div class="step-icon"><i class="fas fa-paper-plane"></i></div>
                                <div class="step-label">Terkirim</div>
                            </div>
                            <div class="progress-line"></div>
                            <div class="progress-step {{ $pendaftaran->status == 'diterima' || $pendaftaran->status == 'ditolak' ? 'active' : '' }}">
                                <div class="step-icon"><i class="fas fa-search"></i></div>
                                <div class="step-label">Diverifikasi</div>
                            </div>
                            <div class="progress-line"></div>
                            <div class="progress-step {{ $pendaftaran->status == 'diterima' ? 'active' : '' }}">
                                <div class="step-icon"><i class="fas fa-check-circle"></i></div>
                                <div class="step-label">Diterima</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <div class="info-header">
                            <i class="fas fa-user-graduate"></i>
                            <h4>Informasi Pendaftaran</h4>
                        </div>
                        
                        <div class="form-group">
                            <span class="form-label">Asal Kampus/Sekolah:</span>
                            <p>{{ $pendaftaran->asal_kampus }}</p>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <span class="form-label">Tanggal Mulai:</span>
                                <p>{{ \Carbon\Carbon::parse($pendaftaran->tanggal_mulai)->format('d M Y') }}</p>
                            </div>
                            <div class="form-col">
                                <span class="form-label">Tanggal Selesai:</span>
                                <p>{{ \Carbon\Carbon::parse($pendaftaran->tanggal_selesai)->format('d M Y') }}</p>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <span class="form-label">Durasi Magang:</span>
                            <p>{{ \Carbon\Carbon::parse($pendaftaran->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($pendaftaran->tanggal_selesai)) + 1 }} hari</p>
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <div class="info-header">
                            <i class="fas fa-file-alt"></i>
                            <h4>Dokumen Pendaftaran</h4>
                        </div>
                        
                        <div class="form-group">
                            <span class="form-label">Surat Pengantar:</span>
                            <div class="document-box">
                                <i class="fas fa-file-pdf document-icon"></i>
                                <div class="document-info">
                                    <span class="document-name">Surat_Pengantar.pdf</span>
                                    <div class="document-actions">
                                        <a href="{{ route('view.pdf', Crypt::encryptString($pendaftaran->id)) }}" target="_blank" class="btn btn-sm btn-outline">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                        <a href="{{ asset('storage/' . $pendaftaran->surat_pengantar) }}" download class="btn btn-sm btn-outline">
                                            <i class="fas fa-download"></i> Unduh
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="status-section">
                        <div class="info-header">
                            <i class="fas fa-info-circle"></i>
                            <h4>Status Pendaftaran</h4>
                        </div>
                        
                        <div class="form-group">
                            <div class="status-box {{ $pendaftaran->status }}">
                                @if($pendaftaran->status == 'pending')
                                    <div class="status-icon"><i class="fas fa-clock"></i></div>
                                    <div class="status-details">
                                        <h5>Menunggu Validasi</h5>
                                        <p>Pendaftaran Anda sedang dalam proses validasi. Mohon tunggu konfirmasi selanjutnya.</p>
                                        <div class="submission-date">
                                            <i class="fas fa-calendar-alt"></i> Dikirim pada: {{ \Carbon\Carbon::parse($pendaftaran->created_at)->format('d M Y H:i') }}
                                        </div>
                                    </div>
                                @elseif($pendaftaran->status == 'diterima')
                                    <div class="status-icon"><i class="fas fa-check-circle"></i></div>
                                    <div class="status-details">
                                        <h5>Diterima</h5>
                                        <p>Selamat! Pendaftaran Anda telah diterima.</p>
                                        <div class="submission-date">
                                            <i class="fas fa-calendar-check"></i> Diterima pada: {{ \Carbon\Carbon::parse($pendaftaran->updated_at)->format('d M Y H:i') }}
                                        </div>
                                        
                                        <!-- Informasi tambahan untuk status diterima -->
                                        <div class="acceptance-info">
                                            <h6><i class="fas fa-info-circle"></i> Langkah Selanjutnya</h6>
                                            <ol>
                                                <li>Datang pada tanggal {{ \Carbon\Carbon::parse($pendaftaran->tanggal_mulai)->format('d M Y') }} pukul 08.00 WIB</li>
                                                <li>Pakaian formal (kemeja putih, celana/rok hitam)</li>
                                                <li>Membawa kartu identitas</li>
                                            </ol>
                                        </div>
                                    </div>
                                @elseif($pendaftaran->status == 'ditolak')
                                    <div class="status-icon"><i class="fas fa-times-circle"></i></div>
                                    <div class="status-details">
                                        <h5>Ditolak</h5>
                                        <div class="rejection-reason">
                                            <h6><i class="fas fa-exclamation-triangle"></i> Alasan Penolakan:</h6>
                                            <p>{{ $pendaftaran->alasan_penolakan }}</p>
                                        </div>
                                        <div class="submission-date">
                                            <i class="fas fa-calendar-times"></i> Ditolak pada: {{ \Carbon\Carbon::parse($pendaftaran->updated_at)->format('d M Y H:i') }}
                                        </div>
                                        <a href="{{ route('hapus.pendaftaran', $pendaftaran->id) }}" class="btn btn-primary mt-3">Ajukan Pendaftaran Ulang</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tampilkan anggota kelompok jika ada -->
                    @if(isset($pendaftaran->anggota) && count($pendaftaran->anggota) > 0)
                    <div class="info-section">
                        <div class="info-header">
                            <i class="fas fa-users"></i>
                            <h4>Anggota Kelompok</h4>
                        </div>
                        
                        <div class="member-list">
                            @foreach($pendaftaran->anggota as $anggota)
                            <div class="member-card">
                                <div class="member-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="member-info">
                                    <h5>{{ $anggota->nama_anggota }}</h5>
                                    <p class="member-nim">{{ $anggota->nim_anggota }}</p>
                                    <p><i class="fas fa-graduation-cap"></i> {{ $anggota->jurusan }}</p>
                                    <p><i class="fas fa-envelope"></i> {{ $anggota->email_anggota }}</p>
                                    @if($anggota->no_hp_anggota)
                                    <p><i class="fas fa-phone"></i> {{ $anggota->no_hp_anggota }}</p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <div class="action-buttons">
                        @if($pendaftaran->status == 'pending')
                        <a href="{{ route('batalkan.pendaftaran', $pendaftaran->id) }}" class="btn btn-outline" 
                           onclick="return confirm('Apakah Anda yakin ingin membatalkan pendaftaran?')">
                            <i class="fas fa-times"></i> Batalkan Pendaftaran
                        </a>
                        @endif
                        <a href="{{ route('cetak.bukti', $pendaftaran->id) }}" class="btn btn-primary" target="_blank">
                            <i class="fas fa-print"></i> Cetak Bukti Pendaftaran
                        </a>
                    </div>
                </div>
            @else
                <!-- Form Pendaftaran Magang -->
                <div class="card">
                    <h3>Form Pendaftaran Magang</h3>
                    <form action="{{ route('form.pendaftaran') }}" method="POST" enctype="multipart/form-data" id="form-pendaftaran">
                        @csrf
                        <!-- Form kampus dengan pencarian di dalam dropdown -->
                        <div class="form-group">
                            <label class="form-label">Asal Kampus/Sekolah</label>
                            <div class="select-container">
                                <!-- Select box yang terlihat -->
                                <div class="select-box">
                                    <span class="selected-text">-- Pilih Kampus --</span>
                                    <span class="arrow"><i class="fas fa-chevron-down"></i></span>
                                </div>
                                
                                <!-- Options container dengan search di dalamnya -->
                                <div class="options-container">
                                    <!-- Search box di dalam dropdown -->
                                    <div class="search-box">
                                        <input type="text" id="search-kampus-input" placeholder="Cari nama kampus...">
                                    </div>
                                    
                                    <!-- Daftar opsi -->
                                    <div id="kampus-options">
                                        <!-- Opsi akan diisi oleh JavaScript -->
                                    </div>
                                </div>
                                
                                <!-- Select box asli (tersembunyi) -->
                                <select id="kampus-dropdown" name="kampus_dropdown">
                                    <option value="">-- Pilih Kampus --</option>
                                    @foreach($universitas as $univ)
                                        <option value="{{ trim($univ->nama_universitas) }}">{{ trim($univ->nama_universitas) }}</option>
                                    @endforeach
                                    <option value="other">Lainnya (Ketik Manual)</option>
                                </select>
                                
                                <!-- Input manual untuk kampus yang tidak ada di daftar -->
                                <input type="text" class="form-control" id="asal-kampus-manual" name="asal_kampus" placeholder="Ketik nama kampus" style="display: none;" required>
                                @error('asal_kampus')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <label class="form-label">Tanggal Mulai Magang</label>
                                <input type="date" class="form-control" name="tanggal_mulai" required>
                            </div>
                            <div class="form-col">
                                <label class="form-label">Tanggal Selesai Magang</label>
                                <input type="date" class="form-control" name="tanggal_selesai" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Surat Pengantar (PDF)</label>
                            <div class="file-input-container">
                                <label class="file-input-label">
                                    <i class="fas fa-cloud-upload-alt"></i> Pilih File PDF
                                    <input type="file" class="file-input" name="surat_pengantar" accept="application/pdf" required onchange="updateFileName(this)">
                                </label>
                                <div class="file-name" id="file-name">Belum ada file dipilih</div>
                            </div>
                        </div>
                        
                        <div class="member-section">
                            <h5>Data Anggota Kelompok (Maks. 6 orang)</h5>
                            <div id="member-container" class="member-container"></div>
                            <button type="button" class="btn btn-outline btn-sm" onclick="tambahAnggota()">
                                <i class="fas fa-plus"></i> Tambah Anggota
                            </button>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Kirim Pendaftaran</button>
                    </form>
                </div>
            @endif
        @endif
    </div>
    
    <!-- Script untuk dropdown kampus -->
    <script src="{{ asset('js/dropdown-kampus.js') }}"></script>
    
    <!-- Script untuk notifikasi email -->
    <script>
    function confirmNotification() {
        Swal.fire({
            title: 'Aktifkan Notifikasi?',
            text: 'Anda akan menerima email saat pendaftaran magang dibuka.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4361ee',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Aktifkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire(
                    'Berhasil!',
                    'Notifikasi telah diaktifkan. Kami akan mengirimkan email saat pendaftaran magang dibuka kembali.',
                    'success'
                );
            }
        });
    }
    </script>
</body>
</html>