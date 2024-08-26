@extends('adminlte::page')

@section('title', 'Gestión de Roles')

@section('content_header')
    <h1>Gestión de Roles</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Roles</h3>
            <div class="card-tools">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#nuevoRolModal">Nuevo</button>
                    <form id="reporteForm" method="GET" action="{{ route('roles.reporte') }}" target="_blank">
                    <input type="hidden" name="rol" id="searchInput">
                    <button type="submit" class="btn btn-success mt-2">Generar Reporte</button>
                </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        @if($hasPermission)
            <div class="table-container">
                <table id="roles-table" class="table table-striped table-bordered shadow-lg mt-4" style="width: 100%">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>ID ROL</th>
                            <th>ROL</th>
                            <th>DESCRIPCION</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($Roles as $rol)
                            <tr>
                                <td>{{ $rol["ID_ROL"] }}</td>
                                <td>{{ $rol["ROL"] }}</td>
                                <td>{{ $rol["DESCRIPCION"] }}</td>
                                <td>
                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editarRolModal{{ $rol['ID_ROL'] }}">Editar</button>
                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#eliminarRolModal{{ $rol['ID_ROL'] }}">Eliminar</button>
                                </td>
                            </tr>

                        <!-- Modal de editar rol -->
                        <div class="modal fade" id="editarRolModal{{ $rol['ID_ROL'] }}" tabindex="-1" role="dialog" aria-labelledby="editarRolModalLabel{{ $rol['ID_ROL'] }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editarRolModalLabel{{ $rol['ID_ROL'] }}">Editar Rol</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form class="editar-rol-form" data-id="{{ $rol['ID_ROL'] }}">
                                            @csrf
                                            <div class="form-group">
                                                <label for="rol">Rol:</label>
                                                <input type="text" class="form-control" id="rol" name="rol" value="{{ $rol['ROL'] }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="descripcion">Descripción:</label>
                                                <textarea class="form-control" id="descripcion" name="descripcion" maxlength="70" required>{{ $rol['DESCRIPCION'] }}</textarea>
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

                        <!-- Modal de eliminar rol -->
                        <div class="modal fade" id="eliminarRolModal{{ $rol['ID_ROL'] }}" tabindex="-1" role="dialog" aria-labelledby="eliminarRolModalLabel{{ $rol['ID_ROL'] }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="eliminarRolModalLabel{{ $rol['ID_ROL'] }}">Eliminar Rol</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>¿Estás seguro de que deseas eliminar a "{{ $rol['ROL'] }}"?</p>
                                        <form class="eliminar-rol-form" data-id="{{ $rol['ID_ROL'] }}">
                                            @csrf
                                            <button type="submit" class="btn btn-danger">Eliminar</button>
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                        </form>
                                    </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div class="alert alert-danger">
                No tienes permisos para ver los roles.
            </div>
        @endif
    </div>
    <!-- Modal de nuevo rol -->
