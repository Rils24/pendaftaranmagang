<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}