<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ReservacionesController;
use App\Http\Controllers\ResidentesController;
use App\Http\Controllers\AnuncioEventoController;
use App\Http\Controllers\VisitantesController;
use App\Http\Controllers\VisitanteRecurrente;
use App\Http\Controllers\BitacoraUsuario;
use App\Http\Controllers\BitacoraVisita;
use App\Http\Controllers\Condominios;
use App\Http\Controllers\EstadoReservacion;
use App\Http\Controllers\EstadodeUsuario;
use App\Http\Controllers\EstadoPersonaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\Rol;
use App\Http\Controllers\Permisos;
use App\Http\Controllers\Instalaciones;
use App\Http\Controllers\HomeController;
use PragmaRX\Google2FALaravel\Middleware as Google2FAMiddleware;
use App\Http\Controllers\Auth\RegisterController;
use App\Models\User;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SetPasswordController;
use App\Http\Controllers\AuthPrimerIngresoController;
use App\Mail\BienvenidaMail;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\parentescos;
use App\Http\Controllers\TipoPersona;
use App\Http\Controllers\HistorialContraseñas;
use App\Http\Controllers\TipoContacto;
use App\Http\Controllers\TipoCondominio;
use App\Http\Controllers\Parametros;




// Ruta principal de la aplicación
Route::get('/', function () {
    return view('auth.login');
});

// Rutas de autenticación
Auth::routes(['verify' => true]);

// Ruta para cerrar sesión
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Ruta para el inicio de sesión
Route::post('login', [LoginController::class, 'login'])->name('login');

// Rutas para 2FA Google
Route::aliasMiddleware('2fa', \App\Http\Middleware\Google2FAMiddleware::class);

// Rutas protegidas por 2fa
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::middleware(['auth', '2fa'])->group(function () {
        Route::get('/home', [HomeController::class, 'index'])->name('home');
    });
});


// Rutas para completar el registro y verificar 2fa
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/complete-registration', [TwoFactorController::class, 'showVerify2faForm'])->name('complete.registration')->middleware('auth');
    Route::post('/verify-2fa', [TwoFactorController::class, 'verify2fa'])->name('verify.2fa')->middleware('auth');
});


/*RUTAS PARA REGISTER*/
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
    Route::get('/completar-registro', [RegisterController::class, 'completeRegistration'])->name('completar-registro');
    Route::post('/verificar-2fa', [RegisterController::class, 'verify2FA'])->name('verificar-2fa');
    Route::get('/approve-user/{userId}', [RegisterController::class, 'approveUser'])->name('approve.user');
});


/*FIN RUTAS PARA REGISTER*/

/* RUTAS Usuario*/
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/Usuarios', [UsuarioController::class, 'GetUsuarios'])->name('Usuarios')->middleware('auth');
    Route::get('/Usuarios-fetch', [UsuarioController::class, 'fetchUsuarios'])->name('Usuarios-fetch')->middleware('auth');
    Route::post('/usuarios/crear', [UsuarioController::class, 'crear'])->name('usuarios.crear')->middleware('auth');
    Route::post('/usuarios/editar/{id}', [UsuarioController::class, 'editar'])->name('usuarios.editar')->middleware('auth');
    Route::post('/usuarios/{id}/generar-password', [UsuarioController::class, 'generarPassword'])->name('usuarios.generarPassword');
    Route::post('/usuarios/eliminar', [UsuarioController::class, 'eliminar'])->name('usuarios.eliminar');
    Route::get('/reporte-usuarios', [UsuarioController::class, 'generarReporte'])->name('usuarios.reporte');
    Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::get('password/reset/{token}', [UsuarioController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('password/reset', [UsuarioController::class, 'resetPassword'])->name('password.update');
    Route::get('register/2fa/{id_usuario}', [UsuarioController::class, 'register2FA'])->name('register.2fa');
    Route::post('verify-2fa-register/{id_usuario}', [UsuarioController::class, 'verifyRegister2FA'])->name('verify.register.2fa'); // Cambia el nombre de la ruta
    });
    });

