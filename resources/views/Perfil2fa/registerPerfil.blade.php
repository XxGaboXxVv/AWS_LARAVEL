@extends('layouts.app')

@section('title', 'Configurar 2FA')

@section('content_header')
    <h1>Configurar 2FA</h1>
@stop

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">Configurar Google Authenticator</div>
            <div class="card-body text-center">
                <p>Configura la autenticación de dos factores escaneando el código QR a continuación. <p>Alternativamente, puedes usar esta clave secreta para configurar manualmente tu aplicación de autenticación: <strong>{{ $secret }}</strong></p>
                <p>Después de configurar tu aplicación de autenticación, ingresa el código generado por la aplicación en el siguiente formulario.</p>
                <div>{!! $QR_Image !!}</div>
                <p>Debes configurar tu aplicación de Google Authenticator antes de continuar. No podrás iniciar sesión de otra manera.</p>
                <a href="{{ route('completeRegistration') }}" class="btn btn-primary">Completar Registro</a>
            </div>
        </div>
    </div>
@stop
