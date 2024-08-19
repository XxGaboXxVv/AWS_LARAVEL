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
                <div>{!! $QR_Image !!}</div>
                <p>Debes configurar tu aplicación de Google Authenticator antes de continuar. No podrás iniciar sesión de otra manera.</p>
                <form action="{{ route('verify.register.2fa', $user->ID_USUARIO) }}" method="POST">@csrf
             <div class="form-group">
            <label for="one_time_password">Ingrese el Codigo OTP Generado por su Aplicacion</label>
            <input type="text" class="form-control" id="one_time_password" name="one_time_password" required>
        
            <button type="submit" class="btn btn-primary">Completar Registro</button>
             </form>
             @if($errors->any())
                    <div class="col-md-12">
                        <div class="alert alert-danger">
                          <strong>Contraseña por tiempo limitado fue escrita erroneamente, Por favor intente de Nuevo</strong>
                        </div>
                    </div>
                @endif

@stop
