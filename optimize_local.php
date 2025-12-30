<?php

/**
 * Performance Optimization Script
 * 
 * Jalankan script ini untuk mengoptimalkan performa aplikasi di local development:
 * php artisan tinker --execute="require 'optimize_local.php';"
 * 
 * Atau langsung jalankan: php optimize_local.php
 */

echo "=== Optimisasi Performa untuk Local Development ===\n\n";

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    die("Script ini harus dijalankan dari command line.\n");
}

// Load Laravel bootstrap
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

echo "1. Membersihkan cache...\n";
Artisan::call('cache:clear');
echo "   ✓ Cache cleared\n";

Artisan::call('config:clear');
echo "   ✓ Config cache cleared\n";

Artisan::call('route:clear');
echo "   ✓ Route cache cleared\n";

Artisan::call('view:clear');
echo "   ✓ View cache cleared\n";

echo "\n2. Membersihkan cache widget...\n";
// Clear widget cache
$cachesToClear = [
    'magang_overview_stats',
    'magang_overview_status',
    'magang_overview_requirement',
    'magang_overview_monthly',
    'magang_overview_weekly',
    'magang_overview_today',
    'magang_overview_activity',
];

foreach ($cachesToClear as $cacheKey) {
    Cache::forget($cacheKey);
}
echo "   ✓ Widget cache cleared\n";

echo "\n3. Memeriksa koneksi database...\n";
try {
    $pdo = DB::connection()->getPdo();
    echo "   ✓ Database connected: " . DB::connection()->getDatabaseName() . "\n";
} catch (\Exception $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n4. Memeriksa index database...\n";
try {
    $indexes = DB::select("SHOW INDEX FROM pendaftaran_magangs");
    echo "   ✓ Found " . count($indexes) . " indexes on pendaftaran_magangs table\n";
} catch (\Exception $e) {
    echo "   ⚠ Could not check indexes: " . $e->getMessage() . "\n";
}

echo "\n=== Tips Optimisasi Tambahan ===\n";
echo "
Untuk performa maksimal di local, update file .env Anda:

1. Ganti driver cache dan session dari 'database' ke 'file':
   CACHE_STORE=file
   SESSION_DRIVER=file

2. Atau gunakan 'array' untuk testing tercepat (tidak persist):
   CACHE_STORE=array
   SESSION_DRIVER=file

3. Untuk mode development, aktifkan debug:
   APP_DEBUG=true
   
4. Setelah mengubah .env, jalankan:
   php artisan config:clear

";

echo "\n=== Selesai! ===\n";
echo "Aplikasi Anda seharusnya lebih cepat sekarang.\n";
echo "Restart server dengan: php artisan serve\n";
