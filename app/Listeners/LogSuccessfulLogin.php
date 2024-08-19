<?php
namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use DateTime;
use DateTimeZone;

class LogSuccessfulLogin
{
    public function handle(Login $event)
    {
        if (!$event->user) {
            return;
        }
        $date = new DateTime('now', new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone('America/Tegucigalpa'));
        $user = $event->user;

        $objectId = $this->getObjectId();

        DB::table('TBL_MS_BITACORA')->insert([
            'ID_USUARIO' => $user->ID_USUARIO,
            'ID_OBJETO' => $objectId, // Captura el ID del objeto
            'FECHA' => $date->format('Y-m-d H:i:s'),
            'ACCION' => 'Inicio de sesión',
            'DESCRIPCION' => 'Inicio de sesión del usuario: ' . $user->NOMBRE_USUARIO . ' (ID: ' . $user->ID_USUARIO . ')',
        ]);
    }

    private function getObjectId()
    {
        // Busca en la tabla TBL_OBJETOS el ID_OBJETO para el objeto específico 'LOGIN'
        $object = DB::table('TBL_OBJETOS')
                    ->where('OBJETO', '=', 'INICIO DE SESION') // Asume que 'LOGIN' es el nombre del objeto en la tabla
                    ->first();
        
        \Log::info('Objeto encontrado en TBL_OBJETOS:', [$object]);

        return $object ? $object->ID_OBJETO : null;
    }
}