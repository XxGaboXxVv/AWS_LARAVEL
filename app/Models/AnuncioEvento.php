<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnuncioEvento extends Model
{
    use HasFactory;

    protected $table = 'TBL_ANUNCIOS_EVENTOS';
    public $timestamps = false;
    protected $primaryKey = 'ID_ANUNCIOS_EVENTOS'; // Custom primary key
    protected $fillable = [
        'TITULO',
        'DESCRIPCION',
        'IMAGEN',
        'FECHA_HORA'
    ];
}
