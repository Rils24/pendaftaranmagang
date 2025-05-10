<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function showOtpForm()
    {
        return view('auth.otp-verify');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)
                    ->where('otp_code', $request->otp_code)
                    ->where('otp_expires_at', '>=', now())
                    ->first();

        if ($user) {
            $user->update([
                'is_verified' => true,
                'otp_code' => null,
                'otp_expires_at' => null,
            ]);

            return redirect()->route('login')->with('success', 'Akun Anda telah berhasil diverifikasi.');
        }

        return back()->withErrors(['otp_code' => 'Kode OTP tidak valid atau sudah kedaluwarsa.']);
    }
}
