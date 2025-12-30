<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_pendaftaran',
        'max_anggota',
        'require_surat_pengantar',
        'admin_email',
        'send_admin_notifications',
        'send_user_notifications',
        'site_title',
        'tagline',
        'logo',
        'primary_color',
        'secondary_color',
        'font_family',
        'welcome_message',
        'faq_content',
        'contact_info',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'mail_from_address',
        'storage_driver',
        'max_upload_size',
    ];

    protected $casts = [
        'status_pendaftaran' => 'boolean',
        'require_surat_pengantar' => 'boolean',
        'send_admin_notifications' => 'boolean',
        'send_user_notifications' => 'boolean',
        'max_anggota' => 'integer',
        'max_upload_size' => 'integer',
        'smtp_port' => 'integer',
    ];
    
    /**
     * Cache key for settings
     */
    protected static string $cacheKey = 'app_settings';
    protected static int $cacheTTL = 3600; // 1 hour
    
    /**
     * Get cached settings instance
     * This prevents multiple database queries for the same settings
     */
    public static function getCached(): ?self
    {
        return Cache::remember(static::$cacheKey, static::$cacheTTL, function() {
            return static::first();
        });
    }
    
    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget(static::$cacheKey);
    }
    
    /**
     * Boot method to auto-clear cache on update
     */
    protected static function booted(): void
    {
        static::saved(function () {
            static::clearCache();
        });
        
        static::deleted(function () {
            static::clearCache();
        });
    }
}