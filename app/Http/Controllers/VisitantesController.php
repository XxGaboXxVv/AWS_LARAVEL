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


class VisitantesController extends Controller
{
    use LogsActivity, HandlesAuthorizationExceptions;

    public function Visitante()
    {
        $hasPermission = true;
        try {
            $this->authorize('view', User::class);
        } catch (AuthorizationException $e) {
            $hasPermission = false;
        }
        $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
        $response = Http::get($baseUrl.'/SEL_REGVISITAS');
        $visitantes = $response->json();
        
        // Registrar la actividad solo si se tiene permiso
        if ($hasPermission) {
            $this->logActivity('visitantes', 'get');
        }

        // Obtener personas
        $personas = $this->getPersonas();

        // Asignar los nombres de personas a los visitantes
        foreach ($visitantes as &$regvisita) {
            $regvisita['PERSONA'] = $personas->firstWhere('ID_PERSONA', $regvisita['ID_PERSONA'])->NOMBRE_PERSONA ?? 'Desconocido';
        }

        return view('visitantes', compact('visitantes', 'personas','hasPermission'));
    }

    public function getPersonas()
    {
        return DB::table('TBL_PERSONAS')->select('ID_PERSONA', 'NOMBRE_PERSONA')->get();
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
        'nombre_visitante' => 'required|string|max:100',
        'dni_visitante' => 'required|string|max:50',
        'num_personas' => 'required|integer',
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

        $date = new DateTime('now', new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone('America/Tegucigalpa'));

        // Crear nuevo registro de visita
        $idVisitante = DB::table('TBL_REGVISITAS')->insertGetId([
            'ID_PERSONA' => $persona->ID_PERSONA,
            'NOMBRE_VISITANTE' => $data['nombre_visitante'],
            'DNI_VISITANTE' => $data['dni_visitante'],
            'NUM_PERSONAS' => $data['num_personas'],
            'NUM_PLACA' => $data['num_placa'],
            'FECHA_HORA' => $date->format('Y-m-d H:i:s'),
        ]);

        // Crear nuevo registro de bitácora de visita
        DB::table('TBL_BITACORA_VISITA')->insert([
            'ID_PERSONA' => $persona->ID_PERSONA,
            'ID_VISITANTE' => $idVisitante,
            'NUM_PERSONA' => $data['num_personas'],
            'NUM_PLACA' => $data['num_placa'],
            'FECHA_HORA' => $date->format('Y-m-d H:i:s'),
        ]);

        // Registrar la actividad en los logs (opcional)
        $this->logActivity('visita', 'post', $data);

        return response()->json(['success' => 'Visitante creado con éxito.']);
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

    // Validar los nuevos datos
    $data = $request->validate([
        'persona_descripcion' => 'required|string|max:255',
        'nombre_visitante' => 'required|string|max:100',
        'dni_visitante' => 'required|string|max:50',
        'num_personas' => 'required|integer',
        'num_placa' => 'nullable|string|max:15',
    ]);

    // Verificar si la persona existe
    $persona = DB::table('TBL_PERSONAS')
        ->where('NOMBRE_PERSONA', $data['persona_descripcion'])
        ->first();

    if (!$persona) {
        return response()->json(['error' => 'La persona no existe.'], 400);
    }

    // Obtener los datos actuales del visitante
    $baseUrl = Config::get('api.base_url');
    $response = Http::get($baseUrl.'/SEL_REGVISITAS');
    if (!$response->successful()) {
        return redirect()->route('Visitantes')->withErrors('Error al obtener los datos del visitante.');
    }
    $visitantes = $response->json();
    $visitanteActual = collect($visitantes)->firstWhere('ID_VISITANTE', $id);
    if (is_null($visitanteActual)) {
        return redirect()->route('Visitantes')->withErrors('No se encontraron datos para el visitante.');
    }

    // Actualizar los datos del visitante
    $updateResponse = Http::post($baseUrl.'/PUT_REGVISITAS', [
        'P_ID_VISITANTE' => $id,
        'P_ID_PERSONA' => $persona->ID_PERSONA,
        'P_NOMBRE_VISITANTE' => $data['nombre_visitante'],
        'P_DNI_VISITANTE' => $data['dni_visitante'],
        'P_NUM_PERSONAS' => $data['num_personas'],
        'P_NUM_PLACA' => $data['num_placa'],
        'P_FECHA_HORA' => now()->format('Y-m-d H:i:s'),
    ]);

    if (!$updateResponse->successful()) {
        return response()->json(['error' => 'Error al actualizar el visitante.'], 500);
    }

    // Actualizar el registro en la bitácora de visita
    DB::table('TBL_BITACORA_VISITA')->where('ID_VISITANTE', $id)->update([
        'ID_PERSONA' => $persona->ID_PERSONA,
        'NUM_PERSONA' => $data['num_personas'],
        'NUM_PLACA' => $data['num_placa'],
        'FECHA_HORA' => now()->format('Y-m-d H:i:s'),
    ]);

    // Registrar la actividad en los logs
    $oldData = [
        'P_ID_PERSONA' => $visitanteActual['ID_PERSONA'],
        'P_NOMBRE_VISITANTE' => $visitanteActual['NOMBRE_VISITANTE'],
        'P_DNI_VISITANTE' => $visitanteActual['DNI_VISITANTE'],
        'P_NUM_PERSONAS' => $visitanteActual['NUM_PERSONAS'],
        'P_NUM_PLACA' => $visitanteActual['NUM_PLACA'],
        'P_FECHA_HORA' => $visitanteActual['FECHA_HORA'],
    ];

    $newData = [
        'P_ID_PERSONA' => $persona->ID_PERSONA,
        'P_NOMBRE_VISITANTE' => $data['nombre_visitante'],
        'P_DNI_VISITANTE' => $data['dni_visitante'],
        'P_NUM_PERSONAS' => $data['num_personas'],
        'P_NUM_PLACA' => $data['num_placa'],
        'P_FECHA_HORA' => now()->format('Y-m-d H:i:s'),
    ];

    $this->logActivity(
        'visitante ' . $visitanteActual['ID_VISITANTE'],
        'put',
        $newData,
        $oldData
    );

    return response()->json(['success' => 'Visitante actualizado correctamente.']);
}



