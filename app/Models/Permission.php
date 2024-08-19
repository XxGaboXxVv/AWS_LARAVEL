<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'TBL_PERMISOS';

    public function role()
    {
        return $this->belongsTo(Role::class, 'ID_ROL');
    }
}
