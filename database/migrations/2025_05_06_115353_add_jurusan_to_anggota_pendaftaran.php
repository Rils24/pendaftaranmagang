<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('anggota_pendaftaran', function (Blueprint $table) {
            $table->string('jurusan')->nullable()->after('nim_anggota');
        });
    }

    public function down()
    {
        Schema::table('anggota_pendaftaran', function (Blueprint $table) {
            $table->dropColumn('jurusan');
        });
    }
};