    public function eliminar($id)
    {
        try {
            $this->authorize('delete', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Eliminar.']);
        }

        // Obtener los datos del visitante antes de eliminarlo para el log de actividad
        $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
        $response = Http::get($baseUrl.'/SEL_REGVISITAS');
        if (!$response->successful()) {
            return redirect()->route('Visitantes')->withErrors('Error al obtener los datos del visitante.');
        }
        $visitantes = $response->json();
        $visitanteActual = collect($visitantes)->firstWhere('ID_VISITANTE', $id);
        if (is_null($visitanteActual)) {
            return redirect()->route('Visitantes')->withErrors('No se encontraron datos para el visitante.');
        }

        // Realizar la eliminación
        $response = Http::post($baseUrl.'/DEL_REGVISITAS', ['P_ID_VISITANTE' => $id]);

        if ($response->successful()) {
            $this->logActivity('residente', 'delete', [], $visitanteActual);
            return response()->json(['success' => 'Visitante eliminado con éxito.']);
        } else {
            $errorMessage = $response->json('error'); // Asegúrate de capturar el mensaje de error
            if (str_contains($errorMessage, 'relacionado con otros registros')) {
                return response()->json(['error' => 'No se puede eliminar el Visitante porque está relacionado con otros registros.']);
            }
            return response()->json(['error' => 'Error al eliminar Estado.']);
    }
}

    public function generarReporte(Request $request)
    {
        $query = strtoupper($request->input('persona_descripcion'));
        $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
        $response = Http::get($baseUrl.'/SEL_REGVISITAS');
        if ($response->successful()) {
            $visitantes = $response->json();

            // Obtener los datos adicionales
            $personas = $this->getPersonas();

            // Asignar los nombres de personas a visitantes
            foreach ($visitantes as &$regvisita) {
                $regvisita['PERSONA'] = $personas->firstWhere('ID_PERSONA', $regvisita['ID_PERSONA'])->NOMBRE_PERSONA ?? 'Desconocido';
            }

            // Filtrar los visitantes si se ha proporcionado un nombre de PERSONA
            if ($query) {
                $visitantes = array_filter($visitantes, function ($regvisita) use ($query) {
                    return stripos($regvisita['PERSONA'], $query) !== false;
                });
            }

            // Generar el PDF
            $pdf = Pdf::loadView('reportes.Visitantes', ['visitantes' => $visitantes]);

            return $pdf->stream('reporte_visitantes.pdf');
        } else {
            return response()->json(['error' => 'No se pudo generar el reporte.'], 500);
 }
}
}