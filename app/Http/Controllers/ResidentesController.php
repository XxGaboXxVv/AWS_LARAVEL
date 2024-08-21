<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Traits\LogsActivity;
use Illuminate\Auth\Access\AuthorizationException;
use App\Traits\HandlesAuthorizationExceptions;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Config;



class ResidentesController extends Controller
{
    use LogsActivity;
    use HandlesAuthorizationExceptions;

    public function GetResidentes(Request $request)
    {
        $hasPermission = true;
        try {
            $this->authorize('view', User::class);
        } catch (AuthorizationException $e) {
            $hasPermission = false;
        }

        $query = $request->get('nombre');
        $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
        $url = $baseUrl . '/SEL_PERSONA';

        if ($query) {
            $url .= '?nombre=' . urlencode($query);
        }
        // Registrar la actividad solo si se tiene permiso
        if ($hasPermission) {
            $this->logActivity('residentes', 'get');
        }
    

        $response = Http::get($url);

        if ($response->successful()) {
            $Residentes = $response->json();

            // Obtener datos adicionales
            $Contacto = $this->getContacto();
            $TipoContacto = $this->getTipoContacto();
            $tipopersona = $this->getTipoPersona();
            $estadopersona = $this->getEstadoPersona();
            $Parentesco = $this->getParentesco();
            $Condominio = $this->getCondominio();
            $TipoCondominio = $this->getTipoCondominio();

            foreach ($Residentes as &$residente) {
                $contacto = $Contacto->firstWhere('ID_CONTACTO', $residente['ID_CONTACTO']);
                $condominio= $Condominio->firstWhere('ID_CONDOMINIO', $residente['ID_CONDOMINIO']);

                $residente['CONTACTO'] = $contacto->DESCRIPCION ?? 'Desconocido';
                $residente['ID_TIPO_CONTACTO'] = $contacto->ID_TIPO_CONTACTO ?? null;
                $residente['TIPO_CONTACTO'] = $contacto ? ($TipoContacto->firstWhere('ID_TIPO_CONTACTO', $contacto->ID_TIPO_CONTACTO)->DESCRIPCION ?? 'Desconocido') : 'Desconocido';
                $residente['TIPO_PERSONA'] = $tipopersona->firstWhere('ID_TIPO_PERSONA', $residente['ID_TIPO_PERSONA'])->DESCRIPCION ?? 'Desconocido';
                $residente['ESTADO_PERSONA'] = $estadopersona->firstWhere('ID_ESTADO_PERSONA', $residente['ID_ESTADO_PERSONA'])->DESCRIPCION ?? 'Desconocido';
                $residente['PARENTESCO'] = $Parentesco->firstWhere('ID_PARENTESCO', $residente['ID_PARENTESCO'])->DESCRIPCION ?? 'Desconocido';
                $residente['CONDOMINIO'] = $condominio->DESCRIPCION ?? 'Desconocido';
                $residente['ID_TIPO_CONDOMINIO'] = $condominio->ID_TIPO_CONDOMINIO ?? 'Desconocido';
            }

            return view('residentes', compact('Residentes', 'Condominio','Contacto', 'TipoContacto', 'tipopersona', 'estadopersona', 'Parentesco','TipoCondominio','hasPermission'));
        } else {
            return view('error')->withErrors('Error al obtener la lista de Residentes.');
        }
    }

    // Funciones para obtener datos adicionales
    public function getContacto()
    {
        return DB::table('TBL_CONTACTOS')->select('ID_CONTACTO', 'ID_TIPO_CONTACTO', 'DESCRIPCION')->get();
    }

    public function getTipoPersona()
    {
        return DB::table('TBL_TIPO_PERSONAS')->select('ID_TIPO_PERSONA',  'DESCRIPCION')->get();
    }

    public function getEstadoPersona()
    {
        return DB::table('TBL_ESTADO_PERSONA')->select('ID_ESTADO_PERSONA','DESCRIPCION')->get();
    }

    public function getParentesco()
    {
        return DB::table('TBL_PARENTESCOS')->select('ID_PARENTESCO', 'DESCRIPCION')->get();
    }

    public function getCondominio()
    {
        return DB::table('TBL_CONDOMINIOS')->select('ID_CONDOMINIO', 'ID_TIPO_CONDOMINIO', 'DESCRIPCION')->get();
    }

