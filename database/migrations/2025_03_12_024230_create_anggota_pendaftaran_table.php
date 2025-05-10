<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('anggota_pendaftaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_magangs')->onDelete('cascade');
            $table->string('nama_anggota');
            $table->string('nim_anggota')->nullable();
            $table->string('jurusan_anggota')->nullable(); // Kolom jurusan baru
            $table->string('email_anggota')->nullable();
            $table->string('no_hp_anggota')->nullable();
            $table->timestamps();
        });
    }
    

    public function down()
    {
        Schema::dropIfExists('anggota_pendaftaran');
    }
};
