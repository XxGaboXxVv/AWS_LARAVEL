<?php
namespace App\Http\Controllers;

use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Access\AuthorizationException;
use App\Traits\HandlesAuthorizationExceptions;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class TipoPersona extends Controller
{
    use LogsActivity, HandlesAuthorizationExceptions;

    public function getTipoPersona()
    {
        $hasPermission = true;
        try {
            $this->authorize('view', User::class);
        } catch (AuthorizationException $e) {
            $hasPermission = false;
        }

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl . '/SEL_TBL_TIPO_PERSONAS');
        $tipoPersonas = $response->json();

        if ($hasPermission) {
            $this->logActivity('tipo_persona', 'get');
        }

        
        return view('TipoPersona', compact('tipoPersonas', 'hasPermission'));
    }

    public function crear(Request $request)
    {
        try {
            $this->authorize('insert', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'No tienes permisos para crear un Tipo de Persona.']);
        }

        $data = $request->validate([
            'descripcion' => 'required|string|max:255',
        ]);

        $baseUrl = Config::get('api.base_url');
        $response = Http::post($baseUrl . '/POST_TBL_TIPO_PERSONAS', [
            'P_DESCRIPCION' => $data['descripcion']
        ]);

        if ($response->successful()) {
            $this->logActivity('tipo_persona', 'post', $data);
            return response()->json(['success' => 'Tipo de Persona creado con éxito.']);
        } else {
            return response()->json(['error' => 'Hubo un problema al crear el Tipo de Persona.'], 500);
        }
    }

    public function actualizar(Request $request, $id)
    {
        try {
            $this->authorize('update', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'No tienes permisos para actualizar un Tipo de Persona.']);
        }

        $data = $request->validate([
            'descripcion' => 'required|string|max:255',
        ]);

        // Obtener los datos actuales del Tipo de Persona
        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl . '/SEL_TBL_TIPO_PERSONAS');
        if (!$response->successful()) {
            return redirect()->route('TipoPersona')->withErrors('Error al obtener los datos del Tipo de Persona.');
        }

        $tipoPersonas = $response->json();
        $tipoPersonaActual = collect($tipoPersonas)->firstWhere('ID_TIPO_PERSONA', $id);
        if (is_null($tipoPersonaActual)) {
            return redirect()->route('TipoPersona')->withErrors('No se encontraron datos para el Tipo de Persona.');
        }

        // Actualizar los datos del Tipo de Persona
        $updateResponse = Http::post($baseUrl . '/PUT_TBL_TIPO_PERSONAS', [
            'P_ID_TIPO_PERSONA' => $id,
            'P_DESCRIPCION' => $data['descripcion']
        ]);

        if ($updateResponse->successful()) {
            // Registrar la actividad en los logs
            $oldData = [
                'P_ID_TIPO_PERSONA' => $tipoPersonaActual['ID_TIPO_PERSONA'],
                'P_DESCRIPCION' => $tipoPersonaActual['DESCRIPCION'],
            ];

            $newData = [
                'P_ID_TIPO_PERSONA' => $id,
                'P_DESCRIPCION' => $data['descripcion'],
            ];

            $this->logActivity(
                'tipo_persona ' . $tipoPersonaActual['ID_TIPO_PERSONA'],
                'put',
                $newData,
                $oldData
            );

            return response()->json(['success' => 'Tipo de Persona actualizado correctamente.']);
        } else {
            return response()->json(['error' => 'Hubo un error al actualizar el Tipo de Persona.'], 500);
        }
    }

    public function eliminar($id)
    {
        try {
            $this->authorize('delete', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'No tienes permisos para eliminar un Tipo de Persona.']);
        }

        // Obtener los datos del Tipo de Persona antes de eliminarlo para el log de actividad
        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl . '/SEL_TBL_TIPO_PERSONAS');
        if (!$response->successful()) {
            return redirect()->route('TipoPersona')->withErrors('Error al obtener los datos del Tipo de Persona.');
        }

        $tipoPersonas = $response->json();
        $tipoPersonaActual = collect($tipoPersonas)->firstWhere('ID_TIPO_PERSONA', $id);
        if (is_null($tipoPersonaActual)) {
            return redirect()->route('TipoPersona')->withErrors('No se encontraron datos para el Tipo de Persona.');
        }

        // Realizar la eliminación
        $response = Http::post($baseUrl . '/DEL_TBL_TIPO_PERSONAS', ['P_ID_TIPO_PERSONA' => $id]);

        if ($response->successful()) {
            // Registrar la actividad en los logs
            $this->logActivity('tipo_persona', 'delete', [], $tipoPersonaActual);
            return response()->json(['success' => 'Tipo de Persona eliminado correctamente.']);
        } else {
            $errorMessage = $response->json('error'); // Asegúrate de capturar el mensaje de error
            if (str_contains($errorMessage, 'relacionado con otros registros')) {
                return response()->json(['error' => 'No se puede eliminar el Tipo de Persona porque está relacionado con otros registros.']);
            }
            return response()->json(['error' => 'Error al eliminar Tipo de Persona.']);
    }
    }

    public function generarReporte(Request $request)
    {
        $query = strtoupper($request->input('descripcion'));
        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl . '/SEL_TBL_TIPO_PERSONAS');
        $tipoPersonas = $response->json();

        if ($response->successful()) {
            if ($query) {
                $tipoPersonas = array_filter($tipoPersonas, function ($tipoPersona) use ($query) {
                    return stripos($tipoPersona['DESCRIPCION'], $query) !== false;
                });
            }

            // Generar el PDF
            $pdf = Pdf::loadView('reportes.TipoPersonas', compact('tipoPersonas'));
            return $pdf->stream('reporte_TipoPersonas.pdf');
        } else {
            return back()->withErrors(['error' => 'No se pudo obtener la lista de tipos de personas.']);
        }
    }
}