    public function getTipoContacto()
    {
        return DB::table('TBL_TIPO_CONTACTO')->select('ID_TIPO_CONTACTO', 'DESCRIPCION')->get();
    }

    public function getTipoCondominio()
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
        'P_NOMBRE_PERSONA' => 'required|string|max:60',
        'P_DNI_PERSONA' => 'required|string|max:50',
        'contacto_descripcion' => 'required|string|max:255',
        'id_tipo_contacto' => 'required|integer',
        'id_estado_persona' => 'required|integer',
        'P_ID_TIPO_PERSONA' => 'required|integer',
        'P_ID_PARENTESCO' => 'required|integer',
        'condominio_descripcion' => 'required|string|max:255',
    ]);

    try {
        // Crear nuevo contacto
        $contacto = DB::table('TBL_CONTACTOS')->insertGetId([
            'ID_TIPO_CONTACTO' => $data['id_tipo_contacto'],
            'DESCRIPCION' => $data['contacto_descripcion'],
        ]);

        // Verificar si el condominio existe
        $condominio = DB::table('TBL_CONDOMINIOS')
            ->where('DESCRIPCION', strtoupper($data['condominio_descripcion']))
            ->first();

        if (!$condominio) {
            return response()->json(['error' => 'El condominio no existe.'], 400);
        }

        // Crear nuevo residente
        DB::table('TBL_PERSONAS')->insert([
            'NOMBRE_PERSONA' => $data['P_NOMBRE_PERSONA'],
            'DNI_PERSONA' => $data['P_DNI_PERSONA'],
            'ID_CONTACTO' => $contacto,
            'ID_TIPO_PERSONA' => $data['P_ID_TIPO_PERSONA'],
            'ID_ESTADO_PERSONA' => $data['id_estado_persona'],
            'ID_PARENTESCO' => $data['P_ID_PARENTESCO'],
            'ID_CONDOMINIO' => $condominio->ID_CONDOMINIO,
            'ID_PADRE' => 1,
        ]);

        // Registrar la actividad en los logs
        $this->logActivity('residente', 'post', $data);

        return response()->json(['success' => 'Residente agregado con éxito.']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Se produjo un error: ' . $e->getMessage()], 500);
    }
}

    
public function editar(Request $request, $id)
{
    try {
        $this->authorize('update', User::class); 
    } catch (AuthorizationException $e) {
        return response()->json(['error'=> 'No tienes permisos para poder Actualizar.']);
    }

    // Obtener los datos actuales del residente
    $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
    $response = Http::get($baseUrl.'/SEL_PERSONA');
    if (!$response->successful()) {
        return redirect()->route('Residentes')->withErrors('Error al obtener los datos del residente.');
    }
    $residentes = $response->json();
    $residenteActual = collect($residentes)->firstWhere('ID_PERSONA', $id);
    if (is_null($residenteActual)) {
        return redirect()->route('Residentes')->withErrors('No se encontraron datos para el residente.');
    }

    // Validar los nuevos datos
    $data = $request->validate([
        'nombre' => 'required|string|max:60',
        'dni' => 'required|string|max:50',
        'contacto_descripcion' => 'required|string|max:255',
        'id_tipo_contacto' => 'required|integer',
        'id_estado_persona' => 'required|integer',
        'ID_TIPO_PERSONA' => 'required|integer',
        'id_parentesco' => 'required|integer',
        'condominio_descripcion' => 'required|string|max:255',
        'id_padre' => 'required|integer',
    ]);

    // Verificar si el nuevo condominio existe
    $condominio = DB::table('TBL_CONDOMINIOS')
        ->where('DESCRIPCION', strtoupper($data['condominio_descripcion']))
        ->first();

    if (!$condominio) {
        return response()->json(['error' => 'El condominio no existe.'], 400);
    }

    // Obtener y actualizar los datos del contacto
    $contactoId = $residenteActual['ID_CONTACTO'];
    $contactoResponse = Http::get($baseUrl.'/SEL_CONTACTOS');
    if (!$contactoResponse->successful()) {
        return redirect()->route('Residentes')->withErrors('Error al obtener los datos del contacto.');
    }
    $contactos = $contactoResponse->json();
    $contactoActual = collect($contactos)->firstWhere('ID_CONTACTO', $contactoId);
    if (is_null($contactoActual)) {
        return redirect()->route('Residentes')->withErrors('No se encontraron datos para el contacto.');
    }

    $contactoData = [
        'P_ID_CONTACTO' => $contactoId,
        'P_ID_TIPO_CONTACTO' => $data['id_tipo_contacto'],
        'P_DESCRIPCION' => $data['contacto_descripcion']
    ];

    $contactoUpdateResponse = Http::post($baseUrl.'/PUT_CONTACTOS', $contactoData);
    if (!$contactoUpdateResponse->successful()) {
        return redirect()->route('Residentes')->withErrors('Error al actualizar la descripción del contacto.');
    }

    // Obtener los datos actuales del condominio (pero no actualizarlos)
    $CondominioId = $residenteActual['ID_CONDOMINIO'];
    $condominioResponse = Http::get($baseUrl.'/SEL_CONDOMINIOS');
    if (!$condominioResponse->successful()) {
        return redirect()->route('Residentes')->withErrors('Error al obtener los datos del condominio.');
    }
    $condominios = $condominioResponse->json();
    $condominioActual = collect($condominios)->firstWhere('ID_CONDOMINIO', $CondominioId);
    if (is_null($condominioActual)) {
        return redirect()->route('Residentes')->withErrors('No se encontraron datos para el condominio.');
    }

    // Actualizar los datos del residente
    $residenteData = [
        'P_ID_PERSONA' => $id,
        'P_NOMBRE_PERSONA' => $data['nombre'],
        'P_DNI_PERSONA' => $data['dni'],
        'P_ID_ESTADO_PERSONA' => $data['id_estado_persona'],
        'P_ID_TIPO_PERSONA' => $data['ID_TIPO_PERSONA'],
        'P_ID_PARENTESCO' => $data['id_parentesco'],
        'P_ID_PADRE' => $data['id_padre'],
        'P_ID_CONDOMINIO' => $condominio->ID_CONDOMINIO,  // Usar el ID del condominio existente
    ];

    $updateResponse = Http::post($baseUrl.'/PUT_PERSONA', $residenteData);
    if (!$updateResponse->successful()) {
        return response()->json(['error' => 'Error al actualizar los datos del residente.'], 500);
    }

    // Registrar la actividad en los logs
    $oldData = [
        'P_NOMBRE_PERSONA' => $residenteActual['NOMBRE_PERSONA'],
        'P_DNI_PERSONA' => $residenteActual['DNI_PERSONA'],
        'P_ID_ESTADO_PERSONA' => $residenteActual['ID_ESTADO_PERSONA'],
        'P_ID_TIPO_PERSONA' => $residenteActual['ID_TIPO_PERSONA'],
        'P_ID_PARENTESCO' => $residenteActual['ID_PARENTESCO'],
        'P_ID_PADRE' => $residenteActual['ID_PADRE'],
        'P_DESCRIPCION_CONTACTO' => $contactoActual['DESCRIPCION'],
        'P_TIPO_CONTACTO' => $contactoActual['ID_TIPO_CONTACTO'],
        'P_DESCRIPCION_CONDOMINIO' => $condominioActual['DESCRIPCION'],
        'P_TIPO_CONDOMINIO' => $condominioActual['ID_TIPO_CONDOMINIO']
    ];

    $newData = [
        'P_NOMBRE_PERSONA' => $data['nombre'],
        'P_DNI_PERSONA' => $data['dni'],
        'P_ID_ESTADO_PERSONA' => $data['id_estado_persona'],
        'P_ID_TIPO_PERSONA' => $data['ID_TIPO_PERSONA'],
        'P_ID_PARENTESCO' => $data['id_parentesco'],
        'P_ID_PADRE' => $data['id_padre'],
        'P_DESCRIPCION_CONTACTO' => $data['contacto_descripcion'],
        'P_TIPO_CONTACTO' => $data['id_tipo_contacto'],
        'P_DESCRIPCION_CONDOMINIO' => $data['condominio_descripcion']
    ];

    $this->logActivity(
        'residente ' . $residenteActual['NOMBRE_PERSONA'],
        'put',
        $newData,
        $oldData,
    );   
    return response()->json(['success' => 'Residente actualizado con éxito.']);
}



