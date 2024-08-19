<?php
namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use DateTime;
use DateTimeZone;

class LogSuccessfulLogout
{
    public function handle(Logout $event)
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
            'ACCION' => 'Cierre de sesión',
            'DESCRIPCION' => 'Cierre de sesión del usuario: ' . $user->NOMBRE_USUARIO . ' (ID: ' . $user->ID_USUARIO . ')',
        ]);
    }
    private function getObjectId()
    {
        // Busca en la tabla TBL_OBJETOS el ID_OBJETO para el objeto específico 'GET'
        $object = DB::table('TBL_OBJETOS')
                    ->where('OBJETO', '=', 'CIERRE DE SESION') // Asume que 'GET' es el nombre del objeto en la tabla
                    ->first();
        
        \Log::info('Objeto encontrado en TBL_OBJETOS:', [$object]);

        return $object ? $object->ID_OBJETO : null;
    }
}
