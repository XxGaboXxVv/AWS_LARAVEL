<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Traits\LogsActivity;
use DateTime;
use DateTimeZone;
use App\Mail\BienvenidaMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\EstadoUsuario;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Access\AuthorizationException;
use App\Traits\HandlesAuthorizationExceptions;
use Illuminate\Support\Facades\Config;


class UsuarioController extends Controller
{
    use LogsActivity;   
    use HandlesAuthorizationExceptions;

  
    public function GetUsuarios(Request $request)
{
    $hasPermission = true;
    try {
        $this->authorize('view', User::class);
    } catch (AuthorizationException $e) {
        $hasPermission = false;
    }

    $query = $request->get('nombre');
    $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
    $url = $baseUrl . '/SEL_USUARIO';

    if ($query) {
        $url .= '?nombre=' . urlencode($query);
    }

    $response = Http::get($url);

    if ($response->successful()) {
        $Usuarios = $response->json();
        
        // Registrar la actividad solo si se tiene permiso
         if ($hasPermission) {
            $this->logActivity('usuarios', 'get');
        }

        // Obtener roles y estados de usuario
        $roles = $this->getRoles();
        $estadosUsuario = $this->getEstadosUsuario();

       // Asignar las descripciones de roles y estados a los usuarios
        foreach ($Usuarios as &$usuario) {
            $usuario['ROL'] = $roles->firstWhere('ID_ROL', $usuario['ID_ROL'])->ROL ?? 'Desconocido';
            $usuario['ESTADO_USUARIO'] = $estadosUsuario->firstWhere('ID_ESTADO_USUARIO', $usuario['ID_ESTADO_USUARIO'])->DESCRIPCION ?? 'Desconocido'; // Formatear la fecha y hora
        $usuario['FECHA_VENCIMIENTO'] = $usuario['FECHA_VENCIMIENTO'] ? \Carbon\Carbon::parse($usuario['FECHA_VENCIMIENTO'])->format('Y-m-d H:i:s') : '';
    }
         // Obtener el parámetro de fecha de vencimiento
         $parametroFechaVencimiento = DB::table('TBL_MS_PARAMETROS')
         ->where('PARAMETRO', 'FECHA_VENCIMIENTO')
         ->value('VALOR');

     // Si no se encuentra el parámetro, se usa un valor por defecto (ejemplo: 90 días)
     $diasVencimiento = $parametroFechaVencimiento ? intval($parametroFechaVencimiento) : 90;

     return view('usuarios', compact('Usuarios', 'roles', 'estadosUsuario', 'diasVencimiento','hasPermission'));
 } else {
     return view('error')->withErrors('Error al obtener la lista de Usuarios.');
 }
}
public function fetchUsuarios(Request $request)
{
    $start = $request->input('start', 0);
    $length = $request->input('length', 10);
    $search = $request->input('search.value', '');

    $baseUrl = Config::get('api.base_url');
    $response = Http::get($baseUrl . '/SEL_USUARIO');

    if ($response->failed()) {
        return response()->json(['error' => 'No se pudo obtener los datos'], 500);
    }

    $Usuarios = $response->json();

  
        // Obtener roles y estados de usuario
        $roles = $this->getRoles();
        $estadosUsuario = $this->getEstadosUsuario();

        // Asignar las descripciones de roles y estados a los usuarios
        foreach ($Usuarios as &$usuario) {
            $usuario['ROL'] = $roles->firstWhere('ID_ROL', $usuario['ID_ROL'])->ROL ?? 'Desconocido';
            $usuario['ESTADO_USUARIO'] = $estadosUsuario->firstWhere('ID_ESTADO_USUARIO', $usuario['ID_ESTADO_USUARIO'])->DESCRIPCION ?? 'Desconocido'; // Formatear la fecha y hora
        $usuario['FECHA_VENCIMIENTO'] = $usuario['FECHA_VENCIMIENTO'] ? \Carbon\Carbon::parse($usuario['FECHA_VENCIMIENTO'])->format('Y-m-d H:i:s') : '';
    }
         // Obtener el parámetro de fecha de vencimiento
         $parametroFechaVencimiento = DB::table('TBL_MS_PARAMETROS')
         ->where('PARAMETRO', 'FECHA_VENCIMIENTO')
         ->value('VALOR');

     // Si no se encuentra el parámetro, se usa un valor por defecto (ejemplo: 90 días)
     $diasVencimiento = $parametroFechaVencimiento ? intval($parametroFechaVencimiento) : 90;

    if ($search) {
        $Usuarios = array_filter($Usuarios, function ($usuario) use ($search) {
            return stripos($usuario['ROL'], $search) !== false ||
                stripos($usuario['NOMBRE_USUARIO'], $search) !== false ||
                stripos($usuario['ESTADO_USUARIO'], $search) !== false ||
                stripos($usuario['EMAIL'], $search) !== false ||
                stripos($usuario['FECHA_VENCIMIENTO'], $search) !== false;
        });
    }

    $totalData = count($Usuarios);
    $Usuarios = array_slice($Usuarios, $start, $length);

    return response()->json([
        "draw" => intval($request->input('draw')),
        "recordsTotal" => $totalData,
        "recordsFiltered" => $totalData,
        "data" => $Usuarios
    ]);
}

    
    // Función para obtener los roles
    public function getRoles()
    {
    return DB::table('TBL_MS_ROLES')->select('ID_ROL', 'ROL')->get();
    }

// Función para obtener los estados de usuario
    public function getEstadosUsuario()
    {
    return DB::table('TBL_ESTADO_USUARIO')->select('ID_ESTADO_USUARIO', 'DESCRIPCION')->get();
    }

