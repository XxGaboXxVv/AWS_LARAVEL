<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use DateTimeZone;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use App\Traits\LogsActivity;
use Illuminate\Auth\Access\AuthorizationException;
use App\Traits\HandlesAuthorizationExceptions;

class BitacoraVisita extends Controller
{
    use LogsActivity, HandlesAuthorizationExceptions;

    public function bitacora()
    {
        $hasPermission = true;
        try {
            $this->authorize('view', User::class);
        } catch (AuthorizationException $e) {
            $hasPermission = false;
        }

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl .'/SEL_BITACORA_VISITA');
        $bitacoraVisitas = $response->json();

        // Registrar la actividad solo si se tiene permiso
        if ($hasPermission) {
            $this->logActivity('bitacora_visita', 'get');
        }

        // Obtener personas y visitantes
        $personas = $this->getPersonas();
        $visitantes = $this->getRegvisitas();

        // Asignar los nombres de personas y visitantes a las bitácoras de visitas
        foreach ($bitacoraVisitas as &$visita) {
            $visita['PERSONA'] = $personas->firstWhere('ID_PERSONA', $visita['ID_PERSONA'])->NOMBRE_PERSONA ?? 'Desconocido';
            $visita['VISITANTE'] = $visitantes->firstWhere('ID_VISITANTE', $visita['ID_VISITANTE'])->NOMBRE_VISITANTE ?? 'Desconocido';
        }

