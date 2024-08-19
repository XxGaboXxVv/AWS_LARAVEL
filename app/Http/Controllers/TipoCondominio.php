<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Config;
use App\Traits\LogsActivity;
use Illuminate\Auth\Access\AuthorizationException;
use App\Traits\HandlesAuthorizationExceptions;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class TipoCondominio extends Controller
{
    use LogsActivity, HandlesAuthorizationExceptions;

    public function getTipoCondominio()
    {
        $hasPermission = true;
        try {
            $this->authorize('view', User::class);
        } catch (AuthorizationException $e) {
            $hasPermission = false;
        }

        $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
        $response = Http::get($baseUrl.'/SEL_TIPO_CONDOMINIO');
        $tipoCondominio = $response->json();

        // Registrar la actividad solo si se tiene permiso
        if ($hasPermission) {
            $this->logActivity('tipo_condominio', 'get');
        }

        return view('TipoCondominio', compact('tipoCondominio', 'hasPermission'));
    }

    public function crear(Request $request)
    {
        try {
            $this->authorize('insert', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Crear.']);
        }

        $data = $request->validate([
            'descripcion' => 'required|string|max:255',
        ]);

        $response = Http::post(Config::get('api.base_url').'/POST_TIPO_CONDOMINIO', [
            'P_DESCRIPCION' => $data['descripcion'],
        ]);

        if ($response->successful()) {
            $this->logActivity('tipo_condominio', 'post', $data);
            return response()->json(['success' => 'Tipo de Condominio creado con éxito.']);
        } else {
            return response()->json(['error' => 'Hubo un problema al crear el Tipo de Condominio.'], 500);
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
            'descripcion' => 'required|string|max:255',
        ]);

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_TIPO_CONDOMINIO');
        if (!$response->successful()) {
            return redirect()->route('TipoCondominio')->withErrors('Error al obtener los datos del tipo de condominio.');
        }

        $tipoCondominios = $response->json();
        $tipoCondominioActual = collect($tipoCondominios)->firstWhere('ID_TIPO_CONDOMINIO', $id);
        if (is_null($tipoCondominioActual)) {
            return redirect()->route('TipoCondominio')->withErrors('No se encontraron datos para el tipo de condominio.');
        }

        $updateResponse = Http::post($baseUrl.'/PUT_TIPO_CONDOMINIO', [
            'P_ID_TIPO_CONDOMINIO' => $id,
            'P_DESCRIPCION' => $data['descripcion'],
        ]);

        if ($updateResponse->successful()) {
              // Registrar la actividad en los logs
              $oldData = [
                'P_ID_TIPO_CONDOMINIO' => $tipoCondominioActual['ID_TIPO_CONDOMINIO'],
                'P_DESCRIPCION' => $tipoCondominioActual['DESCRIPCION'],
            ];

            $newData = [
                'P_ID_TIPO_CONTACTO' => $id,
                'P_DESCRIPCION' => $data['descripcion'],
            ];

            $this->logActivity(
                'tipo_condominio ' . $id,
                'put',
                $newData,
                $oldData
            );
            return response()->json(['success' => 'Tipo de Condominio actualizado correctamente.']);
        } else {
            return response()->json(['error' => 'Hubo un error al actualizar el Tipo de Condominio.'], 500);
        }
    }

    public function eliminar($id)
    {
        try {
            $this->authorize('delete', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Eliminar.']);
        }

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_TIPO_CONDOMINIO');
        if (!$response->successful()) {
            return redirect()->route('TipoCondominio')->withErrors('Error al obtener los datos del tipo de condominio.');
        }

        $tipoCondominios = $response->json();
        $tipoCondominioActual = collect($tipoCondominios)->firstWhere('ID_TIPO_CONDOMINIO', $id);
        if (is_null($tipoCondominioActual)) {
            return redirect()->route('TipoCondominio')->withErrors('No se encontraron datos para el tipo de condominio.');
        }

        $response = Http::post($baseUrl.'/DEL_TIPO_CONDOMINIO', ['P_ID_TIPO_CONDOMINIO' => $id]);

        if ($response->successful()) {
            $this->logActivity('tipo_condominio', 'delete', [], [
                'P_ID_TIPO_CONDOMINIO' => $tipoCondominioActual['ID_TIPO_CONDOMINIO'],
                'P_DESCRIPCION' => $tipoCondominioActual['DESCRIPCION']
            ]);
            return response()->json(['success' => 'Tipo de Condominio eliminado correctamente.']);
        } else {
            $errorMessage = $response->json('error'); // Asegúrate de capturar el mensaje de error
            if (str_contains($errorMessage, 'relacionado con otros registros')) {
                return response()->json(['error' => 'No se puede eliminar el Tipo de Condominio porque está relacionado con otros registros.']);
            }
            return response()->json(['error' => 'Error al eliminar Estado.']);
    }
}

    public function generarReporte(Request $request)
    {
        $query = strtoupper($request->input('descripcion'));
        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_TIPO_CONDOMINIO');
        $tipoCondominio = $response->json();
    
        if ($response->successful()) {
            if ($query) {
                $tipoCondominio  = array_filter($tipoCondominio, function($tipoCondominio) use ($query) {
                    return stripos($tipoCondominio['DESCRIPCION'], $query) !== false;
                });
            }
    
            // Generar el PDF
            $pdf = Pdf::loadView('reportes.TipoCondominio', compact('tipoCondominio'));
            return $pdf->stream('reporte_TipoCondominio.pdf');
        } else {
            // Manejar el caso en que la solicitud a la API falle
            return back()->withErrors(['error' => 'No se pudo obtener la lista de tipos de condominio.']);
        }
    }
}
