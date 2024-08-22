@extends('adminlte::page')

@section('title', 'Gestión de Visitantes Recurrentes')

@section('content_header')
    <h1>Gestión de Visitantes Recurrentes</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Visitantes Recurrentes</h3>
            <div class="card-tools">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#nuevoVisitanteRecurrenteModal">Nuevo</button>
                    <form id="reporteForm" method="GET" action="{{ route('visitante-recurrente.reporte') }}" target="_blank">
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
    <table id="visitantes-recurrentes-table" class="table table-striped table-bordered shadow-lg mt-4" style="width: 100%">
        <thead class="bg-primary text-white">
            <tr>
                <th>ID</th>
                <th>PERSONA</th>
                <th>NOMBRE VISITANTE</th>
                <th>DNI VISITANTE</th>
                <th>NÚMERO DE PERSONAS</th>
                <th>NÚMERO DE PLACA</th>
                <th>FECHA Y HORA</th>
                <th>FECHA DE VENCIMIENTO</th>
                <th>ACCIONES</th>
            </tr>
        </thead>
        <tbody>
            @foreach($Recurrentes as $recurrente)
                <tr>
                    <td>{{ $recurrente['ID_VISITANTES_RECURRENTES'] }}</td>
                    <td>{{ $recurrente['PERSONA'] }}</td>
                    <td>{{ $recurrente['NOMBRE_VISITANTE'] }}</td>
                    <td>{{ $recurrente['DNI_VISITANTE'] }}</td>
                    <td>{{ $recurrente['NUM_PERSONAS'] }}</td>
                    <td>{{ $recurrente['NUM_PLACA'] }}</td>
                    <td>{{ $recurrente['FECHA_HORA'] ? \Carbon\Carbon::parse($recurrente['FECHA_HORA'])->format('Y-m-d') : '' }}</td>
                    <td>{{ $recurrente['FECHA_VENCIMIENTO'] ? \Carbon\Carbon::parse($recurrente['FECHA_VENCIMIENTO'])->format('Y-m-d') : '' }}</td>
                    <td>
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editarVisitanteRecurrenteModal{{ $recurrente['ID_VISITANTES_RECURRENTES'] }}">Editar</button>
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#eliminarVisitanteRecurrenteModal{{ $recurrente['ID_VISITANTES_RECURRENTES'] }}">Eliminar</button>
                    </td>
                </tr>

                <!-- Modal de editar visitante recurrente -->
                <div class="modal fade" id="editarVisitanteRecurrenteModal{{ $recurrente['ID_VISITANTES_RECURRENTES'] }}" tabindex="-1" role="dialog" aria-labelledby="editarVisitanteRecurrenteModalLabel{{ $recurrente['ID_VISITANTES_RECURRENTES'] }}" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editarVisitanteRecurrenteModalLabel{{ $recurrente['ID_VISITANTES_RECURRENTES'] }}">Editar Visitante Recurrente</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form class="editar-visitante-recurrente-form" data-id="{{ $recurrente['ID_VISITANTES_RECURRENTES'] }}">
                                    @csrf
                                    <div class="form-group">
                                        <label for="persona_descripcion">Nombre del Residente:</label>
                                        <input type="text" class="form-control" id="persona_descripcion" name="persona_descripcion" value="{{ $recurrente['PERSONA'] }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="nombre_visitante">Nombre Visitante:</label>
                                        <input type="text" class="form-control" id="nombre_visitante" name="nombre_visitante" value="{{ $recurrente['NOMBRE_VISITANTE'] }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="dni_visitante">DNI Visitante:</label>
                                        <input type="text" class="form-control" id="dni_visitante" name="dni_visitante" value="{{ $recurrente['DNI_VISITANTE'] }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="num_personas">Número de Personas:</label>
                                        <input type="text" class="form-control" id="num_personas" name="num_personas" value="{{ $recurrente['NUM_PERSONAS'] }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="num_placa">Número de Placa:</label>
                                        <input type="text" class="form-control" id="num_placa" name="num_placa" value="{{ $recurrente['NUM_PLACA'] }}" >
                                    </div>
                                    <div class="form-group">
                                        <label for="fecha_vencimiento">Fecha de Vencimiento:</label>
                                        <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" value="{{ $recurrente['FECHA_VENCIMIENTO'] }}" required>
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

               <!-- Modal de eliminar visitante recurrente -->
               <div class="modal fade" id="eliminarVisitanteRecurrenteModal{{ $recurrente['ID_VISITANTES_RECURRENTES'] }}" tabindex="-1" role="dialog" aria-labelledby="eliminarVisitanteRecurrenteModalLabel{{ $recurrente['ID_VISITANTES_RECURRENTES'] }}" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="eliminarVisitanteRecurrenteModalLabel{{ $recurrente['ID_VISITANTES_RECURRENTES'] }}">Eliminar Visitante Recurrente</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                            <p>¿Estás seguro de que deseas eliminar este visitante recurrente "{{ $recurrente['ID_PERSONA'] }}"?</p>
                           </div>
                          <div class="modal-footer">
                          <form class="eliminar-visitante-recurrente-form" data-id="{{ $recurrente['ID_VISITANTES_RECURRENTES'] }}" method="POST">
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
    </div>
    @else
            <div class="alert alert-danger">
                No tienes permisos para ver los visitantes recurrentes.
            </div>
        @endif
