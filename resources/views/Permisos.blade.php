@extends('adminlte::page')

@section('title', 'Gestión de Permisos')

@section('content_header')
    <h1>Gestión de Permisos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Permisos</h3>
            <div class="card-tools">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#nuevoPermisoModal">Nuevo</button>
                    <form id="reporteForm" method="GET" action="{{ route('permisos.reporte') }}" target="_blank">
                    <input type="hidden" name="id_rol" id="searchInput">
                    <button type="submit" class="btn btn-success mt-2">Generar Reporte</button>
                </form>
                </div>
            </div>
        </div>
    </div>
  
    <div class="card-body">
            @if($hasPermission)
        <div class="table-container">
            <table id="permisos-table" class="table table-striped table-bordered shadow-lg mt-4" style="width: 100%">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>ID</th>
                        <th>ROL</th>
                        <th>OBJETO</th>
                        <th>PERMISO INSERCION</th>
                        <th>PERMISO ELIMINACION</th>
                        <th>PERMISO ACTUALIZACION</th>
                        <th>PERMISO CONSULTAR</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($Permisos as $permiso)
                        <tr>
                            <td>{{ $permiso["ID_PERMISO"] }}</td>
                            <td>{{ $permiso["ROL"] }}</td>
                            <td>{{ $permiso["OBJETO"] }}</td>
                            <td>{{ $permiso["PERMISO_INSERCION"] }}</td>
                            <td>{{ $permiso["PERMISO_ELIMINACION"] }}</td>
                            <td>{{ $permiso["PERMISO_ACTUALIZACION"] }}</td>
                            <td>{{ $permiso["PERMISO_CONSULTAR"] }}</td>
                            <td>
                                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editarPermisoModal{{ $permiso['ID_PERMISO'] }}">Editar</button>
                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#eliminarPermisoModal{{ $permiso['ID_PERMISO'] }}">Eliminar</button>
                                <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#mostrarMas{{ $permiso['ID_PERMISO'] }}">Mostrar más</button>
                            </td>
                        </tr>

                        <!-- Modal de "Mostrar más" -->
                        <div class="modal fade" id="mostrarMas{{ $permiso['ID_PERMISO'] }}" tabindex="-1" role="dialog" aria-labelledby="mostrarMas{{ $permiso['ID_PERMISO'] }}Label" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="mostrarMas{{ $permiso['ID_PERMISO'] }}Label">Detalles del Permiso</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>ID Permiso: {{ $permiso["ID_PERMISO"] }}</p>
                                        <p>Rol: {{ $permiso["ROL"] }}</p>
                                        <p>Objeto: {{ $permiso["OBJETO"] }}</p>
                                        <p>Permiso Inserción: {{ $permiso["PERMISO_INSERCION"] }}</p>
                                        <p>Permiso Eliminación: {{ $permiso["PERMISO_ELIMINACION"] }}</p>
                                        <p>Permiso Actualización: {{ $permiso["PERMISO_ACTUALIZACION"] }}</p>
                                        <p>Permiso Consultar: {{ $permiso["PERMISO_CONSULTAR"] }}</p>
                                        <p>Fecha Creación: {{ $permiso["FECHA_CREACION"] ? \Carbon\Carbon::parse($permiso["FECHA_CREACION"])->setTimezone('America/Tegucigalpa')->format('Y-m-d') : '' }}</p>
                                        <p>Creado Por: {{ $permiso["CREADO_POR"] }}</p>
                                        <p>Fecha Modificación: {{ $permiso["FECHA_MODIFICACION"] ? \Carbon\Carbon::parse($permiso["FECHA_MODIFICACION"])->setTimezone('America/Tegucigalpa')->format('Y-m-d ') : '' }}</p>
                                        <p>Modificado Por: {{ $permiso["MODIFICADO_POR"] }}</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal de editar permiso -->
