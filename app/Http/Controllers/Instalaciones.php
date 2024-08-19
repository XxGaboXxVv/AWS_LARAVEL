<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Traits\LogsActivity;
use Illuminate\Auth\Access\AuthorizationException;
use App\Traits\HandlesAuthorizationExceptions;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use DateTimeZone;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class Instalaciones extends Controller
{
    use LogsActivity, HandlesAuthorizationExceptions;

    public function getInstalaciones()
    {
        $hasPermission = true;
        try {
            $this->authorize('view', User::class);
        } catch (AuthorizationException $e) {
            $hasPermission = false;
        }
        
        $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
        $response = Http::get($baseUrl.'/SEL_INSTALACIONES');
        $instalaciones = $response->json();
        
        // Registrar la actividad solo si se tiene permiso
        if ($hasPermission) {
            $this->logActivity('instalaciones', 'get');
        }

        return view('Instalaciones', compact('instalaciones', 'hasPermission'));
    }

    public function crear(Request $request)
    {
        try {
            $this->authorize('insert', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Crear.']);
        }

        $data = $request->validate([
            'nombre_instalacion' => 'required|string|max:255',
            'capacidad' => 'required|integer',
            'precio' => 'required|numeric',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        try {
            // Crear nueva instalación
            DB::table('TBL_INSTALACIONES')->insert([
                'NOMBRE_INSTALACION' => $data['nombre_instalacion'],
                'CAPACIDAD' => $data['capacidad'],
                'PRECIO' => $data['precio'],
                'DESCRIPCION' => $data['descripcion'],
            ]);

            // Registrar la actividad en los logs
            $this->logActivity('instalacion', 'post', $data);

            return response()->json(['success' => 'Instalación creada con éxito.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Se produjo un error: ' . $e->getMessage()], 500);
        }
    }

    public function actualizar(Request $request, $id)
    {
        try {
            $this->authorize('update', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Actualizar.']);
        }

        $data = $request->validate([
            'nombre_instalacion' => 'required|string|max:255',
            'capacidad' => 'required|integer',
            'precio' => 'required|numeric',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        try {
            // Obtener los datos actuales de la instalación
            $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
            $response = Http::get($baseUrl.'/SEL_INSTALACIONES');
            if (!$response->successful()) {
                return redirect()->route('Instalaciones')->withErrors('Error al obtener los datos de la instalación.');
            }
            $instalaciones = $response->json();
            $instalacionActual = collect($instalaciones)->firstWhere('ID_INSTALACION', $id);
            if (is_null($instalacionActual)) {
                return redirect()->route('Instalaciones')->withErrors('No se encontraron datos para la instalación.');
            }

            // Actualizar los datos de la instalación
            $updateResponse = Http::post($baseUrl.'/PUT_INSTALACIONES', [
                'P_ID_INSTALACION' => $id,
                'P_NOMBRE_INSTALACION' => $data['nombre_instalacion'],
                'P_CAPACIDAD' => $data['capacidad'],
                'P_PRECIO' => $data['precio'],
                'P_DESCRIPCION' => $data['descripcion'],
            ]);

            if (!$updateResponse->successful()) {
                return response()->json(['error' => 'Error al actualizar la instalación.'], 500);
            }

           // Registrar la actividad en los logs
           $oldData = [
            'P_ID_INSTALACION' => $instalacionActual['ID_INSTALACION'],
            'P_NOMBRE_INSTALACION' => $instalacionActual['NOMBRE_INSTALACION'],
            'P_CAPACIDAD' => $instalacionActual['CAPACIDAD'],
            'P_PRECIO' => $instalacionActual['PRECIO'],
            'P_DESCRIPCION' => $instalacionActual['DESCRIPCION'],

         
        ];

        $newData = [
            'P_ID_INSTALACION' => $id,
            'P_NOMBRE_INSTALACION' => $data['descripcion'],
            'P_DESCRIPCION' => $data['nombre_instalacion'],
            'P_DESCRIPCION' => $data['capacidad'],
            'P_DESCRIPCION' => $data['precio'],
            'P_DESCRIPCION' => $data['descripcion'],
           
        ];

        $this->logActivity(
            'instalacion' . $id,
            'put',
            $newData,
            $oldData
        );


            return response()->json(['success' => 'Instalación actualizada correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Se produjo un error: ' . $e->getMessage()], 500);
        }
    }

    public function eliminar($id)
    {
        try {
            $this->authorize('delete', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Eliminar.']);
        }


            // Obtener los datos de la instalación antes de eliminarla para el log de actividad
            $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
            $response = Http::get($baseUrl.'/SEL_INSTALACIONES');
            if (!$response->successful()) {
                return redirect()->route('Instalaciones')->withErrors('Error al obtener los datos de la instalación.');
            }
            $instalaciones = $response->json();
            $instalacionActual = collect($instalaciones)->firstWhere('ID_INSTALACION', $id);
            if (is_null($instalacionActual)) {
                return redirect()->route('Instalaciones')->withErrors('No se encontraron datos para la instalación.');
            }

            // Realizar la eliminación
            $response = Http::post($baseUrl.'/DEL_INSTALACIONES', ['P_ID_INSTALACION' => $id]);

            if ($response->successful()) {
                $this->logActivity('instalacion', 'delete', [], $instalacionActual);
                return response()->json(['success' => 'Instalación eliminada con éxito.']);
            } else {
                $errorMessage = $response->json('error'); // Asegúrate de capturar el mensaje de error
                if (str_contains($errorMessage, 'relacionado con otros registros')) {
                    return response()->json(['error' => 'No se puede eliminar la Instalacion porque está relacionado con otros registros.']);
                }
                return response()->json(['error' => 'Error al eliminar Estado.']);
        }
    }


    public function generarReporte(Request $request)
    {
        $query = strtoupper($request->input('nombre_instalacion'));       
        $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
        $response = Http::get($baseUrl.'/SEL_INSTALACIONES');
        
        if ($response->successful()) {
            $instalaciones = $response->json();

            // Filtrar las instalaciones si se ha proporcionado un nombre
            if ($query) {
                $instalaciones = array_filter($instalaciones, function ($instalacion) use ($query) {
                    return strpos(strtoupper($instalacion['NOMBRE_INSTALACION']), $query) !== false;
                });
            }

            // Generar el PDF
            $pdf = Pdf::loadView('reportes.Instalaciones', compact('instalaciones'));

            return $pdf->stream('reporte_instalaciones.pdf');
        } else {
            return response()->json(['error' => 'No se pudo generar el reporte.'], 500);
        }
    }
}