    public function crear(Request $request)
    {
        try {
            $this->authorize('insert', User::class);
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Crear.']);
        }
       
    
        $date = new DateTime('now', new DateTimeZone('America/Tegucigalpa'));
    
        $data = $request->validate([
            'id_rol' => 'required|integer',
            'nombre_usuario' => 'required|string|max:70|',
            'email' => 'required|string|max:70|email|unique:TBL_MS_USUARIO|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
        ], [
            'email.unique' => 'El correo electrónico ya ha sido registrado.',
            'nombre_usuario.regex' => 'El nombre de usuario solo puede contener letras mayúsculas y espacios.',
            'email.regex' => 'El correo electrónico contiene caracteres no permitidos.',
        ]);
    
        $plainPassword = Str::random(10);
        $hashedPassword = Hash::make($plainPassword);
    
        $google2fa = app('pragmarx.google2fa');
        $google2fa_secret = $google2fa->generateSecretKey();
    
        $parametroFechaVencimiento = DB::table('TBL_MS_PARAMETROS')
            ->where('PARAMETRO', 'FECHA_VENCIMIENTO')
            ->value('VALOR');
    
        $diasVencimiento = $parametroFechaVencimiento ? intval($parametroFechaVencimiento) : 90;
    
        $fechaVencimiento = $date->modify("+$diasVencimiento days")->format('Y-m-d H:i:s');
    
        $payload = [
            'ID_ROL' => $data['id_rol'],
            'NOMBRE_USUARIO' => $data['nombre_usuario'],
            'ID_ESTADO_USUARIO' => 4,
            'EMAIL' => $data['email'],
            'CONTRASEÑA' => $hashedPassword,
            'PRIMER_INGRESO' => null,
            'FECHA_ULTIMA_CONEXION' => null,
            'FECHA_VENCIMIENTO' => $fechaVencimiento,
            'google2fa_secret' => $google2fa_secret,
            'INTENTOS_FALLIDOS' => 0,
            'INTENTOS_FALLIDOS_OTP' => 0,
            'ULTIMOS_INTENTOS_FALLIDOS' => null,
        ];
    
        Log::info('Payload enviado al API:', $payload);
    
        try {
            $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
            $response = Http::post($baseUrl . '/POST_USUARIO', $payload);
    
            if ($response->successful()) {
                $usuario = $response->json();

    
                if (!isset($usuario['ID_USUARIO']) || $usuario['ID_USUARIO'] == 0) {
                    $usuario['ID_USUARIO'] = DB::table('TBL_MS_USUARIO')
                        ->where('EMAIL', $data['email'])
                        ->value('ID_USUARIO');
    
                    if (!$usuario['ID_USUARIO']) {
                        Log::error('No se pudo obtener ID_USUARIO de la base de datos.');
                        return response()->json(['error' => 'Error al agregar Usuario.'], 500);
                    }
                }
    // Generar el token de restablecimiento después de crear el usuario
    $token = $this->generateResetToken($data['email']);
    
                Log::info('Respuesta de la creación de usuario', ['response' => $usuario]);
                $usuario['CONTRASEÑA'] = $plainPassword;
                $this->enviarCorreoBienvenida($usuario);
                $this->logActivity('usuario', 'POST', $data);
    
                return response()->json(['success' => 'Usuario ingresado exitosamente']);
            } else {
                Log::error('Error en la API:', ['response' => $response->body()]);
                return response()->json(['error' => 'Error al agregar Usuario.'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Excepción al crear usuario:', ['exception' => $e]);
            return response()->json(['error' => 'Error al agregar Usuario.'], 500);
        }
    }
    
    

    public function register2FA($id_usuario)
    {
        $user = User::find($id_usuario);

        if (!$user) {
            return redirect()->route('Usuarios')->withErrors('Usuario no encontrado.');
        }

        $google2fa = app('pragmarx.google2fa');
        $QR_Image = $google2fa->getQRCodeInline(
            config('app.name'),
            $user->EMAIL,
            $user->google2fa_secret
        );

        return view('googleauth2fa.register', [
            'QR_Image' => $QR_Image,
            'secret' => $user->google2fa_secret,
            'user' => $user
        ]);
    }

    public function verifyRegister2FA(Request $request, $id_usuario)
{
    // Validar el código OTP
    $request->validate([
        'one_time_password' => 'required|digits:6',
    ]);

    // Buscar el usuario por ID
    $user = User::find($id_usuario);

    if (!$user) {
        return redirect()->route('Usuarios')->withErrors('Usuario no encontrado.');
    }

    // Verificar el código OTP con Google 2FA
    $google2fa = app('pragmarx.google2fa');
    $valid = $google2fa->verifyKey($user->google2fa_secret, $request->input('one_time_password'));

    if ($valid) {
        // Verificar si el token existe en la tabla de reinicio de contraseña
        $token = DB::table('TBL_REINICIO_CONTRASEÑA')->where('EMAIL', $user->EMAIL)->value('TOKEN');

        if (!$token) {
            return redirect()->route('login')->withErrors('El enlace de restablecimiento de contraseña no es válido o ha expirado.');
        }

        // Redirigir a la página de restablecimiento de contraseña con el token válido
        return redirect()->route('password.reset', ['token' => $token]);
    } else {
        // Código OTP inválido
        return redirect()->back()->withErrors(['error' => 'Código OTP inválido. Inténtalo de nuevo.']);
    }
}


    private function generateResetToken($email)
    {
        $date = new DateTime('now', new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone('America/Tegucigalpa'));

        $token = Str::random(60);
        DB::table('TBL_REINICIO_CONTRASEÑA')->insert([
            'EMAIL' => $email,
            'TOKEN' => $token,
            'CREADO' => $date->format('Y-m-d H:i:s')
        ]);
        return $token;
    }

    private function enviarCorreoBienvenida($usuario)
{
    \Log::info('Datos del usuario:', ['usuario' => $usuario]);

    if (isset($usuario['EMAIL']) && isset($usuario['ID_USUARIO'])) {
        \Log::info('Enviando correo a:', ['EMAIL' => $usuario['EMAIL']]);

        $details = [
            'title' => 'Bienvenido a la Villa Las Acacias',
            'body' => 'Se le ha creado una cuenta Nueva. Su Nombre de Usuario es el correo con el que se creó la cuenta: ' . $usuario['EMAIL'] . ' y Su  contraseña temporal es: ' . $usuario['CONTRASEÑA'] . '. Por favor, haga clic en el siguiente enlace para configurar la autenticación de dos factores y luego restablecer tu contraseña:',
            'link' => route('register.2fa', ['id_usuario' => $usuario['ID_USUARIO']])
        ];

        try {
            Mail::to($usuario['EMAIL'])->send(new BienvenidaMail($details));
            \Log::info('Correo enviado exitosamente.');
        } catch (\Exception $e) {
            \Log::error('Error al enviar el correo:', ['error' => $e->getMessage()]);
        }
    } else {
        \Log::error('Datos de usuario incompletos:', ['usuario' => $usuario]);
    }
}

    public function showResetPasswordForm($token)
    {
        // Verificar el token y obtener el usuario correspondiente
        $user = DB::table('TBL_REINICIO_CONTRASEÑA')->where('TOKEN', $token)->first();

        if (!$user) {
            return redirect()->route('login')->withErrors('El enlace de restablecimiento de contraseña no es válido o ha expirado.');
        }

        return view('auth.reset_password', ['token' => $token, 'email' => $user->EMAIL]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'current_password' => 'required',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])(?!.*\s).{8,}$/',
                'confirmed',
            ],
        ], [
            'password.regex' => 'La contraseña debe tener al menos una mayúscula, una minúscula, un número, un carácter especial, y no debe contener espacios.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe contener mínimo 8 caracteres.'
        ]);
    
