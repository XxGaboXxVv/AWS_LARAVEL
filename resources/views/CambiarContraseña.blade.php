@extends('adminlte::page')

@section('title', 'Cambiar Contraseña')

@section('content_header')
    <h1>Cambiar Contraseña</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form>
                <div class="form-group">
                    <label for="current_password">Contraseña Actual</label>
                    <input type="password" class="form-control" id="current_password" placeholder="Contraseña Actual">
                </div>
                <div class="form-group">
                    <label for="new_password">Nueva Contraseña</label>
                    <input type="password" class="form-control" id="new_password" placeholder="Nueva Contraseña">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar Nueva Contraseña</label>
                    <input type="password" class="form-control" id="confirm_password" placeholder="Confirmar Nueva Contraseña">
                </div>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </form>
        </div>
    </div>
@stop
