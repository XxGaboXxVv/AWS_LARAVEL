@extends('adminlte::page')

@section('title', 'Reservaciones')

@section('content_header')
    <h1>Gestión de Reservaciones</h1>
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
        <h3 class="card-title">Listado de Reservaciones</h3>
        <div class="card-tools">
            <div class="d-flex justify-content-end">
                <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#modalAgregarReservacion">Nuevo</button>
                <form id="reporteForm" method="GET" action="{{ route('reservaciones.reporte') }}" target="_blank">
                    <input type="hidden" name="nombre" id="searchInput">
                    <button type="submit" class="btn btn-success mt-2">Generar Reporte</button>
                </form>
            </div>
        </div>
    </div>
     @if($hasPermission)
    <div class="table-container">
        <table id="reservaciones" class="table table-striped table-bordered shadow-lg mt-4" style="width: 100%">
            <thead class="bg-primary text-white">
                <tr>
                    <th>ID RESERVA</th>
                    <th>RESIDENTE</th>
                    <th>INSTALACIÓN</th>
                    <th>ESTADO RESERVA</th>
                    <th>TIPO EVENTO</th>
                    <th>FECHA Y HORA</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
               
        </table>
    </div>
    @else
            <div class="alert alert-danger">
                No tienes permisos para ver las reservaciones.
            </div>
        @endif
</div>


