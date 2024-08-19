<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Access\AuthorizationException;
use App\Traits\HandlesAuthorizationExceptions;
use App\Traits\LogsActivity;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class parentescos extends Controller
{
    use LogsActivity, HandlesAuthorizationExceptions;

    public function getParentescos()
    {
        $hasPermission = true;
        try {
            $this->authorize('view', User::class);
        } catch (AuthorizationException $e) {
            $hasPermission = false;
        }

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_PARENTESCOS');
        $parentescos = $response->json();

        if ($hasPermission) {
            $this->logActivity('parentescos', 'get');
        }

        return view('parentescos', compact('parentescos', 'hasPermission'));
    }

    public function crear(Request $request)
    {
        try {
            $this->authorize('insert', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Crear.']);
        }

        $data = $request->validate([
            'descripcion' => 'required|string|max:255'
        ]);

        try {
            $response = Http::post(Config::get('api.base_url').'/POST_PARENTESCOS', [
                'P_DESCRIPCION' => $data['descripcion']
            ]);

            if ($response->successful()) {
                $this->logActivity('parentescos', 'post', $data);
                return response()->json(['success' => 'Parentesco creado con éxito.']);
            } else {
                return response()->json(['error' => 'Hubo un problema al crear el Parentesco.'], 500);
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
            'descripcion' => 'required|string|max:255'
        ]);

        try {
            $baseUrl = Config::get('api.base_url');
            $response = Http::get($baseUrl.'/SEL_PARENTESCOS');
            $parentescos = $response->json();
            $parentescoActual = collect($parentescos)->firstWhere('ID_PARENTESCO', $id);

            if (is_null($parentescoActual)) {
                return back()->withErrors('No se encontraron datos para el Parentesco.');
            }

            $updateResponse = Http::post($baseUrl.'/PUT_PARENTESCOS', [
                'P_ID_PARENTESCO' => $id,
                'P_DESCRIPCION' => $data['descripcion']
            ]);

            if ($updateResponse->successful()) {
                // Registrar la actividad en los logs
                $oldData = [
                    'P_ID_PARENTESCO' => $parentescoActual['ID_PARENTESCO'],
                    'P_DESCRIPCION' => $parentescoActual['DESCRIPCION'],
                ];

                $newData = [
                    'P_ID_PARENTESCO' => $id,
                    'P_DESCRIPCION' => $data['descripcion'],
                ];

                $this->logActivity(
                    'parentesco ' . $parentescoActual['ID_PARENTESCO'],
                    'put',
                    $newData,
                    $oldData
                );

                return response()->json(['success' => 'Parentesco actualizado correctamente.']);
            } else {
                return response()->json(['error' => 'Hubo un error al actualizar el Parentesco.'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Se produjo un error: ' . $e->getMessage()], 500);
        }
    }

    public function eliminar($id)
    {
        try {
            $this->authorize('delete', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'No tienes permisos para poder Eliminar.']);
        }
    
        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl . '/SEL_PARENTESCOS');
        $parentescos = $response->json();
    
        $parentescoActual = collect($parentescos)->firstWhere('ID_PARENTESCO', $id);
    
        if (is_null($parentescoActual)) {
            return back()->withErrors('No se encontraron datos para el Parentesco.');
        }
    
        $deleteResponse = Http::post($baseUrl . '/DEL_TBL_PARENTESCOS', ['P_ID_PARENTESCO' => $id]);
    
        if ($deleteResponse->successful()) {
            // Registrar la actividad en los logs
            $this->logActivity('parentesco ' . $parentescoActual['ID_PARENTESCO'], 'delete', [], $parentescoActual);
            return response()->json(['success' => 'Parentesco eliminado correctamente.']);
        } else {
            // Captura el mensaje de error y responde con un mensaje personalizado
            $errorMessage = $deleteResponse->json('error');
            if (str_contains($errorMessage, 'relacionado con otros registros')) {
                return response()->json(['error' => 'No se puede eliminar el Parentesco porque está relacionado con otros registros.']);
            }
            return response()->json(['error' => 'Error al eliminar Parentesco.']);
        }
    }
    

    public function generarReporte(Request $request)
    {
        $consulta = strtoupper($request->input('descripcion'));

        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl.'/SEL_PARENTESCOS');
        $parentescos = $response->json();

        if ($response->successful()) {
            if ($consulta) {
                $parentescos = array_filter($parentescos, function ($parentesco) use ($consulta) {
                    return stripos($parentesco['DESCRIPCION'], $consulta) !== false;
                });
            }

            $pdf = Pdf::loadView('reportes.parentescos', compact('parentescos'));
            return $pdf->stream('reporte_parentescos.pdf');
        } else {
            return back()->withErrors(['error' => 'No se pudo obtener la lista de parentescos.']);
        }
    }
}
