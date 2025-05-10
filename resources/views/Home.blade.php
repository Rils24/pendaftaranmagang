<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Pendaftaran Magang</title>
  <meta name="description" content="Pendaftaran Magang untuk mahasiswa dan pencari kerja.">
  <meta name="keywords" content="Magang, Pendaftaran, Karir, Pelatihan">
  <link href="{{ asset('bootslander/assets/img/Logo.png') }}" rel="icon">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&family=Poppins:wght@100;200;300;400;500;600;700;800;900&family=Raleway:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

  <link href="{{ asset('bootslander/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('bootslander/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('bootslander/assets/vendor/aos/aos.css') }}" rel="stylesheet">
  <link href="{{ asset('bootslander/assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
  <link href="{{ asset('bootslander/assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('Bootslander/assets/css/main.css') }}">
</head>

<body class="index-page">

<header id="header" class="header d-flex align-items-center fixed-top">
  <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">
    <a href="index.html" class="logo d-flex align-items-center">
      <img src="{{ asset('bootslander/assets/img/Logo.png') }}" alt="Logo">
      <h1 class="sitename">Pendaftaran Magang</h1>
    </a>
    <nav id="navmenu" class="navmenu">
      <ul>
        <li><a href="#hero" class="active">Home</a></li>
        <li><a href="#about">Alur Pendaftaran</a></li>
        <li><a href="#details">Sejarah</a></li>
        <li><a href="#gallery">Gallery</a></li>
        <li><a href="#maps">Lokasi</a></li>
        <li><a href="{{ route('login') }}">Login</a></li>
        <li><a href="{{ route('register') }}">Registrasi</a></li>
      </ul>
      <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
    </nav>
  </div>
</header>

<main class="main">

  <section id="hero" class="hero section dark-background">
    <div class="container">
      <div class="row gy-4 justify-content-between align-items-center">
        <div class="col-lg-6 d-flex flex-column justify-content-center" data-aos="fade-in">
          <h1>Daftar Magang Sekarang <span>Dengan Kami</span></h1>
          <p class="mb-4 hero-description">
            Raih pengalaman berharga dan tingkatkan skill digital Anda bersama Komdigi Medan
          </p>
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <a href="{{ route('login') }}" class="btn-get-started">Mulai Pendaftaran</a>
            <a href="https://youtu.be/FKr4VOVSzgE?si=umrKLhQCy9wABWi7" class="glightbox btn-watch-video d-flex align-items-center">
              <i class="bi bi-play-circle-fill"></i><span>Tonton Video</span>
            </a>
          </div>
        </div>
        <div class="col-lg-5 col-md-8 col-sm-10 order-lg-last hero-img" data-aos="zoom-out" data-aos-delay="100">
          <img src="{{ asset('bootslander/assets/img/gambar22.png') }}" class="img-fluid animated" alt="">
        </div>
      </div>
    </div>

    <svg class="hero-waves" xmlns="http://www.w3.org/2000/svg" viewBox="0 24 150 28" preserveAspectRatio="none">
      <defs>
        <path id="wave-path" d="M-160 44c30 0 58-18 88-18s58 18 88 18 58-18 88-18 58 18 88 18v44h-352z"></path>
      </defs>
      <g class="wave1"><use xlink:href="#wave-path" x="50" y="3"></use></g>
      <g class="wave2"><use xlink:href="#wave-path" x="50" y="0"></use></g>
      <g class="wave3"><use xlink:href="#wave-path" x="50" y="9"></use></g>
    </svg>
  </section>

  @php
  // Mengambil persyaratan magang yang aktif berdasarkan waktu dunia nyata
  $requirement = \App\Models\InternshipRequirement::currentlyActive()
                  ->latest()
                  ->first();
@endphp

