<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Access\AuthorizationException;
use App\Traits\LogsActivity;
use App\Traits\HandlesAuthorizationExceptions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use ZipArchive;

class BitacoraUsuario extends Controller
{
    use LogsActivity, HandlesAuthorizationExceptions;

   public function getBitacoraUsuario()
{
    $hasPermission = true;
    try {
        $this->authorize('view', User::class);
    } catch (AuthorizationException $e) {
        $hasPermission = false;
    }

    $baseUrl = Config::get('api.base_url');
    $response = Http::get($baseUrl . '/SEL_TBL_MS_BITACORA');
    $bitacoraUsuario = $response->json();

    $usuarios = $this->obtenerUsuarios();
    $objetos = $this->obtenerObjetos();

    if ($hasPermission) {
        $this->logActivity('bitacora_usuario', 'get');
    }

    return view('BitacoraUsuario', compact('bitacoraUsuario', 'hasPermission', 'usuarios', 'objetos'));
}


    public function fetchBitacoraUsuario(Request $request)
    {
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search.value');
    
        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl . '/SEL_TBL_MS_BITACORA');
        $bitacoraUsuario = $response->json();
    
        // Supongamos que tienes métodos para obtener los nombres de usuario y objeto
        $usuarios = $this->obtenerUsuarios();
        $objetos = $this->obtenerObjetos();
    
        // Reemplazar IDs con nombres
        foreach ($bitacoraUsuario as &$bitacora) {
            $bitacora['ID_USUARIO'] = $usuarios[$bitacora['ID_USUARIO']] ?? $bitacora['ID_USUARIO'];
            $bitacora['ID_OBJETO'] = $objetos[$bitacora['ID_OBJETO']] ?? $bitacora['ID_OBJETO'];
        }
    
        if ($search) {
            $bitacoraUsuario = array_filter($bitacoraUsuario, function ($bitacora) use ($search) {
                return strpos($bitacora['ID_USUARIO'], $search) !== false ||
                       strpos($bitacora['ACCION'], $search) !== false ||
                       strpos($bitacora['DESCRIPCION'], $search) !== false;
            });
        }
    
