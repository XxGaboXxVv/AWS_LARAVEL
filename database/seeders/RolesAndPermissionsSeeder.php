<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Permisos para el rol Administrador (ID_ROL = 1)
        $permisosAdmin = [
            'PERMISO_INSERCION' => true,
            'PERMISO_ELIMINACION' => true,
            'PERMISO_ACTUALIZACION' => true,
            'PERMISO_CONSULTAR' => true,
            'FECHA_CREACION' => now(),
            'CREADO_POR' => 'Administrador'
        ];

        // Permisos para el rol Usuario (ID_ROL = 2)
        $permisosUsuario = [
            'PERMISO_INSERCION' => false,
            'PERMISO_ELIMINACION' => false,
            'PERMISO_ACTUALIZACION' => false,
            'PERMISO_CONSULTAR' => true,
            'FECHA_CREACION' => now(),
            'CREADO_POR' => 'Administrador'
        ];

        // Asumiendo que tienes 10 objetos en tu sistema
        for ($i = 1; $i <= 10; $i++) {
            DB::table('TBL_PERMISOS')->insert(array_merge(['ID_ROL' => 1, 'ID_OBJETOS' => $i], $permisosAdmin));
            DB::table('TBL_PERMISOS')->insert(array_merge(['ID_ROL' => 2, 'ID_OBJETOS' => $i], $permisosUsuario));
        }
    }
}