@if($requirement)
<section id="about" class="about section">
<div class="container" data-aos="fade-up" data-aos-delay="100">
  <div class="row align-items-xl-center gy-5">
    <div class="col-xl-5 content" data-aos="fade-up">
      <div class="magang-box">
        <h3 class="fw-bold">Persyaratan Magang</h3>
        <ul class="magang-requirements">
          <li><i class="bi bi-file-earmark-text"></i>
              <div><strong>Dokumen:</strong><br>{!! $requirement->documents !!}</div>
          </li>
          <li><i class="bi bi-calendar-event"></i>
              <div><strong>Batas Waktu:</strong><br>{{ \Carbon\Carbon::parse($requirement->deadline)->format('d F Y') }}</div>
          </li>
          <li><i class="bi bi-people"></i>
              <div><strong>Kuota:</strong><br>{{ $requirement->quota }} Orang</div>
          </li>
          <li><i class="bi bi-calendar"></i>
              <div><strong>Periode:</strong><br>{{ $requirement->period }}</div>
          </li>
          <li><i class="bi bi-geo-alt"></i>
              <div><strong>Lokasi:</strong><br>{{ $requirement->location }}</div>
          </li>
          @if($requirement->additional_info)
          <li><i class="bi bi-info-circle"></i>
              <div><strong>Info Tambahan:</strong><br>{!! $requirement->additional_info !!}</div>
          </li>
          @endif
      </ul>
      </div>
    </div>
    <div class="col-xl-7 d-flex flex-column align-items-center text-center">
      <div class="row gy-4 icon-boxes justify-content-center">
        <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
          <div class="icon-box">
            <div class="number-badge">1</div>
            <i class="bi bi-person-add"></i>
            <h3>Registrasi Akun</h3>
            <p>Perwakilan kelompok mendaftar akun dengan mengisi data diri lengkap melalui website kami.</p>
          </div>
        </div>
        <div class="col-md-6" data-aos="fade-up" data-aos-delay="300">
          <div class="icon-box">
            <div class="number-badge">2</div>
            <i class="bi bi-file-earmark-text"></i>
            <h3>Lengkapi Form</h3>
            <p>Isi form pendaftaran kelompok beserta data anggota (maksimal 6 orang).</p>
          </div>
        </div>
        <div class="col-md-6" data-aos="fade-up" data-aos-delay="400">
          <div class="icon-box">
            <div class="number-badge">3</div>
            <i class="bi bi-hourglass-split"></i>
            <h3>Proses Seleksi</h3>
            <p>Tim kami akan melakukan seleksi dan menginformasikan hasil melalui WhatsApp.</p>
          </div>
        </div>
        <div class="col-md-6" data-aos="fade-up" data-aos-delay="500">
          <div class="icon-box">
            <div class="number-badge">4</div>
            <i class="bi bi-calendar-check"></i>
            <h3>Konfirmasi & Mulai</h3>
            <p>Jika lolos seleksi, lakukan konfirmasi dan ikuti arahan teknis.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</section>
@else
<section id="about" class="about section">
<div class="container" data-aos="fade-up" data-aos-delay="100">
  <div class="row align-items-center">
    <div class="col-12 text-center">
      <div class="alert alert-info p-5" role="alert">
        <h4 class="alert-heading mb-4"><i class="bi bi-info-circle-fill me-2"></i>Tidak Ada Periode Magang Aktif</h4>
        <p>Saat ini tidak ada periode pendaftaran magang yang sedang aktif berdasarkan tanggal saat ini.</p>
        <hr>
        <p class="mb-0">Silakan periksa kembali halaman ini di lain waktu atau hubungi kami untuk informasi lebih lanjut.</p>
      </div>
    </div>
  </div>
