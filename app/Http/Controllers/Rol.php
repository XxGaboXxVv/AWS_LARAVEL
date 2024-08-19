<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Config;
use App\Traits\LogsActivity;
use Illuminate\Auth\Access\AuthorizationException;
use App\Traits\HandlesAuthorizationExceptions;


class Rol extends Controller
{
    use LogsActivity;
    use HandlesAuthorizationExceptions;


    public function getRoles()
    {
        $hasPermission = true;
        try {
            $this->authorize('view', User::class);
        } catch (AuthorizationException $e) {
            $hasPermission = false;
        }
    
        $baseUrl = Config::get('api.base_url'); 
        $response = Http::get($baseUrl.'/SEL_TBL_MS_ROLES');
        $Roles = $response->json();
    
        // Registrar la actividad solo si se tiene permiso
        if ($hasPermission) {
            $this->logActivity('roles', 'get');
        }
    
        return view('Rol', compact('Roles', 'hasPermission'));
    }

    public function crear(Request $request)
    {
        try {
            $this->authorize('insert', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Crear.']);
        }
        $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
        $response = Http::post($baseUrl.'/POST_TBL_MS_ROLES', [
            'P_ROL' => $request->input('rol'),
            'P_DESCRIPCION' => $request->input('descripcion')
        ]);

        if ($response->successful()) {
            // Registrar la actividad
            $this->logActivity('rol', 'post', $request->all());

            return response()->json(['success' => 'Rol creado con éxito.']);
        } else {
            return response()->json(['error' => 'Hubo un problema al crear el Rol.'], 500);
        }
    }

    public function actualizar(Request $request, $id)
    {
        try {
            $this->authorize('update', User::class); 
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Actualizar.']);
        }
        $request->validate([
            'rol' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
        ]);
    
        // Obtener los datos actuales del rol antes de actualizar
        $baseUrl = Config::get('api.base_url');
        $oldDataResponse = Http::get($baseUrl . '/SEL_TBL_MS_ROLES');
    
        if (!$oldDataResponse->successful()) {
            return response()->json(['error' => 'Error al obtener los datos actuales del rol.'], 500);
        }
    
        // Suponiendo que la API devuelve una lista de roles
        $roles = $oldDataResponse->json();
        $rolActual = collect($roles)->firstWhere('ID_ROL', $id);
    
        if (is_null($rolActual)) {
            return response()->json(['error' => 'No se encontraron datos para el rol especificado.'], 404);
        }
    
        // Mapear los datos antiguos
        $oldDataArray = [
            'P_ROL' => $rolActual['ROL'],
            'P_DESCRIPCION' => $rolActual['DESCRIPCION'],
        ];
    
        // Preparar los nuevos datos
        $newData = [
            'P_ROL' => $request->input('rol'),
            'P_DESCRIPCION' => $request->input('descripcion'),
        ];
    
        // Actualizar los datos del rol
        $updateResponse = Http::post($baseUrl . '/PUT_TBL_MS_ROLES', [
            'P_ID_ROL' => $id,
            'P_ROL' => $request->input('rol'),
            'P_DESCRIPCION' => $request->input('descripcion')
        ]);
    
        if ($updateResponse->successful()) {
            // Registrar la actividad en los logs
            $this->logActivity('rol', 'put', $newData, $oldDataArray);
    
            return response()->json(['success' => 'Rol actualizado correctamente.']);
        } else {
            return response()->json(['error' => 'Hubo un error al actualizar el Rol.'], 500);
        }
    }

    

    public function eliminar($id)
    {
        try {
            $this->authorize('delete', User::class); 
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Eliminar.']);
        }
        // Obtener los datos actuales del rol específico antes de eliminar
        $response = Http::get(Config::get('api.base_url').'/SEL_TBL_MS_ROLES', ['P_ID_ROL' => $id]);
        $oldData = collect($response->json())->firstWhere('ID_ROL', $id);
    
        if (!$oldData) {
            return response()->json(['error' => 'Rol no encontrado.'], 404);
        }
    
        $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
        $response = Http::post($baseUrl.'/DEL_TBL_MS_ROLES', [
            'P_ID_ROL' => $id
        ]);
    
        if ($response->successful()) {
            // Registrar la actividad solo del rol eliminado
            $this->logActivity('rol', 'delete', [], $oldData);
    
            return response()->json(['success' => 'Rol eliminado correctamente.']);
        } else {
            // Analizar el error recibido
            $errorMessage = $response->json('error');
            if (str_contains($errorMessage, 'relacionado con otros registros')) {
                return response()->json(['error' => 'No se puede eliminar el Rol porque está relacionado con otros registros.']);
            }
            return response()->json(['error' => 'Error al eliminar Rol.']);
        }
    }
    

    public function generarReporte(Request $request)
    {
        $baseUrl = Config::get('api.base_url');
        $query = strtoupper($request->input('rol'));
        $response = Http::get($baseUrl.'/SEL_TBL_MS_ROLES');
        $Roles = $response->json();
            
    
        if ($response->successful()) {
           
            if ($query) {
                $Roles = array_filter($Roles, function($role) use ($query) {
                    return stripos($role['ROL'], $query) !== false;
                });
            }
    
            $this->logActivity('reporte de roles', 'get', [], [], ['filtro' => $query]);
    
            $pdf = Pdf::loadView('reportes.roles', compact('Roles'));
            return $pdf->stream('reporte_roles.pdf');
        } else {
            return back()->withErrors(['error' => 'No se pudo obtener la lista de roles.']);
        }
    }
    
}
