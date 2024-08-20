<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;



class PerfilController extends Controller
{
    public function mostrar()
    {
        $user = Auth::user();

        // Obtener el estado del usuario desde la tabla TBL_ESTADO_USUARIO
        $estadoUsuario = DB::table('TBL_ESTADO_USUARIO')
            ->where('ID_ESTADO_USUARIO', $user->ID_ESTADO_USUARIO)
            ->value('DESCRIPCION');
        return view('perfil', [
            'userData' => [
                'NOMBRE_USUARIO' => $user->NOMBRE_USUARIO,
                'EMAIL' => $user->EMAIL,
                'google2fa_secret' => $user->google2fa_secret,
                'ESTADO_USUARIO' => $estadoUsuario,
            ]
        ]);
    }
    

// Función para obtener los estados de usuario
public function getEstadosUsuario()
{
    return \DB::table('TBL_ESTADO_USUARIO')->select('ID_ESTADO_USUARIO', 'DESCRIPCION')->get();
}
   
   
public function actualizarPerfil(Request $request)
{
    $user = Auth::user();

    $request->validate([
        'NOMBRE_USUARIO' => 'required|string|max:255|regex:/^[A-Z\s]+$/u',
        'EMAIL' => 'required|string|email|max:255|unique:TBL_MS_USUARIO,email,' . $user->ID_USUARIO . ',ID_USUARIO',
    ], [
        'NOMBRE_USUARIO.regex' => 'El nombre de usuario solo puede contener letras mayúsculas y espacios.',
    ]);

    $user->NOMBRE_USUARIO = $request->input('NOMBRE_USUARIO');
    $user->EMAIL = $request->input('EMAIL');
    $user->save();

    return response()->json(['success' => 'Usuario actualizado con éxito.']);
}

public function cambiarContraseña(Request $request)
{
    $request->validate([
        'contraseña_actual' => 'required',
        'nueva_contraseña' => [
            'required', 
            'string', 
            'min:8', 
            'confirmed',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])(?!.*\s).{8,}$/', 
        ],
    ], [
        'nueva_contraseña.regex' => 'La contraseña debe tener al menos una mayúscula, una minúscula, un número, un carácter especial, y no debe contener espacios.',
        'nueva_contraseña.confirmed' => 'Las contraseñas no coinciden.', 
        'nueva_contraseña.min' => 'La contraseña debe contener mínimo 8 caracteres.',
    ]);

    $user = Auth::user();

    if (!Hash::check($request->input('contraseña_actual'), $user->CONTRASEÑA)) {
        throw ValidationException::withMessages([
            'contraseña_actual' => 'La contraseña actual no es correcta.',
        ]);
    }

    // Actualizar la contraseña
    $user->CONTRASEÑA = Hash::make($request->input('nueva_contraseña'));

    // Obtener la duración de la contraseña desde la tabla de parámetros
    $duracionDias = DB::table('TBL_MS_PARAMETROS')
        ->where('PARAMETRO', 'FECHA_VENCIMIENTO')
        ->value('VALOR');

        // Convertir el valor a un número entero
$duracionDias = intval($duracionDias);

    if (!$duracionDias) {
        $duracionDias = 90; // Valor por defecto en caso de no encontrar el parámetro
    }

    // Calcular la nueva fecha de vencimiento
    $user->FECHA_VENCIMIENTO = Carbon::now()->addDays($duracionDias);

    // Guardar los cambios
    $user->save();

    return response()->json(['success' => 'La contraseña se ha actualizado correctamente.']);
}

public function toggle2fa(Request $request)
{
    $user = Auth::user();
    $google2fa = app('pragmarx.google2fa');

    if ($user->google2fa_secret) {
        // Desactivar 2FA
        $user->google2fa_secret = null;
        $user->save();
        session()->flash('success', 'La autenticación de dos factores se ha desactivado correctamente.');
    } else {
        // Activar 2FA
        $secretKey = $google2fa->generateSecretKey();
        $user->google2fa_secret = $secretKey;
        $user->save();

        $QR_Image = $google2fa->getQRCodeInline(
            config('app.name'),
            $user->email,
            $secretKey
        );

        return view('Perfil2fa.registerPerfil', ['QR_Image' => $QR_Image, 'secret' => $secretKey]);
    }

    return redirect()->route('perfil');
}

public function completeRegistration(Request $request)
{
    session()->flash('success', 'La autenticación de dos factores se ha activado correctamente.');
    return redirect()->route('perfil');
}
}