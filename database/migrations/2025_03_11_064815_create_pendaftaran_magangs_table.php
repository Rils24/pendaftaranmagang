<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pendaftaran_magangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relasi ke tabel users
            $table->string('asal_kampus');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('surat_pengantar');
            $table->enum('status', ['pending', 'diterima', 'ditolak'])->default('pending');
            $table->text('alasan_penolakan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pendaftaran_magangs');
    }
};