        $tokenData = DB::table('TBL_REINICIO_CONTRASEÑA')->where('TOKEN', $request->token)->first();
    
        if (!$tokenData) {
            return redirect()->route('login')->withErrors('El enlace de restablecimiento de contraseña no es válido o ha expirado.');
        }
    
        $user = User::where('EMAIL', $tokenData->EMAIL)->first();
    
        if (!$user || !Hash::check($request->current_password, $user->CONTRASEÑA)) {
            return redirect()->back()->withErrors('La contraseña temporal no es correcta.');
        }
    
        // Actualizar la contraseña del usuario
        $user->CONTRASEÑA = Hash::make($request->password);
        $user->ID_ESTADO_USUARIO = 1; // Cambia el estado del usuario a "Activo"
        
        if (is_null($user->PRIMER_INGRESO)) {
            $user->PRIMER_INGRESO = Carbon::now('America/Tegucigalpa');
        }
        
        $user->INTENTOS_FALLIDOS = 0; // Resetear intentos fallidos
        $user->INTENTOS_FALLIDOS_OTP = 0;
    
        // Obtener la duración de la contraseña desde la tabla de parámetros
        $duracionDias = DB::table('TBL_MS_PARAMETROS')
            ->where('PARAMETRO', 'FECHA_VENCIMIENTO')
            ->value('VALOR');
    