/*RUTAS PARA REINICIO DE CONTRASEÑA*/ 
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
Route::get('password/request', [UsuarioController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('password/email', [UsuarioController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [UsuarioController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('password/reset-2fa/{id_usuario}', [UsuarioController::class, 'verifyResetPassword2FA'])->name('verify.reset.password.2fa');
Route::post('password/reset', [UsuarioController::class, 'resetPassword'])->name('password.update');

    });

/*FIN RUTAS PARA REINICIO DE CONTRASEÑA*/ 


/*RUTAS PARA ANUNCIOS Y EVENTOS*/ 
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/AnuncioEvento', [AnuncioEventoController::class, 'index'])->name('AnuncioEvento')->middleware(middleware:'auth');;
    Route::post('/anuncios-eventos/guardar', [AnuncioEventoController::class, 'guardar'])->name('guardar_anuncio_evento');
    Route::post('/anuncios-eventos/actualizar/{id}', [AnuncioEventoController::class, 'actualizar'])->name('actualizar_anuncio_evento');
    Route::post('/anuncios-eventos/eliminar', [AnuncioEventoController::class, 'eliminar'])->name('eliminar_anuncio_evento');
});


/*RUTAS PARA RESIDENTES*/ 
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
Route::get('/Residentes', [ResidentesController::class, 'GetResidentes'])->name('Residentes')->middleware('auth');
Route::get('/Residentes-fetch', [ResidentesController::class, 'fetchResidentes'])->name('Residentes-fetch')->middleware('auth');
Route::post('/residentes/crear', [ResidentesController::class, 'crear'])->name('residentes.crear')->middleware('auth');
Route::post('/residentes/editar/{id}', [ResidentesController::class, 'editar'])->name('residentes.editar');
Route::post('/residentes/eliminar/{id}', [ResidentesController::class, 'eliminar'])->name('residentes.eliminar')->middleware('auth');
Route::get('/reporte-residentes', [ResidentesController::class, 'generarReporte'])->name('residentes.reporte');

    });



/* RUTAS PARA VISITANTES */
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/Visitantes', [VisitantesController::class, 'Visitante'])->name('Visitantes')->middleware('auth');
    Route::get('/Visitantes-fetch', [VisitantesController::class, 'fetchVisitantes'])->name('Visitantes.fetch')->middleware('auth');
    Route::post('/visitantes/guardar', [VisitantesController::class, 'crear'])->name('visitantes.guardar')->middleware('auth');
    Route::post('/visitantes/actualizar/{id}', [VisitantesController::class, 'actualizar'])->name('visitantes.actualizar')->middleware('auth');
    Route::post('/visitantes/eliminar/{id}', [VisitantesController::class, 'eliminar'])->name('visitantes.eliminar')->middleware('auth');
    Route::get('/reporte-visitantes', [VisitantesController::class, 'generarReporte'])->name('visitantes.reporte')->middleware('auth');
});


/*RUTAS PARA VISITANTES RECURRENTES*/ 
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/VisitanteRecurrente', [VisitanteRecurrente::class, 'getRecurrente'])->name('VisitanteRecurrente')->middleware('auth');
    Route::get('/Visitantes_Recurrentes-fetch', [VisitanteRecurrente::class, 'fetchVisitantesRecurrentes'])->name('Visitantes_Recurrentes.fetch')->middleware('auth');
    Route::post('/visitante-recurrente/crear', [VisitanteRecurrente::class, 'crear'])->name('visitante-recurrente.crear');
    Route::post('/visitante-recurrente/actualizar/{id}', [VisitanteRecurrente::class, 'actualizar'])->name('visitante-recurrente.actualizar');
    Route::post('/visitante-recurrente/eliminar/{id}', [VisitanteRecurrente::class, 'eliminar'])->name('visitante-recurrente.eliminar');
    Route::get('/reporte-visitante-recurrente', [VisitanteRecurrente::class, 'generarReporte'])->name('visitante-recurrente.reporte');
});



/* Reservaciones */
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/reservaciones', [ReservacionesController::class, 'reserva'])->name('reservaciones')->middleware('auth');
    Route::get('/Reservaciones-fetch', [ReservacionesController::class, 'fetchReservaciones'])->name('Reservaciones.fetch')->middleware('auth');
    Route::post('/reservaciones/guardar', [ReservacionesController::class, 'crear'])->name('reservaciones.guardar');
    Route::post('/reservaciones/actualizar/{id}', [ReservacionesController::class, 'actualizar'])->name('reservaciones.actualizar');
    Route::post('/reservaciones/eliminar/{id}', [ReservacionesController::class, 'eliminar'])->name('reservaciones.eliminar');
    Route::get('/reporte-reservaciones', [ReservacionesController::class, 'generarReporte'])->name('reservaciones.reporte');
});

