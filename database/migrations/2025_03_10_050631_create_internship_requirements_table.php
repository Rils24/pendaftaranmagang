<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('internship_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('documents'); // CV, Cover Letter, dll
            $table->date('deadline'); // Tanggal batas pendaftaran
            $table->integer('quota'); // Kuota peserta
            $table->string('period'); // Periode magang
            $table->string('location'); // Lokasi magang
            $table->text('additional_info')->nullable(); // Info tambahan
            $table->boolean('is_active')->default(true); // Tambahkan kolom is_active
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_requirements');
    }
};