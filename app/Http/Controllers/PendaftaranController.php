<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\PendaftaranMagang;
use App\Models\AnggotaMagang;
use App\Models\Setting;
use App\Models\Universitas;
use Illuminate\Support\Facades\Crypt;

class PendaftaranController extends Controller
{
    // Menampilkan form pendaftaran atau status pendaftaran
    public function create()
    {
        $user = Auth::user();

        // OPTIMIZED: Cache daftar universitas selama 1 jam
        $universitas = \Illuminate\Support\Facades\Cache::remember('universitas_list', 3600, function() {
            return Universitas::select('id', 'nama_universitas')
                ->orderBy('nama_universitas')
                ->get()
                ->map(function($univ) {
                    $univ->nama_universitas = trim(preg_replace('/[\r\n\t]+/', '', $univ->nama_universitas));
                    return $univ;
                });
        });

        // OPTIMIZED: Gunakan select untuk mengambil kolom yang diperlukan saja
        $pendaftaran = PendaftaranMagang::with('anggota:id,pendaftaran_id,nama_anggota,nim_anggota,jurusan,email_anggota,no_hp_anggota')
                        ->select('id', 'user_id', 'asal_kampus', 'jurusan', 'tanggal_mulai', 'tanggal_selesai', 'surat_pengantar', 'status', 'alasan_penolakan', 'created_at')
                        ->where('user_id', $user->id)
                        ->latest()
                        ->first();

        return view('user.pendaftaran', compact('pendaftaran', 'universitas'));
    }

    // Menyimpan data pendaftaran baru
    public function store(Request $request)
    {
        // Tambahkan logging untuk debug
        Log::info('Data jurusan dalam request:', ['jurusan' => $request->jurusan ?? 'Tidak ada data jurusan']);
        
        // OPTIMIZED: Gunakan cached setting
        $setting = Setting::getCached();
        if (!$setting || !$setting->status_pendaftaran) {
            return redirect()->back()->with('error', 'Pendaftaran saat ini sedang ditutup.');
        }

        // Cek status pendaftaran terakhir user
        $pendaftaranLama = PendaftaranMagang::where('user_id', Auth::id())->latest()->first();

        if ($pendaftaranLama) {
            if ($pendaftaranLama->status == 'pending') {
                return redirect()->back()->with('error', 'Anda sudah mendaftar dan masih dalam status pending.');
            } elseif ($pendaftaranLama->status == 'diterima') {
                return redirect()->back()->with('error', 'Anda sudah diterima, tidak bisa mendaftar lagi.');
            } elseif ($pendaftaranLama->status == 'ditolak') {
                // Hapus pendaftaran lama jika user mencoba mendaftar ulang
                $pendaftaranLama->delete();
            }
        }

        // Validasi input
        $request->validate([
            'asal_kampus' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'surat_pengantar' => 'required|file|mimes:pdf|max:2048',
            'nama_anggota.*' => 'nullable|string|max:255',
            'nim_anggota.*' => 'nullable|string|max:50',
            'jurusan.*' => 'required|string|max:255', // Jurusan wajib diisi
            'email_anggota.*' => 'nullable|email|max:255',
            'no_hp_anggota.*' => 'nullable|string|min:10|max:15',
        ]);

        // Bersihkan input asal kampus
        $kampus = trim(preg_replace('/[\r\n\t\s]+/', ' ', $request->input('asal_kampus')));
        
        // Cek apakah asal kampus perlu disimpan ke database
        $existingUniversitas = Universitas::whereRaw('LOWER(nama_universitas) = ?', [strtolower($kampus)])->first();
        
        if (!$existingUniversitas) {
            // Simpan asal kampus baru ke database
            Universitas::create(['nama_universitas' => $kampus]);
        }

        // Upload file surat pengantar
        $suratPath = $request->file('surat_pengantar')->store('surat_pengantar', 'public');

        // Simpan data pendaftaran utama
        $pendaftaran = PendaftaranMagang::create([
            'user_id' => Auth::id(),
            'asal_kampus' => $kampus,
            'jurusan' => 'Lihat di data anggota', // Placeholder jurusan
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'surat_pengantar' => $suratPath,
            'status' => 'pending',
        ]);

        // Simpan data anggota jika ada
        if ($request->has('nama_anggota')) {
            $anggotaCount = 0;
            
            foreach ($request->nama_anggota as $key => $nama) {
                if (!empty(trim($nama))) { // Pastikan tidak menyimpan input kosong
                    // Log untuk debug
                    Log::info('Menyimpan data anggota:', [
                        'nama' => trim($nama),
                        'nim' => $request->nim_anggota[$key] ?? null,
                        'jurusan' => $request->jurusan[$key] ?? null,
                        'email' => $request->email_anggota[$key] ?? null,
                        'no_hp' => $request->no_hp_anggota[$key] ?? null
                    ]);
                    
                    $anggota = AnggotaMagang::create([
                        'pendaftaran_id' => $pendaftaran->id,
                        'nama_anggota' => trim($nama),
                        'nim_anggota' => $request->nim_anggota[$key] ?? null,
                        'jurusan' => $request->jurusan[$key], // Pastikan jurusan disimpan
                        'email_anggota' => $request->email_anggota[$key] ?? null,
                        'no_hp_anggota' => $request->no_hp_anggota[$key] ?? null,
                    ]);
                    $anggotaCount++;
                }
            }

            // Log jumlah anggota
            Log::info('Pendaftaran ID: ' . $pendaftaran->id . ' - Jumlah anggota: ' . $anggotaCount);
        }

        return redirect()->route('pendaftaran')->with('success', 'Pendaftaran berhasil dikirim!');
    }