        if (!$duracionDias) {
            $duracionDias = 90; // Valor por defecto en caso de no encontrar el parámetro
        }
    
        // Convertir el valor a un número entero
        $duracionDias = intval($duracionDias);
        // Calcular la nueva fecha de vencimiento
        $user->FECHA_VENCIMIENTO = Carbon::now()->addDays($duracionDias);
    
        // Guardar los cambios en el usuario
        $user->save();
    
        // Guardar la nueva contraseña en el historial
        DB::table('TBL_MS_HIST_CONTRASEÑA')->insert([
            'ID_USUARIO' => $user->ID_USUARIO,
            'CONTRASEÑA' => Hash::make($request->password),
        ]);
    
        // Eliminar el token de restablecimiento para que no pueda ser reutilizado
        DB::table('TBL_REINICIO_CONTRASEÑA')->where('TOKEN', $request->token)->delete();
    
        \Log::info('Cuenta activada y primer ingreso registrado, redirigiendo a login');
        return redirect()->route('login')->with('success', 'Su contraseña ha sido restablecida con éxito. Por favor, inicia sesión.');
    }
    


    public function editar(Request $request, $id)
    {
        if ($id == 1) {
            return response()->json(['error'=>'No se puede editar el superadministrador.']);
        }
    
        try {
            $this->authorize('update', User::class); 
        } catch (AuthorizationException $e) {
            return response()->json(['error'=> 'No tienes permisos para poder Actualizar.']);
        }
        $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
        $response = Http::get($baseUrl . '/SEL_USUARIO');
    
        if (!$response->successful()) {
            return response()->json([ 'error'=>'Error al obtener los datos del Usuario.']);
        }
    
        $usuarios = $response->json();
        $usuarioActual = collect($usuarios)->firstWhere('ID_USUARIO', $id);
    
        if (is_null($usuarioActual)) {
            return response()->json(['error', 'No se encontraron datos para el Usuario.']);
        }
    
        $data = $request->validate([
            'id_rol' => 'required|integer',
            'nombre_usuario' => 'required|string|max:70|',
            'email' => 'required|string|max:70|email',
        ], [
            'email.email' => 'El correo electrónico debe ser una dirección de correo válida.',

        ]);
    
        $oldData = [
            'id_rol' => $usuarioActual['ID_ROL'],
            'nombre_usuario' => $usuarioActual['NOMBRE_USUARIO'],
            'email' => $usuarioActual['EMAIL'],
            'contraseña' => $usuarioActual['CONTRASEÑA'],
            'id_estado_usuario' => $usuarioActual['ID_ESTADO_USUARIO'],
        ];
    
        $newData = [
            'id_rol' => $data['id_rol'],
            'nombre_usuario' => $data['nombre_usuario'],
            'email' => $data['email'],
            'id_estado_usuario' => $request->input('id_estado_usuario', $usuarioActual['ID_ESTADO_USUARIO']),
            'contraseña' => $usuarioActual['CONTRASEÑA'],
        ];
    
        $dataToSend = [
            'P_ID_USUARIO' => $id,
            'P_ID_ROL' => $newData['id_rol'],
            'P_NOMBRE_USUARIO' => $newData['nombre_usuario'],
            'P_ID_ESTADO_USUARIO' => $newData['id_estado_usuario'],
            'P_EMAIL' => $newData['email'],
            'P_CONTRASEÑA' => $newData['contraseña'],
            'P_PRIMER_INGRESO' => null,
            'P_FECHA_ULTIMA_CONEXION' => null,
            'P_FECHA_VENCIMIENTO' => null,
            'P_google2fa_secret' => null,
            'P_INTENTOS_FALLIDOS' => null,
            'P_INTENTOS_FALLIDOS_OTP' => null,
            'P_ULTIMOS_INTENTOS_FALLIDOS' => null,
        ];

        $response = Http::post($baseUrl .'/PUT_USUARIO', $dataToSend);

        if ($response->successful()) {
            $this->logActivity('usuario ' . $usuarioActual['NOMBRE_USUARIO'], 'put', $newData, $oldData);
            return response()->json(['success' => 'Usuario actualizado con éxito.']);
        } elseif ($response->status() == 409) {
            return response()->json(['error' => 'El correo electrónico ingresado ya ha sido registrado.']);
        } else {
            return response()->json(['error' => 'Error al actualizar Usuario.']);
        }
    }
    
 
    

    public function generarPassword($id)
    {
        // Verificar si el usuario es el superadmin
        if ($id == 1) {
            session()->flash('error', 'No se puede editar el superadministrador.');
            return redirect()->route('Usuarios');
        }
    
        try {
            $this->authorize('update', User::class);
        } catch (AuthorizationException $e) {
            return $this->handleAuthorizationException($e);
        }
    
        $usuario = User::findOrFail($id);
        
        // Guardar los datos antiguos para la bitácora
        $oldData = $usuario->toArray();
    
        // Generar una nueva contraseña aleatoria
        $nuevaContraseña = Str::random(12);
    
        // Encriptar la nueva contraseña
        $usuario->CONTRASEÑA = Hash::make($nuevaContraseña);
    
        // Generar el secreto 2FA
        $google2fa = app('pragmarx.google2fa');
        $google2fa_secret = $google2fa->generateSecretKey();
        $usuario->google2fa_secret = $google2fa_secret;
    
        // Consulta el parámetro de fecha de vencimiento
        $parametroFechaVencimiento = DB::table('TBL_MS_PARAMETROS')
            ->where('PARAMETRO', 'FECHA_VENCIMIENTO')
            ->value('VALOR');
    
        // Si no se encuentra el parámetro, se usa un valor por defecto (ejemplo: 90 días)
        $diasVencimiento = $parametroFechaVencimiento ? intval($parametroFechaVencimiento) : 90;
    
        // Actualizar la fecha de vencimiento
        $date = new DateTime('now', new DateTimeZone('America/Tegucigalpa'));
        $fechaVencimiento = $date->modify("+$diasVencimiento days")->format('Y-m-d H:i:s');
        $usuario->FECHA_VENCIMIENTO = $fechaVencimiento;
    
    // Generar el token de restablecimiento
     $token = $this->generateResetToken($usuario->EMAIL);

        // Guardar la nueva contraseña y la fecha de vencimiento en la base de datos
        $usuario->save();
        
    
        $details = [
            'link' => route('register.2fa', ['id_usuario' => $usuario['ID_USUARIO']])
        ];
    
        // Enviar correo al usuario con la nueva contraseña
        Mail::to($usuario->EMAIL)->send(new \App\Mail\NewPasswordMail($nuevaContraseña, $details));
    
        // Loguear la actividad, indicando que la contraseña ha sido actualizada
        $this->logActivity('usuario ' . $usuario->NOMBRE_USUARIO, 'put', ['CONTRASEÑA' => 'actualizada'], $oldData);
        session()->flash('success', 'Nueva contraseña generada y enviada por correo al usuario.');
        return redirect()->back();
    }
    
    public function eliminar(Request $request)
    {
        try {
            $this->authorize('delete', User::class);
        } catch (AuthorizationException $e) {
            Session::flash('error', 'No tienes permiso para poder Eliminar.');
            return redirect()->back();
        }
    
        $id = $request->input('P_ID_USUARIO');
        
        if ($id == 1) {
            session()->flash('error', 'No se puede eliminar el superadministrador.');
            return redirect()->route('Usuarios');
        }
    
        // Obtener los datos del usuario antes de eliminarlo para el log de actividad
        $baseUrl = Config::get('api.base_url');
        $response = Http::get($baseUrl .'/SEL_USUARIO');
        if (!$response->successful()) {
            return redirect()->route('Usuarios')->withErrors('Error al obtener los datos al usuario.');
        }
        
        $usuarios = $response->json();
        $usuarioActual = collect($usuarios)->firstWhere('ID_USUARIO', $id);
        if (is_null($usuarioActual)) {
            return redirect()->route('Usuarios')->withErrors('No se encontraron datos para el usuario.');
        }
    
        try {
            $response = Http::post($baseUrl . '/DEL_USUARIO', [
                'P_ID_USUARIO' => $id,
            ]);
    
            if ($response->successful()) {
                $this->logActivity('usuario', 'delete', [], $usuarioActual);
                session()->flash('success', 'Usuario eliminado con éxito.');
                return redirect()->route('Usuarios');
            } else {
                session()->flash('error', 'Error al eliminar Usuario.');
                return redirect()->route('Usuarios');
            }
        } catch (\Exception $e) {
            \Log::error('Excepción al eliminar usuario:', ['exception' => $e]);
            session()->flash('error', 'No se puede eliminar el Usuario porque está relacionado con otros registros.');
            return redirect()->route('Usuarios');
        }
    }
    

    public function showForgotPasswordForm()
{
    return view('auth.forgot_password');
}

