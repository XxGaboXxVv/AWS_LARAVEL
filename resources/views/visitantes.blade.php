@extends('adminlte::page')

@section('title', 'Visitantes')

@section('content_header')
    <h1>Gestión de Visitantes</h1>
@stop

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Visitantes</h3>
            <div class="card-tools">
            <div class="d-flex justify-content-end">
                <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#modalAgregarVisitante">Nuevo</button>
                <form id="reporteForm" method="GET" action="{{ route('visitantes.reporte') }}" target="_blank">
                    <input type="hidden" name="persona_descripcion" id="searchInput">
                    <button type="submit" class="btn btn-success mt-2">Generar Reporte</button>
                </form>
            </div>
        </div>
    </div>
</div>
       
                </div>
            </div>
            </form>
            @if($hasPermission)
        <div class="table-container">
            <table id="visitantes" class="table table-striped table-bordered shadow-lg mt-4" style="width: 100%">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>ID VISITANTE</th>
                        <th>NOMBRE DEL RESIDENTE</th>
                        <th>NOMBRE VISITANTE</th>
                        <th>DNI VISITANTE</th>
                        <th>NÚMERO DE PERSONAS</th>
                        <th>NÚMERO DE PLACA</th>
                        <th>FECHA Y HORA</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($visitantes as $regvisita)
                        <tr>
                            <td>{{ $regvisita["ID_VISITANTE"] }}</td>
                            <td>{{ $regvisita["PERSONA"] }}</td>
                            <td>{{ $regvisita["NOMBRE_VISITANTE"] }}</td>
                            <td>{{ $regvisita["DNI_VISITANTE"] }}</td>
                            <td>{{ $regvisita["NUM_PERSONAS"] }}</td>
                            <td>{{ $regvisita["NUM_PLACA"] ?? 'N/A' }}</td>
                            <td>{{ $regvisita["FECHA_HORA"] }}</td>
                            <td>
                                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editarVisitante{{ $regvisita['ID_VISITANTE'] }}">Editar</button>
                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#eliminarVisitante{{ $regvisita['ID_VISITANTE'] }}">Eliminar</button>
                            </td>
                        </tr>
                        
                        <!-- Modal de edición de visitante -->
                        <div class="modal fade" id="editarVisitante{{ $regvisita['ID_VISITANTE'] }}" tabindex="-1" role="dialog" aria-labelledby="editarVisitante{{ $regvisita['ID_VISITANTE'] }}Label" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editarVisitante{{ $regvisita['ID_VISITANTE'] }}Label">Editar Visitante</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Formulario de edición de visitante --> 
                                        <form class="editar-visitante-form" data-id="{{ $regvisita['ID_VISITANTE'] }}" method="POST">
                                            @csrf
                                            <div class="form-group">
                                            <label for="persona_descripcion">Nombre del Residente:</label>
                                            <input type="text" class="form-control" id="persona_descripcion" name="persona_descripcion" value="{{ $regvisita['PERSONA'] }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="nombre_visitante">Nombre del Visitante:</label>
                                                <input type="text" class="form-control" id="nombre_visitante" name="nombre_visitante" value="{{ $regvisita['NOMBRE_VISITANTE'] }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="dni_visitante">DNI del Visitante:</label>
                                                <input type="text" class="form-control" id="dni_visitante" name="dni_visitante" value="{{ $regvisita['DNI_VISITANTE'] }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="num_personas">Número de Personas:</label>
                                                <input type="number" class="form-control" id="num_personas" name="num_personas" value="{{ $regvisita['NUM_PERSONAS'] }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="num_placa">Número de Placa (Opcional):</label>
                                                <input type="text" class="form-control" id="num_placa" name="num_placa" value="{{ $regvisita['NUM_PLACA'] }}">
                                            </div>
                                    
                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal de eliminación de visitante -->
                        <div class="modal fade" id="eliminarVisitante{{ $regvisita['ID_VISITANTE'] }}" tabindex="-1" role="dialog" aria-labelledby="eliminarVisitante{{ $regvisita['ID_VISITANTE'] }}Label" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="eliminarVisitante{{ $regvisita['ID_VISITANTE'] }}Label">Eliminar Visitante</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>¿Estás seguro de que deseas eliminar al visitante ?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <form class="eliminar-visitante-form" data-id="{{ $regvisita['ID_VISITANTE'] }}" method="POST">
                                            @csrf
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
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
        @else
            <div class="alert alert-danger">
                No tienes permisos para ver los visitantes.
            </div>
        @endif
