<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FALaravel\Google2FA;
use DB;

class TwoFactorController extends Controller
{
    public function showVerify2faForm()
    {
        return view('google2fa.verify2fa');
    }

    public function verify2fa(Request $request)
    {
        $user = Auth::user();
        $google2fa = app('pragmarx.google2fa');

        $secret = $user->google2fa_secret;
        $otp = $request->input('one_time_password');

        if (is_null($otp)) {
            return redirect()->back()->withErrors(['one_time_password' => 'El código OTP es requerido.']);
        }

        $valid = $google2fa->verifyKey($secret, $otp);
        if (!$valid) {
            $user->INTENTOS_FALLIDOS_OTP += 1;
            $user->ULTIMOS_INTENTOS_FALLIDOS = now();

            $parametroIntentosFallidos = DB::table('TBL_MS_PARAMETROS')
                ->where('PARAMETRO', 'INTENTOS_FALLIDOS')
                ->value('VALOR');

            $maxIntentosFallidos = $parametroIntentosFallidos ? intval($parametroIntentosFallidos) : 5;

            if ($user->INTENTOS_FALLIDOS_OTP == $maxIntentosFallidos) {
                $user->save();
                return redirect()->back()->withErrors(['one_time_password' => 'Has alcanzado el número máximo de intentos permitidos. Si te equivocas una vez más, tu cuenta será bloqueada.']);
            }

            if ($user->INTENTOS_FALLIDOS_OTP > $maxIntentosFallidos) {
                $user->ID_ESTADO_USUARIO = 3; // Bloqueado
                $user->save();

                Auth::logout();
                return redirect()->route('login')->withErrors(['email' => 'Tu cuenta ha sido bloqueada por demasiados intentos fallidos de OTP, para poder ingresar restablezca su contraseña o comuníquese con el administrador.']);
            }

            $user->save();
            return redirect()->back()->withErrors(['one_time_password' => 'El código OTP no es válido.']);
        } else {
            $user->INTENTOS_FALLIDOS_OTP = 0;
            $user->save();

            $request->session()->put('2fa_passed', true);

            return redirect()->intended('/home');
        }
    }
}
