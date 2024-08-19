<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use DateTime;
use DateTimeZone;

class LogPageChanges
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            \Log::info('Middleware LogPageChanges se está ejecutando.');
    
            $date = new DateTime('now', new DateTimeZone('UTC'));
            $date->setTimezone(new DateTimeZone('America/Tegucigalpa'));

            $objectId = $this->getObjectId(); // Se pasa $request

            DB::table('TBL_MS_BITACORA')->insert([
                'ID_USUARIO' => Auth::user()->ID_USUARIO,
                'ID_OBJETO' => $objectId, 
                'FECHA' => $date->format('Y-m-d H:i:s'),
                'ACCION' => 'Cambio de página',
                'DESCRIPCION' => 'El usuario ' . Auth::user()->NOMBRE_USUARIO . ' (ID: ' . Auth::user()->ID_USUARIO . ') visitó la página ' . $request->path(),
            ]);
        }
    
        return $next($request);
    }

    private function getObjectId()
    {
        // Busca en la tabla TBL_OBJETOS el ID_OBJETO para el objeto específico 'GET'
        $object = DB::table('TBL_OBJETOS')
                    ->where('OBJETO', '=', 'CAMBIO DE PAGINA') // Asume que 'GET' es el nombre del objeto en la tabla
                    ->first();
        
        \Log::info('Objeto encontrado en TBL_OBJETOS:', [$object]);

        return $object ? $object->ID_OBJETO : null;
    }
}