public function sendResetLinkEmail(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    $user = User::where('EMAIL', $request->email)->first();

    if (!$user) {
        return back()->withErrors(['email' => 'No se encontró ningún usuario con ese correo electrónico.']);
    }

    // Generar una contraseña temporal
    $temporaryPassword = Str::random(10);

    // Generar el secreto 2FA
    $google2fa = app('pragmarx.google2fa');
    $google2fa_secret = $google2fa->generateSecretKey();

    // Guardar la contraseña temporal en el historial de contraseñas
    DB::table('TBL_MS_HIST_CONTRASEÑA')->insert([
        'ID_USUARIO' => $user->ID_USUARIO,
        'CONTRASEÑA' => bcrypt($temporaryPassword),
    ]);

    // Actualizar la contraseña del usuario y el secreto 2FA
    $user->CONTRASEÑA = bcrypt($temporaryPassword);
    $user->google2fa_secret = $google2fa_secret;
    $user->save();

     // Generar el token de restablecimiento
     $token = $this->generateResetToken($user->EMAIL);

    // Enviar el correo de restablecimiento de contraseña con la contraseña temporal
    $details = [
        'title' => 'Restablecimiento de contraseña',
        'body' => 'Su contraseña temporal es: ' . $temporaryPassword . '. Por favor, configure la autenticación de dos factores y luego restablezca su contraseña:',
        'link' => route('register.2fa', ['id_usuario' => $user->ID_USUARIO])
    ];

    try {
        Mail::to($request->email)->send(new \App\Mail\BienvenidaMail($details));
        return back()->with('status', 'Enlace de restablecimiento de contraseña enviado a su correo electrónico con una contraseña temporal.');
    } catch (\Exception $e) {
        Log::error('Error al enviar el correo:', ['error' => $e->getMessage()]);
        return back()->withErrors(['email' => 'Hubo un problema al enviar el correo. Por favor, inténtelo de nuevo más tarde.']);
    }
}



