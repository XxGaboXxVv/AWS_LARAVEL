@extends('adminlte::page')

@section('title', 'Gestión de Instalaciones')

@section('content_header')
    <h1>Gestión de Instalaciones</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Instalaciones</h3>
            <div class="card-tools">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#nuevaInstalacionModal">Nuevo</button>
                    <form id="reporteForm" method="GET" action="{{ route('Instalaciones.reporte') }}" target="_blank">
                    <input type="hidden" name="nombre_instalacion" id="searchInput">
                    <button type="submit" class="btn btn-success mt-2">Generar Reporte</button>
                </form>
                </div>
            </div>
        </div>
        <div class="card-body">
        @if($hasPermission)
            <div class="table-container">
                <table id="instalaciones-table" class="table table-striped table-bordered shadow-lg mt-4" style="width: 100%">
                    <thead class="bg-primary text-white">
                        <tr>
                        <th>ID DE LA INSTALACION</th>
                        <th>NOMBRE DE LA  INSTALACION</th>
                        <th>CAPACIDAD</th>
                        <th>PRECIO</th>
                        <th>DESCRIPCION</th>
                        <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($instalaciones as $instalacion)
                            <tr>
                                <td>{{ $instalacion["ID_INSTALACION"] }}</td>
                                <td>{{ $instalacion["NOMBRE_INSTALACION"] }}</td>
                                <td>{{ $instalacion["CAPACIDAD"] }}</td>
                                <td>{{ $instalacion["PRECIO"] }}</td>
                                <td>{{ $instalacion["DESCRIPCION"] }}</td>
                                <td>
                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editarInstalacionModal{{ $instalacion['ID_INSTALACION'] }}">Editar</button>
                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#eliminarInstalacionModal{{ $instalacion['ID_INSTALACION'] }}">Eliminar</button>
                                </td>
                            </tr>

                            <!-- Modal de editar instalación -->
                            <div class="modal fade" id="editarInstalacionModal{{ $instalacion['ID_INSTALACION'] }}" tabindex="-1" role="dialog" aria-labelledby="editarInstalacionModalLabel{{ $instalacion['ID_INSTALACION'] }}" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editarInstalacionModalLabel{{ $instalacion['ID_INSTALACION'] }}">Editar Instalación</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form class="editar-instalacion-form" data-id="{{ $instalacion['ID_INSTALACION'] }}">
                                                @csrf
                                                <div class="form-group">
                                                    <label for="nombre_instalacion">Nombre:</label>
                                                    <input type="text" class="form-control" id="nombre_instalacion" name="nombre_instalacion" value="{{ $instalacion['NOMBRE_INSTALACION'] }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="capacidad">Capacidad:</label>
                                                    <input type="number" class="form-control" id="capacidad" name="capacidad" value="{{ $instalacion['CAPACIDAD'] }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="precio">Precio:</label>
                                                    <input type="number" class="form-control" id="precio" name="precio" value="{{ $instalacion['PRECIO'] }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="descripcion">Descripción:</label>
                                                    <input type="text" class="form-control" id="descripcion" name="descripcion" value="{{ $instalacion['DESCRIPCION'] }}" required>
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

                            <!-- Modal de eliminar instalación -->
                            <div class="modal fade" id="eliminarInstalacionModal{{ $instalacion['ID_INSTALACION'] }}" tabindex="-1" role="dialog" aria-labelledby="eliminarInstalacionModalLabel{{ $instalacion['ID_INSTALACION'] }}" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="eliminarInstalacionModalLabel{{ $instalacion['ID_INSTALACION'] }}">Eliminar Instalación</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p>¿Estás seguro de que deseas eliminar la instalación "{{ $instalacion['NOMBRE_INSTALACION'] }}"?</p>
                                            <form class="eliminar-instalacion-form" data-id="{{ $instalacion['ID_INSTALACION'] }}">
                                                @csrf
                                                <button type="submit" class="btn btn-danger">Eliminar</button>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            </form>
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
                No tienes permisos para ver las Instalaciones.
            </div>
        @endif
    </div>

    <!-- Modal de nueva instalación -->
    <div class="modal fade" id="nuevaInstalacionModal" tabindex="-1" role="dialog" aria-labelledby="nuevaInstalacionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nuevaInstalacionModalLabel">Nueva Instalación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="nueva-instalacion-form">
                        @csrf
                        <div class="form-group">
                            <label for="nombre_instalacion">Nombre:</label>
                            <input type="text" class="form-control" id="nombre_instalacion" name="nombre_instalacion" required>
                        </div>
                        <div class="form-group">
                            <label for="capacidad">Capacidad:</label>
                            <input type="number" class="form-control" id="capacidad" name="capacidad" required>
                        </div>
                        <div class="form-group">
                            <label for="precio">Precio:</label>
                            <input type="number" class="form-control" id="precio" name="precio" required>
                        </div>
                        <div class="form-group">
                            <label for="descripcion">Descripción:</label>
                            <input type="text" class="form-control" id="descripcion" name="descripcion" required>
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
        var table = $('#instalaciones-table').DataTable({
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

         // Captura y manejo de búsqueda en DataTables
         table.on('search.dt', function() {
            var searchValue = table.search();
            $('#searchInput').val(searchValue); // Asigna el valor al campo oculto del formulario
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

    const tituloFields = document.querySelectorAll('input[name="descripcion"], input[name="nombre_instalacion"], input[name="capacidad"]');
    tituloFields.forEach(function(input) {
        input.addEventListener('input', function() {
            validarInput(input);
        });
    });
    // Función para convertir a mayúsculas
 function convertirAMayusculas() {
        // Especificar los campos que deben ser convertidos a mayúsculas
        var fieldsToConvert = ['nombre_instalacion', 'capacidad', 'precio','descripcion'];
        fieldsToConvert.forEach(function(fieldId) {
            var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
            inputs.forEach(function(input) {
                input.value = input.value.toUpperCase();
            });
        });
    }

    // Asignar evento input a los campos específicos
    ['nombre_instalacion','capacidad','precio','descripcion'].forEach(function(fieldId) {
        document.querySelectorAll('input[id^="' + fieldId + '"]').forEach(function(input) {
            input.addEventListener('input', function() {
                input.value = input.value.toUpperCase();
            });
        });
    });
 

    
 // Función para restringir caracteres especiales
 function restringirCaracteres() {
        // Especificar los campos que deben ser restringidos
        var fieldsToRestrict = ['nombre_instalacion','descripcion'];
        fieldsToRestrict.forEach(function(fieldId) {
            var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
            inputs.forEach(function(input) {
                input.addEventListener('input', function(e) {
                    const validChars = /^[a-zA-Z\s]*$/;
                    if (!validChars.test(input.value)) {
                        input.value = input.value.replace(/[^a-zA-Z\s]/g, '');
                    }
                });
            });
        });
    }


    // Función para restringir la entrada a solo números positivos
function permitirSoloNumerosPositivos() {
    // Especificar los campos que deben ser restringidos
    var fieldsToRestrict = ['capacidad', 'precio'];
    
    fieldsToRestrict.forEach(function(fieldId) {
        var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
        
        inputs.forEach(function(input) {
            input.addEventListener('input', function(e) {
                // Remover cualquier carácter que no sea un número
                input.value = input.value.replace(/[^0-9]/g, '');
            });
        });
    });
}

    // Función para limitar el tamaño de los caracteres en ciertos campos
    function limitarTamañoCaracteres() {
        // Especificar los campos y sus tamaños máximos
        var fieldsWithMaxLength = {
            'nombre_instalacion': 20,
            'capacidad':20,
            'precio': 30,
            'descripcion': 30
            
        
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
    }


    // Asignar evento input a los campos específicos
    restringirCaracteres();
    limitarTamañoCaracteres();
    permitirSoloNumerosPositivos();
    
     // AJAX form submission for creating a Instalaciones
     $('#nueva-instalacion-form').on('submit', function(event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: '{{ route("Instalaciones.crear") }}',
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
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al procesar la solicitud.',
                    });
                }
            });
        });

        // AJAX form submission for updating a Instalaciones
        $('.editar-instalacion-form').on('submit', function(event) {
            event.preventDefault();

            var id = $(this).data('id');
            var formData = new FormData(this);

            $.ajax({
                url: '{{ route("Instalaciones.actualizar", ":id") }}'.replace(':id', id),
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
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al procesar la solicitud.',
                    });
                }
            });
        });

        // AJAX form submission for deleting a Instalaciones
        $('.eliminar-instalacion-form').on('submit', function(event) {
            event.preventDefault();

            var id = $(this).data('id');

            $.ajax({
                url: '{{ route("Instalaciones.eliminar", ":id") }}'.replace(':id', id),
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
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al procesar la solicitud.',
                    });
                }
            });
        });
   
    document.getElementById('generarReporteBtn').addEventListener('click', function() {
    window.open('{{ route("Instalaciones.reporte") }}', '_blank');
});
});

</script>
@stop