public function eliminar($id)
{
    try {
        $this->authorize('delete', User::class); 
    } catch (AuthorizationException $e) {
        return response()->json(['error'=> 'No tienes permisos para poder Eliminar.']);
    }

    // Obtener los datos del residente antes de eliminarlo para el log de actividad
    $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
    $response = Http::get($baseUrl.'/SEL_PERSONA');
    if (!$response->successful()) {
        return redirect()->route('Residentes')->withErrors('Error al obtener los datos del residente.');
    }
    $residentes = $response->json();
    $residenteActual = collect($residentes)->firstWhere('ID_PERSONA', $id);
    if (is_null($residenteActual)) {
        return redirect()->route('Residentes')->withErrors('No se encontraron datos para el residente.');
    }

    // Realizar la eliminación
    $response = Http::post($baseUrl.'/DEL_PERSONA', ['P_ID_PERSONA' => $id]);

    if ($response->successful()) {
        $this->logActivity('residente', 'delete', [], $residenteActual);

        return response()->json(['success' => 'Residente eliminado con éxito.']);
    } else {
        // Analizar el error recibido
        $errorMessage = $response->json('error');
        if (str_contains($errorMessage, 'relacionado con otros registros')) {
            return response()->json(['error' => 'No se puede eliminar el residente porque está relacionado con otros registros.']);
        }
        return response()->json(['error' => 'Error al eliminar residente.']);
    }
}