/*RUTAS PARA PERFIL*/
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
Route::middleware(['auth'])->group(function () {
Route::get('/perfil', [PerfilController::class, 'mostrar'])->name('perfil');
Route::post('/perfil/actualizar', [PerfilController::class, 'actualizarPerfil'])->name('perfil.actualizar');
Route::post('/perfil/cambiar-contraseña', [PerfilController::class, 'cambiarContraseña'])->name('perfil.cambiarContraseña');
Route::post('/perfil/2fa', [PerfilController::class, 'toggle2fa'])->name('perfil.2fa');
Route::get('/Perfil', [PerfilController::class, 'completeRegistration'])->name('completeRegistration');

    });
    });


/*RUTAS PARA SEGURIDAD*/
 /*Bitacora Usuario*/
 Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/bitacora-usuario', [BitacoraUsuario::class, 'getBitacoraUsuario'])->middleware(middleware:'auth');;
    Route::get('/get-bitacora-usuario', [BitacoraUsuario::class, 'fetchBitacoraUsuario'])->middleware(middleware:'auth');;
    Route::post('/BitacoraUsuario/guardar', [BitacoraUsuario::class, 'crear'])->name('bitacoraUsuario.guardar')->middleware(middleware:'auth');;
    Route::post('/BitacoraUsuario/actualizar/{id}', [BitacoraUsuario::class, 'actualizar'])->name('bitacoraUsuario.actualizar')->middleware(middleware:'auth');;
    Route::post('/BitacoraUsuario/eliminar/{id}', [BitacoraUsuario::class, 'eliminar'])->name('bitacoraUsuario.eliminar')->middleware(middleware:'auth');;
    Route::get('/reporte-BitacoraUsuario', [BitacoraUsuario::class, 'generarReporte'])->name('bitacoraUsuario.reporte')->middleware(middleware:'auth');;
    });



/*Bitacora visita*/
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/BitacoraVisita', [BitacoraVisita::class, 'bitacora'])->name('BitacoraVisita')->middleware('auth');
    route::get('/fetch-bitacora-visita', [BitacoraVisita::class, 'fetchBitacoraVisita'])->name('fetch.bitacora.visita');
    Route::post('/bitacora/guardar', [BitacoraVisita::class, 'crear'])->name('bitacora.guardar')->middleware(middleware:'auth');;
    Route::post('/bitacora/actualizar/{id}', [BitacoraVisita::class, 'actualizar'])->name('bitacora.actualizar')->middleware(middleware:'auth');;
    Route::post('/bitacora/eliminar/{id}', [BitacoraVisita::class, 'eliminar'])->name('bitacora.eliminar')->middleware(middleware:'auth');;
    Route::get('/reporte-bitacora', [BitacoraVisita::class, 'generarReporte'])->name('bitacora.reporte')->middleware(middleware:'auth');;
});


/*Rol*/
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
Route::get('/Rol', [Rol::class, 'getRoles'])->name('Rol')->middleware(middleware:'auth');
Route::post('/roles/guardar', [Rol::class, 'crear'])->name('roles.guardar')->middleware(middleware:'auth');;
Route::post('/roles/actualizar/{id}', [Rol::class, 'actualizar'])->name('roles.actualizar')->middleware(middleware:'auth');;
Route::post('/roles/eliminar/{id}', [Rol::class, 'eliminar'])->name('roles.eliminar')->middleware(middleware:'auth');;
Route::get('/reporte-roles', [Rol::class, 'generarReporte'])->name('roles.reporte')->middleware(middleware:'auth');;

});

/*Parentescos*/  

Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/parentescos', [parentescos::class, 'getParentescos'])->name('parentescos')->middleware(middleware:'auth');;
    Route::post('/parentescos', [parentescos::class, 'crear'])->name('parentescos.crear')->middleware(middleware:'auth');;
    Route::post('/parentescos/{id}', [parentescos::class, 'actualizar'])->name('parentescos.actualizar')->middleware(middleware:'auth');;
    Route::post('/parentescos/eliminar/{id}', [parentescos::class, 'eliminar'])->name('parentescos.eliminar')->middleware(middleware:'auth');;
    Route::get('/parentescos/reporte', [parentescos::class, 'generarReporte'])->name('parentescos.reporte')->middleware(middleware:'auth');;
    
    });
    
    
