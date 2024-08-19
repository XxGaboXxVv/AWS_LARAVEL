<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parametro extends Model
{
    protected $table = 'TBL_MS_PARAMETROS';
    protected $primaryKey = 'ID_PARAMETRO';
    public $timestamps = false; 

    public static function obtenerValor($parametro)
    {
        return self::where('PARAMETRO', $parametro)->value('VALOR');
    }
}