<!-- Modal para agregar reservación -->
<div class="modal fade" id="modalAgregarReservacion" tabindex="-1" role="dialog" aria-labelledby="modalAgregarReservacionLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgregarReservacionLabel">Agregar Reservación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulario para agregar reservación -->
                <form id="nueva-reservacion-form" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="persona_descripcion">Nombre del Residente:</label>
                        <input type="text" class="form-control" id="persona_descripcion" name="persona_descripcion" required>
                    </div>
                    <div class="form-group">
                        <label for="id_instalacion">Instalación:</label>
                        <select class="form-control" id="id_instalacion" name="id_instalacion" required>
                            @foreach($instalaciones as $instalacion)
                                <option value="{{ $instalacion->ID_INSTALACION }}">{{ $instalacion->NOMBRE_INSTALACION }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_estado_reserva">Estado de la Reserva:</label>
                        <select class="form-control" id="id_estado_reserva" name="id_estado_reserva" required>
                            @foreach($estadoreservas as $estado)
                                <option value="{{ $estado->ID_ESTADO_RESERVA }}">{{ $estado->DESCRIPCION }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tipo_evento">Tipo de Evento:</label>
                        <input type="text" class="form-control" id="tipo_evento" name="tipo_evento" required>
                    </div>
                    <div class="form-group">
                        <label for="hora_fecha">Fecha y Hora de la Reservacion</label>
                        <input type="text" class="form-control" id="hora_fecha" name="hora_fecha" placeholder="Ejemplo de formato 2024-08-26 11:23:20" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar Reservación</button>
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
        // Inicialización de DataTables
        var table = $('#reservaciones').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
            "url": "{{ route('Reservaciones.fetch') }}",
            "type": "GET"
        },
        "columns": [
            { "data": "ID_RESERVA" },
            { "data": "PERSONA" },
            { "data": "INSTALACION" },
            { "data": "ESTADO_RESERVA" },
            { "data": "TIPO_EVENTO" },
            { "data": "HORA_FECHA" },
            {
                "data": null,
                "orderable": false,
                "searchable": false,
                
         "render": function(data, type, row) {
                    return `
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editarReservacion${row.ID_RESERVA}">Editar</button>
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#eliminarReservacion${row.ID_RESERVA}">Eliminar</button>
                        
 <!-- Modal de edición de reservación -->
        <div class="modal fade" id="editarReservacion${row.ID_RESERVA}" tabindex="-1" role="dialog" aria-labelledby="editarReservacion${row.ID_RESERVA}Label" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarReservacion${row.ID_RESERVA}Label">Editar Reservación</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Formulario de edición de reservación -->
                        <form class="editar-reservacion-form" data-id="${row.ID_RESERVA}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="persona_descripcion">Nombre del Residente:</label>
                                <input type="text" class="form-control" id="persona_descripcion" name="persona_descripcion" value="${row.PERSONA}" required>
                            </div>
                            <div class="form-group">
                                <label for="id_instalacion">Instalación:</label>
                                <select class="form-control" id="id_instalacion" name="id_instalacion" required>
                                    @foreach($instalaciones as $instalacion)
                                        <option value="{{ $instalacion->ID_INSTALACION }}"${row.ID_INSTALACION == {{ $instalacion->ID_INSTALACION }} ? 'selected' : ''}>
                                            {{ $instalacion->NOMBRE_INSTALACION }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="id_estado_reserva">Estado de la Reserva:</label>
                                <select class="form-control" id="id_estado_reserva" name="id_estado_reserva" required>
                                    @foreach($estadoreservas as $estado)
                                        <option value="{{ $estado->ID_ESTADO_RESERVA }}"${row.ID_ESTADO_RESERVA == {{ $estado->ID_ESTADO_RESERVA }} ? 'selected' : ''}>
                                            {{ $estado->DESCRIPCION }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="tipo_evento">Tipo de Evento:</label>
                                <input type="text" class="form-control" id="tipo_evento" name="tipo_evento" value="${row.TIPO_EVENTO}" required>
                            </div>
                            <div class="form-group">
                                <label for="hora_fecha">Fecha y Hora de la Reserva:</label>
                                <input type="text" class="form-control" id="hora_fecha" name="hora_fecha" value="${row.HORA_FECHA}" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
                     <!-- Modal de eliminación de reservación -->
        <div class="modal fade" id="eliminarReservacion${row.ID_RESERVA}" tabindex="-1" role="dialog" aria-labelledby="eliminarReservacion${row.ID_RESERVA}Label" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="eliminarReservacion${row.ID_RESERVA}Label">Eliminar Reservación</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas eliminar la reservación para "${row.PERSONA}"?</p>
                    </div>
                    <div class="modal-footer">
                        <form class="eliminar-reservacion-form" data-id="${row.ID_RESERVA}" method="POST">
                            @csrf
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
       `;
                }
            }
        ],
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
          // Verifica el valor antes de enviar el formulario
    $('#reporteForm').on('submit', function(e) {
        var searchValue = $('#searchInput').val();
        console.log("Valor enviado para el reporte: " + searchValue);  // Verifica el valor antes de enviar el formulario
    });

        // Validar entrada en tiempo real
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
        

        // Asignación de validaciones a los campos de descripción y título
        const descripcionFields = document.querySelectorAll('textarea[name="descripcion"]');
        descripcionFields.forEach(function(input) {
            input.addEventListener('input', function() {
                validarInput(input);
            });
        });

        const tituloFields = document.querySelectorAll('input[name="persona_descripcion"], input[name="id_instalacion"], input[name="tipo_evento"]');
        tituloFields.forEach(function(input) {
            input.addEventListener('input', function() {
                validarInput(input);
            });
        });

        // Función para convertir a mayúsculas
        function convertirAMayusculas() {
            var fieldsToConvert = ['persona_descripcion', 'tipo_evento'];
            fieldsToConvert.forEach(function(fieldId) {
                var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
                inputs.forEach(function(input) {
                    input.value = input.value.toUpperCase();
                });
            });
        }

        // Asignar evento input a los campos específicos para convertir a mayúsculas
        ['persona_descripcion','tipo_evento'].forEach(function(fieldId) {
            document.querySelectorAll('input[id^="' + fieldId + '"]').forEach(function(input) {
                input.addEventListener('input', function() {
                    input.value = input.value.toUpperCase();
                });
            });
        });

        // Función para restringir caracteres especiales
       function restringirCaracteres() {
    var fieldsToRestrict = ['persona_descripcion', 'tipo_evento'];
    fieldsToRestrict.forEach(function(fieldId) {
        var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
        inputs.forEach(function(input) {
            input.addEventListener('input', function(e) {
                // Regex to allow letters (including accented ones), ñ and spaces
                const validChars = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/;
                if (!validChars.test(input.value)) {
                    input.value = input.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
                }
            });
        });
    });
}

$(document).on('input', 'input[name="persona_descripcion"], input[name="tipo_evento"]', function() {
    validarInput(this);
});

        // Función para limitar el tamaño de los caracteres en ciertos campos
        function limitarTamañoCaracteres() {
            var fieldsWithMaxLength = {
                'persona_descripcion': 70,
                'tipo_evento': 50,
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

        // Asignar eventos de restricción y limitación a los campos específicos
        restringirCaracteres();
        limitarTamañoCaracteres();

        // Manejo de envío de formularios AJAX
        $('#nueva-reservacion-form').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: '{{ route("reservaciones.guardar") }}',
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

        // AJAX form submission for editing a reserva
$(document).on('submit', '.editar-reservacion-form', function(event) {
    event.preventDefault();
    var reservaId = $(this).data('id');
    var formData = new FormData(this);
    $.ajax({
        url: '{{ route("reservaciones.actualizar", "") }}/' + reservaId,
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


        // AJAX form submission for deleting a reserva
       $(document).on('submit', '.eliminar-reservacion-form', function(event) {
        event.preventDefault();
        var reservaId = $(this).data('id');
        $.ajax({
            url: '{{ route("reservaciones.eliminar", "") }}/' + reservaId,
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

        // Evento para generar reporte
        document.getElementById('generarReporteBtn').addEventListener('click', function() {
            window.open('{{ route("reservaciones.reporte") }}', '_blank');
        });
    });
</script>
@endsection
