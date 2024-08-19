<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Access\AuthorizationException;
use App\Traits\LogsActivity;
use App\Traits\HandlesAuthorizationExceptions;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class TipoContacto extends Controller
{
    use LogsActivity, HandlesAuthorizationExceptions;

    public function getTipoContacto()
    {
        $hasPermission = true;
        try {
            $this->authorize('view', User::class);
        } catch (AuthorizationException $e) {
            $hasPermission = false;
        }

        $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
        $response = Http::get($baseUrl.'/SEL_TIPO_CONTACTO');
        $tipoContactos = $response->json();

        // Registrar la actividad solo si se tiene permiso
        if ($hasPermission) {
            $this->logActivity('tipocontacto', 'get');
        }

        return view('TipoContacto', compact('tipoContactos', 'hasPermission'));
    }

    public function crear(Request $request)
    {
        try {
            $this->authorize('insert', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'No tienes permisos para crear un Tipo de Contacto.']);
        }

        $data = $request->validate([
            'descripcion' => 'required|string|max:255',
        ]);

        try {
            $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
            $response = Http::post($baseUrl.'/POST_TIPO_CONTACTO', [
                'P_DESCRIPCION' => $data['descripcion']
            ]);

            if ($response->successful()) {
                $this->logActivity('tipocontacto', 'post', $data);
                return response()->json(['success' => 'Tipo de Contacto creado con éxito.']);
            } else {
                return response()->json(['error' => 'Hubo un problema al crear el Tipo de Contacto.'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Se produjo un error: ' . $e->getMessage()], 500);
        }
    }

    public function actualizar(Request $request, $id)
    {
        try {
            $this->authorize('update', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'No tienes permisos para actualizar un Tipo de Contacto.']);
        }

        $data = $request->validate([
            'descripcion' => 'required|string|max:255',
        ]);

        try {
            $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración

            // Obtener los datos actuales antes de la actualización
            $response = Http::get($baseUrl.'/SEL_TIPO_CONTACTO');
            $tipoContactos = $response->json();
            $tipoContactoActual = collect($tipoContactos)->firstWhere('ID_TIPO_CONTACTO', $id);

            if (is_null($tipoContactoActual)) {
                return response()->json(['error' => 'No se encontraron datos para el Tipo de Contacto.']);
            }

            // Realizar la actualización
            $updateResponse = Http::post($baseUrl.'/PUT_TIPO_CONTACTO', [
                'P_ID_TIPO_CONTACTO' => $id,
                'P_DESCRIPCION' => $data['descripcion']
            ]);

            if ($updateResponse->successful()) {
                // Registrar la actividad en los logs
                $oldData = [
                    'P_ID_TIPO_CONTACTO' => $tipoContactoActual['ID_TIPO_CONTACTO'],
                    'P_DESCRIPCION' => $tipoContactoActual['DESCRIPCION'],
                ];

                $newData = [
                    'P_ID_TIPO_CONTACTO' => $id,
                    'P_DESCRIPCION' => $data['descripcion'],
                ];

                $this->logActivity(
                    'tipocontacto ' . $id,
                    'put',
                    $newData,
                    $oldData
                );

                return response()->json(['success' => 'Tipo de Contacto actualizado correctamente.']);
            } else {
                return response()->json(['error' => 'Hubo un error al actualizar el Tipo de Contacto.'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Se produjo un error: ' . $e->getMessage()], 500);
        }
    }

    public function eliminar($id)
    {
        try {
            $this->authorize('delete', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'No tienes permisos para eliminar un Tipo de Contacto.']);
        }

    
            $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración

            // Obtener los datos actuales antes de la eliminación
            $response = Http::get($baseUrl.'/SEL_TIPO_CONTACTO');
            $tipoContactos = $response->json();
            $tipoContactoActual = collect($tipoContactos)->firstWhere('ID_TIPO_CONTACTO', $id);

            if (is_null($tipoContactoActual)) {
                return response()->json(['error' => 'No se encontraron datos para el Tipo de Contacto.']);
            }

            // Realizar la eliminación
            $deleteResponse = Http::post($baseUrl.'/DEL_TIPO_CONTACTO', [
                'P_ID_TIPO_CONTACTO' => $id
            ]);

            if ($deleteResponse->successful()) {
                // Registrar la actividad en los logs
                $this->logActivity(
                    'tipocontacto ' . $id,
                    'delete',
                    [],
                    $tipoContactoActual
                );

                return response()->json(['success' => 'Tipo de Contacto eliminado correctamente.']);
            } else {
                $errorMessage = $deleteResponse->json('error'); // Asegúrate de capturar el mensaje de error
                if (str_contains($errorMessage, 'relacionado con otros registros')) {
                    return response()->json(['error' => 'No se puede eliminar el Tipo de Contacto porque está relacionado con otros registros.']);
                }
                return response()->json(['error' => 'Error al eliminar Estado.']);
        }
    }
    

    public function generarReporte(Request $request)
    {
        $query = strtoupper($request->input('descripcion'));
        $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
        $response = Http::get($baseUrl.'/SEL_TIPO_CONTACTO');
        $tipoContactos = $response->json();

        if ($response->successful()) {
            if ($query) {
                $tipoContactos = array_filter($tipoContactos, function($tipoContacto) use ($query) {
                    return stripos($tipoContacto['DESCRIPCION'], $query) !== false;
                });
            }

            // Generar el PDF
            $pdf = Pdf::loadView('reportes.TipoContacto', compact('tipoContactos'));
            return $pdf->stream('reporte_TipoContactos.pdf');
        } else {
            return response()->json(['error' => 'No se pudo generar el reporte.'], 500);
        }
    }
}
