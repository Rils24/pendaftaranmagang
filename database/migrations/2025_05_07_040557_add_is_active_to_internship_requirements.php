<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Jika kolom is_active belum ada
        if (!Schema::hasColumn('internship_requirements', 'is_active')) {
            Schema::table('internship_requirements', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('location');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('internship_requirements', 'is_active')) {
            Schema::table('internship_requirements', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};