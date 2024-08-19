@extends('adminlte::page')

@section('title', 'Página Principal')

@section('content_header')
    <h1>Bienvenido a la Villa Las Acacias</h1>
@stop

@section('content')
    @if ($advertenciaVencimiento)
        <div class="alert alert-warning">
            <strong>Advertencia:</strong> Tu contraseña vencerá en {{ $diasRestantes }} días. Por favor, cambiela pronto, Puede hacerlo en la pagina de perfil en el apartado de cambiar contraseña .
        </div>
    @endif
    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $numeroUsuarios }}</h3>
                    <p>Usuarios Registrados</p>
                    <!--<p>Hoy: {{ $usuariosDia }}</p>
                    <p>Este Mes: {{ $usuariosMes }}</p>
                    <p>Este Año: {{ $usuariosAno }}</p>-->
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $numeroReservas }}</h3>
                    <p>Reservas Totales</p>
                  <!--  <p>Hoy: {{ $reservasDia }}</p>
                    <p>Este Mes: {{ $reservasMes }}</p>
                    <p>Este Año: {{ $reservasAno }}</p>-->
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $numeroVisitantes }}</h3>
                    <p>Visitantes Registrados</p>
                    <!--<p>Hoy: {{ $visitantesDia }}</p>
                    <p>Este Mes: {{ $visitantesMes }}</p>
                    <p>Este Año: {{ $visitantesAno }}</p>-->
                </div>
                <div class="icon">
                    <i class="fas fa-user-friends"></i>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.34/moment-timezone-with-data.min.js"></script>
@stop