<div class="modal fade" id="nuevoRolModal" tabindex="-1" role="dialog" aria-labelledby="nuevoRolModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevoRolModalLabel">Nuevo </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulario para agregar nuevo rol -->
                <form id="nuevo-rol-form">
                    @csrf
                    <!-- Campos del formulario -->
                    <div class="form-group">
                        <label for="rol">Rol:</label>
                        <input type="text" class="form-control" id="rol" name="rol" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción:</label>
                        <textarea class="form-control" id="descripcion" name="descripcion"  maxlength="70" required></textarea>
                    </div>
                    <!-- Agrega los campos adicionales que necesites -->
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

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
        var table = $('#roles-table').DataTable({
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
// Restricción para evitar caracteres especiales no deseados en el campo de NOMBRE_USUARIO
const tituloFields = document.querySelectorAll('input[name="rol"]');
        tituloFields.forEach(function(input) {
            input.addEventListener('input', function(e) {
                const validChars = /^[a-zA-Z\s]*$/;
                if (!validChars.test(input.value)) {
                    input.value = input.value.replace(/[^A-Z\s]/g, '');
                }
            });
        });
        const descripcionFields = document.querySelectorAll('textarea[name="descripcion"]');
    descripcionFields.forEach(function(input) {
        input.addEventListener('input', function(e) {
            // Convertir texto a mayúsculas
            input.value = input.value.toUpperCase();

            // Limitar los caracteres permitidos
            const invalidChars = /[<>(){}[\]=;%^&*,"'+-:]/g;
            if (invalidChars.test(input.value)) {
                input.value = input.value.replace(invalidChars, '');
            }
        });
    });

    // Función para convertir a mayúsculas
 function convertirAMayusculas() {
        // Especificar los campos que deben ser convertidos a mayúsculas
        var fieldsToConvert = ['rol'];
        fieldsToConvert.forEach(function(fieldId) {
            var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
            inputs.forEach(function(input) {
                input.value = input.value.toUpperCase();
            });
        });
    }

    // Asignar evento input a los campos específicos
    ['rol'].forEach(function(fieldId) {
        document.querySelectorAll('input[id^="' + fieldId + '"]').forEach(function(input) {
            input.addEventListener('input', function() {
                input.value = input.value.toUpperCase();
            });
        });
    });

// Función para limitar el tamaño de los caracteres en ciertos campos
function limitarTamañoCaracteres() {
        // Especificar los campos y sus tamaños máximos
        var fieldsWithMaxLength = {
            'rol': 30            
        };

        Object.keys(fieldsWithMaxLength).forEach(function(fieldId) {
            var maxLength = fieldsWithMaxLength[fieldId];
            var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
            inputs.forEach(function(input) {
                input.addEventListener('input', function(e) {
                    if (input.value.length > maxLength) {
                        input.value = input.value.slice(0, maxLength);
                    }
                });
            });
        });
    }$(document).ready(function() {
    // Validación para evitar más de 3 veces la misma letra
    function validarRepeticiones(input) {
        const value = input.value;
        const regex = /(.)\1{2,}/g; // Busca cualquier carácter repetido 3 veces o más
        if (regex.test(value)) {
            input.value = value.replace(regex, '');
        }
    }

    // Validación para evitar dobles espacios
    function validarDobleEspacio(input) {
        const value = input.value;
        input.value = value.replace(/\s{2,}/g, ' '); // Reemplaza cualquier doble espacio por un solo espacio
    }

    // Asignar las validaciones a los campos correspondientes
    function aplicarValidaciones(input) {
        input.addEventListener('input', function() {
            validarRepeticiones(input);
            validarDobleEspacio(input);
        });
    }

    // Aplicar las validaciones a los campos de rol y descripción
    const rolFields = document.querySelectorAll('input[name="rol"]');
    rolFields.forEach(function(input) {
        aplicarValidaciones(input);
    });

    const descripcionFields = document.querySelectorAll('textarea[name="descripcion"]');
    descripcionFields.forEach(function(input) {
        aplicarValidaciones(input);
    });
});
    limitarTamañoCaracteres();

        // AJAX form submission for creating a role
        $('#nuevo-rol-form').on('submit', function(event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: '{{ route("roles.guardar") }}',
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

        // AJAX form submission for editing a role
        $('.editar-rol-form').on('submit', function(event) {
            event.preventDefault();

            var rolId = $(this).data('id');
            var formData = new FormData(this);

            $.ajax({
                url: '{{ route("roles.actualizar", ":id") }}'.replace(':id', rolId),
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

        // AJAX form submission for deleting a role
        $('.eliminar-rol-form').on('submit', function(event) {
            event.preventDefault();

            var rolId = $(this).data('id');

            $.ajax({
                url: '{{ route("roles.eliminar", ":id") }}'.replace(':id', rolId),
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
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
    window.open('{{ route("roles.reporte") }}', '_blank');
});
});
    
</script>
@stop
