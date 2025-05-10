<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel; // Tambahkan ini

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'otp_code',
        'otp_expires_at',
        'is_verified',
        'role', // Tambahkan role agar bisa bedakan admin dan user
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    // ✅ Perbaiki metode yang benar untuk Filament v3
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin'; // Hanya admin yang bisa masuk ke Filament
    }
}
