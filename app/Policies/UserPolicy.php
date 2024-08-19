<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserPolicy
{
    public function update(User $user)
    {
        return $this->hasPermission($user->ID_ROL, 'PERMISO_ACTUALIZACION');
    }

    public function delete(User $user)
    {
        return $this->hasPermission($user->ID_ROL, 'PERMISO_ELIMINACION');
    }

    public function insert(User $user)
    {
        return $this->hasPermission($user->ID_ROL, 'PERMISO_INSERCION');
    }
    public function view(User $user)
    {
        return $this->hasPermission($user->ID_ROL, 'PERMISO_CONSULTAR');
    }
    private function hasPermission($roleId, $permissionColumn)
    {
      

        // Obtén los permisos desde la tabla TBL_PERMISOS
        $permission = DB::table('TBL_PERMISOS')
            ->where('ID_ROL', $roleId)
            ->value($permissionColumn);

        return $permission == 1;
    }
}
