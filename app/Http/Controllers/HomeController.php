<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use DB;
use App\Models\User;
use Illuminate\Support\Facades\Config;


class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $baseUrl = Config::get('api.base_url'); // Obtiene la URL base de la API desde la configuración
        // Obtener datos de las APIs
        $usuariosResponse = Http::get($baseUrl. '/SEL_USUARIO');
        $reservasResponse = Http::get($baseUrl.'/SEL_TBL_RESERVAS');
        $visitantesResponse = Http::get($baseUrl.'/SEL_REGVISITAS');
        $visitantesRecurrentesResponse = Http::get($baseUrl.'/SEL_VISITANTES_RECURRENTES');

        // Obtener los datos en formato de colecciones
        $usuarios = collect($usuariosResponse->json());
        $reservas = collect($reservasResponse->json());
        $visitantes = collect($visitantesResponse->json());
        $visitantesRecurrentes = collect($visitantesRecurrentesResponse->json());

        // Convertir fechas a la zona horaria de Tegucigalpa
        $usuarios = $usuarios->map(function ($item) {
            if (isset($item['PRIMER_INGRESO'])) {
                $item['PRIMER_INGRESO'] = Carbon::parse($item['PRIMER_INGRESO'])->setTimezone('America/Tegucigalpa');
            }
            return $item;
        });

        $reservas = $reservas->map(function ($item) {
            if (isset($item['HORA_FECHA'])) {
                $item['HORA_FECHA'] = Carbon::parse($item['HORA_FECHA'])->setTimezone('America/Tegucigalpa');
            }
            return $item;
        });

        $visitantes = $visitantes->map(function ($item) {
            if (isset($item['FECHA_HORA'])) {
                $item['FECHA_HORA'] = Carbon::parse($item['FECHA_HORA'])->setTimezone('America/Tegucigalpa');
            }
            return $item;
        });

        $visitantesRecurrentes = $visitantesRecurrentes->map(function ($item) {
            if (isset($item['FECHA_HORA'])) {
                $item['FECHA_HORA'] = Carbon::parse($item['FECHA_HORA'])->setTimezone('America/Tegucigalpa');
            }
            return $item;
        });

        // Contar el número de registros
        $numeroUsuarios = $usuarios->count();
        $numeroReservas = $reservas->count();
        $numeroVisitantes = $visitantes->count() + $visitantesRecurrentes->count();

        // Filtrar registros por día, mes y año
        $today = Carbon::today('America/Tegucigalpa');
        $currentMonth = Carbon::now('America/Tegucigalpa')->month;
        $currentYear = Carbon::now('America/Tegucigalpa')->year;

        $usuariosDia = $usuarios->filter(function ($item) use ($today) {
            return isset($item['PRIMER_INGRESO']) && Carbon::parse($item['PRIMER_INGRESO'])->isToday();
        })->count();

        $reservasDia = $reservas->filter(function ($item) use ($today) {
            return isset($item['HORA_FECHA']) && Carbon::parse($item['HORA_FECHA'])->isToday();
        })->count();

        $visitantesDia = $visitantes->filter(function ($item) use ($today) {
            return isset($item['FECHA_HORA']) && Carbon::parse($item['FECHA_HORA'])->isToday();
        })->count() + $visitantesRecurrentes->filter(function ($item) use ($today) {
            return isset($item['FECHA_HORA']) && Carbon::parse($item['FECHA_HORA'])->isToday();
        })->count();

        $usuariosMes = $usuarios->filter(function ($item) use ($currentMonth) {
            return isset($item['PRIMER_INGRESO']) && Carbon::parse($item['PRIMER_INGRESO'])->month === $currentMonth;
        })->count();

        $reservasMes = $reservas->filter(function ($item) use ($currentMonth) {
            return isset($item['HORA_FECHA']) && Carbon::parse($item['HORA_FECHA'])->month === $currentMonth;
        })->count();

        $visitantesMes = $visitantes->filter(function ($item) use ($currentMonth) {
            return isset($item['FECHA_HORA']) && Carbon::parse($item['FECHA_HORA'])->month === $currentMonth;
        })->count() + $visitantesRecurrentes->filter(function ($item) use ($currentMonth) {
            return isset($item['FECHA_HORA']) && Carbon::parse($item['FECHA_HORA'])->month === $currentMonth;
        })->count();

        $usuariosAno = $usuarios->filter(function ($item) use ($currentYear) {
            return isset($item['PRIMER_INGRESO']) && Carbon::parse($item['PRIMER_INGRESO'])->year === $currentYear;
        })->count();

        $reservasAno = $reservas->filter(function ($item) use ($currentYear) {
            return isset($item['HORA_FECHA']) && Carbon::parse($item['HORA_FECHA'])->year === $currentYear;
        })->count();

        $visitantesAno = $visitantes->filter(function ($item) use ($currentYear) {
            return isset($item['FECHA_HORA']) && Carbon::parse($item['FECHA_HORA'])->year === $currentYear;
        })->count() + $visitantesRecurrentes->filter(function ($item) use ($currentYear) {
            return isset($item['FECHA_HORA']) && Carbon::parse($item['FECHA_HORA'])->year === $currentYear;
        })->count();
// Obtener el usuario autenticado
$user = auth()->user();

// Verificar si la contraseña está cerca de vencerse
$fechaVencimiento = Carbon::parse($user->FECHA_VENCIMIENTO);
$diasRestantes = Carbon::now()->diffInDays($fechaVencimiento, false);

// Definir el umbral de días antes de la advertencia (por ejemplo, 7 días)
$diasUmbral = DB::table('TBL_MS_PARAMETROS')
                ->where('PARAMETRO', 'DIAS_ANTICIPACION_VENCIMIENTO')
                ->value('VALOR');

$diasUmbral = intval($diasUmbral);

if (is_null($diasUmbral)) {
    $diasUmbral = 7; // Valor por defecto si no se encuentra el parámetro
}

$advertenciaVencimiento = $diasRestantes <= $diasUmbral && $diasRestantes > 0;

        return view('home', compact(
            'numeroUsuarios', 'numeroReservas', 'numeroVisitantes',
            'usuariosDia', 'reservasDia', 'visitantesDia',
            'usuariosMes', 'reservasMes', 'visitantesMes',
            'usuariosAno', 'reservasAno', 'visitantesAno',
        'advertenciaVencimiento', 'diasRestantes'
        ));
    }

    public function principal()
    {
        return redirect()->route('login');
    }
}
