<?php

namespace App\Traits;

use Illuminate\Auth\Access\AuthorizationException;

trait HandlesAuthorizationExceptions
{
    protected function handleAuthorizationException(AuthorizationException $exception)
    {
        return redirect()->back()->withErrors(['authorization' => 'No tienes permiso para realizar esta acción.']);
    }
}