<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use DateTimeZone;
use Illuminate\Auth\Access\AuthorizationException;
use App\Traits\LogsActivity;
use App\Traits\HandlesAuthorizationExceptions;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class Permisos extends Controller
{
    use LogsActivity, HandlesAuthorizationExceptions;

    public function getPermisos()
    {
        $hasPermission = true;
        try {
            $this->authorize('view', User::class);
        } catch (AuthorizationException $e) {
            $hasPermission = false;
        }

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_PERMISOS');
        $Permisos = $response->json();

        $roles = $this->getRoles();
        $objetos = $this->getObjetos();

        foreach ($Permisos as &$permiso) {
            $permiso['ROL'] = $roles->firstWhere('ID_ROL', $permiso['ID_ROL'])->ROL ?? 'Desconocido';
            $permiso['OBJETO'] = $objetos->firstWhere('ID_OBJETO', $permiso['ID_OBJETO'])->OBJETO ?? 'Desconocido';
        }

        // Registrar la actividad solo si se tiene permiso
        if ($hasPermission) {
            $this->logActivity('permisos', 'get');
        }

        return view('Permisos', compact('Permisos', 'roles', 'objetos', 'hasPermission'));
    }

    public function getRoles()
    {
        return DB::table('TBL_MS_ROLES')->select('ID_ROL', 'ROL')->get();
    }

    public function getObjetos()
    {
        return DB::table('TBL_OBJETOS')->select('ID_OBJETO', 'OBJETO')->get();
    }

    public function crear(Request $request)
    {
        try {
            $this->authorize('insert', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'No tienes permisos para crear.'], 403);
        }

        $data = $request->validate([
            'id_rol' => 'required|integer',
            'id_objeto' => 'required|integer',
            'permiso_insercion' => 'required|boolean',
            'permiso_eliminacion' => 'required|boolean',
            'permiso_actualizacion' => 'required|boolean',
            'permiso_consultar' => 'required|boolean',
            'creado_por' => 'nullable|string|max:100',
        ]);

        $date = new DateTime('now', new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone('America/Tegucigalpa'));

        $response = Http::post(Config::get('api.base_url') . '/POST_PERMISOS', [
            'P_ID_ROL' => $data['id_rol'],
            'P_ID_OBJETO' => $data['id_objeto'],
            'P_PERMISO_INSERCION' => $data['permiso_insercion'],
            'P_PERMISO_ELIMINACION' => $data['permiso_eliminacion'],
            'P_PERMISO_ACTUALIZACION' => $data['permiso_actualizacion'],
            'P_PERMISO_CONSULTAR' => $data['permiso_consultar'],
            'P_FECHA_CREACION' => $date->format('Y-m-d'),
            'P_CREADO_POR' => $data['creado_por']
        ]);

        if ($response->successful()) {
            $this->logActivity('permiso', 'post', $data);
            return response()->json(['success' => 'Permiso creado con éxito.']);
        } else {
            return response()->json(['error' => 'Hubo un problema al crear el permiso.'], 500);
        }
    }

    public function actualizar(Request $request, $id)
    {
        try {
            $this->authorize('update', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'No tienes permisos para actualizar.'], 403);
        }

        $data = $request->validate([
            'id_rol' => 'required|integer',
            'id_objeto' => 'required|integer',
            'permiso_insercion' => 'required|boolean',
            'permiso_eliminacion' => 'required|boolean',
            'permiso_actualizacion' => 'required|boolean',
            'permiso_consultar' => 'required|boolean',
            'creado_por' => 'nullable|string|max:100',
            'fecha_modificacion' => 'nullable|date',
            'modificado_por' => 'nullable|string|max:100'
        ]);

        $response = Http::post(Config::get('api.base_url') . '/PUT_PERMISOS', [
            'P_ID_PERMISO' => $id,
            'P_ID_ROL' => $data['id_rol'],
            'P_ID_OBJETO' => $data['id_objeto'],
            'P_PERMISO_INSERCION' => $data['permiso_insercion'],
            'P_PERMISO_ELIMINACION' => $data['permiso_eliminacion'],
            'P_PERMISO_ACTUALIZACION' => $data['permiso_actualizacion'],
            'P_PERMISO_CONSULTAR' => $data['permiso_consultar'],
            'P_FECHA_CREACION' => now()->format('Y-m-d'),
            'P_CREADO_POR' => $data['creado_por'],
            'P_FECHA_MODIFICACION' => $data['fecha_modificacion'],
            'P_MODIFICADO_POR' => $data['modificado_por']
        ]);

        if ($response->successful()) {
            $this->logActivity('permiso ' . $id, 'put', $data);
            return response()->json(['success' => 'Permiso actualizado correctamente.']);
        } else {
            return response()->json(['error' => 'Hubo un error al actualizar el permiso.'], 500);
        }
    }

    public function eliminar($id)
{
    try {
        $this->authorize('delete', User::class);
    } catch (AuthorizationException $e) {
        return response()->json(['error' => 'No tienes permisos para eliminar.'], 403);
    }

    $baseUrl = Config::get('api.base_url');

    // Obtener los datos del permiso antes de eliminarlo para el log de actividad
    $response = Http::get($baseUrl . '/SEL_PERMISOS');
    if (!$response->successful()) {
        return redirect()->route('Permisos')->withErrors('Error al obtener los datos del permiso.');
    }

    $permisos = $response->json();
    $permisoActual = collect($permisos)->firstWhere('ID_PERMISO', $id);
    if (is_null($permisoActual)) {
        return redirect()->route('Permisos')->withErrors('No se encontraron datos para el permiso.');
    }

    // Realizar la eliminación
    $response = Http::post($baseUrl . '/DEL_PERMISOS', ['P_ID_PERMISO' => $id]);

    if ($response->successful()) {
        $this->logActivity('permiso', 'delete', [], $permisoActual);
        return response()->json(['success' => 'Permiso eliminado correctamente.']);
    } else {
        $errorMessage = $response->json('error'); // Capturar el mensaje de error
        if (str_contains($errorMessage, 'relacionado con otros registros')) {
            return response()->json(['error' => 'No se puede eliminar el Permiso porque está relacionado con otros registros.']);
        }
        return response()->json(['error' => 'Hubo un error al eliminar el permiso.'], 500);
    }
}
public function generarReporte(Request $request)
{
    $query = strtoupper($request->input('id_rol'));
    $baseUrl = Config::get('api.base_url');
    $response = Http::get($baseUrl.'/SEL_PERMISOS');
    $Permisos = $response->json();

    if ($response->successful()) {
        

        // Obtener roles y objetos
        $roles = $this->getRoles();
        $objetos = $this->getObjetos();

        // Asignar los nombres de roles y objetos a los permisos
        foreach ($Permisos as &$permiso) {
            $permiso['ROL'] = $roles->firstWhere('ID_ROL', $permiso['ID_ROL'])->ROL ?? 'Desconocido';
            $permiso['OBJETO'] = $objetos->firstWhere('ID_OBJETO', $permiso['ID_OBJETO'])->OBJETO ?? 'Desconocido';
        }

        if ($query) {
            $Permisos = array_filter($Permisos, function ($permiso) use ($query) {
                return stripos($permiso['ROL'], $query) !== false;
            });
        }

        // Generar el PDF
        $pdf = Pdf::loadView('reportes.Permisos', compact('Permisos', 'roles', 'objetos'));
        return $pdf->stream('reporte_Permisos.pdf');
    } else {
        // Manejar el caso en que la solicitud a la API falle
        return back()->withErrors(['error' => 'No se pudo obtener la lista de permisos.']);
    }
}
}
