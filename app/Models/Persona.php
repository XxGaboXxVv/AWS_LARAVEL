<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;


    public function contacto() {
        return $this->belongsTo(Contacto::class, 'ID_CONTACTO');
    }

}