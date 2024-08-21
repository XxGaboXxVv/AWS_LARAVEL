@extends('adminlte::page')

@section('title', 'Respaldos de la Base de Datos')

@section('content_header')
    <h1>Respaldos de la Base de Datos</h1>
@stop

@section('content')
 

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de respaldos</h3>
            <div class="card-tools">
                <a href="{{ route('backups.create') }}" class="btn btn-primary">Crear Respaldo</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nombre del Archivo</th>
                        <th>Tamaño</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
    @forelse($backups as $backup)
    <tr>
        <td>{{ $backup['file_name'] }}</td>
        <td>{{ number_format($backup['file_size'] / 1048576, 2) }} MB</td>
        <td>
            <a href="{{ route('backups.download', $backup['file_name']) }}" class="btn btn-success">Descargar</a>
            <form action="{{ route('backups.delete', $backup['file_name']) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </form>
            @if (!$backup['is_zip'])
                <a href="{{ route('backups.zip', $backup['file_name']) }}" class="btn btn-info">Convertir a ZIP</a>
            @endif
            <pre>{{ print_r($backups) }}</pre>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="3">No hay respaldos disponibles.</td>
    </tr>
@endforelse
</tbody>
            </table>
        </div>
    </div>
@stop
@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            @if($errors->has('authorization'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ $errors->first('authorization') }}',
                });
            @endif

            // SweetAlert configurations for success and error messages
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: '{{ session('success') }}',
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ session('error') }}',
                });
            @endif

            @error('email')
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ $message }}',
                });
            @enderror
        });
          // SweetAlert configurations for success and error messages
          @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: '{{ session('success') }}',
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ session('error') }}',
                });
            @endif
        @error('email')
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ $message }}', // Utiliza la variable $message que contiene el mensaje de error específico
        });
    @enderror
        
    </script>
    
@stop
