<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\LogsActivity;
use Illuminate\Auth\Access\AuthorizationException;
use App\Traits\HandlesAuthorizationExceptions;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class Condominios extends Controller
{
    use LogsActivity, HandlesAuthorizationExceptions;

    public function getCondominios()
    {
        $hasPermission = true;
        try {
            $this->authorize('view', User::class);
        } catch (AuthorizationException $e) {
            $hasPermission = false;
        }

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_CONDOMINIOS');
        $Condominios = $response->json();

        // Registrar la actividad solo si se tiene permiso
        if ($hasPermission) {
            $this->logActivity('condominios', 'get');
        }

        // Obtener Tipo de Condominio
        $tipodecondominios = $this->getcondominio();

        // Asignar los nombres de Tipo de Condominio a los Condominios
        foreach ($Condominios as &$Condominio) {
            $tipoCondominio = $tipodecondominios->firstWhere('ID_TIPO_CONDOMINIO', $Condominio['ID_TIPO_CONDOMINIO']);
            $Condominio['TIPOCONDOMINIO'] = $tipoCondominio->DESCRIPCION ?? 'Desconocido';
        }
        
        return view('Condominios', compact('Condominios', 'tipodecondominios', 'hasPermission'));
    }
    
public function fetchCondominios(Request $request)
{
    $start = $request->input('start');
    $length = $request->input('length');
    $search = $request->input('search.value');

    $baseUrl = Config::get('api.base_url');
    $response = Http::get($baseUrl . '/SEL_CONDOMINIOS');
    $Condominios = $response->json();

   // Obtener Tipo de Condominio
        $tipodecondominios = $this->getcondominio();

        // Asignar los nombres de Tipo de Condominio a los Condominios
        foreach ($Condominios as &$Condominio) {
            $tipoCondominio = $tipodecondominios->firstWhere('ID_TIPO_CONDOMINIO', $Condominio['ID_TIPO_CONDOMINIO']);
            $Condominio['TIPOCONDOMINIO'] = $tipoCondominio->DESCRIPCION ?? 'Desconocido';
        }
    // Filtrado de búsqueda
    if ($search) {
        $Condominios = array_filter($Condominios, function ($Condominio) use ($search) {
            return stripos($Condominio['TIPOCONDOMINIO'], $search) !== false ||
            stripos($Condominio['DESCRIPCION'], $search) !== false;
                   
        });
    }

    // Paginación
    $totalData = count($Condominios);
    $Condominios = array_slice($Condominios, $start, $length);

    return response()->json([
        "draw" => intval($request->input('draw')),
        "recordsTotal" => $totalData,
        "recordsFiltered" => $totalData,
        "data" => $Condominios
    ]);
}

    public function getcondominio()
    {
        return DB::table('TBL_TIPO_CONDOMINIO')->select('ID_TIPO_CONDOMINIO', 'DESCRIPCION')->get();
    }

    public function crear(Request $request)
    {
        try {
            $this->authorize('insert', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Crear.']);
        }

        $data = $request->validate([
            'TIPOCONDOMINIO' => 'required|integer',
            'descripcion' => 'required|string|max:255',
        ]);

        $response = Http::post(Config::get('api.base_url').'/POST_CONDOMINIOS', [
            'P_ID_TIPO_CONDOMINIO' => $data['TIPOCONDOMINIO'],
            'P_DESCRIPCION' => $data['descripcion'],
        ]);

        if ($response->successful()) {
            $this->logActivity('condominio', 'post', $data);
            return response()->json(['success' => 'Condominio creado con éxito.']);
        } else {
            return response()->json(['error' => 'Hubo un problema al crear el condominio.'], 500);
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
            'tipo_condominio' => 'required|integer',
            'descripcion' => 'required|string|max:255',
        ]);

        // Obtener los datos actuales del condominio
        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_CONDOMINIOS');
        if (!$response->successful()) {
            return redirect()->route('Condominios')->withErrors('Error al obtener los datos del condominio.');
        }

        $Condominios = $response->json();
        $condominioActual = collect($Condominios)->firstWhere('ID_CONDOMINIO', $id);
        if (is_null($condominioActual)) {
            return redirect()->route('Condominios')->withErrors('No se encontraron datos para el condominio.');
        }

        // Actualizar los datos del condominio
        $updateResponse = Http::post($baseUrl.'/PUT_CONDOMINIOS', [
            'P_ID_CONDOMINIO' => $id,
            'P_ID_TIPO_CONDOMINIO' => $data['tipo_condominio'],
            'P_DESCRIPCION' => $data['descripcion'],
        ]);

        if (!$updateResponse->successful()) {
            return response()->json(['error' => 'Error al actualizar el condominio.'], 500);
        }
        
       // Registrar la actividad en los logs
       $oldData = [
    'P_ID_TIPO_CONDOMINIO' => $condominioActual['ID_TIPO_CONDOMINIO'],
    'P_DESCRIPCION' => $condominioActual['DESCRIPCION'],
];

        $newData = [
    'P_ID_TIPO_CONDOMINIO' => $data['tipo_condominio'],
    'P_DESCRIPCION' => $data['descripcion'],
];

     $this->logActivity(
    'condominio ' . $condominioActual['ID_CONDOMINIO'],
    'put',
    $newData,
    $oldData
);


        return response()->json(['success' => 'Condominio actualizado correctamente.']);
    }

    public function eliminar($id)
{
    try {
        $this->authorize('delete', User::class);
    } catch (AuthorizationException $e) {
        return response()->json(['error' => 'No tienes permisos para poder Eliminar.']);
    }

    // Obtener los datos del condominio antes de eliminarlo para el log de actividad
    $baseUrl = Config::get('api.base_url');
    $response = Http::get($baseUrl . '/SEL_CONDOMINIOS');
    if (!$response->successful()) {
        return redirect()->route('Condominios')->withErrors('Error al obtener los datos del condominio.');
    }

    $Condominios = $response->json();
    $condominioActual = collect($Condominios)->firstWhere('ID_CONDOMINIO', $id);
    if (is_null($condominioActual)) {
        return redirect()->route('Condominios')->withErrors('No se encontraron datos para el condominio.');
    }

    $deleteResponse = Http::post($baseUrl . '/DEL_CONDOMINIOS', ['P_ID_CONDOMINIO' => $id]);

    if ($deleteResponse->successful()) {
        $this->logActivity('condominio', 'delete', [], $condominioActual);
        return response()->json(['success' => 'Condominio eliminado con éxito.']);
    } else {
        // Capturar el mensaje de error correctamente
        $errorMessage = $deleteResponse->json('error');
        if (str_contains($errorMessage, 'relacionado con otros registros')) {
            return response()->json(['error' => 'No se puede eliminar el Condominio porque está relacionado con otros registros.']);
        }
        return response()->json(['error' => 'Error al eliminar Condominio.']);
    }
}

    public function generarReporte(Request $request)
    {
        $query = strtoupper($request->input('condominio'));

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_CONDOMINIOS');
        if (!$response->successful()) {
            return response()->json(['error' => 'No se pudo obtener la lista de condominios.'], 500);
        }

        $Condominios = $response->json();

        // Obtener Tipo de Condominio
        $tipodecondominios = $this->getcondominio();

        // Asignar los nombres de Tipo de Condominio a los Condominios
        foreach ($Condominios as &$Condominio) {
            $tipoCondominio = $tipodecondominios->firstWhere('ID_TIPO_CONDOMINIO', $Condominio['ID_TIPO_CONDOMINIO']);
            $Condominio['TIPOCONDOMINIO'] = $tipoCondominio->DESCRIPCION ?? 'Desconocido';
        }

        // Filtrar los condominios si se ha proporcionado un nombre de condominio
        if ($query) {
            $Condominios = array_filter($Condominios, function ($condominio) use ($query) {
                return strpos(strtoupper($condominio['DESCRIPCION']), $query) !== false;
            });
        }

        // Generar el PDF
        $pdf = Pdf::loadView('reportes.Condominios', ['Condominios' => $Condominios]);

        return $pdf->stream('reporte_condominios.pdf');
    }
}