    // Membatalkan pendaftaran
    public function batalkanPendaftaran($id)
    {
        try {
            $pendaftaran = PendaftaranMagang::findOrFail($id);
            
            // Periksa apakah pendaftaran milik user yang sedang login
            if ($pendaftaran->user_id != Auth::id()) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk membatalkan pendaftaran ini.');
            }
            
            // Periksa apakah status pendaftaran masih pending
            if ($pendaftaran->status != 'pending') {
                return redirect()->back()->with('error', 'Hanya pendaftaran dengan status menunggu yang dapat dibatalkan.');
            }
            
            // Hapus pendaftaran
            $pendaftaran->delete();
            
            return redirect()->route('pendaftaran')->with('success', 'Pendaftaran magang berhasil dibatalkan.');
        } catch (\Exception $e) {
            Log::error('Error saat membatalkan pendaftaran: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat membatalkan pendaftaran.');
        }
    }

    // Method untuk menghapus pendaftaran yang ditolak dan redirect ke form baru
    public function hapusPendaftaran($id)
    {
        try {
            $pendaftaran = PendaftaranMagang::findOrFail($id);
            
            // Periksa apakah pendaftaran milik user yang sedang login
            if ($pendaftaran->user_id != Auth::id()) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menghapus pendaftaran ini.');
            }
            
            // Periksa apakah status pendaftaran ditolak
            if ($pendaftaran->status != 'ditolak') {
                return redirect()->back()->with('error', 'Hanya pendaftaran dengan status ditolak yang dapat dihapus.');
            }
            
            // Hapus pendaftaran
            $pendaftaran->delete();
            
            // Redirect ke halaman form pendaftaran baru
            return redirect()->route('pendaftaran')->with('success', 'Data pendaftaran yang ditolak berhasil dihapus. Silakan melengkapi form pendaftaran baru.');
        } catch (\Exception $e) {
            Log::error('Error saat menghapus pendaftaran: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus pendaftaran.');
        }
    }

    // Mencetak bukti pendaftaran
    public function cetakBukti($id)
    {
        try {
            $pendaftaran = PendaftaranMagang::with('anggota')->findOrFail($id);
            
            // Periksa apakah pendaftaran milik user yang sedang login
            if ($pendaftaran->user_id != Auth::id()) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mencetak bukti pendaftaran ini.');
            }
            
            return view('user.cetak-bukti', compact('pendaftaran'));
        } catch (\Exception $e) {
            Log::error('Error saat mencetak bukti: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mencetak bukti pendaftaran.');
        }
    }

    // Melihat PDF
    public function viewPdf($encryptedId)
    {
        try {
            $id = Crypt::decryptString($encryptedId);
            $pendaftaran = PendaftaranMagang::findOrFail($id);
            
            // Pastikan hanya pemilik pendaftaran yang bisa melihat
            if ($pendaftaran->user_id != Auth::id()) {
                abort(403, 'Unauthorized access');
            }
        
            $path = storage_path('app/public/' . $pendaftaran->surat_pengantar);
            
            // Periksa apakah file ada
            if (!file_exists($path)) {
                abort(404, 'File not found');
            }
        
            return response()->file($path, [
                'Content-Type' => 'application/pdf'
            ]);
        } catch (\Exception $e) {
            Log::error('Error saat melihat PDF: ' . $e->getMessage());
            abort(404, 'File tidak ditemukan atau tidak dapat diakses');
        }
    }

    // Mencetak data pendaftaran (untuk admin)
    public function print($id)
    {
        try {
            $pendaftaran = PendaftaranMagang::with('anggota', 'user')->findOrFail($id);
            
            // Tidak perlu pengecekan user_id karena ini diakses oleh admin
            // Tapi bisa ditambahkan pengecekan role admin jika diperlukan
            // if (!Auth::user()->isAdmin()) {
            //     return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mencetak data pendaftaran ini.');
            // }
            
            return view('admin.cetak-pendaftaran', compact('pendaftaran'));
        } catch (\Exception $e) {
            Log::error('Error saat mencetak data: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mencetak data pendaftaran.');
        }
    }
}