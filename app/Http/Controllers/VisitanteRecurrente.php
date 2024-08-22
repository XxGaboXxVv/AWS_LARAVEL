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

class VisitanteRecurrente extends Controller
{
   use LogsActivity, HandlesAuthorizationExceptions;

    public function getRecurrente()
    {

    $hasPermission = true;
        try {
            $this->authorize('view', User::class);
        } catch (AuthorizationException $e) {
            $hasPermission = false;
        }
        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_VISITANTES_RECURRENTES');
        $Recurrentes = $response->json();

        // Obtener personas
        $personas = $this->getPersonas();

        // Asignar los nombres de personas a los Visitantes Recurrentes
        foreach ($Recurrentes as &$recurrente) {
            $recurrente['PERSONA'] = $personas->firstWhere('ID_PERSONA', $recurrente['ID_PERSONA'])->NOMBRE_PERSONA ?? 'Desconocido';
        }

        return view('VisitanteRecurrentes', compact('Recurrentes', 'personas','hasPermission'));
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
        return response()->json(['error' => 'No tienes permisos para poder Crear.']);
    }

    // Validar los datos del formulario
    $data = $request->validate([
        'persona_descripcion' => 'required|string|max:255',
        'nombre_visitante' => 'required|string|max:60',
        'dni_visitante' => 'required|string|max:50',
        'num_personas' => 'required|integer',
        'num_placa' => 'nullable|string|max:30',
        'fecha_vencimiento' => 'required|date',
    ]);

