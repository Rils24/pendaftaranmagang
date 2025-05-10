<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class InternshipRequirement extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'documents',
        'deadline',
        'quota',
        'period',
        'location',
        'is_active',
        'additional_info',
    ];
    
    protected $casts = [
        'deadline' => 'date',
        'quota' => 'integer',
        'is_active' => 'boolean',
    ];
    
    // Accessor untuk membersihkan dokumen dari tag <p>
    public function getDocumentsAttribute($value)
    {
        return $this->stripPTags($value);
    }
    
    // Accessor untuk membersihkan info tambahan dari tag <p>
    public function getAdditionalInfoAttribute($value)
    {
        return $this->stripPTags($value);
    }
    
    // Metode pembantu untuk menghapus tag <p>
    protected function stripPTags($value)
    {
        if (empty($value)) {
            return $value;
        }
        
        // Hapus tag <p> dengan konten di dalamnya
        $value = preg_replace('/<\/?p>/', '', $value);
        
        // Hapus tag <p> kosong atau hanya berisi spasi
        $value = preg_replace('/^\s*<\/?p>\s*$/', '', $value);
        
        return trim($value);
    }
    
    // Relasi dengan pendaftaran magang
    public function pendaftaranMagang(): HasMany
    {
        return $this->hasMany(PendaftaranMagang::class, 'requirement_id');
    }
    
    // Mendapatkan jumlah pendaftar yang diterima
    public function getAcceptedCountAttribute(): int
    {
        return $this->pendaftaranMagang()->where('status', 'diterima')->count();
    }
    
    // Mendapatkan sisa kuota yang tersedia
    public function getAvailableQuotaAttribute(): int
    {
        return $this->quota - $this->accepted_count;
    }
    
    // Memeriksa apakah periode sudah kedaluwarsa
    public function getIsExpiredAttribute(): bool
    {
        return $this->deadline < now();
    }
    
    // Memeriksa apakah periode magang saat ini aktif berdasarkan waktu dunia nyata
    public function isCurrentlyActive(): bool
    {
        // Jika flag is_active false atau deadline sudah lewat, maka tidak aktif
        return $this->is_active && !$this->is_expired;
    }
    
    // Scope untuk mendapatkan hanya persyaratan yang benar-benar aktif saat ini
    public function scopeCurrentlyActive($query)
    {
        return $query->where('is_active', true)
                     ->whereDate('deadline', '>=', now());
    }
    
    // Scope untuk mendapatkan hanya persyaratan aktif dan belum kedaluwarsa (tetap dipertahankan)
    public function scopeActiveAndNotExpired($query)
    {
        return $query->where('is_active', true)
                     ->whereDate('deadline', '>=', now());
    }
}