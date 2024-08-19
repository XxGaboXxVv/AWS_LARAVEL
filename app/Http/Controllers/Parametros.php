<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Auth\Access\AuthorizationException;
use App\Traits\HandlesAuthorizationExceptions;
use Illuminate\Support\Facades\Config;

class Parametros extends Controller
{
    use LogsActivity;
    use HandlesAuthorizationExceptions;
    public function getParametros()
    {
        $hasPermission = true;
        try {
            $this->authorize('view', User::class);
        } catch (AuthorizationException $e) {
            $hasPermission = false;
        }

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_TBL_MS_PARAMETROS');
        $parametros = $response->json();

        // Obtener usuarios
        $Usuarios = $this->getUsuarios();

        // Asignar los nombres a los usuarios
        foreach ($parametros as &$parametro) {
            $parametro['NOMBRE_USUARIO'] = $Usuarios->firstWhere('ID_USUARIO', $parametro['ID_USUARIO'])->NOMBRE_USUARIO ?? 'Desconocido';
        }

        return view('Parametros', compact('parametros', 'Usuarios','hasPermission'));
    }

    public function getUsuarios()
    {
        return DB::table('TBL_MS_USUARIO')->select('ID_USUARIO', 'NOMBRE_USUARIO')->get();
    }

    public function crear(Request $request)
    {
        try {
            $this->authorize('insert', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Crear.']);
        }

        $data = $request->validate([
            'parametro' => 'required|string|max:100',
            'valor' => 'required|string|max:100',
            'nombre_usuario' => 'required|string|max:100',
        ]);

        try {
            // Verificar si el usuario existe
            $usuario = DB::table('TBL_MS_USUARIO')
                ->where('NOMBRE_USUARIO', $data['nombre_usuario'])
                ->first();

            if (!$usuario) {
                return response()->json(['error' => 'El usuario no existe.'], 400);
            }

            $fechaCreacion = Carbon::now('America/Tegucigalpa');

            // Crear nuevo registro en la tabla de parámetros
            $response = Http::post(Config::get('api.base_url').'/POST_TBL_MS_PARAMETROS', [
                'P_ID_USUARIO' => $usuario->ID_USUARIO,
                'P_PARAMETRO' => $data['parametro'],
                'P_VALOR' => $data['valor'],
                'P_FECHA_CREACION' => $fechaCreacion->toDateTimeString(),
            ]);

            if ($response->successful()) {
                return response()->json(['success' => 'Parámetro creado con éxito.']);
            } else {
                return response()->json(['error' => 'Hubo un problema al crear el parámetro.'], 500);
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
            return response()->json(['error'=> 'No tienes permisos para poder Actualizar.']);
        }
    
        $data = $request->validate([
            'nombre_usuario' => 'required|string|max:100',
            'parametro' => 'required|string|max:100',
            'valor' => 'required|numeric|max:100',
            
        ]);
    
        // Verificar si el usuario existe
        $usuario = DB::table('TBL_MS_USUARIO')
            ->where('NOMBRE_USUARIO', $data['nombre_usuario'])
            ->first();
    
        if (!$usuario) {
            return response()->json(['error' => 'El usuario no existe.'], 400);
        }
    
        // Obtener los datos actuales del parámetro antes de actualizar
        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl . '/SEL_TBL_MS_PARAMETROS');
        $parametros = $response->json();
        $parametroActual = collect($parametros)->firstWhere('ID_PARAMETRO', $id);
        
        if (is_null($parametroActual)) {
            return redirect()->route('Parametros')->withErrors('No se encontraron datos para el parámetro.');
        }
    
        $fechaModificacion = Carbon::now('America/Tegucigalpa')->toDateTimeString();
    
        $response = Http::post($baseUrl . '/PUT_TBL_MS_PARAMETROS', [
            'P_ID_PARAMETRO' => $id,
            'P_ID_USUARIO' => $usuario->ID_USUARIO,
            'P_PARAMETRO' => $data['parametro'],
            'P_VALOR' => $data['valor'],
            'P_FECHA_MODIFICACION' => $fechaModificacion,
        ]);
    
        if ($response->successful()) {
            // Registrar la actividad en los logs
            $oldData = [
                'P_ID_USUARIO' => $parametroActual['ID_USUARIO'],
                'P_PARAMETRO' => $parametroActual['PARAMETRO'],
                'P_VALOR' => $parametroActual['VALOR'],
                'P_FECHA_MODIFICACION' => $parametroActual['FECHA_MODIFICACION'],
            ];
    
            $newData = [
                'P_ID_USUARIO' => $usuario->ID_USUARIO,
                'P_PARAMETRO' => $data['parametro'],
                'P_VALOR' => $data['valor'],
                'P_FECHA_MODIFICACION' => $fechaModificacion,
            ];
    
            $this->logActivity(
                'parametro ' . $parametroActual['ID_PARAMETRO'],
                'put',
                $newData,
                $oldData
            );
    
            return response()->json(['success' => 'Parámetro actualizado correctamente.']);
        } else {
            return response()->json(['error' => 'Hubo un error al actualizar el parámetro.'], 500);
        }
    }
    
    public function eliminar($id)
    {
        try {
            $this->authorize('delete', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Eliminar.']);
        }
    
        // Obtener los datos del parámetro antes de eliminarlo para el log de actividad
        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_TBL_MS_PARAMETROS');
        if (!$response->successful()) {
            return redirect()->route('Parametros')->withErrors('Error al obtener los datos del parámetro.');
        }
    
        $parametros = $response->json();
        $parametroActual = collect($parametros)->firstWhere('ID_PARAMETRO', $id);
        if (is_null($parametroActual)) {
            return redirect()->route('Parametros')->withErrors('No se encontraron datos para el parámetro.');
        }
    
        $response = Http::post($baseUrl.'/DEL_TBL_MS_PARAMETROS', ['P_ID_PARAMETRO' => $id]);
    
        if ($response->successful()) {
            $this->logActivity('parametro', 'delete', [], $parametroActual);
            return response()->json(['success' => 'Parámetro eliminado correctamente.']);
        } else {
            $errorMessage = $response->json('error'); // Asegúrate de capturar el mensaje de error
            if (str_contains($errorMessage, 'relacionado con otros registros')) {
                return response()->json(['error' => 'No se puede eliminar el Parametro porque está relacionado con otros registros.']);
            }
            return response()->json(['error' => 'Error al eliminar Parametro.']);
    }
}
    

    public function generarReporte(Request $request)
    {
        $query = strtoupper($request->input('parametro'));
        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_TBL_MS_PARAMETROS');
        
        if ($response->successful()) {
            $parametros = $response->json();

            // Obtener usuarios
            $Usuarios = $this->getUsuarios();

            // Asignar los nombres de usuarios a los parámetros
            foreach ($parametros as &$parametro) {
                $parametro['NOMBRE_USUARIO'] = $Usuarios->firstWhere('ID_USUARIO', $parametro['ID_USUARIO'])->NOMBRE_USUARIO ?? 'Desconocido';
            }

            // Filtrar parámetros si se ha proporcionado un nombre
            if ($query) {
                $parametros = array_filter($parametros, function ($parametro) use ($query) {
                    return stripos($parametro['PARAMETRO'], $query) !== false;
                });
            }

            // Generar el PDF
            $pdf = Pdf::loadView('reportes.Parametros', compact('parametros'));

            return $pdf->stream('reporte_Parametros.pdf');
        } else {
            return response()->json(['error' => 'No se pudo generar el reporte.'], 500);
        }
    }
    
}

