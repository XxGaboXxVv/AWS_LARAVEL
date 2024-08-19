<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('TBL_MS_USUARIO', function (Blueprint $table) {
            $table->increments('ID_USUARIO');
            $table->integer('ID_ROL')->nullable();
            $table->string('NOMBRE_USUARIO', 70);
            $table->integer('ID_ESTADO_USUARIO')->nullable();
            $table->string('EMAIL', 70)->unique();
            $table->string('CONTRASEÑA', 60)->nullable();
            $table->dateTime('PRIMER_INGRESO')->nullable();
            $table->dateTime('FECHA_ULTIMA_CONEXION')->nullable();
            $table->dateTime('FECHA_VENCIMIENTO')->nullable();
            $table->string('google_id')->nullable();
            $table->text('google2fa_secret')->nullable();
           
        });
    }

    public function down()
    {
        Schema::dropIfExists('TBL_MS_USUARIO');
    }

};
