<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Google2FAMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if ($user && $user->google2fa_secret && !$request->session()->get('2fa_passed', false)) {
            return redirect()->route('complete.registration');
        }
        return $next($request);
    }
}