</div>

<!-- Modal de nuevo visitante recurrente -->
<div class="modal fade" id="nuevoVisitanteRecurrenteModal" tabindex="-1" role="dialog" aria-labelledby="nuevoVisitanteRecurrenteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevoVisitanteRecurrenteModalLabel">Agregar Nuevo Visitante Recurrente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="nuevo-visitante-recurrente-form">
                    @csrf
                    <div class="form-group">
                        <label for="persona_descripcion">Nombre del Residente:</label>
                        <input type="text" class="form-control" id="persona_descripcion" name="persona_descripcion" required>
                    </div>
                    <div class="form-group">
                        <label for="nombre_visitante">Nombre Visitante:</label>
                        <input type="text" class="form-control" id="nombre_visitante" name="nombre_visitante" required>
                    </div>
                    <div class="form-group">
                        <label for="dni_visitante">DNI Visitante:</label>
                        <input type="text" class="form-control" id="dni_visitante" name="dni_visitante" required>
                    </div>
                    <div class="form-group">
                        <label for="num_personas">Número de Personas:</label>
                        <input type="text" class="form-control" id="num_personas" name="num_personas" required>
                    </div>
                    <div class="form-group">
                        <label for="num_placa">Número de Placa (Opcional):</label>
                        <input type="text" class="form-control" id="num_placa" name="num_placa" >
                    </div>
                    <div class="form-group">
                        <label for="fecha_vencimiento">Fecha de Vencimiento:</label>
                        <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" required>
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
        var table = $('#visitantes-recurrentes-table').DataTable({
            "language": {
                "lengthMenu": "Mostrar MENU registros por página",
                "zeroRecords": "No se encontraron resultados",
                "info": "Mostrando página PAGE de PAGES",
                "infoEmpty": "No hay registros disponibles",
                "infoFiltered": "(filtrado de MAX registros totales)",
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
        var fieldsToConvert = ['nombre_visitante','num_placa','persona_descripcion'];
        fieldsToConvert.forEach(function(fieldId) {
            var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
            inputs.forEach(function(input) {
                input.value = input.value.toUpperCase();
            });
        });
    }

    // Asignar evento input a los campos específicos
    ['nombre_visitante','persona_descripcion','num_placa'].forEach(function(fieldId) {
        document.querySelectorAll('input[id^="' + fieldId + '"]').forEach(function(input) {
            input.addEventListener('input', function() {
                input.value = input.value.toUpperCase();
            });
        });
    });
 

    
 // Función para restringir caracteres especiales
 function restringirCaracteres() {
        // Especificar los campos que deben ser restringidos
        var fieldsToRestrict = ['nombre_visitante','persona_descripcion'];
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

    function restringirCaracter() {
    // Especificar los campos que deben ser restringidos
    var fieldsToRestrict = ['num_placa'];
    fieldsToRestrict.forEach(function(fieldId) {
        var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
        inputs.forEach(function(input) {
            input.addEventListener('input', function(e) {
                const validChars = /^[a-zA-Z0-9\s]*$/; // Permite letras, números y espacios
                if (!validChars.test(input.value)) {
                    input.value = input.value.replace(/[^a-zA-Z0-9\s]/g, '');
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
            'num_personas': 10,
            'num_placa': 70
        
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

    const tituloFields = document.querySelectorAll('input[name="persona_descripcion"], input[name="nombre_visitante"], input[name="dni_visitante"], input[name="num_personas"], input[name="num_placa"], input[name="fecha_vencimiento"]');
    tituloFields.forEach(function(input) {
        input.addEventListener('input', function() {
            validarInput(input);
        });
    });
    }


    // Asignar evento input a los campos específicos
    restringirCaracteres();
    restringirCaracter();
    limitarTamañoCaracteres();
    permitirSoloNumerosPositivos();


            // AJAX form submission for creating a visitantes recurrentes
            $('#nuevo-visitante-recurrente-form').on('submit', function(event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: '{{ route("visitante-recurrente.crear") }}',
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

            // AJAX form submission for editing a visitantes recurrentes
            $('.editar-visitante-recurrente-form').on('submit', function(event) {
            event.preventDefault();

            var recurrenteId = $(this).data('id');
            var formData = new FormData(this);

            $.ajax({
                url: '{{ route("visitante-recurrente.actualizar", "") }}/' + recurrenteId,
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
       
            // AJAX form submission for deleting a visitantes recurrentes
           $('.eliminar-visitante-recurrente-form').on('submit', function(event) {
            event.preventDefault();

            var recurrenteId = $(this).data('id');

            $.ajax({
                url: '{{ route("visitante-recurrente.eliminar", "") }}/' + recurrenteId,
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
    window.open('{{ route("visitante-recurrente.reporte") }}', '_blank');
});
});
   </script>
@stop
