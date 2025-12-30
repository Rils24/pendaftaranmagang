<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adding indexes to improve query performance
     */
    public function up(): void
    {
        // Add indexes to pendaftaran_magangs table
        Schema::table('pendaftaran_magangs', function (Blueprint $table) {
            // Index for status filter (most used)
            $table->index('status', 'idx_pendaftaran_status');
            
            // Index for asal_kampus filter
            $table->index('asal_kampus', 'idx_pendaftaran_kampus');
            
            // Index for date range queries
            $table->index('created_at', 'idx_pendaftaran_created');
            $table->index('tanggal_mulai', 'idx_pendaftaran_mulai');
            $table->index('tanggal_selesai', 'idx_pendaftaran_selesai');
            
            // Composite index for common filter combinations
            $table->index(['status', 'created_at'], 'idx_pendaftaran_status_created');
            $table->index(['user_id', 'status'], 'idx_pendaftaran_user_status');
        });

        // Add indexes to users table if not exists
        Schema::table('users', function (Blueprint $table) {
            $table->index('email', 'idx_users_email');
            $table->index('role', 'idx_users_role');
        });

        // Add indexes to internship_requirements table
        Schema::table('internship_requirements', function (Blueprint $table) {
            $table->index('is_active', 'idx_requirements_active');
            $table->index('deadline', 'idx_requirements_deadline');
            $table->index(['is_active', 'deadline'], 'idx_requirements_active_deadline');
        });

        // Add indexes to anggota_pendaftaran table
        Schema::table('anggota_pendaftaran', function (Blueprint $table) {
            $table->index('pendaftaran_id', 'idx_anggota_pendaftaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftaran_magangs', function (Blueprint $table) {
            $table->dropIndex('idx_pendaftaran_status');
            $table->dropIndex('idx_pendaftaran_kampus');
            $table->dropIndex('idx_pendaftaran_created');
            $table->dropIndex('idx_pendaftaran_mulai');
            $table->dropIndex('idx_pendaftaran_selesai');
            $table->dropIndex('idx_pendaftaran_status_created');
            $table->dropIndex('idx_pendaftaran_user_status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_role');
        });

        Schema::table('internship_requirements', function (Blueprint $table) {
            $table->dropIndex('idx_requirements_active');
            $table->dropIndex('idx_requirements_deadline');
            $table->dropIndex('idx_requirements_active_deadline');
        });

        Schema::table('anggota_pendaftaran', function (Blueprint $table) {
            $table->dropIndex('idx_anggota_pendaftaran');
        });
    }
};
