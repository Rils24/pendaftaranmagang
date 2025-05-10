<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Exception;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);

            // Generate OTP
            $otp = random_int(100000, 999999);

            // Simpan data ke session
            $registerData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'otp' => $otp,
            ];
            Session::put('register_data', $registerData);

            // Kirim OTP ke email
            Mail::to($request->email)->send(new OtpMail($otp, $request->email, $request->name));

            return redirect()->route('otp.verify.form')->with('success', 'Kode OTP telah dikirim ke email Anda.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function showOtpVerificationForm()
    {
        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric',
        ]);

        $registerData = Session::get('register_data');

        if (!$registerData) {
            return redirect()->route('register')->with('error', 'Session OTP tidak ditemukan.');
        }

        if ($request->otp != $registerData['otp']) {
            return redirect()->back()->with('error', 'Kode OTP salah.');
        }

        // Simpan user ke database setelah OTP benar
        $user = User::create([
            'name' => $registerData['name'],
            'email' => $registerData['email'],
            'password' => $registerData['password'],
            'email_verified_at' => now(),
            'is_verified' => 1,
        ]);

        // Hapus session OTP setelah registrasi selesai
        Session::forget('register_data');

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
    }

    public function resendOtp()
    {
        $registerData = Session::get('register_data');

        if (!$registerData) {
            return redirect()->route('register')->with('error', 'Session OTP tidak ditemukan. Silakan daftar ulang.');
        }

        try {
            // Generate OTP baru
            $newOtp = random_int(100000, 999999);
            $registerData['otp'] = $newOtp;

            // Simpan OTP baru ke session
            Session::put('register_data', $registerData);

            // Kirim ulang OTP ke email
            Mail::to($registerData['email'])->send(new OtpMail($newOtp, $registerData['email'], $registerData['name']));

            return redirect()->back()->with('success', 'Kode OTP baru telah dikirim ke email Anda.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengirim ulang OTP: ' . $e->getMessage());
        }
    }
}
