<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Traits\LogsActivity;
use Illuminate\Auth\Access\AuthorizationException;
use App\Traits\HandlesAuthorizationExceptions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class EstadodeUsuario extends Controller
{
    use LogsActivity, HandlesAuthorizationExceptions;

    public function getEstadodeUsuario()
    {
        $hasPermission = true;
        try {
            $this->authorize('view', User::class);
        } catch (AuthorizationException $e) {
            $hasPermission = false;
        }

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl . '/SEL_ESTADO_USUARIO');
        $EstadodeUsuario = $response->json();

        if ($hasPermission) {
            $this->logActivity('estado de usuario', 'get');
        }

        return view('EstadodeUsuario', compact('EstadodeUsuario', 'hasPermission'));
    }

    public function crear(Request $request)
    {
        try {
            $this->authorize('insert', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'No tienes permisos para poder Crear.']);
        }

        $data = $request->validate([
            'descripcion' => 'required|string|max:255',
        ]);

        $baseUrl = Config::get('api.base_url');
        $response = Http::post($baseUrl . '/POST_ESTADO_USUARIO', [
            'P_DESCRIPCION' => $data['descripcion']
        ]);

        if ($response->successful()) {
            $this->logActivity('estado de usuario', 'post', $data);
            return response()->json(['success' => 'Estado de usuario creado con éxito.']);
        } else {
            return response()->json(['error' => 'Hubo un problema al crear el Estado de usuario.'], 500);
        }
    }

    public function actualizar(Request $request, $id)
    {
        try {
            $this->authorize('update', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'No tienes permisos para poder Actualizar.']);
        }

        $data = $request->validate([
            'descripcion' => 'required|string|max:255',
        ]);

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl . '/SEL_ESTADO_USUARIO');
        $EstadodeUsuario = $response->json();
        $estadoActual = collect($EstadodeUsuario)->firstWhere('ID_ESTADO_USUARIO', $id);

        if (is_null($estadoActual)) {
            return redirect()->route('EstadodeUsuario')->withErrors('No se encontraron datos para el estado de usuario.');
        }

        $updateResponse = Http::post($baseUrl . '/PUT_ESTADO_USUARIO', [
            'P_ID_ESTADO_USUARIO' => $id,
            'P_DESCRIPCION' => $data['descripcion']
        ]);

        if ($updateResponse->successful()) {
            // Registrar la actividad en los logs
            $oldData = [
                'P_ID_ESTADO_USUARIO' => $estadoActual['ID_ESTADO_USUARIO'],
                'P_DESCRIPCION' => $estadoActual['DESCRIPCION'],
            ];

            $newData = [
                'P_ID_ESTADO_USUARIO' => $id,
                'P_DESCRIPCION' => $data['descripcion'],
            ];

            $this->logActivity(
                'estado de usuario ' . $estadoActual['ID_ESTADO_USUARIO'],
                'put',
                $newData,
                $oldData
            );

            return response()->json(['success' => 'Estado de usuario actualizado correctamente.']);
        } else {
            return response()->json(['error' => 'Hubo un error al actualizar el Estado de usuario.'], 500);
        }
    }

    public function eliminar($id)
    {
        try {
            $this->authorize('delete', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'No tienes permisos para poder Eliminar.']);
        }

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl . '/SEL_ESTADO_USUARIO');
        $EstadodeUsuario = $response->json();
        $estadoActual = collect($EstadodeUsuario)->firstWhere('ID_ESTADO_USUARIO', $id);

        if (is_null($estadoActual)) {
            return redirect()->route('EstadodeUsuario')->withErrors('No se encontraron datos para el estado de usuario.');
        }

        $deleteResponse = Http::post($baseUrl . '/DEL_ESTADO_USUARIO', ['P_ID_ESTADO_USUARIO' => $id]);

        if ($deleteResponse->successful()) {
            // Registrar la actividad en los logs
            $this->logActivity(
                'estado de usuario ' . $estadoActual['ID_ESTADO_USUARIO'],
                'delete',
                [],
                $estadoActual
            );

            return response()->json(['success' => 'Estado de usuario eliminado correctamente.']);
        } else {
            $errorMessage = $deleteResponse->json('error'); // Asegúrate de capturar el mensaje de error
            if (str_contains($errorMessage, 'relacionado con otros registros')) {
                return response()->json(['error' => 'No se puede eliminar el Estado porque está relacionado con otros registros.']);
            }
            return response()->json(['error' => 'Error al eliminar Estado.']);
    }
}

    public function generarReporte(Request $request)
    {
        $query = strtoupper($request->input('descripcion'));

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl . '/SEL_ESTADO_USUARIO');
        $EstadodeUsuario = $response->json();

        if ($response->successful()) {
            if ($query) {
                $EstadodeUsuario = array_filter($EstadodeUsuario, function ($estado) use ($query) {
                    return stripos($estado['DESCRIPCION'], $query) !== false;
                });
            }

            $pdf = Pdf::loadView('reportes.EstadodeUsuarios', compact('EstadodeUsuario'));
            return $pdf->stream('reporte_EstadodeUsuarios.pdf');
        } else {
            return back()->withErrors(['error' => 'No se pudo obtener la lista de Estados de Usuario.']);
        }
    }
}