        $totalData = count($bitacoraUsuario);
        $filteredData = array_slice($bitacoraUsuario, $start, $length);
    
        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalData,
            "data" => $filteredData
        ]);
    }
    
    private function obtenerUsuarios()
    {
        // Suponiendo que puedes obtener los usuarios desde la base de datos o una API
        // Aquí un ejemplo usando la base de datos de Laravel:
        return User::pluck('NOMBRE_USUARIO', 'ID_USUARIO')->toArray();
    }
    
    private function obtenerObjetos()
    {
        // Suponiendo que puedes obtener los objetos desde la base de datos o una API
        return DB::table('TBL_OBJETOS')->pluck('OBJETO', 'ID_OBJETO')->toArray();
    }
    

    public function crear(Request $request)
    {
        try {
            $this->authorize('insert', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'No tienes permisos para crear una bitácora.'], 403);
        }
    
        $data = $request->validate([
            'nombre_usuario' => 'required|string|max:255',
            'ID_OBJETO' => 'required|string|max:255',
            'ACCION' => 'required|string|max:255',
            'DESCRIPCION' => 'nullable|string|max:100',
        ]);
    
        try {
            // Buscar el ID del usuario basado en el nombre
            $usuario = DB::table('TBL_MS_USUARIO')
                ->where('NOMBRE_USUARIO', $data['nombre_usuario'])
                ->first();
    
            if (!$usuario) {
                return response()->json(['error' => 'El usuario no existe.'], 400);
            }
    
            // Hacer la llamada a la API externa para insertar en la base de datos MySQL
            $baseUrl = Config::get('api.base_url');
            $response = Http::post($baseUrl . '/POST_TBL_MS_BITACORA', [
                'P_ID_USUARIO' => $usuario->ID_USUARIO, // Aquí se utiliza el ID obtenido del usuario
                'P_ID_OBJETO' => $data['ID_OBJETO'],
                'P_ACCION' => $data['ACCION'],
                'P_DESCRIPCION' => $data['DESCRIPCION'],
            ]);
    
            if ($response->successful()) {
                return response()->json(['message' => 'Bitácora creada correctamente'], 200);
            } else {
                return response()->json(['message' => 'Error al crear la bitácora en la API externa'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Se produjo un error: ' . $e->getMessage()], 500);
        }
    }
    

    

    public function actualizar(Request $request, $id)
    {
        try {
            $this->authorize('update', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'No tienes permisos para actualizar la bitácora.'], 403);
        }

        $data = $request->validate([
            'P_ID_USUARIO' => 'required|string|max:255',
            'P_ID_OBJETO' => 'required|string|max:255',
            'P_ACCION' => 'required|string|max:255',
            'P_DESCRIPCION' => 'nullable|string|max:1000',
        ]);

        $baseUrl = Config::get('api.base_url');
        $response = Http::post($baseUrl . '/PUT_TBL_MS_BITACORA', array_merge(['P_ID_BITACORA' => $id], $data));

        if ($response->successful()) {
            $oldData = []; // Obtener los datos anteriores si es necesario
            $this->logActivity('bitacora_usuario ' . $id, 'put', $data, $oldData);
            return response()->json(['message' => 'Bitácora actualizada correctamente'], 200);
        } else {
            return response()->json(['message' => 'Error al actualizar la bitácora'], 500);
        }
    }

    public function eliminar($id)
    {
        try {
            $this->authorize('delete', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'No tienes permisos para eliminar la bitácora.'], 403);
        }

        $baseUrl = Config::get('api.base_url');
        $response = Http::post($baseUrl . '/DEL_TBL_MS_BITACORA', ['P_ID_BITACORA' => $id]);

        if ($response->successful()) {
            $oldData = []; // Obtener los datos anteriores si es necesario
            $this->logActivity('bitacora_usuario ' . $id, 'delete', [], $oldData);
            return response()->json(['message' => 'Bitácora eliminada correctamente'], 200);
        } else {
            $errorMessage = $response->json('error'); // Asegúrate de capturar el mensaje de error
            if (str_contains($errorMessage, 'relacionado con otros registros')) {
                return response()->json(['error' => 'No se puede eliminar el Registro porque está relacionado con otros registros.']);
            }
            return response()->json(['error' => 'Error al eliminar Estado.']);
    }
}

public function generarReporte(Request $request)
{
    $id_usuario = strtoupper($request->input('id_usuario'));

    $baseUrl = Config::get('api.base_url');
    $response = Http::get($baseUrl . '/SEL_TBL_MS_BITACORA');
    $bitacoraUsuario = $response->json();

    $usuarios = $this->obtenerUsuarios();
    $objetos = $this->obtenerObjetos();

   

        // Reemplazar IDs con nombres en el array de bitácoras
        foreach ($bitacoraUsuario as &$bitacora) {
            $bitacora['ID_USUARIO'] = $usuarios[$bitacora['ID_USUARIO']] ?? $bitacora['ID_USUARIO'];
            $bitacora['ID_OBJETO'] = $objetos[$bitacora['ID_OBJETO']] ?? $bitacora['ID_OBJETO'];
        }

        $perPage = 50; // Ajusta este valor según tus necesidades
        $pages = array_chunk($bitacoraUsuario, $perPage);
       
        if ($response->successful()) {
            if ($id_usuario) {
                $bitacoraUsuario = array_filter($bitacoraUsuario, function($bitacora) use ($id_usuario) {
                    return stripos($bitacora['ID_USUARIO'], $id_usuario) !== false;
                });
            }
        $html = view('reportes.BitacoraUsuario', [
            'pages' => $pages,
        ])->render();

        $htmlFilePath = storage_path('app/public/reporte_bitacora.html');
        file_put_contents($htmlFilePath, $html);

        $zip = new ZipArchive;
        $zipFilePath = storage_path('app/public/reporte_bitacora.zip');

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $zip->addFile($htmlFilePath, 'reporte_bitacora.html');
            $zip->close();
        }

        unlink($htmlFilePath);

        return response()->download($zipFilePath);
    } else {
        return back()->withErrors(['error' => 'No se pudo obtener la lista de roles.']);
    }
}

    }