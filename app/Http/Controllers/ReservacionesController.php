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

class ReservacionesController extends Controller
{
    use LogsActivity, HandlesAuthorizationExceptions;

    public function reserva()
    {
        $hasPermission = true;
        try {
            $this->authorize('view', User::class);
        } catch (AuthorizationException $e) {
            $hasPermission = false;
        }
        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_TBL_RESERVAS');
        $reservaciones = $response->json();

        $personas = $this->getPersonas();
        $instalaciones = $this->getInstalaciones();
        $estadoreservas = $this->getEstadoreservas();

        foreach ($reservaciones as &$reserva) {
            $reserva['PERSONA'] = $personas->firstWhere('ID_PERSONA', $reserva['ID_PERSONA'])->NOMBRE_PERSONA ?? 'Desconocido';
            $reserva['INSTALACION'] = $instalaciones->firstWhere('ID_INSTALACION', $reserva['ID_INSTALACION'])->NOMBRE_INSTALACION ?? 'Desconocido';
            $reserva['ESTADO_RESERVA'] = $estadoreservas->firstWhere('ID_ESTADO_RESERVA', $reserva['ID_ESTADO_RESERVA'])->DESCRIPCION ?? 'Desconocido';
        }

        if ($hasPermission) {
            $this->logActivity('reservaciones', 'get');
        }

        return view('reservaciones', compact('reservaciones', 'personas', 'instalaciones', 'estadoreservas', 'hasPermission'));
    }

    public function getPersonas()
    {
        return DB::table('TBL_PERSONAS')->select('ID_PERSONA', 'NOMBRE_PERSONA')->get();
    }

    public function getInstalaciones()
    {
        return DB::table('TBL_INSTALACIONES')->select('ID_INSTALACION', 'NOMBRE_INSTALACION')->get();
    }

    public function getEstadoreservas()
    {
        return DB::table('TBL_ESTADO_RESERVA')->select('ID_ESTADO_RESERVA', 'DESCRIPCION')->get();
    }

    public function crear(Request $request)
    {
        try {
            $this->authorize('insert', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'No tienes permisos para poder Crear.']);
        }

        $data = $request->validate([
            'persona_descripcion' => 'required|string|max:255',
            'id_instalacion' => 'required|integer',
            'id_estado_reserva' => 'required|integer',
            'tipo_evento' => 'required|string|max:70',
        ]);

