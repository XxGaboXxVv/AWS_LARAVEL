<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblReinicioContraseñaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('TBL_REINICIO_CONTRASEÑA', function (Blueprint $table) {
            $table->id('ID_REINICIO_CONTRASEÑA');
            $table->string('EMAIL')->index();
            $table->string('TOKEN');
            $table->timestamp('CREADO')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('TBL_REINICIO_CONTRASEÑA');
    }
}
