<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnggotaMagang extends Model
{
    use HasFactory;

    protected $table = 'anggota_pendaftaran';

    protected $fillable = [
        'pendaftaran_id',
        'nama_anggota',
        'nim_anggota',
        'jurusan',
        'email_anggota',
        'no_hp_anggota',
    ];

    public function anggota()
    {
        return $this->belongsTo(PendaftaranMagang::class, 'pendaftaran_id');
    }
}