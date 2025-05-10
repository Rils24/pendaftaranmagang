<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Jalankan migrasi database.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('otp_code')->nullable(); // Kode OTP untuk verifikasi email
            $table->timestamp('otp_expires_at')->nullable(); // Waktu kadaluarsa OTP
            $table->boolean('is_verified')->default(false); // Status verifikasi akun
            $table->string('role')->default('user'); // Tambahkan role (admin/user)
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // ✅ Insert admin default
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'), // Ubah password sesuai kebutuhan
            'role' => 'admin', // Menandakan ini adalah admin
            'is_verified' => true, // Anggap admin sudah diverifikasi
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Rollback migrasi database.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