/*Estado RESERVA*/
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/Estado/de/Reservacion', [EstadoReservacion::class, 'getEstadoReservacion'])->name('Estado_de_Reservacion')->middleware(middleware:'auth');;
    Route::post('/EstadoReservacion/guardar', [EstadoReservacion::class, 'crear'])->name('estados_reservacion.guardar');
    Route::post('/EstadoReservacion/actualizar/{id}', [EstadoReservacion::class, 'actualizar'])->name('estados_reservacion.actualizar');
    Route::post('/EstadoReservacion/eliminar/{id}', [EstadoReservacion::class, 'eliminar'])->name('estados_reservacion.eliminar');
    Route::get('/reporte-EstadoReservacion', [EstadoReservacion::class, 'generarReporte'])->name('estados_reservacion.reporte');
    });
/*Estado persona*/
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/Estado/de/Persona', [EstadoPersonaController::class, 'getEstadoPersona'])->name('Estado_de_Persona')->middleware(middleware:'auth');;
    Route::post('/EstadoPersona/guardar', [EstadoPersonaController::class, 'crear'])->name('estados_persona.guardar');
    Route::post('/EstadoPersona/actualizar/{id}', [EstadoPersonaController::class, 'actualizar'])->name('estados_persona.actualizar');
    Route::post('/EstadoPersona/eliminar/{id}', [EstadoPersonaController::class, 'eliminar'])->name('estados_persona.eliminar');
    Route::get('/reporte-EstadoPersona', [EstadoPersonaController::class, 'generarReporte'])->name('estados_persona.reporte');
    });


/*Estado Usuario*/
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/Estado/de/Usuario', [EstadodeUsuario::class, 'getEstadodeUsuario'])->name('Estado_de_Usuario')->middleware(middleware:'auth');;
    Route::post('/EstadodeUsuario/guardar', [EstadodeUsuario::class, 'crear'])->name('estados.guardar');
    Route::post('/EstadodeUsuario/actualizar/{id}', [EstadodeUsuario::class, 'actualizar'])->name('estados.actualizar');
    Route::post('/EstadodeUsuario/eliminar/{id}', [EstadodeUsuario::class, 'eliminar'])->name('estados.eliminar');
    Route::get('/reporte-EstadodeUsuario', [EstadodeUsuario::class, 'generarReporte'])->name('estados.reporte');
    });

/*MANTENIMIENTO*/
/*INSTALACIONES*/
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/Instalaciones', [Instalaciones::class, 'getInstalaciones'])->name('Instalaciones')->middleware(middleware:'auth');;
    Route::post('/Instalaciones/crear', [Instalaciones::class, 'crear'])->name('Instalaciones.crear');
    Route::post('/Instalaciones/actualizar/{id}', [Instalaciones::class, 'actualizar'])->name('Instalaciones.actualizar');
    Route::post('/Instalaciones/eliminar/{id}', [Instalaciones::class, 'eliminar'])->name('Instalaciones.eliminar');
    Route::get('/Instalaciones/reporte', [Instalaciones::class, 'generarReporte'])->name('Instalaciones.reporte');
});

/*Condominios*/
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/Condominios', [Condominios::class, 'getCondominios'])->name('Condominios')->middleware(middleware:'auth');;
    Route::get('/Condominios-fetch', [Condominios::class, 'fetchCondominios'])->name('Condominios.fetch')->middleware('auth');
    Route::post('/Condominios/guardar', [Condominios::class, 'crear'])->name('condominios.store');
    Route::post('/Condominios/actualizar/{id}', [Condominios::class, 'actualizar'])->name('condominios.actualizar');
    Route::post('/Condominios/eliminar/{id}', [Condominios::class, 'eliminar'])->name('condominios.eliminar');
    Route::get('/reporte-Condominios', [Condominios::class, 'generarReporte'])->name('condominios.reporte');
    });



/*Backup de la base de datos */
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('backup', [BackupController::class, 'listBackups'])->name('backup')->middleware(middleware:'auth');;
    Route::get('backup/create', [BackupController::class, 'createBackup'])->name('backups.create');
    Route::get('backup/download/{file_name}', [BackupController::class, 'downloadBackup'])->name('backups.download');
    Route::get('/backups/zip/{file_name}', [BackupController::class, 'convertToZip'])->name('backups.zip');
    Route::delete('/backups/{file_name}', [BackupController::class, 'deleteBackup'])->name('backups.delete');
    });
    