public function verifyResetPassword2FA(Request $request, $id_usuario)
{
    $request->validate([
        'one_time_password' => 'required|digits:6',
    ]);

    $user = User::find($id_usuario);

    if (!$user) {
        session()->flash('error', 'Usuario no encontrado.');
        return redirect()->route('login');
    }

    if (!$user->google2fa_secret) {
        session()->flash('error', 'No se ha configurado la autenticación de dos factores.');
        return redirect()->back();
    }

    $google2fa = app('pragmarx.google2fa');
    $valid = $google2fa->verifyKey($user->google2fa_secret, $request->input('one_time_password'));

    if ($valid) {
        // Verificar si el token existe en la tabla de reinicio de contraseña
        $token = DB::table('TBL_REINICIO_CONTRASEÑA')->where('EMAIL', $user->EMAIL)->value('TOKEN');

        if (!$token) {
            return redirect()->route('login')->withErrors('El enlace de restablecimiento de contraseña no es válido o ha expirado.');
        }

        // Redirigir a la página de restablecimiento de contraseña con el token válido
        return redirect()->route('password.reset', ['token' => $token]);
    } else {
        // Código OTP inválido
        return redirect()->back()->withErrors(['error' => 'Código OTP inválido. Inténtalo de nuevo.']);
    }
}