</div>
</section>
@endif
<!-- Details Section -->
<section id="details" class="details section">
  <div class="container section-title" data-aos="fade-up">
    <h2>Sejarah</h2>
    <div><span>Kominfo Digital Training</span> <span class="description-title">Medan</span></div>
  </div>

  <div class="container">
    <div class="row gy-4 align-items-center features-item">
      <div class="col-md-5 d-flex align-items-center" data-aos="zoom-out" data-aos-delay="100">
        <img src="{{ asset('bootslander/assets/img/details-1.png') }}" class="img-fluid" alt="">
      </div>
      <div class="col-md-7" data-aos="fade-up" data-aos-delay="100">
        <h3>Lahir dari Visi Digital Indonesia</h3>
        <p class="fst-italic">
          Komdigi Medan didirikan sebagai bagian dari program transformasi digital Indonesia yang dikembangkan Kementerian Komunikasi dan Informatika.
        </p>
        <ul>
          <li><i class="bi bi-check"></i><span> Didirikan tahun 2021 sebagai pusat literasi digital.</span></li>
          <li><i class="bi bi-check"></i> <span>Melayani wilayah Medan dan Sumatera Utara.</span></li>
          <li><i class="bi bi-check"></i> <span>Menjadi garda terdepan transformasi digital di Sumatera.</span></li>
        </ul>
      </div>
    </div>

    <div class="row gy-4 align-items-center features-item">
      <div class="col-md-5 order-1 order-md-2 d-flex align-items-center" data-aos="zoom-out" data-aos-delay="200">
        <img src="{{ asset('bootslander/assets/img/details-2.png') }}" class="img-fluid" alt="">
      </div>
      <div class="col-md-7 order-2 order-md-1" data-aos="fade-up" data-aos-delay="200">
        <h3>Program Pelatihan Digital Komprehensif</h3>
        <p class="fst-italic">
          Menyediakan beragam program pelatihan digital untuk meningkatkan kemampuan masyarakat dan UMKM.
        </p>
        <p>
          Program kami mencakup pelatihan digital marketing, e-commerce, coding, desain grafis, dan pengembangan website. Kami berkomitmen untuk menciptakan ekosistem digital yang kuat di Sumatera Utara.
        </p>
      </div>
    </div>

    <div class="row gy-4 align-items-center features-item">
      <div class="col-md-5 d-flex align-items-center" data-aos="zoom-out">
        <img src="{{ asset('bootslander/assets/img/details-3.png') }}" class="img-fluid" alt="">
      </div>
      <div class="col-md-7" data-aos="fade-up">
        <h3>Kolaborasi dengan Perguruan Tinggi</h3>
        <p>Membangun kemitraan dengan universitas terkemuka di Medan untuk program magang yang terintegrasi dengan kurikulum pendidikan tinggi.</p>
        <ul>
          <li><i class="bi bi-check"></i> <span>Kemitraan dengan USU, UNIMED, dan UINSU.</span></li>
          <li><i class="bi bi-check"></i><span> Program magang berbasis proyek nyata.</span></li>
          <li><i class="bi bi-check"></i> <span>Sertifikasi resmi Kominfo untuk peserta magang.</span></li>
        </ul>
      </div>
    </div>

    <div class="row gy-4 align-items-center features-item">
      <div class="col-md-5 order-1 order-md-2 d-flex align-items-center" data-aos="zoom-out">
        <img src="{{ asset('bootslander/assets/img/details-4.png') }}" class="img-fluid" alt="">
      </div>
      <div class="col-md-7 order-2 order-md-1" data-aos="fade-up">
        <h3>Kontribusi Nyata untuk Pertumbuhan Digital</h3>
        <p class="fst-italic">
          Telah melatih lebih dari 20.000 peserta sejak didirikan, dengan tingkat kepuasan peserta mencapai 95%.
        </p>
        <p>
          Komdigi Medan berperan penting dalam mengembangkan startup lokal, UMKM digital, dan menciptakan talenta digital yang siap bersaing di era industri 4.0. Kami terus berinovasi untuk menciptakan dampak positif bagi masyarakat Medan.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- Gallery Section -->
