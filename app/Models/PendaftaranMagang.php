<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Mail\PendaftaranMagangMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class PendaftaranMagang extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'requirement_id',
        'asal_kampus',
        'jurusan',
        'tanggal_mulai',
        'tanggal_selesai',
        'surat_pengantar',
        'status',
        'alasan_penolakan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];
    
    protected $appends = [
        'durasi_magang',
        'durasi_minggu', 
        'durasi_bulan',
        'status_label',
        'periode_info'
    ];

    /**
     * Get the user that owns the pendaftaran
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the internship requirement associated with this pendaftaran
     */
    public function requirement(): BelongsTo
    {
        return $this->belongsTo(InternshipRequirement::class, 'requirement_id');
    }

    /**
     * Get the anggota for this pendaftaran
     */
    public function anggota(): HasMany
    {
        return $this->hasMany(AnggotaMagang::class, 'pendaftaran_id');
    }
    
    /**
     * Menghitung durasi magang dalam hari
     */
    public function getDurasiMagangAttribute(): int
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
            return 0;
        }
        
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }
    
    /**
     * Menghitung durasi magang dalam minggu
     */
    public function getDurasiMingguAttribute(): int
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
            return 0;
        }
        
        return ceil($this->durasi_magang / 7);
    }
    
    /**
     * Menghitung durasi magang dalam bulan
     */
    public function getDurasiBulanAttribute(): float
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
            return 0;
        }
        
        return round($this->durasi_magang / 30, 1);
    }
    
    /**
     * Mendapatkan label status yang lebih user-friendly
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'diterima' => 'Disetujui',
            'ditolak' => 'Ditolak',
            default => ucfirst($this->status)
        };
    }
    
    /**
     * Mendapatkan informasi tentang periode magang terkait
     */
    public function getPeriodeInfoAttribute(): ?array
    {
        $periode = InternshipRequirement::where('deadline', '>=', $this->created_at)
            ->where('created_at', '<=', $this->created_at)
            ->orderBy('deadline', 'asc')
            ->first();
            
        if (!$periode) {
            return null;
        }
        
        $acceptedCount = static::where('status', 'diterima')
            ->where('created_at', '>=', $periode->created_at)
            ->where('created_at', '<=', $periode->deadline)
            ->count();
            
        $remainingQuota = $periode->quota - $acceptedCount;
        $percentFilled = $periode->quota > 0 ? round(($acceptedCount / $periode->quota) * 100) : 0;
        $isActive = $periode->isCurrentlyActive();
        $daysLeft = now()->diffInDays($periode->deadline, false);
        
        return [
            'id' => $periode->id,
            'period' => $periode->period,
            'quota' => $periode->quota,
            'filled' => $acceptedCount,
            'remaining' => $remainingQuota,
            'percent_filled' => $percentFilled,
            'is_active' => $isActive,
            'deadline' => $periode->deadline,
            'days_left' => $daysLeft,
            'quota_status' => $remainingQuota <= 0 ? 'full' : ($remainingQuota <= 3 ? 'critical' : 'available'),
        ];
    }
    
    /**
     * Kirim notifikasi status pendaftaran
     */
    public function sendStatusNotification(string $status, ?string $alasan = null): void
    {
        if (!$this->user || !$this->user->email) {
            return;
        }
        
        // Kirim email ke pendaftar
        Mail::to($this->user->email)->send(new PendaftaranMagangMail($status, $alasan));
        
        // Kirim ke admin jika pengaturan diaktifkan
        $setting = Setting::first();
        if ($setting && $setting->send_admin_notifications && $setting->admin_email) {
            Mail::to($setting->admin_email)->send(new PendaftaranMagangMail(
                $status, 
                $alasan, 
                true, 
                $this->id
            ));
        }
        
        // Kirim notifikasi ke semua anggota tim jika diaktifkan
        if ($setting && $setting->notify_team_members) {
            foreach ($this->anggota as $anggota) {
                if ($anggota->email_anggota) {
                    Mail::to($anggota->email_anggota)->send(new PendaftaranMagangMail(
                        $status,
                        $alasan,
                        false,
                        $this->id,
                        true
                    ));
                }
            }
        }
        
        // Log aktivitas notifikasi
        // Ini bisa diimplementasikan di masa depan jika dibutuhkan
    }
    
    /**
     * Cek apakah pendaftaran ini masih dalam periode aktif
     */
    public function isInActivePeriod(): bool
    {
        $periodeInfo = $this->periode_info;
        return $periodeInfo ? $periodeInfo['is_active'] : false;
    }
    
    /**
     * Cek apakah masih ada kuota tersedia untuk periode ini
     */
    public function hasAvailableQuota(): bool
    {
        $periodeInfo = $this->periode_info;
        return $periodeInfo ? $periodeInfo['remaining'] > 0 : false;
    }
    
    /**
     * Hitung berapa lama pendaftaran sudah menunggu (dalam hari)
     */
    public function getWaitingTimeInDays(): int
    {
        if ($this->status !== 'pending') {
            return 0;
        }
        
        return $this->created_at->diffInDays(now());
    }
    
    /**
     * Mendapatkan pendaftaran yang statusnya pending
     */
    public function scopePending($query): Builder
    {
        return $query->where('status', 'pending');
    }
    
    /**
     * Mendapatkan pendaftaran yang diterima
     */
    public function scopeDiterima($query): Builder
    {
        return $query->where('status', 'diterima');
    }
    
    /**
     * Mendapatkan pendaftaran yang ditolak
     */
    public function scopeDitolak($query): Builder
    {
        return $query->where('status', 'ditolak');
    }
    
    /**
     * Mendapatkan pendaftaran berdasarkan periode tertentu
     */
    public function scopeByPeriode($query, $periodeId): Builder
    {
        $periode = InternshipRequirement::find($periodeId);
        
        if (!$periode) {
            return $query->whereNull('id'); // Will return empty result
        }
        
        return $query
            ->where('created_at', '>=', $periode->created_at)
            ->where('created_at', '<=', $periode->deadline);
    }
    
    /**
     * Mendapatkan pendaftaran yang lama menunggu (lebih dari 7 hari)
     */
    public function scopeLongWaiting($query, int $days = 7): Builder
    {
        return $query
            ->where('status', 'pending')
            ->where('created_at', '<=', now()->subDays($days));
    }
    
    /**
     * Mendapatkan pendaftaran berdasarkan durasi magang
     */
    public function scopeByDurasiMagang($query, int $minDays = null, int $maxDays = null): Builder
    {
        return $query
            ->when($minDays, function ($query) use ($minDays) {
                return $query->whereRaw('DATEDIFF(tanggal_selesai, tanggal_mulai) + 1 >= ?', [$minDays]);
            })
            ->when($maxDays, function ($query) use ($maxDays) {
                return $query->whereRaw('DATEDIFF(tanggal_selesai, tanggal_mulai) + 1 <= ?', [$maxDays]);
            });
    }
    
    /**
     * Mendapatkan pendaftaran dari kampus tertentu
     */
    public function scopeFromCampus($query, $kampus): Builder
    {
        return $query->where('asal_kampus', 'like', "%{$kampus}%");
    }
    
    /**
     * Mendapatkan pendaftaran saat ini (tiga bulan terakhir)
     */
    public function scopeRecent($query, int $months = 3): Builder
    {
        return $query->whereDate('created_at', '>=', now()->subMonths($months));
    }
    
    /**
     * Mendapatkan pendaftaran aktif (sedang dalam masa magang berdasarkan tanggal)
     */
    public function scopeActive($query): Builder
    {
        $today = Carbon::today();
        
        return $query
            ->where('status', 'diterima')
            ->where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today);
    }
    
    /**
     * Mendapatkan pendaftaran mendatang (belum mulai magang)
     */
    public function scopeUpcoming($query): Builder
    {
        $today = Carbon::today();
        
        return $query
            ->where('status', 'diterima')
            ->where('tanggal_mulai', '>', $today);
    }
    
    /**
     * Mendapatkan pendaftaran yang masa magangnya telah selesai
     */
    public function scopeCompleted($query): Builder
    {
        $today = Carbon::today();
        
        return $query
            ->where('status', 'diterima')
            ->where('tanggal_selesai', '<', $today);
    }
    
    /**
     * Menagkap peristiwa saat model ini disimpan
     */
    protected static function booted()
    {
        static::saved(function ($pendaftaran) {
            // Jika status berubah menjadi diterima, cek apakah perlu melakukan log
            if ($pendaftaran->isDirty('status') && $pendaftaran->status === 'diterima') {
                // Disini bisa ditambahkan fitur pencatatan kapan status berubah
                // atau fitur lain yang perlu dijalankan saat status berubah
            }
        });
    }
}