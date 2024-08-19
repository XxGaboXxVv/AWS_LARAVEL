<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblPermisosTable extends Migration
{
    public function up()
    {
        Schema::create('TBL_PERMISOS', function (Blueprint $table) {
            $table->integer('ID_ROL');
            $table->integer('ID_OBJETO');
            $table->boolean('PERMISO_INSERCION')->default(false);
            $table->boolean('PERMISO_ELIMINACION')->default(false);
            $table->boolean('PERMISO_ACTUALIZACION')->default(false);
            $table->boolean('PERMISO_CONSULTAR')->default(false);
            $table->date('FECHA_CREACION');
            $table->string('CREADO_POR', 100);
            $table->date('FECHA_MODIFICACION')->nullable();
            $table->string('MODIFICADO_POR', 100)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('TBL_PERMISOS');
    }
}
