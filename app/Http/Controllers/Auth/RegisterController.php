<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    use RegistersUsers {
        register as registration;
    }

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'NOMBRE_USUARIO' => ['required', 'string', 'regex:/^[A-Z\s]+$/', 'max:70'],
            'EMAIL' => ['required', 'string', 'email', 'max:70', 'unique:TBL_MS_USUARIO'],
            'CONTRASEÑA' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])(?!.*\s).{8,}$/',
            ],
        ], [
            'NOMBRE_USUARIO.regex' => 'El nombre de usuario solo puede contener letras mayúsculas y espacios.',
            'CONTRASEÑA.regex' => 'La contraseña debe tener al menos una mayúscula, una minúscula, un número, un carácter especial, y no debe contener espacios.',
            'EMAIL.unique' => 'El correo electrónico ya ha sido registrado.',
            'CONTRASEÑA.confirmed' => 'Las contraseñas no coinciden.',
            'CONTRASEÑA.min' => 'La contraseña debe contener mínimo 8 caracteres.',
            'EMAIL.regex' => 'El correo electrónico contiene caracteres no permitidos.',
        ]);
    }

    protected function create(array $data)
    {
        $date = new DateTime('now', new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone('America/Tegucigalpa'));

        $parametroFechaVencimiento = DB::table('TBL_MS_PARAMETROS')
            ->where('PARAMETRO', 'FECHA_VENCIMIENTO')
            ->value('VALOR');

        $diasVencimiento = $parametroFechaVencimiento ? intval($parametroFechaVencimiento) : 90;
        $fechaVencimiento = $date->modify("+$diasVencimiento days")->format('Y-m-d H:i:s');

        return User::create([
            'ID_ROL' => 2,
            'NOMBRE_USUARIO' => $data['NOMBRE_USUARIO'],
            'EMAIL' => $data['EMAIL'],
            'CONTRASEÑA' => Hash::make($data['CONTRASEÑA']),
            'PRIMER_INGRESO' => $date->format('Y-m-d H:i:s'),
            'FECHA_ULTIMA_CONEXION' => null,
            'FECHA_VENCIMIENTO' => $fechaVencimiento,
            'google2fa_secret' => $data['google2fa_secret'],
            'ID_ESTADO_USUARIO' => 5,
            'INTENTOS_FALLIDOS' => 0,
            'INTENTOS_FALLIDOS_OTP' => 0,
            'ULTIMOS_INTENTOS_FALLIDOS' => null,
        ]);
    }

    public function register(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $google2fa = app('pragmarx.google2fa');

        $registration_data = $request->all();
        $registration_data["google2fa_secret"] = $google2fa->generateSecretKey();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $request->session()->put('registration_data', $registration_data);

        $QR_Image = $google2fa->getQRCodeInline(
            config('app.name'),
            $registration_data['EMAIL'],
            $registration_data['google2fa_secret']
        );

        return view('google2fa.register', [
            'QR_Image' => $QR_Image,
            'secret' => $registration_data['google2fa_secret']
        ]);
    }

    public function completeRegistration(Request $request)
    {
        return view('google2fa.registerverify2fa');
    }

    public function verify2FA(Request $request)
    {
        $registration_data = $request->session()->get('registration_data', []);

        if (empty($registration_data)) {
            return redirect()->route('register')->withErrors(['error' => 'No se encontraron datos de registro en la sesión.']);
        }

        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($registration_data['google2fa_secret'], $request->input('one_time_password'));

        if ($valid) {
            $request->merge($registration_data);
            $user = $this->create($registration_data);

            if ($user) {
                $registration_data['ID_USUARIO'] = $user->ID_USUARIO;
                \Log::info('Usuario creado con ID: ' . $user->ID_USUARIO);
            } else {
                \Log::error('Error al crear el usuario para el email: ' . $registration_data['EMAIL']);
                return redirect()->back()->withErrors(['error' => 'Error al crear el usuario.']);
            }

            // Enviar correo al superadministrador después del registro exitoso
            $this->sendAdminNotification($registration_data);

            // Cerrar sesión del usuario
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Redirigir al login después del registro
            return redirect('/login')->with('success', 'Registro exitoso. Por favor, espera la aprobación del administrador.');
        } else {
            return redirect()->back()->withErrors(['error' => 'Código OTP inválido. Inténtalo de nuevo.']);
        }
    }

    protected function sendAdminNotification($userData)
    {
        $superAdmin = User::where('ID_ROL', 1)->first(); // Suponiendo que el superadmin tiene el rol con ID 1

        if ($superAdmin) {
            $data = [
                'nombre' => $userData['NOMBRE_USUARIO'],
                'email' => $userData['EMAIL'],
                'userId' => $userData['ID_USUARIO'], // Asegurarse de pasar el ID del usuario
            ];

            Mail::send('emails.new_user_registration', $data, function ($message) use ($superAdmin) {
                $message->to($superAdmin->EMAIL, $superAdmin->NOMBRE_USUARIO)
                    ->subject('Nuevo usuario pendiente de aprobación');
            });
        }
    }

    public function approveUser($userId)
    {
        $user = User::find($userId);

        if ($user && $user->ID_ESTADO_USUARIO == 5) { // 5 = Pendiente
            $user->ID_ESTADO_USUARIO = 1; // 1 = Activo
            $user->save();

            return redirect()->back()->with('success', 'Usuario aprobado exitosamente.');
        }

        return redirect()->back()->withErrors(['error' => 'No se pudo aprobar el usuario.']);
    }
}
