<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $user;

    public function __construct($otp, $user)
    {
        $this->otp = $otp;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Kode OTP Verifikasi')
                    ->view('emails.otp-mail')
                    ->with([
                        'otp' => $this->otp,
                        'user' => $this->user // Pastikan user dikirim ke view
                    ]);
    }
}