public function generarReporte(Request $request)
{   
    // Captura el valor de búsqueda del formulario
    $query = strtoupper($request->input('nombre'));

    $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
    $url = $baseUrl . '/SEL_PERSONA';
    $response = Http::get($url);

    if ($response->successful()) {
        $Residentes = $response->json();

        // Obtener datos adicionales
        $Contacto = $this->getContacto();
        $TipoContacto = $this->getTipoContacto();
        $tipopersona = $this->getTipoPersona();
        $estadopersona = $this->getEstadoPersona();
        $Parentesco = $this->getParentesco();
        $Condominio = $this->getCondominio();
        $TipoCondominio = $this->getTipoCondominio();

        // Asignar las descripciones de roles y estados a los usuarios
        foreach ($Residentes as &$residente) {
            $contacto = $Contacto->firstWhere('ID_CONTACTO', $residente['ID_CONTACTO']);
            $condominio= $Condominio->firstWhere('ID_CONDOMINIO', $residente['ID_CONDOMINIO']);

            $residente['CONTACTO'] = $contacto->DESCRIPCION ?? 'Desconocido';
            $residente['ID_TIPO_CONTACTO'] = $contacto->ID_TIPO_CONTACTO ?? null;
            $residente['TIPO_CONTACTO'] = $contacto ? ($TipoContacto->firstWhere('ID_TIPO_CONTACTO', $contacto->ID_TIPO_CONTACTO)->DESCRIPCION ?? 'Desconocido') : 'Desconocido';
            $residente['TIPO_PERSONA'] = $tipopersona->firstWhere('ID_TIPO_PERSONA', $residente['ID_TIPO_PERSONA'])->DESCRIPCION ?? 'Desconocido';
            $residente['ESTADO_PERSONA'] = $estadopersona->firstWhere('ID_ESTADO_PERSONA', $residente['ID_ESTADO_PERSONA'])->DESCRIPCION ?? 'Desconocido';
            $residente['PARENTESCO'] = $Parentesco->firstWhere('ID_PARENTESCO', $residente['ID_PARENTESCO'])->DESCRIPCION ?? 'Desconocido';
            $residente['CONDOMINIO'] = $condominio->DESCRIPCION ?? 'Desconocido';
            $residente['ID_TIPO_CONDOMINIO'] = $condominio->ID_TIPO_CONDOMINIO ?? 'Desconocido';
        }

        // Filtrar los usuarios si se ha proporcionado un valor de búsqueda
        if ($query) {
            $Residentes = array_filter($Residentes, function($residente) use ($query) {
                $matchResidente = stripos($residente['NOMBRE_PERSONA'], $query) !== false;
                $matchCondominio = stripos($residente['CONDOMINIO'], $query) !== false;
                return $matchResidente || $matchCondominio; // Cambia && por || para permitir coincidencias en cualquiera de los dos campos
            });
        }

        $this->logActivity('reporte de roles', 'get', [], [], ['filtro' => $query]);

        // Generar el PDF
        $pdf = Pdf::loadView('reportes.residentes',compact('Residentes', 'Condominio','Contacto', 'TipoContacto', 'tipopersona', 'estadopersona', 'Parentesco','TipoCondominio'));
        return $pdf->stream('reporte_residentes.pdf');
    } else {
        // Manejar el caso en que la solicitud a la API falle
        return back()->withErrors(['error' => 'No se pudo obtener la lista de residentes.']);
    }
}
}