public function generarReporte(Request $request)
{
    $query = strtoupper($request->input('nombre'));  // Captura el valor de búsqueda del formulario
    $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
    $url = $baseUrl . '/SEL_USUARIO';  // URL de la API para obtener los usuarios

    $response = Http::get($url);

    if ($response->successful()) {
        $Usuarios = $response->json(); 

        // Obtener roles y estados de usuario
        $roles = $this->getRoles();
        $estadosUsuario = $this->getEstadosUsuario();

        // Asignar las descripciones de roles y estados a los usuarios
        foreach ($Usuarios as &$usuario) {
            $usuario['ROL'] = $roles->firstWhere('ID_ROL', $usuario['ID_ROL'])->ROL ?? 'Desconocido';
            $usuario['ESTADO_USUARIO'] = $estadosUsuario->firstWhere('ID_ESTADO_USUARIO', $usuario['ID_ESTADO_USUARIO'])->DESCRIPCION ?? 'Desconocido';
        }

        // Filtrar los usuarios si se ha proporcionado un nombre
        if ($query) {
            $Usuarios = array_filter($Usuarios, function($usuario) use ($query) {
                return stripos($usuario['NOMBRE_USUARIO'], $query) !== false;
            });
        }

        // Generar el PDF
        $pdf = Pdf::loadView('reportes.usuarios', compact('Usuarios', 'roles', 'estadosUsuario'));
        return $pdf->stream('reporte_usuarios.pdf');
    } else {
        // Manejar el caso en que la solicitud a la API falle
        return back()->withErrors(['error' => 'No se pudo obtener la lista de usuarios.']);
    }
}


}