</div>

<!-- Modal para agregar visitante -->
<div class="modal fade" id="modalAgregarVisitante" tabindex="-1" role="dialog" aria-labelledby="modalAgregarVisitanteLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgregarVisitanteLabel">Agregar Visitante</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulario para agregar visitante -->
                <form id="nuevo-visitante-form" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="persona_descripcion">Nombre del Residente:</label>
                        <input type="text" class="form-control" id="persona_descripcion" name="persona_descripcion" required>
                    </div>
                    <div class="form-group">
                        <label for="nombre_visitante">Nombre del Visitante:</label>
                        <input type="text" class="form-control" id="nombre_visitante" name="nombre_visitante" required>
                    </div>
                    <div class="form-group">
                        <label for="dni_visitante">DNI del Visitante:</label>
                        <input type="text" class="form-control" id="dni_visitante" name="dni_visitante" required>
                    </div>
                    <div class="form-group">
                        <label for="num_personas">Número de Personas:</label>
                        <input type="number" class="form-control" id="num_personas" name="num_personas" required>
                    </div>
                    <div class="form-group">
                        <label for="num_placa">Número de Placa (Opcional):</label>
                        <input type="text" class="form-control" id="num_placa" name="num_placa">
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Visitante</button>
                </form>
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
        var table = $('#visitantes').DataTable({
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

    // Función para convertir a mayúsculas
 function convertirAMayusculas() {
        // Especificar los campos que deben ser convertidos a mayúsculas
        var fieldsToConvert = ['id_persona', 'nombre_visitante', 'dni_visitante','num_personas','num_placa','persona_descripcion'];
        fieldsToConvert.forEach(function(fieldId) {
            var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
            inputs.forEach(function(input) {
                input.value = input.value.toUpperCase();
            });
        });
    }

    // Asignar evento input a los campos específicos
    ['id_persona', 'nombre_visitante','persona_descripcion'].forEach(function(fieldId) {
        document.querySelectorAll('input[id^="' + fieldId + '"]').forEach(function(input) {
            input.addEventListener('input', function() {
                input.value = input.value.toUpperCase();
            });
        });
    });
 
// Restricción para evitar caracteres especiales no deseados en el campo de EMAIL
const emailFields = document.querySelectorAll('input[name="num_placa"]');
        emailFields.forEach(function(email) {
            email.addEventListener('keypress', function(e) {
                const invalidChars = ['<', '>', '(', ')', '{', '}', '[', ']', '=', ';','+','-','/','%'];
                if (invalidChars.includes(e.key)) {
                    e.preventDefault();
                }
            });
        });

    
 // Función para restringir caracteres especiales
 function restringirCaracteres() {
        // Especificar los campos que deben ser restringidos
        var fieldsToRestrict = ['id_persona', 'nombre_visitante','persona_descripcion'];
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
    var fieldsToRestrict = ['dni_visitante', 'num_personas'];
    
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
            'persona_descripcion': 70,
            'nombre_visitante':70,
            'dni_visitante': 30,
            'num_personas': 30,
            'num_placa': 30
        
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

    const tituloFields = document.querySelectorAll('input[name="nombre_visitante"], input[name="dni_visitante"], input[name="num_placa"], input[name="persona_descripcion"], input[name="nombre_visitante"], input[name="num_personas"], input[name="num_placa"]');
    tituloFields.forEach(function(input) {
        input.addEventListener('input', function() {
            validarInput(input);
        });
    });

    // Asignar evento input a los campos específicos
    restringirCaracteres();
    limitarTamañoCaracteres();
    permitirSoloNumerosPositivos();

        // AJAX form submission for creating a visitante
        $('#nuevo-visitante-form').on('submit', function(event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: '{{ route("visitantes.guardar") }}',
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

        // AJAX form submission for editing a visitante
        $('.editar-visitante-form').on('submit', function(event) {
            event.preventDefault();

            var visitanteId = $(this).data('id');
            var formData = new FormData(this);

            $.ajax({
                url: '{{ route("visitantes.actualizar", "") }}/' + visitanteId,
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

        // AJAX form submission for deleting a visitante
        $('.eliminar-visitante-form').on('submit', function(event) {
            event.preventDefault();

            var visitanteId = $(this).data('id');

            $.ajax({
                url: '{{ route("visitantes.eliminar", "") }}/' + visitanteId,
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
    window.open('{{ route("visitantes.reporte") }}', '_blank');
});
});

</script>
@endsection