<div class="modal fade" id="editarPermisoModal{{ $permiso['ID_PERMISO'] }}" tabindex="-1" role="dialog" aria-labelledby="editarPermisoModalLabel{{ $permiso['ID_PERMISO'] }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarPermisoModalLabel{{ $permiso['ID_PERMISO'] }}">Editar Permiso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="editar-permiso-form" id="editar-permiso-form-{{ $permiso['ID_PERMISO'] }}" data-id="{{ $permiso['ID_PERMISO'] }}">
                    @csrf
                    <div class="form-group">
                        <label for="id_rol">Rol:</label>
                        <select class="form-control" id="id_rol_{{ $permiso['ID_PERMISO'] }}" name="id_rol" required>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->ID_ROL }}"{{ $permiso['ID_ROL'] == $rol->ID_ROL ? 'selected' : '' }}>
                                    {{ $rol->ROL }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_objeto">Objeto:</label>
                        <select class="form-control" id="id_objeto_{{ $permiso['ID_PERMISO'] }}" name="id_objeto" required>
                            @foreach($objetos as $objeto)
                                <option value="{{ $objeto->ID_OBJETO }}"{{ $permiso['ID_OBJETO'] == $objeto->ID_OBJETO ? 'selected' : '' }}>
                                    {{ $objeto->OBJETO }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="permiso_insercion_{{ $permiso['ID_PERMISO'] }}">Permiso Inserción:</label>
                        <input type="text" class="form-control" id="permiso_insercion_{{ $permiso['ID_PERMISO'] }}" name="permiso_insercion" value="{{ $permiso['PERMISO_INSERCION'] }}" required>
                    </div>
                    <div class="form-group">
                        <label for="permiso_eliminacion_{{ $permiso['ID_PERMISO'] }}">Permiso Eliminación:</label>
                        <input type="text" class="form-control" id="permiso_eliminacion_{{ $permiso['ID_PERMISO'] }}" name="permiso_eliminacion" value="{{ $permiso['PERMISO_ELIMINACION'] }}" required>
                    </div>
                    <div class="form-group">
                        <label for="permiso_actualizacion_{{ $permiso['ID_PERMISO'] }}">Permiso Actualización:</label>
                        <input type="text" class="form-control" id="permiso_actualizacion_{{ $permiso['ID_PERMISO'] }}" name="permiso_actualizacion" value="{{ $permiso['PERMISO_ACTUALIZACION'] }}" required>
                    </div>
                    <div class="form-group">
                        <label for="permiso_consultar_{{ $permiso['ID_PERMISO'] }}">Permiso Consultar:</label>
                        <input type="text" class="form-control" id="permiso_consultar_{{ $permiso['ID_PERMISO'] }}" name="permiso_consultar" value="{{ $permiso['PERMISO_CONSULTAR'] }}" required>
                    </div>
                    <div class="form-group">
                        <label for="creado_por_{{ $permiso['ID_PERMISO'] }}">Creado Por:</label>
                        <input type="text" class="form-control" id="creado_por_{{ $permiso['ID_PERMISO'] }}" name="creado_por" value="{{ $permiso['CREADO_POR'] }}" required>
                    </div>

                    <div class="form-group">
                        <label for="fecha_modificacion_{{ $permiso['ID_PERMISO'] }}">Fecha de Modificación:</label>
                        <input type="text" class="form-control" id="fecha_modificacion_{{ $permiso['ID_PERMISO'] }}" name="fecha_modificacion" value="{{ \Carbon\Carbon::parse($permiso['FECHA_MODIFICACION'])->format('Y-m-d') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="modificado_por_{{ $permiso['ID_PERMISO'] }}">Modificado Por:</label>
                        <input type="text" class="form-control" id="modificado_por_{{ $permiso['ID_PERMISO'] }}" name="modificado_por" value="{{ $permiso['MODIFICADO_POR'] }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

                        <!-- Modal de eliminar permiso -->
<div class="modal fade" id="eliminarPermisoModal{{ $permiso['ID_PERMISO'] }}" tabindex="-1" role="dialog" aria-labelledby="eliminarPermisoModalLabel{{ $permiso['ID_PERMISO'] }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eliminarPermisoModalLabel{{ $permiso['ID_PERMISO'] }}">Eliminar Permiso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar este permiso?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <!-- Formulario para eliminar el permiso -->
                <form class="eliminar-permiso-form" data-id="{{ $permiso['ID_PERMISO'] }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
                    @endforeach
                </tbody>
            </table>
        </div>
       
    </div>
   
    <!-- Modal de nuevo permiso -->
    <div class="modal fade" id="nuevoPermisoModal" tabindex="-1" role="dialog" aria-labelledby="nuevoPermisoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nuevoPermisoModalLabel">Nuevo Permiso</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="nuevo-permiso-form">
                        @csrf
                        <div class="form-group">
                                           <label for="id_rol">Rol:</label>
                                          <select class="form-control" id="id_rol" name="id_rol" required>
                                         @foreach($roles as $rol)
                                        <option value="{{ $rol->ID_ROL }}"{{ $permiso['ID_ROL'] == $rol->ID_ROL ? 'selected' : '' }}>
                                       {{ $rol->ROL }}
                                           </option>
                                            @endforeach
                                         </select>
                                          </div>
                                          <div class="form-group">
                                           <label for="id_objeto">Objeto:</label>
                                          <select class="form-control" id="id_objeto" name="id_objeto" required>
                                           @foreach($objetos as $objeto)
                                        <option value="{{ $objeto->ID_OBJETO }}"{{ $permiso['ID_OBJETO'] == $objeto->ID_OBJETO ? 'selected' : '' }}>
                                        {{ $objeto->OBJETO }}
                                        </option>
                                        @endforeach
                                     </select>
                                      </div>

                        <div class="form-group">
                            <label for="permiso_insercion">Permiso Inserción:</label>
                            <input type="text" class="form-control" id="permiso_insercion" name="permiso_insercion" required>
                        </div>
                        <div class="form-group">
                            <label for="permiso_eliminacion">Permiso Eliminación:</label>
                            <input type="text" class="form-control" id="permiso_eliminacion" name="permiso_eliminacion" required>
                        </div>
                        <div class="form-group">
                            <label for="permiso_actualizacion">Permiso Actualización:</label>
                            <input type="text" class="form-control" id="permiso_actualizacion" name="permiso_actualizacion" required>
                        </div>
                        <div class="form-group">
                            <label for="permiso_consultar">Permiso Consultar:</label>
                            <input type="text" class="form-control" id="permiso_consultar" name="permiso_consultar" required>
                        </div>
                        <div class="form-group">
                            <label for="creado_por">Creado Por:</label>
                            <input type="text" class="form-control" id="creado_por" name="creado_por" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
 @else
            <div class="alert alert-danger">
                No tienes permisos para ver los parametros.
            </div>
        @endif
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css" rel="stylesheet">
    <style>
        .table-container {
            overflow-x: auto;
        }
        @media print {
            .table-container {
                overflow-x: visible;
            }
            table {
                width: 100% !important;
            }
            .card-tools {
                display: none;
            }
            .btn, .form-inline, .modal {
                display: none;
            }
        }
    </style>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        var table = $('#permisos-table').DataTable({
            "language": {
                "lengthMenu": "Mostrar _MENU_ registros por página",
                "zeroRecords": "No se encontraron resultados",
                "info": "Mostrando página _PAGE_ de _PAGES_",
                "infoEmpty": "No hay registros disponibles",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "search": "Buscar:",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "emptyTable": "No hay datos disponibles en la tabla",
                "infoThousands": ",",
                "decimal": ".",
                "thousands": ",",
                "aria": {
                    "sortAscending": ": activar para ordenar la columna de manera ascendente",
                    "sortDescending": ": activar para ordenar la columna de manera descendente"
                },
                "buttons": {
                    "print": "Imprimir"
                }
            },
            "buttons": [
                {
                    extend: 'print',
                    text: 'Imprimir',
                    exportOptions: {
                        columns: ':visible'
                    },
                    customize: function (win) {
                        $(win.document.body).find('table').css('width', '100%');
                    }
                }
            ]
        });
        table.on('search.dt', function() {
        var searchValue = table.search();  // Captura el valor de búsqueda actual
        console.log("Valor de búsqueda capturado: " + searchValue);  // Verifica el valor
        $('#searchInput').val(searchValue);  // Asigna el valor al campo oculto del formulario
    });

    // Verifica el valor antes de enviar el formulario
    $('#reporteForm').on('submit', function(e) {
        var searchValue = $('#searchInput').val();
        console.log("Valor enviado para el reporte: " + searchValue);  // Verifica el valor antes de enviar el formulario
    });

       
 // Validar más de 3 letras repetidas y doble espacio en el campo de descripción
 function validarInput(input) {
        // Evitar más de 3 letras consecutivas repetidas
        const repetidas = /(.)\1{2,}/g;
        if (repetidas.test(input.value)) {
            input.value = input.value.replace(repetidas, function(match) {
                return match.slice(0, 2); // Deja solo dos letras
            });
        }

        // Evitar doble espacio
        input.value = input.value.replace(/ {2,}/g, ' ');

    }

    // Aplicar validaciones en los campos de descripción y título
    const descripcionFields = document.querySelectorAll('textarea[name="descripcion"]');
    descripcionFields.forEach(function(input) {
        input.addEventListener('input', function() {
            validarInput(input);
        });
    });

    const tituloFields = document.querySelectorAll('input[name="modificado_por"],input[name="creado_por"],input[name="permiso_insercion"], input[name="permiso_eliminacion"], input[name="permiso_actualizacion"], input[name="permiso_consultar"]');
    tituloFields.forEach(function(input) {
        input.addEventListener('input', function() {
            validarInput(input);
        });
    });
    // Restricción para evitar caracteres especiales no deseados en el campo de EMAIL
const emailFields = document.querySelectorAll('input[name="permiso_insercion"], input[name="permiso_eliminacion"], input[name="permiso_actualizacion"], input[name="permiso_consultar"]');
        emailFields.forEach(function(email) {
            email.addEventListener('keypress', function(e) {
                const invalidChars = ['<', '>', '(', ')', '{', '}', '[', ']', '=', ';','+','-','/','%','$','#','!','@','^','&'];
                if (invalidChars.includes(e.key)) {
                    e.preventDefault();
                }
            });
        });

        // Función para convertir a mayúsculas
function convertirAMayusculas() {
        // Especificar los campos que deben ser convertidos a mayúsculas
        var fieldsToConvert = ['modificado_por', 'creado_por'];
        fieldsToConvert.forEach(function(fieldId) {
            var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
            inputs.forEach(function(input) {
                input.value = input.value.toUpperCase();
            });
        });
    }

    // Asignar evento input a los campos específicos
    ['modificado_por', 'creado_por'].forEach(function(fieldId) {
        document.querySelectorAll('input[id^="' + fieldId + '"]').forEach(function(input) {
            input.addEventListener('input', function() {
                input.value = input.value.toUpperCase();
            });
        });
    });
 
        // AJAX form submission for creating a bitácora de visita
        $('#nuevo-permiso-form').on('submit', function(event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: '{{ route("permisos.crear") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.success,
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseText,
                    });
                }
            });
        });

        // AJAX form submission for editing a bitácora de visita
        $('.editar-permiso-form').on('submit', function(event) {
            event.preventDefault();

            var permisoId = $(this).data('id');
            var formData = new FormData(this);

            $.ajax({
                url: '{{ route("permisos.actualizar", "") }}/' + permisoId,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.success,
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseText,
                    });
                }
            });
        });

        // AJAX form submission for deleting a bitácora de visita
        $('.eliminar-permiso-form').on('submit', function(event) {
            event.preventDefault();

            var permisoId = $(this).data('id');

            $.ajax({
                url: '{{ route("permisos.eliminar", "") }}/' + permisoId,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.success,
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseText,
                    });
                }
            });
        });
   
    document.getElementById('generarReporteBtn').addEventListener('click', function() {
    window.open('{{ route("permisos.reporte") }}', '_blank');
});
});
</script>
@endsection
