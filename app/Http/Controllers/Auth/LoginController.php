<?php
namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use DB;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
   
  
    
    public function logout(Request $request)
    {
        $user = Auth::user();
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $date->setTimezone(new \DateTimeZone('America/Tegucigalpa'));
        $user->FECHA_ULTIMA_CONEXION = $date->format('Y-m-d H:i:s');
        $user->save();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    protected function authenticated(Request $request, $user)
    {
        $user->INTENTOS_FALLIDOS = 0;
        $user->save();

        if ($user->google2fa_secret) {
            return redirect()->route('complete.registration');
        }

        return redirect()->intended($this->redirectPath());
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    protected function attemptLogin(Request $request)
{
    $request->validate([
        'email' => 'required|string|email|max:70|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/',
        'password' => 'required|string',
    ], [
        'email.regex' => 'El correo electrónico contiene caracteres no permitidos.',
    ]);

    $user = User::where('EMAIL', $request->input('email'))->first();

    if ($user) {
        // Verifica si la contraseña ya ha vencido
        $fechaVencimiento = Carbon::parse($user->FECHA_VENCIMIENTO);
        if (Carbon::now()->greaterThanOrEqualTo($fechaVencimiento)) {
            // Cambiar el estado del usuario a "requiere restablecimiento de contraseña" (ID_ESTADO_USUARIO = 4)
            $user->ID_ESTADO_USUARIO = 2;
            $user->save();

        }
       // Verifica si el usuario está en un estado que no permite el acceso
       if (in_array($user->ID_ESTADO_USUARIO, [2, 3, 4, 5])) {
        return false ;
    }
}

return $this->guard()->attempt(
    $this->credentials($request), $request->filled('remember')
);
}


protected function sendFailedLoginResponse(Request $request)
{
    $user = User::where('email', $request->input('email'))->first();

    if ($user) {
        // Verifica si el usuario tiene un estado inactivo antes de intentar asignar "INTENTOS_FALLIDOS"
        if ($user->ID_ESTADO_USUARIO != 2) {
            $user->INTENTOS_FALLIDOS += 1;

            $date = new \DateTime('now', new \DateTimeZone('UTC'));
            $date->setTimezone(new \DateTimeZone('America/Tegucigalpa'));
            $user->ULTIMOS_INTENTOS_FALLIDOS = $date->format('Y-m-d H:i:s');
            $user->save();

            // Consulta el parámetro de intentos fallidos
            $parametroIntentosFallidos = DB::table('TBL_MS_PARAMETROS')
                ->where('PARAMETRO', 'INTENTOS_FALLIDOS')
                ->value('VALOR');

            // Si no se encuentra el parámetro, se usa un valor por defecto (ejemplo: 5 intentos)
            $maxIntentosFallidos = $parametroIntentosFallidos ? intval($parametroIntentosFallidos) : 5;

            if ($user->INTENTOS_FALLIDOS == $maxIntentosFallidos) {
                return redirect()->route('login')->withErrors(['email' => 'Has alcanzado el número máximo de intentos permitidos. Si se equivoca una vez más, su cuenta será bloqueada.']);
            }

            if ($user->INTENTOS_FALLIDOS > $maxIntentosFallidos) {
                $user->ID_ESTADO_USUARIO = 3; // Bloqueado
                $user->save();
                return redirect()->route('login')->withErrors(['email' => 'Tu cuenta ha sido bloqueada por demasiados intentos fallidos, para poder ingresar restablezca su contraseña o comuníquese con el administrador.']);
            }
        }

        // Verifica si la contraseña ya ha vencido
        $fechaVencimiento = Carbon::parse($user->FECHA_VENCIMIENTO);
        if (Carbon::now()->greaterThanOrEqualTo($fechaVencimiento)) {
            // Cambiar el estado del usuario a "requiere restablecimiento de contraseña" (ID_ESTADO_USUARIO = 2)
            $user->ID_ESTADO_USUARIO = 2;
            $user->save();

            return redirect()->route('login')->withErrors(['email' => 'Tu contraseña ha vencido. Por favor, restablécela antes de ingresar.']);
        }

        if ($user->ID_ESTADO_USUARIO == 4) {
            return redirect()->route('login')->withErrors(['email' => 'Necesitas restablecer tu contraseña antes de poder acceder.']);
        }

        if ($user->ID_ESTADO_USUARIO == 5) {
            return redirect()->route('login')->withErrors(['email' => 'Necesitas obtener acceso por parte de la Administración.']);
        }
    }

    return redirect()->route('login')->withErrors(['email' => 'Usuario/Contraseña inválidos']);
}
}