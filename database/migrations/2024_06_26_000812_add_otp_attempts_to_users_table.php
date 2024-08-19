<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up():void
{
    Schema::table('TBL_MS_USUARIO', function (Blueprint $table) {
        $table->integer('INTENTOS_FALLIDOS_OTP')->default(0);
    });
}

public function down():void
{
    Schema::table('TBL_MS_USUARIO', function (Blueprint $table) {
        $table->dropColumn('INTENTOS_FALLIDOS_OTP');
    });
}
};