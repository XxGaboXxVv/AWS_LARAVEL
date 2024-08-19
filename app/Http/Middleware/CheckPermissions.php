<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permissionType
     * @return mixed
     */
    public function handle($request, Closure $next, $permissionType)
    {
        $user = Auth::user();

        if ($user->ID_ROL == 1) {
            // El administrador tiene todos los permisos
            return $next($request);
        }

        $permissions = DB::table('TBL_PERMISOS')
            ->where('ID_ROL', $user->ID_ROL)
            ->first();

        if ($permissions && $permissions->{$permissionType} == 1) {
            return $next($request);
        }

        return redirect()->route('home')->withErrors('No tienes permiso para realizar esta acción.');
    }
}