        try {
            $persona = DB::table('TBL_PERSONAS')
                ->where('NOMBRE_PERSONA', $data['persona_descripcion'])
                ->first();

            if (!$persona) {
                return response()->json(['error' => 'La persona no existe.'], 400);
            }

            $date = new DateTime('now', new DateTimeZone('UTC'));
            $date->setTimezone(new DateTimeZone('America/Tegucigalpa'));

            DB::table('TBL_RESERVAS')->insert([
                'ID_PERSONA' => $persona->ID_PERSONA,
                'ID_INSTALACION' => $data['id_instalacion'],
                'ID_ESTADO_RESERVA' => $data['id_estado_reserva'],
                'TIPO_EVENTO' => $data['tipo_evento'],
                'HORA_FECHA' => $date->format('Y-m-d H:i:s'),
            ]);

            $this->logActivity('reserva', 'post', $data);

            return response()->json(['success' => 'Reserva creada con éxito.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Se produjo un error: ' . $e->getMessage()], 500);
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
            'persona_descripcion' => 'required|string|max:255',
            'id_instalacion' => 'required|integer',
            'id_estado_reserva' => 'required|integer',
            'tipo_evento' => 'required|string|max:70',
        ]);

        $persona = DB::table('TBL_PERSONAS')
            ->where('NOMBRE_PERSONA', $data['persona_descripcion'])
            ->first();

        if (!$persona) {
            return response()->json(['error' => 'La persona no existe.'], 400);
        }

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_TBL_RESERVAS');
        if (!$response->successful()) {
            return redirect()->route('reservaciones')->withErrors('Error al obtener los datos de la reserva.');
        }

        $reservas = $response->json();
        $reservaActual = collect($reservas)->firstWhere('ID_RESERVA', $id);

        if (is_null($reservaActual)) {
            return redirect()->route('reservaciones')->withErrors('No se encontraron datos para la reserva.');
        }

        $updateResponse = Http::post($baseUrl.'/PUT_TBL_RESERVAS', [
            'P_ID_RESERVA' => $id,
            'P_ID_PERSONA' => $persona->ID_PERSONA,
            'P_ID_INSTALACION' => $data['id_instalacion'],
            'P_ID_ESTADO_RESERVA' => $data['id_estado_reserva'],
            'P_TIPO_EVENTO' => $data['tipo_evento'],
            'P_HORA_FECHA' => now()->format('Y-m-d H:i:s'),
        ]);

        if (!$updateResponse->successful()) {
            return response()->json(['error' => 'Error al actualizar la reserva.'], 500);
        }

        $oldData = [
            'P_ID_PERSONA' => $reservaActual['ID_PERSONA'],
            'P_ID_INSTALACION' => $reservaActual['ID_INSTALACION'],
            'P_ID_ESTADO_RESERVA' => $reservaActual['ID_ESTADO_RESERVA'],
            'P_TIPO_EVENTO' => $reservaActual['TIPO_EVENTO'],
            'P_HORA_FECHA' => $reservaActual['HORA_FECHA'],
        ];

        $newData = [
            'P_ID_PERSONA' => $persona->ID_PERSONA,
            'P_ID_INSTALACION' => $data['id_instalacion'],
            'P_ID_ESTADO_RESERVA' => $data['id_estado_reserva'],
            'P_TIPO_EVENTO' => $data['tipo_evento'],
            'P_HORA_FECHA' => now()->format('Y-m-d H:i:s'),
        ];

        $this->logActivity(
            'reserva ' . $reservaActual['ID_RESERVA'],
            'put',
            $newData,
            $oldData
        );

        return response()->json(['success' => 'Reserva actualizada correctamente.']);
    }

    public function eliminar($id)
    {
        try {
            $this->authorize('delete', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'No tienes permisos para poder Eliminar.']);
        }

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_TBL_RESERVAS');
        if (!$response->successful()) {
            return redirect()->route('reservaciones')->withErrors('Error al obtener los datos de la reserva.');
        }
        $reservas = $response->json();
        $reservaActual = collect($reservas)->firstWhere('ID_RESERVA', $id);
        if (is_null($reservaActual)) {
            return redirect()->route('reservaciones')->withErrors('No se encontraron datos para la reserva.');
        }

        $response = Http::post($baseUrl.'/DEL_TBL_RESERVAS', ['P_ID_RESERVA' => $id]);

        if ($response->successful()) {
            $this->logActivity('reserva', 'delete', [], $reservaActual);
            return response()->json(['success' => 'Reserva eliminada con éxito.']);
        } else {
            return response()->json(['error' => 'Error al eliminar reserva.'], 500);
        }
    }

    public function generarReporte(Request $request)
    {
        $query = strtoupper($request->input('persona_descripcion'));
        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_TBL_RESERVAS');
        
        if ($response->successful()) {
            $reservaciones = $response->json();

            // Obtener datos adicionales
            $personas = $this->getPersonas();
            $instalaciones = $this->getInstalaciones();
            $estadoreservas = $this->getEstadoreservas();

            // Asignar los nombres de personas, instalaciones y estado reservas en Reservaciones
            foreach ($reservaciones as &$reserva) {
                $reserva['PERSONA'] = $personas->firstWhere('ID_PERSONA', $reserva['ID_PERSONA'])->NOMBRE_PERSONA ?? 'Desconocido';
                $reserva['INSTALACION'] = $instalaciones->firstWhere('ID_INSTALACION', $reserva['ID_INSTALACION'])->NOMBRE_INSTALACION ?? 'Desconocido';
                $reserva['ESTADO_RESERVA'] = $estadoreservas->firstWhere('ID_ESTADO_RESERVA', $reserva['ID_ESTADO_RESERVA'])->DESCRIPCION ?? 'Desconocido';
            }

            // Filtrar las reservaciones si se ha proporcionado un nombre de PERSONA
            if ($query) {
                $reservaciones = array_filter($reservaciones, function ($reserva) use ($query) {
                    return stripos($reserva['PERSONA'], $query) !== false;
                });
            }

            // Generar el PDF
            $pdf = Pdf::loadView('reportes.Reservaciones', ['reservaciones' => $reservaciones]);

            return $pdf->stream('reporte_Reservaciones.pdf');
        } else {
            return response()->json(['error' => 'No se pudo generar el reporte.'], 500);
}
}
}