<section id="gallery" class="gallery section">
  <div class="container section-title" data-aos="fade-up">
    <h2>Gallery</h2>
    <div><span>Momen-momen</span> <span class="description-title">Kegiatan</span></div>
  </div>

  <div class="container" data-aos="fade-up" data-aos-delay="100">
    <div class="row g-0">
      <div class="col-lg-3 col-md-4">
        <div class="gallery-item">
          <a href="{{ asset('bootslander/assets/img/gallery/gallery-1.jpg') }}" class="glightbox" data-gallery="images-gallery">
            <img src="{{ asset('bootslander/assets/img/gallery/gallery-1.jpg') }}" alt="" class="img-fluid">
          </a>
        </div>
      </div>

      <div class="col-lg-3 col-md-4">
        <div class="gallery-item">
          <a href="{{ asset('bootslander/assets/img/gallery/gallery-2.jpg') }}" class="glightbox" data-gallery="images-gallery">
            <img src="{{ asset('bootslander/assets/img/gallery/gallery-2.jpg') }}" alt="" class="img-fluid">
          </a>
        </div>
      </div>

      <div class="col-lg-3 col-md-4">
        <div class="gallery-item">
          <a href="{{ asset('bootslander/assets/img/gallery/gallery-3.jpg') }}" class="glightbox" data-gallery="images-gallery">
            <img src="{{ asset('bootslander/assets/img/gallery/gallery-3.jpg') }}" alt="" class="img-fluid">
          </a>
        </div>
      </div>

      <div class="col-lg-3 col-md-4">
        <div class="gallery-item">
          <a href="{{ asset('bootslander/assets/img/gallery/gallery-4.jpg') }}" class="glightbox" data-gallery="images-gallery">
            <img src="{{ asset('bootslander/assets/img/gallery/gallery-4.jpg') }}" alt="" class="img-fluid">
          </a>
        </div>
      </div>

      <div class="col-lg-3 col-md-4">
        <div class="gallery-item">
          <a href="{{ asset('bootslander/assets/img/gallery/gallery-5.jpg') }}" class="glightbox" data-gallery="images-gallery">
            <img src="{{ asset('bootslander/assets/img/gallery/gallery-5.jpg') }}" alt="" class="img-fluid">
          </a>
        </div>
      </div>

      <div class="col-lg-3 col-md-4">
        <div class="gallery-item">
          <a href="{{ asset('bootslander/assets/img/gallery/gallery-6.jpg') }}" class="glightbox" data-gallery="images-gallery">
            <img src="{{ asset('bootslander/assets/img/gallery/gallery-6.jpg') }}" alt="" class="img-fluid">
          </a>
        </div>
      </div>

      <div class="col-lg-3 col-md-4">
        <div class="gallery-item">
          <a href="{{ asset('bootslander/assets/img/gallery/gallery-7.jpg') }}" class="glightbox" data-gallery="images-gallery">
            <img src="{{ asset('bootslander/assets/img/gallery/gallery-7.jpg') }}" alt="" class="img-fluid">
          </a>
        </div>
      </div>

      <div class="col-lg-3 col-md-4">
        <div class="gallery-item">
          <a href="{{ asset('bootslander/assets/img/gallery/gallery-8.jpg') }}" class="glightbox" data-gallery="images-gallery">
            <img src="{{ asset('bootslander/assets/img/gallery/gallery-8.jpg') }}" alt="" class="img-fluid">
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Maps Section -->
<section id="maps" class="maps section">
  <div class="container section-title" data-aos="fade-up">
    <h2>Lokasi</h2>
    <div><span>Kantor</span> <span class="description-title">Komdigi Medan</span></div>
  </div>

  <div class="container" data-aos="fade-up" data-aos-delay="100">
    <div class="row gy-4">
      <div class="col-lg-6" data-aos="fade-right">
        <div class="map-info">
          <h3>Alamat Kantor</h3>
          <p>
            <i class="bi bi-geo-alt-fill"></i> 
            Jl. Gatot Subroto No.4, Simpang Selayang, Kec. Medan Tuntungan, Kota Medan, Sumatera Utara 20134
          </p>
          
          <h3 class="mt-4">Kontak</h3>
          <p>
            <i class="bi bi-telephone-fill"></i> 
            (061) 4535234
          </p>
          <p>
            <i class="bi bi-envelope-fill"></i> 
            info@komdigigroup.co.id
          </p>
          
          <h3 class="mt-4">Jam Operasional</h3>
          <p>
            <i class="bi bi-clock-fill"></i> 
            Senin - Jumat: 08:00 - 17:00 WIB
          </p>
          <p>
            <i class="bi bi-clock-fill"></i> 
            Sabtu: 08:00 - 12:00 WIB
          </p>
        </div>
      </div>
      
      <div class="col-lg-6" data-aos="fade-left">
        <div class="map-container">
          <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d31872.77835169364!2d98.68024631564293!3d3.596995099999982!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x303131a7e6f90d93%3A0x1a3b6b9b5e6c7d8e!2sKantor%20Komdigi%20Medan!5e0!3m2!1sen!2sid!4v1620000000000!5m2!1sen!2sid" 
            width="100%" 
            height="400" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy">
          </iframe>
        </div>
      </div>
    </div>
  </div>
</section>

</main>

<footer id="footer" class="footer dark-background">
  <div class="container copyright text-center">
    <p>© <strong class="px-1 sitename">Pendaftaran Magang</strong> All Rights Reserved</p>
  </div>
</footer>

<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
<div id="preloader"></div>

<script src="{{ asset('bootslander/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('bootslander/assets/vendor/aos/aos.js') }}"></script>
<script src="{{ asset('bootslander/assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
<script src="{{ asset('bootslander/assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
<script src="{{ asset('Bootslander/assets/js/main.js') }}"></script>

<script>
  // Header scroll effect
  window.addEventListener('scroll', function() {
    const header = document.getElementById('header');
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  });
</script>

</body>
</html>