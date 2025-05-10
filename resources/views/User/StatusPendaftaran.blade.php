<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pendaftaran Magang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white text-center">
                <h3>Status Pendaftaran Magang</h3>
            </div>
            <div class="card-body text-center">
                @if($pendaftaran)
                    <p><strong>Asal Kampus/Sekolah:</strong> {{ $pendaftaran->asal_kampus }}</p>
                    <p><strong>Jurusan:</strong> {{ $pendaftaran->jurusan }}</p>
                    <p><strong>Tanggal Mulai:</strong> {{ $pendaftaran->tanggal_mulai }}</p>
                    <p><strong>Tanggal Selesai:</strong> {{ $pendaftaran->tanggal_selesai }}</p>
                    <p><strong>Surat Pengantar:</strong> <a href="{{ asset('storage/' . $pendaftaran->surat_pengantar) }}" target="_blank">Lihat File</a></p>

                    <h4>Status Pendaftaran:</h4>
                    @if($pendaftaran->status == 'pending')
                        <span class="badge bg-warning">Menunggu Validasi</span>
                    @elseif($pendaftaran->status == 'diterima')
                        <span class="badge bg-success">Diterima</span>
                        <p class="text-success"><i class="fas fa-check-circle"></i> Selamat! Pendaftaran Anda telah diterima.</p>
                    @elseif($pendaftaran->status == 'ditolak')
                        <span class="badge bg-danger">Ditolak</span>
                        <p class="text-danger"><i class="fas fa-times-circle"></i> Pendaftaran Anda ditolak.</p>
                        <p><strong>Alasan:</strong> {{ $pendaftaran->alasan_penolakan }}</p>
                    @endif
                @else
                    <p class="text-muted">Anda belum mengajukan pendaftaran.</p>
                    <a href="/" class="btn btn-primary">Ajukan Pendaftaran</a>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