/* Rutas para TipoPersona */
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/TipoPersona', [TipoPersona::class, 'getTipoPersona'])->name('TipoPersona')->middleware('auth');
    Route::post('/tipo-persona/crear', [TipoPersona::class, 'crear'])->name('tipo-persona.crear');
    Route::post('/tipo-persona/actualizar/{id}', [TipoPersona::class, 'actualizar'])->name('tipo-persona.actualizar');
    Route::post('/tipo-persona/eliminar/{id}', [TipoPersona::class, 'eliminar'])->name('tipo-persona.eliminar');
    Route::get('/reporte-tipo-persona', [TipoPersona::class, 'generarReporte'])->name('tipo-persona.reporte');
});

 /*Historial de contraseñas*/
 Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/Historial/Contraseñas', [HistorialContraseñas::class, 'getHistorialContraseñas'])->name('Historial_Contraseñas')->middleware(middleware:'auth');
    Route::post('/HistorialContraseñas/guardar', [HistorialContraseñas::class, 'crear'])->name('historial.guardar');
    Route::post('/HistorialContraseñas/actualizar/{id}', [HistorialContraseñas::class, 'actualizar'])->name('historial.actualizar');
    Route::post('/HistorialContraseñas/eliminar/{id}', [HistorialContraseñas::class, 'eliminar'])->name('historial.eliminar');
    Route::get('/reporte-HistorialContraseñas', [HistorialContraseñas::class, 'generarReporte'])->name('historial.reporte');
    });


/*Tipo de contacto*/
    
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/tipo-contacto', [TipoContacto::class, 'getTipoContacto'])->name('tipo-contacto')->middleware(middleware:'auth');;
    Route::post('/tipo-contacto/crear', [TipoContacto::class, 'crear'])->name('tipo-contacto.crear');
    Route::post('/tipo-contacto/actualizar/{id}', [TipoContacto::class, 'actualizar'])->name('tipo-contacto.actualizar');
    Route::post('/tipo-contacto/eliminar/{id}', [TipoContacto::class, 'eliminar'])->name('tipo-contacto.eliminar');
    Route::get('/tipo-contacto/reporte', [TipoContacto::class, 'generarReporte'])->name('tipo-contacto.reporte');
    });
    

/* Permisos */
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/Permisos', [Permisos::class, 'getPermisos'])->name('Permisos')->middleware('auth');
    Route::post('/permisos/crear', [Permisos::class, 'crear'])->name('permisos.crear');
    Route::post('/permisos/actualizar/{id}', [Permisos::class, 'actualizar'])->name('permisos.actualizar');
    Route::post('/permisos/eliminar/{id}', [Permisos::class, 'eliminar'])->name('permisos.eliminar');
    Route::get('/reporte-permisos', [Permisos::class, 'generarReporte'])->name('permisos.reporte');
});


/*Tipo de condominio*/

Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/tipo-condominio', [TipoCondominio::class, 'getTipoCondominio'])->name('tipo-condominio')->middleware(middleware:'auth');;
    Route::post('/tipo-condominio/crear', [TipoCondominio::class, 'crear'])->name('tipo-condominio.crear');
    Route::post('/tipo-condominio/actualizar/{id}', [TipoCondominio::class, 'actualizar'])->name('tipo-condominio.actualizar');
    Route::post('/tipo-condominio/eliminar/{id}', [TipoCondominio::class, 'eliminar'])->name('tipo-condominio.eliminar');
    Route::get('/tipo-condominio/reporte', [TipoCondominio::class, 'generarReporte'])->name('tipo-condominio.reporte');
});

/* RUTAS PARA PARAMETROS */
Route::middleware([App\Http\Middleware\LogPageChanges::class])->group(function () {
    Route::get('/parametros', [Parametros::class, 'getparametros'])->name('parametros')->middleware(middleware:'auth');;
    Route::post('/parametros/crear', [Parametros::class, 'crear'])->name('parametros.store');
    Route::post('/parametros/actualizar/{id}', [Parametros::class, 'actualizar'])->name('parametros.update');
    Route::post('/parametros/eliminar/{id}', [Parametros::class, 'eliminar'])->name('parametros.destroy');
    Route::get('/parametros/reporte', [Parametros::class, 'generarReporte'])->name('parametros.reporte');
});



