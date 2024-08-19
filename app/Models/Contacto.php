<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contacto extends Model
{
    use HasFactory;

    public function tipoContacto() {
        return $this->belongsTo(TipoContacto::class, 'ID_TIPO_CONTACTO');
    }
}