    try {
        // Verificar si la persona existe
        $persona = DB::table('TBL_PERSONAS')
            ->where('NOMBRE_PERSONA', $data['persona_descripcion'])
            ->first();

        if (!$persona) {
            return response()->json(['error' => 'La persona no existe.'], 400);
        }

        // Obtener la fecha actual en la zona horaria de Tegucigalpa
        $date = new DateTime('now', new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone('America/Tegucigalpa'));

        // Crear nuevo registro de visitante recurrente
        $idVisitante = DB::table('TBL_VISITANTES_RECURRENTES')->insertGetId([
            'ID_PERSONA' => $persona->ID_PERSONA,
            'NOMBRE_VISITANTE' => $data['nombre_visitante'],
            'DNI_VISITANTE' => $data['dni_visitante'],
            'NUM_PERSONAS' => $data['num_personas'],
            'NUM_PLACA' => $data['num_placa'],
            'FECHA_HORA' => $date->format('Y-m-d H:i:s'),
            'FECHA_VENCIMIENTO' => $data['fecha_vencimiento'],
        ]);

        // Crear nuevo registro de bitácora de visita
        DB::table('TBL_BITACORA_VISITA')->insert([
            'ID_PERSONA' => $persona->ID_PERSONA,
            'ID_VISITANTE' => $idVisitante,
            'NUM_PERSONA' => $data['num_personas'],
            'NUM_PLACA' => $data['num_placa'],
            'FECHA_HORA' => $date->format('Y-m-d H:i:s'),
            'FECHA_VENCIMIENTO' => $data['fecha_vencimiento'],
        ]);

        // Registrar la actividad en los logs (opcional)
        $this->logActivity('visitante recurrente', 'post', $data);

        return response()->json(['success' => 'Visitante recurrente creado con éxito.']);
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

    // Validar los datos proporcionados por el usuario
    $data = $request->validate([
        'persona_descripcion' => 'required|string|max:255',
        'nombre_visitante' => 'required|string|max:60',
        'dni_visitante' => 'required|string|max:50',
        'num_personas' => 'required|integer',
        'num_placa' => 'nullable|string|max:30',
        'fecha_vencimiento' => 'required|date',
    ]);

    // Verificar si la persona existe en la base de datos
    $persona = DB::table('TBL_PERSONAS')
        ->where('NOMBRE_PERSONA', $data['persona_descripcion'])
        ->first();

    if (!$persona) {
        return response()->json(['error' => 'La persona no existe.'], 400);
    }

    // Obtener los datos actuales del visitante recurrente desde el API
    $baseUrl = Config::get('api.base_url');
    $response = Http::get($baseUrl.'/SEL_VISITANTES_RECURRENTES');
    if (!$response->successful()) {
        return response()->json(['error' => 'Error al obtener los datos del visitante recurrente.'], 500);
    }

    // Buscar el visitante recurrente específico por ID
    $visitantes = $response->json();
    $visitanteActual = collect($visitantes)->firstWhere('ID_VISITANTES_RECURRENTES', $id);
    if (is_null($visitanteActual)) {
        return response()->json(['error' => 'No se encontraron datos para el visitante recurrente.'], 404);
    }

    // Actualizar los datos del visitante recurrente utilizando el API
    $updateResponse = Http::post($baseUrl.'/PUT_VISITANTES_RECURRENTES', [
        'P_ID_VISITANTES_RECURRENTES' => $id,
        'P_ID_PERSONA' => $persona->ID_PERSONA,
        'P_NOMBRE_VISITANTE' => $data['nombre_visitante'],
        'P_DNI_VISITANTE' => $data['dni_visitante'],
        'P_NUM_PERSONAS' => $data['num_personas'],
        'P_NUM_PLACA' => $data['num_placa'],
        'P_FECHA_HORA' => now()->format('Y-m-d H:i:s'),  // Actualización con la fecha y hora actual
        'P_FECHA_VENCIMIENTO' => $data['fecha_vencimiento'],
    ]);

    if (!$updateResponse->successful()) {
        return response()->json(['error' => 'Error al actualizar el visitante recurrente.'], 500);
    }

    // Actualizar los datos en la bitácora de visita
    DB::table('TBL_BITACORA_VISITA')
        ->where('ID_VISITANTE', $id)
        ->update([
            'ID_PERSONA' => $persona->ID_PERSONA,
            'NUM_PERSONA' => $data['num_personas'],
            'NUM_PLACA' => $data['num_placa'],
            'FECHA_HORA' => now()->format('Y-m-d H:i:s'),
            'FECHA_VENCIMIENTO' => $data['fecha_vencimiento'],
        ]);

    // Registrar la actividad en los logs comparando los datos antiguos y los nuevos
    $oldData = [
        'P_ID_PERSONA' => $visitanteActual['ID_PERSONA'],
        'P_NOMBRE_VISITANTE' => $visitanteActual['NOMBRE_VISITANTE'],
        'P_DNI_VISITANTE' => $visitanteActual['DNI_VISITANTE'],
        'P_NUM_PERSONAS' => $visitanteActual['NUM_PERSONAS'],
        'P_NUM_PLACA' => $visitanteActual['NUM_PLACA'],
        'P_FECHA_HORA' => $visitanteActual['FECHA_HORA'],
        'P_FECHA_VENCIMIENTO' => $visitanteActual['FECHA_VENCIMIENTO'],
    ];

    $newData = [
        'P_ID_PERSONA' => $persona->ID_PERSONA,
        'P_NOMBRE_VISITANTE' => $data['nombre_visitante'],
        'P_DNI_VISITANTE' => $data['dni_visitante'],
        'P_NUM_PERSONAS' => $data['num_personas'],
        'P_NUM_PLACA' => $data['num_placa'],
        'P_FECHA_HORA' => now()->format('Y-m-d H:i:s'),
        'P_FECHA_VENCIMIENTO' => $data['fecha_vencimiento'],
    ];

    $this->logActivity(
        'visitante recurrente ' . $visitanteActual['ID_VISITANTES_RECURRENTES'],
        'put',
        $newData,
        $oldData
    );

    return response()->json(['success' => 'Visitante recurrente actualizado correctamente.']);
}





public function eliminar($id)
{
    try {
            $this->authorize('delete', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Eliminar.']);
        }

    // Obtener los datos del visitante recurrente antes de eliminarlo para el log de actividad
     $baseUrl = Config::get('api.base_url');
    $response = Http::get($baseUrl.'/SEL_VISITANTES_RECURRENTES');
    if (!$response->successful()) {
        return redirect()->route('VisitanteRecurrente')->withErrors('Error al obtener los datos del visitante recurrente.');
    }
    $visitantesRecurrentes = $response->json();
    $visitanteRecurrenteActual = collect($visitantesRecurrentes)->firstWhere('ID_VISITANTES_RECURRENTES', $id);
    if (is_null($visitanteRecurrenteActual)) {
        return redirect()->route('VisitanteRecurrente')->withErrors('No se encontraron datos para el visitante recurrente.');
    }

    // Realizar la eliminación
    $deleteResponse = Http::post($baseUrl.'/DEL_VISITANTES_RECURRENTES', [
        'P_ID_VISITANTES_RECURRENTES' => $id
    ]);

    if ($deleteResponse->successful()) {
        $descripcion = 'Visitante recurrente eliminado: ' . $visitanteRecurrenteActual['ID_PERSONA'] . ' (ID: ' . $id . ')';
        $this->logActivity($descripcion, 'DELETE');
        return response()->json(['success' => 'Visitante recurrente eliminado con éxito.']);
    } else {
        return response()->json(['error' => 'Error al eliminar visitante recurrente.'], 500);
    }
}

    public function generarReporte(Request $request)
{
    $query = strtoupper($request->input('persona_descripcion'));
    $baseUrl = Config::get('api.base_url');
    $response = Http::get($baseUrl.'/SEL_VISITANTES_RECURRENTES');
    
    if ($response->successful()) {
        $Recurrentes = $response->json();

       

        // Obtener los datos adicionales de personas
        $personas = $this->getPersonas();

        // Asignar los nombres de personas a los visitantes recurrentes
        foreach ($Recurrentes as &$recurrente) {
            $recurrente['PERSONA'] = $personas->firstWhere('ID_PERSONA', $recurrente['ID_PERSONA'])->NOMBRE_PERSONA ?? 'Desconocido';
        }
         // Filtrar los visitantes recurrentes si se ha proporcionado un nombre de residente
         if ($query) {
            $Recurrentes = array_filter($Recurrentes, function($recurrente) use ($query) {
                return stripos($recurrente['PERSONA'], $query) !== false;
            });
        }

        // Generar el PDF
        $pdf = Pdf::loadView('reportes.visitantesRecurrentes', compact('Recurrentes', 'personas'));

        return $pdf->stream('reporte_visitantesRecurrentes.pdf');
    } else {
        // Manejar el caso en que la solicitud a la API falle
        return back()->withErrors(['error' => 'No se pudo obtener la lista de visitantes recurrentes.']);
}
}
}