        return view('BitacoraVisita', compact('bitacoraVisitas', 'personas', 'visitantes', 'hasPermission'));
    }

    public function getPersonas()
    {
        return DB::table('TBL_PERSONAS')->select('ID_PERSONA', 'NOMBRE_PERSONA')->get();
    }

    public function getRegvisitas()
    {
        return DB::table('TBL_REGVISITAS')->select('ID_VISITANTE', 'NOMBRE_VISITANTE')->get();
    }

    public function crear(Request $request)
    {
        try {
            $this->authorize('insert', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Crear.']);
        }

        $data = $request->validate([
            'persona_descripcion' => 'required|string|max:255',
            'visita_descripcion' => 'required|string|max:255',
            'num_persona' => 'required|integer',
            'num_placa' => 'nullable|string|max:15',
        ]);

        try {
            // Verificar si la persona existe
            $persona = DB::table('TBL_PERSONAS')
                ->where('NOMBRE_PERSONA', $data['persona_descripcion'])
                ->first();

            if (!$persona) {
                return response()->json(['error' => 'La persona no existe.'], 400);
            }

            // Verificar si el visitante existe
            $visita = DB::table('TBL_REGVISITAS')
                ->where('NOMBRE_VISITANTE', $data['visita_descripcion'])
                ->first();

            if (!$visita) {
                return response()->json(['error' => 'El visitante no existe.'], 400);
            }

            $date = new DateTime('now', new DateTimeZone('UTC'));
            $date->setTimezone(new DateTimeZone('America/Tegucigalpa'));

            // Crear nuevo registro de bitácora de visita
            DB::table('TBL_BITACORA_VISITA')->insert([
                'ID_PERSONA' => $persona->ID_PERSONA,
                'ID_VISITANTE' => $visita->ID_VISITANTE,
                'NUM_PERSONA' => $data['num_persona'],
                'NUM_PLACA' => $data['num_placa'],
                'FECHA_HORA' => $date->format('Y-m-d H:i:s'),
            ]);

            // Registrar la actividad en los logs
            $this->logActivity('bitacora_visita', 'post', $data);
            return response()->json(['success' => 'Bitácora de visita creada con éxito.']);

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
            'persona_descripcion' => 'required|string|max:255',
            'visita_descripcion' => 'required|string|max:255',
            'num_persona' => 'required|integer',
            'num_placa' => 'nullable|string|max:15',
        ]);

        // Verificar si la persona existe
        $persona = DB::table('TBL_PERSONAS')
            ->where('NOMBRE_PERSONA', $data['persona_descripcion'])
            ->first();

        if (!$persona) {
            return response()->json(['error' => 'La persona no existe.'], 400);
        }

        // Verificar si el visitante existe
        $visita = DB::table('TBL_REGVISITAS')
            ->where('NOMBRE_VISITANTE', $data['visita_descripcion'])
            ->first();

        if (!$visita) {
            return response()->json(['error' => 'El visitante no existe.'], 400);
        }

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_BITACORA_VISITA');
        if (!$response->successful()) {
            return redirect()->route('BitacoraVisita')->withErrors('Error al obtener los datos de la bitácora de visita.');
        }
        $bitacoraVisitas = $response->json();
        $visitaActual = collect($bitacoraVisitas)->firstWhere('ID_BITACORA_VISITA', $id);
        if (is_null($visitaActual)) {
            return redirect()->route('BitacoraVisita')->withErrors('No se encontraron datos para la bitácora de visita.');
        }

        // Actualizar los datos de la bitácora de visita
        $updateResponse = Http::post($baseUrl.'/PUT_BITACORA_VISITA', [
            'P_ID_BITACORA_VISITA' => $id,
            'P_ID_PERSONA' => $persona->ID_PERSONA,
            'P_ID_VISITANTE' => $visita->ID_VISITANTE,
            'P_NUM_PERSONA' => $data['num_persona'],
            'P_NUM_PLACA' => $data['num_placa'],
            'P_FECHA_HORA' => now()->format('Y-m-d H:i:s'),
        ]);

        if (!$updateResponse->successful()) {
            return response()->json(['error' => 'Error al actualizar la bitácora de visita.'], 500);
        }

        // Registrar la actividad en los logs
        $oldData = [
            'P_ID_PERSONA' => $visitaActual['ID_PERSONA'],
            'P_ID_VISITANTE' => $visitaActual['ID_VISITANTE'],
            'P_NUM_PERSONA' => $visitaActual['NUM_PERSONA'],
            'P_NUM_PLACA' => $visitaActual['NUM_PLACA'],
            'P_FECHA_HORA' => $visitaActual['FECHA_HORA'],
        ];

        $newData = [
            'P_ID_PERSONA' => $persona->ID_PERSONA,
            'P_ID_VISITANTE' => $visita->ID_VISITANTE,
            'P_NUM_PERSONA' => $data['num_persona'],
            'P_NUM_PLACA' => $data['num_placa'],
            'P_FECHA_HORA' => now()->format('Y-m-d H:i:s'),
        ];

        $this->logActivity('bitacora_visita ' . $visitaActual['ID_BITACORA_VISITA'], 'put', $newData, $oldData);

        return response()->json(['success' => 'Bitácora de visita actualizada correctamente.']);
    }

    public function eliminar($id)
    {
        try {
            $this->authorize('delete', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Eliminar.']);
        }

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_BITACORA_VISITA');
        if (!$response->successful()) {
            return redirect()->route('BitacoraVisita')->withErrors('Error al obtener los datos de la bitácora de visita.');
        }
        $bitacoraVisitas = $response->json();
        $visitaActual = collect($bitacoraVisitas)->firstWhere('ID_BITACORA_VISITA', $id);
        if (is_null($visitaActual)) {
            return redirect()->route('BitacoraVisita')->withErrors('No se encontraron datos para la bitácora de visita.');
        }

        // Eliminar la bitácora de visita
        $deleteResponse = Http::post($baseUrl.'/DEL_BITACORA_VISITA', [
            'P_ID_BITACORA_VISITA' => $id,
        ]);

        if (!$deleteResponse->successful()) {
            return response()->json(['error' => 'Error al eliminar la bitácora de visita.'], 500);
        }

        // Registrar la actividad en los logs
        $this->logActivity('bitacora_visita', 'delete', ['id' => $id]);

        return response()->json(['success' => 'Bitácora de visita eliminada correctamente.']);
    }

    public function generarReporte(Request $request)
    {
        $query = strtoupper($request->input('persona_descripcion'));
        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_BITACORA_VISITA');
        $bitacoraVisitas = $response->json();

        // Obtener personas y visitantes
        $personas = $this->getPersonas();
        $visitantes = $this->getRegvisitas();

            
            
            // Asignar los nombres de personas y visitantes a las bitácoras de visitas
            foreach ($bitacoraVisitas as &$visita) {
                $visita['PERSONA'] = $personas->firstWhere('ID_PERSONA', $visita['ID_PERSONA'])->NOMBRE_PERSONA ?? 'Desconocido';
                $visita['VISITANTE'] = $visitantes->firstWhere('ID_VISITANTE', $visita['ID_VISITANTE'])->NOMBRE_VISITANTE ?? 'Desconocido';
            }

            if ($response->successful()) {
                if ($query) {
                    $bitacoraVisitas = array_filter($bitacoraVisitas, function($visita) use ($query) {
                        return stripos($visita['PERSONA'], $query) !== false;
                    });
                }
            // Generar el PDF
            $pdf = Pdf::loadView('reportes.BitacoraVisitas', compact('bitacoraVisitas', 'personas', 'visitantes'));
            return $pdf->stream('reporte_BitacoraVisitas.pdf');
        } else {
            // Manejar el caso en que la solicitud a la API falle
            return back()->withErrors(['error' => 'No se pudo obtener la lista de bitácora de visitas.']);
 }
}
}