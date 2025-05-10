<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Menampilkan form login.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Mengautentikasi pengguna dan menangani login.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Cari pengguna berdasarkan email
        $user = User::where('email', $credentials['email'])->first();

        // Jika pengguna tidak ditemukan
        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak terdaftar.'])->withInput();
        }

        // Coba login
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Cek verifikasi
            if (!$user->is_verified) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun belum diverifikasi.'])->withInput();
            }

            // Log kegiatan login
            Log::info('Successful login', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'ip' => $request->ip()
            ]);

            // Redirect berdasarkan peran
            if ($user->role === 'admin') {
                return redirect('/admin')->with('success', 'Selamat datang, Admin!');
            }

            // Untuk user biasa
            return redirect()->route('user.pendaftaran')->with('success', 'Login berhasil!');
        }

        // Login gagal
        return back()->withErrors(['password' => 'Password yang Anda masukkan salah.'])->withInput();
    }

    /**
     * Logout pengguna.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Log kegiatan logout
        $user = Auth::user();
        if ($user) {
            Log::info('User logged out', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);
        }

        // Proses logout
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda berhasil logout.');
    }
}