<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PendaftaranMagangMail extends Mailable
{
    use Queueable, SerializesModels;

    public $status;
    public $alasan;

    public function __construct($status, $alasan = null)
    {
        $this->status = $status;
        $this->alasan = $alasan;
    }

    public function build()
    {
        return $this->subject("Status Pendaftaran Magang")
                    ->view('emails.pendaftaran-magang');
    }
}
