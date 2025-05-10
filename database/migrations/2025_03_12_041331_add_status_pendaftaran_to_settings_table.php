<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasColumn('settings', 'status_pendaftaran')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->boolean('status_pendaftaran')->default(true);
            });
        }
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('status_pendaftaran');
        });
    }
};
