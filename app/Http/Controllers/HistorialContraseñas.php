<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Config;
use App\Traits\LogsActivity;
use App\Traits\HandlesAuthorizationExceptions;
use App\Models\User;

class HistorialContraseñas extends Controller
{
    use LogsActivity, HandlesAuthorizationExceptions;

    public function getHistorialContraseñas()
    {
        $hasPermission = true;
        try {
            $this->authorize('view', User::class);
        } catch (AuthorizationException $e) {
            $hasPermission = false;
        }

        $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
        $response = Http::get($baseUrl . '/SEL_TBL_MS_HIS_CONTRASENA');
        $HistorialContraseñas = $response->json();

        if ($hasPermission) {
            $this->logActivity('historial_contraseñas', 'get');
        }

        // Obtener usuarios
        $Usuarios = $this->getUsuarios();

        // Asignar los nombres a los usuarios
        foreach ($HistorialContraseñas as &$historial) {
            $usuario = $Usuarios->firstWhere('ID_USUARIO', $historial['ID_USUARIO']);
            $historial['NOMBRE_USUARIO'] = $usuario->NOMBRE_USUARIO ?? 'Desconocido';
        }

        return view('HistorialContraseñas', compact('HistorialContraseñas', 'Usuarios', 'hasPermission'));
    }

    public function getUsuarios()
    {
        return DB::table('TBL_MS_USUARIO')
            ->select(
                'ID_USUARIO',
                'NOMBRE_USUARIO'
            )
            ->get();
    }

    public function crear(Request $request)
    {
        try {
            $this->authorize('insert', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Crear.']);
        }

        $data = $request->validate([
            'nombre_usuario' => 'required|string|max:100',
            'contraseña' => 'required|string|max:100',
        ]);

        // Buscar el ID del usuario basado en el nombre
        $usuario = DB::table('TBL_MS_USUARIO')
            ->where('NOMBRE_USUARIO', $data['nombre_usuario'])
            ->first();

        if (!$usuario) {
            return response()->json(['error' => 'El usuario no existe.'], 400);
        }

        $encryptedPassword = Hash::make($data['contraseña']);

        $response = Http::post(Config::get('api.base_url') . '/POST_TBL_MS_HIS_CONTRASENA', [
            'P_ID_USUARIO' => $usuario->ID_USUARIO,
            'P_CONTRASEÑA' => $encryptedPassword
        ]);

        if ($response->successful()) {
            $this->logActivity('historial_contraseñas', 'post', $data);
            return response()->json(['success' => 'Contraseña creada con éxito.']);
        } else {
            return response()->json(['error' => 'Hubo un problema al crear la contraseña.'], 500);
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
        'contraseña' => 'nullable|string|max:100',  // Permite que la contraseña sea opcional en la validación
    ]);

    

    // Obtener los datos actuales del historial de contraseñas
    $baseUrl = Config::get('api.base_url');
    $response = Http::get($baseUrl . '/SEL_TBL_MS_HIS_CONTRASENA');
    $HistorialContraseñas = $response->json();
    $historialActual = collect($HistorialContraseñas)->firstWhere('ID_HIST', $id);

    if (is_null($historialActual)) {
        return redirect()->route('HistorialContraseñas')->withErrors('No se encontraron datos para el historial.');
    }

    // Encriptar la nueva contraseña si fue proporcionada
    $encryptedPassword = $historialActual['CONTRASEÑA']; // Mantén la contraseña actual si no se proporciona una nueva
    if (!empty($data['contraseña'])) {
        $encryptedPassword = Hash::make($data['contraseña']);
    }

    $response = Http::post( $baseUrl.'/PUT_TBL_MS_HIS_CONTRASENA', [
        'P_ID_HIST' => $id,
        'P_CONTRASEÑA' => $encryptedPassword
    ]);

    if ($response->successful()) {
        // Registrar la actividad en los logs
        $oldData = [
            'P_ID_USUARIO' => $historialActual['ID_USUARIO'],
            'P_CONTRASEÑA' => $historialActual['CONTRASEÑA'], // Puede estar encriptada
        ];

        $newData = [
            'P_ID_USUARIO' => $historialActual['ID_USUARIO'],
            'P_CONTRASEÑA' => $encryptedPassword,
        ];

        $this->logActivity(
            'historial_contraseñas ' . $historialActual['ID_HIST'],
            'put',
            $newData,
            $oldData
        );

        return response()->json(['success' => 'Contraseña actualizada correctamente.']);
    } else {
        return response()->json(['error' => 'Hubo un error al actualizar la contraseña.'], 500);
    }
}



    public function eliminar($id)
    {
        try {
            $this->authorize('delete', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Eliminar.']);
        }

        // Obtener los datos del historial antes de eliminarlo
        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl . '/SEL_TBL_MS_HIS_CONTRASENA');
        if (!$response->successful()) {
            return redirect()->route('HistorialContraseñas')->withErrors('Error al obtener los datos del historial.');
        }
        $HistorialContraseñas = $response->json();
        $historialActual = collect($HistorialContraseñas)->firstWhere('ID_HIST', $id);
        if (is_null($historialActual)) {
            return redirect()->route('HistorialContraseñas')->withErrors('No se encontraron datos para el historial.');
        }

        $response = Http::post($baseUrl . '/DEL_TBL_MS_HIS_CONTRASENA', ['P_ID_HIST' => $id]);

        if ($response->successful()) {
            $this->logActivity('historial_contraseñas', 'delete', [], $historialActual);
            return response()->json(['success' => 'Contraseña eliminada con éxito.']);
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
        $query = strtoupper($request->input('nombre_usuario'));
        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl . '/SEL_TBL_MS_HIS_CONTRASENA');
        $HistorialContraseñas = $response->json();

        if ($response->successful()) {
            // Obtener usuarios
            $Usuarios = $this->getUsuarios();

            // Asignar los nombres a los usuarios
            foreach ($HistorialContraseñas as &$historial) {
                $usuario = $Usuarios->firstWhere('ID_USUARIO', $historial['ID_USUARIO']);
                $historial['NOMBRE_USUARIO'] = $usuario->NOMBRE_USUARIO ?? 'Desconocido';
            }

            if ($query) {
                $HistorialContraseñas = array_filter($HistorialContraseñas, function ($historial) use ($query) {
                    return strpos(strtoupper($historial['NOMBRE_USUARIO']), $query) !== false;
                });
            }

            $pdf = Pdf::loadView('reportes.HistorialContraseñas', compact('HistorialContraseñas'));

            return $pdf->stream('reporte_HistorialContraseñass.pdf');
        } else {
            return response()->json(['error' => 'No se pudo generar el reporte.'], 500);
        }
    }
}
