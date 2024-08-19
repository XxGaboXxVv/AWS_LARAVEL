<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'TBL_MS_ROLES';
    protected $primaryKey = 'ID_ROL';

    public function permisos()
    {
        return $this->hasMany(Permission::class, 'ID_ROL');
    }


}
