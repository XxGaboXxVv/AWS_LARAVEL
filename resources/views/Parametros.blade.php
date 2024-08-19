@extends('adminlte::page')

@section('title', 'Gestión de Parámetros')

@section('content_header')
    <h1>Gestión de Parámetros</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Parámetros</h3>
            <div class="card-tools">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#nuevoParametroModal">Nuevo</button>
                    <form id="reporteForm" method="GET" action="{{ route('parametros.reporte') }}" target="_blank">
                    <input type="hidden" name="parametro" id="searchInput">
                    <button type="submit" class="btn btn-success mt-2">Generar Reporte</button>
                </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
    @if($hasPermission)
        <div class="table-container">
            <table id="parametros-table" class="table table-striped table-bordered shadow-lg mt-4" style="width: 100%">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>ID PARAMETRO</th>
                        <th>USUARIO</th>
                        <th>PARAMETRO</th>
                        <th>VALOR</th>
                        <th>FECHA DE CREACION</th>
                        <th>FECHA DE MODIFICACION</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($parametros as $parametro)
                        <tr>
                            <td>{{ $parametro['ID_PARAMETRO'] }}</td>
                            <td>{{ $parametro['NOMBRE_USUARIO'] }}</td>
                            <td>{{ $parametro['PARAMETRO'] }}</td>
                            <td>{{ $parametro['VALOR'] }}</td>
                            <td>{{ $parametro['FECHA_CREACION'] ? \Carbon\Carbon::parse($parametro['FECHA_CREACION'])->setTimezone('America/Tegucigalpa')->format('Y-m-d ') : '' }}</td>
                            <td>{{ $parametro['FECHA_MODIFICACION'] ? \Carbon\Carbon::parse($parametro['FECHA_MODIFICACION'])->setTimezone('America/Tegucigalpa')->format('Y-m-d ') : '' }}</td>
                            <td>
                                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editarParametroModal{{ $parametro['ID_PARAMETRO'] }}">Editar</button>
                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#eliminarParametroModal{{ $parametro['ID_PARAMETRO'] }}">Eliminar</button>
                            </td>
                        </tr>

                       <!-- Modal de editar parámetro -->
<div class="modal fade" id="editarParametroModal{{ $parametro['ID_PARAMETRO'] }}" tabindex="-1" role="dialog" aria-labelledby="editarParametroModalLabel{{ $parametro['ID_PARAMETRO'] }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarParametroModalLabel{{ $parametro['ID_PARAMETRO'] }}">Editar Parámetro</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="editar-parametro-form" data-id="{{ $parametro['ID_PARAMETRO'] }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="id_usuario">Usuario:</label>
                        <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" value="{{ $parametro['NOMBRE_USUARIO'] }}" required>
                    </div>
                    <div class="form-group">
                        <label for="parametro">Parámetro:</label>
                        <input type="text" class="form-control" id="parametro" name="parametro" value="{{ $parametro['PARAMETRO'] }}" required>
                    </div>
                    <div class="form-group">
                        <label for="valor">Valor:</label>
                        @if($parametro['ID_PARAMETRO'] == 5)
                            <input type="file" class="form-control" id="valor" name="valor_imagen">
                        @else
                            <input type="text" class="form-control" id="valor" name="valor" value="{{ $parametro['VALOR'] }}" required>
                        @endif
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

                        <!-- Modal de eliminar parámetro -->
                        <div class="modal fade" id="eliminarParametroModal{{ $parametro['ID_PARAMETRO'] }}" tabindex="-1" role="dialog" aria-labelledby="eliminarParametroModalLabel{{ $parametro['ID_PARAMETRO'] }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="eliminarParametroModalLabel{{ $parametro['ID_PARAMETRO'] }}">Eliminar Parámetro</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>¿Estás seguro de que deseas eliminar este parámetro?</p>
                                        <form class="eliminar-parametro-form" data-id="{{ $parametro['ID_PARAMETRO'] }}">
                                            @csrf
                                            <button type="submit" class="btn btn-danger">Eliminar</button>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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
                No tienes permisos para ver los parametros.
            </div>
        @endif
    </div>

    <!-- Modal de nuevo parámetro -->
    <div class="modal fade" id="nuevoParametroModal" tabindex="-1" role="dialog" aria-labelledby="nuevoParametroModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nuevoParametroModalLabel">Agregar Nuevo Parámetro</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Formulario para agregar nuevo parámetro -->
                    <form id="nuevo-parametro-form">
                        @csrf
                        <div class="form-group">
                            <label for="id_usuario">Usuario:</label>
                            <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" required>
                        </div>
                        <div class="form-group">
                            <label for="parametro">Parámetro:</label>
                            <input type="text" class="form-control" id="parametro" name="parametro" required>
                        </div>
                        <div class="form-group">
                            <label for="valor">Valor:</label>
                            <input type="text" class="form-control" id="valor" name="valor" required>
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
        var table = $('#parametros-table').DataTable({
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

    const tituloFields = document.querySelectorAll('input[name="nombre_usuario"], input[name="parametro"],input[name="valor"]');
    tituloFields.forEach(function(input) {
        input.addEventListener('input', function() {
            validarInput(input);
        });
    });

     // Función para convertir a mayúsculas
 function convertirAMayusculas() {
        // Especificar los campos que deben ser convertidos a mayúsculas
        var fieldsToConvert = ['nombre_usuario','parametro'];
        fieldsToConvert.forEach(function(fieldId) {
            var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
            inputs.forEach(function(input) {
                input.value = input.value.toUpperCase();
            });
        });
    }

    // Asignar evento input a los campos específicos
    ['nombre_usuario','parametro','valor'].forEach(function(fieldId) {
        document.querySelectorAll('input[id^="' + fieldId + '"]').forEach(function(input) {
            input.addEventListener('input', function() {
                input.value = input.value.toUpperCase();
            });
        });
    });
 

    
 // Función para restringir caracteres especiales
 function restringirCaracteres() {
        // Especificar los campos que deben ser restringidos
        var fieldsToRestrict = ['nombre_usuario'];
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
 // Restricción para evitar caracteres especiales no deseados en el campo de EMAIL
 const emailFields = document.querySelectorAll('input[name="parametro"]');
        emailFields.forEach(function(email) {
            email.addEventListener('keypress', function(e) {
                const invalidChars = ['<', '>', '(', ')', '{', '}', '[', ']', '=', ';','+','-','/','%'];
                if (invalidChars.includes(e.key)) {
                    e.preventDefault();
                }
            });
        });

    // Función para restringir la entrada a solo números positivos
function permitirSoloNumerosPositivos() {
    // Especificar los campos que deben ser restringidos
    var fieldsToRestrict = ['valor'];
    
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
            'nombre_usuario': 30,
            'parametro':30,
            'valor': 30
            
            
        
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
    
   // AJAX form submission for creating a tipo de persona
   $('#nuevo-parametro-form').on('submit', function(event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: '{{ route("parametros.store") }}',
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

        // AJAX form submission for updating a tipo de persona
      $('.editar-parametro-form').on('submit', function(event) {
            event.preventDefault();

            var rolId = $(this).data('id');
            var formData = new FormData(this);

            $.ajax({
                url: '{{ route("parametros.update", ":id") }}'.replace(':id', rolId),
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
        // AJAX form submission for deleting a tipo de persona
        $('.eliminar-parametro-form').on('submit', function(event) {
            event.preventDefault();

            var id = $(this).data('id');

            $.ajax({
                url: '{{ route("parametros.destroy", ":id") }}'.replace(':id', id),
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
    window.open('{{ route("parametros.reporte") }}', '_blank');
});

});
</script>
@endsection
