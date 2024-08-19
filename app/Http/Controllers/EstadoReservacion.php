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

class EstadoReservacion extends Controller
{
    use LogsActivity, HandlesAuthorizationExceptions;

    public function getEstadoReservacion()
    {
        $hasPermission = true;
        try {
            $this->authorize('view', User::class);
        } catch (AuthorizationException $e) {
            $hasPermission = false;
        }

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl .'/SEL_ESTADO_RESERVA');
        $EstadoReservacion = $response->json();

        if ($hasPermission) {
            $this->logActivity('estado de reservacion', 'get');
        }

        return view('EstadoReservacion', compact('EstadoReservacion', 'hasPermission'));
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
        $response = Http::post($baseUrl . '/POST_ESTADO_RESERVA', [
            'P_DESCRIPCION' => $data['descripcion']
        ]);

        if ($response->successful()) {
            $this->logActivity('estado de persona', 'post', $data);
            return response()->json(['success' => 'Estado de reservacion creado con éxito.']);
        } else {
            return response()->json(['error' => 'Hubo un problema al crear el Estado de reservacion.'], 500);
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
        $response = Http::get($baseUrl . '/SEL_ESTADO_RESERVA');
        $EstadoReservacion = $response->json();
        $estadoActual = collect($EstadoReservacion)->firstWhere('ID_ESTADO_RESERVA', $id);

        if (is_null($estadoActual)) {
            return redirect()->route('EstadoReservacion')->withErrors('No se encontraron datos para el estado de usuario.');
        }

        $updateResponse = Http::post($baseUrl . '/PUT_ESTADO_RESERVA', [
            'P_ID_ESTADO_RESERVA' => $id,
            'P_DESCRIPCION' => $data['descripcion']
        ]);

        if ($updateResponse->successful()) {
            // Registrar la actividad en los logs
            $oldData = [
                'P_ID_ESTADO_RESERVA' => $estadoActual['ID_ESTADO_RESERVA'],
                'P_DESCRIPCION' => $estadoActual['DESCRIPCION'],
            ];

            $newData = [
                'P_ID_ESTADO_RESERVA' => $id,
                'P_DESCRIPCION' => $data['descripcion'],
            ];

            $this->logActivity(
                'estado de reservacion ' . $estadoActual['ID_ESTADO_RESERVA'],
                'put',
                $newData,
                $oldData
            );

            return response()->json(['success' => 'Estado de reservacion actualizado correctamente.']);
        } else {
            return response()->json(['error' => 'Hubo un error al actualizar el Estado de reservacion.'], 500);
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
        $response = Http::get($baseUrl . '/SEL_ESTADO_RESERVA');
        $EstadoReservacion = $response->json();
        $estadoActual = collect($EstadoReservacion)->firstWhere('ID_ESTADO_RESERVA', $id);

        if (is_null($estadoActual)) {
            return redirect()->route('EstadoReservacion')->withErrors('No se encontraron datos para el estado de reservacion.');
        }

        $deleteResponse = Http::post($baseUrl . '/DEL_ESTADO_RESERVA', ['P_ID_ESTADO_RESERVA' => $id]);

        if ($deleteResponse->successful()) {
            // Registrar la actividad en los logs
            $this->logActivity(
                'estado de reservacion ' . $estadoActual['ID_ESTADO_RESERVA'],
                'delete',
                [],
                $estadoActual
            );

            return response()->json(['success' => 'Estado de reservacion eliminado correctamente.']);
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
        $response = Http::get($baseUrl . '/SEL_ESTADO_RESERVA');
        $EstadoReservacion = $response->json();

        if ($response->successful()) {
            if ($query) {
                $EstadoReservacion = array_filter($EstadoReservacion, function ($estado) use ($query) {
                    return stripos($estado['DESCRIPCION'], $query) !== false;
                });
            }

            $pdf = Pdf::loadView('reportes.EstadoReservacion', compact('EstadoReservacion'));
            return $pdf->stream('reporte_EstadoReservacion.pdf');
        } else {
            return back()->withErrors(['error' => 'No se pudo obtener la lista de Estados de reservaciones.']);
        }
    }
}
