<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use DateTime;
use DateTimeZone;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Config;
use Illuminate\Auth\Access\AuthorizationException;
use App\Traits\HandlesAuthorizationExceptions;

class AnuncioEventoController extends Controller
{
    use LogsActivity;
    use HandlesAuthorizationExceptions;
    
    public function index()
{
    $hasPermission = true;
    try {
        $this->authorize('view', User::class);
    } catch (AuthorizationException $e) {
        $hasPermission = false;
    }

    $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
    $response = Http::get($baseUrl . '/SEL_ANUNCIOS_EVENTOS');
    $anunciosEventos = collect($response->json());

    // Ordena los anuncios por la fecha más reciente
    $anunciosEventos = $anunciosEventos->sortByDesc(function ($evento) {
        return $evento['FECHA_HORA'];
    });

    $estadosAnuncio = $this->getEstadosAnuncio();
    foreach ($anunciosEventos as &$evento) {
        $evento['ESTADO_ANUNCIO_EVENTO'] = $estadosAnuncio->firstWhere('ID_ESTADO_ANUNCIO_EVENTO', $evento['ID_ESTADO_ANUNCIO_EVENTO'])->DESCRIPCION ?? 'Desconocido';
    }

    $perPage = 4;
    $currentPage = request()->get('page', 1);
    $anunciosEventos = $anunciosEventos->slice(($currentPage - 1) * $perPage, $perPage)->values();
    $paginador = new \Illuminate\Pagination\LengthAwarePaginator(
        $anunciosEventos,
        count($response->json()),
        $perPage,
        $currentPage,
        ['path' => request()->url(), 'query' => request()->query()]
    );

    if ($hasPermission) {
        $this->logActivity('Anuncios y Eventos ', 'get');
    }

    return view('AnuncioEvento', compact('anunciosEventos', 'estadosAnuncio', 'paginador', 'hasPermission'));
}

    public function getEstadosAnuncio()
    {
        return DB::table('TBL_ESTADO_ANUNCIO_EVENTO')->select('ID_ESTADO_ANUNCIO_EVENTO', 'DESCRIPCION')->get();
    }

    public function guardar(Request $request)
    {
        try {
            $this->authorize('insert', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Crear.']);
        }
        $date = new DateTime('now', new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone('America/Tegucigalpa'));

        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'fecha_hora' => 'nullable|date',
            'ID_ESTADO_ANUNCIO_EVENTO' => 'required|exists:TBL_ESTADO_ANUNCIO_EVENTO,ID_ESTADO_ANUNCIO_EVENTO'
        ]);

        $imagen = null;
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $imagen = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $imagen);
        }
        $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
        $response = Http::post($baseUrl.'/POST_ANUNCIOS_EVENTOS', [
            'P_TITULO' => $request->titulo,
            'P_DESCRIPCION' => $request->descripcion,
            'P_IMAGEN' => $imagen,
            'P_FECHA_HORA' => $date->format('Y-m-d H:i:s'),
            'P_ID_ESTADO_ANUNCIO_EVENTO' => $request->ID_ESTADO_ANUNCIO_EVENTO,
        ]);

        if ($response->successful()) {
            $this->logActivity('anuncio o evento', 'post', $request->all());

            return response()->json(['success' => 'Anuncio o Evento creado con éxito.']);
        } else {
            return response()->json(['error' => 'Hubo un problema al crear el Anuncio o Evento.'], 500);
        }
    }

    public function actualizar(Request $request, $id)
    {
        try {
            $this->authorize('update', User::class); 
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Actualizar.']);
        }
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'fecha_hora' => 'nullable|date',
            'ID_ESTADO_ANUNCIO_EVENTO' => 'required|exists:TBL_ESTADO_ANUNCIO_EVENTO,ID_ESTADO_ANUNCIO_EVENTO'
        ]);
    
        $imagen = null;
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $imagen = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $imagen);
        }
    
        // Obtener los datos actuales del anuncio o evento antes de actualizar
        $oldData = DB::table('TBL_ANUNCIOS_EVENTOS')->where('ID_ANUNCIOS_EVENTOS', $id)->first();
        if (!$oldData) {
            return response()->json(['error' => 'No se encontró el anuncio o evento.'], 404);
        }
        $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
        $response = Http::post($baseUrl.'/PUT_ANUNCIOS_EVENTOS', [
            'P_ID_ANUNCIOS_EVENTOS' => $id,
            'P_TITULO' => $request->titulo,
            'P_DESCRIPCION' => $request->descripcion,
            'P_IMAGEN' => $imagen,
            'P_FECHA_HORA' => $request->fecha_hora,
            'P_ID_ESTADO_ANUNCIO_EVENTO' => $request->ID_ESTADO_ANUNCIO_EVENTO,
        ]);
    
        if ($response->successful()) {
            // Obtener los nuevos datos después de actualizar
            $newData = [
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'imagen' => $imagen,
                'fecha_hora' => $request->fecha_hora,
                'ID_ESTADO_ANUNCIO_EVENTO' => $request->ID_ESTADO_ANUNCIO_EVENTO,
            ];
    
            // Convertir los objetos a arrays para el logActivity
            $oldDataArray = [
                'titulo' => $oldData->TITULO,
                'descripcion' => $oldData->DESCRIPCION,
                'imagen' => $oldData->IMAGEN,
                'fecha_hora' => $oldData->FECHA_HORA,
                'ID_ESTADO_ANUNCIO_EVENTO' => $oldData->ID_ESTADO_ANUNCIO_EVENTO,
            ];
            $newDataArray = array_merge($oldDataArray, $newData);
    
            // Registrar la actividad en los logs
            $this->logActivity('anuncio o evento', 'put', $newDataArray, $oldDataArray);
    
            return response()->json(['success' => 'Anuncio o evento actualizado correctamente.']);
        } else {
            return response()->json(['error' => 'Hubo un error al actualizar el anuncio o evento.'], 500);
        }
    }
    

    public function eliminar(Request $request)
    {
        try {
            $this->authorize('delete', User::class); 
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Eliminar.']);
        }
        $id = $request->input('ID_ANUNCIOS_EVENTOS');

        $oldData = DB::table('TBL_ANUNCIOS_EVENTOS')->where('ID_ANUNCIOS_EVENTOS', $id)->first();
        
        try {
        $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
        $response = Http::post($baseUrl.'/DEL_ANUNCIOS_EVENTOS', [
            'P_ID_ANUNCIOS_EVENTOS' => $id,
        ]);

        if ($response->successful()) {
            $this->logActivity('anuncio o evento', 'delete', [], (array)$oldData);
             session()->flash('success', 'Anuncio o evento eliminado correctamente');
            return redirect()->route('AnuncioEvento');
        
        } else {
            return redirect()->route('AnuncioEvento')->with('error', 'Anuncio no encontrado.');
        }
    } catch (\Exception $e) {
        \Log::error('Excepción al eliminar Anuncio:', ['exception' => $e]);
        session()->flash('error', 'No se puede eliminar el Anuncio porque está relacionado con otros registros.');
        return redirect()->route('AnuncioEvento');
    }
}
}