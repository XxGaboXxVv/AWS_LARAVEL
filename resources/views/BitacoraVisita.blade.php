@extends('adminlte::page')

@section('title', 'Gestión de Bitácoras de Visitas')

@section('content_header')
    <h1>Gestión de Bitácoras de Visitas</h1>
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
            <h3 class="card-title">Bitácoras de Visitas</h3>
            <div class="card-tools">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#nuevaVisitaModal">Nuevo</button>
                     <form id="reporteForm" method="GET" action="{{ route('bitacora.reporte') }}" target="_blank">
                    <input type="hidden" name="nombre" id="searchInput">
                    <button type="submit" class="btn btn-success mt-2">Generar Reporte</button>
                </form>
                </div>
            </div>
        </div>
    </div>
    @if($hasPermission)
    <div class="card-body">
        <div class="table-container">
            <table id="bitacora-table" class="table table-striped table-bordered shadow-lg mt-4" style="width: 100%">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>ID</th>
                        <th>Residente</th>
                        <th>VISITANTE</th>
                        <th>VISITANTE RECURRENTE</th>
                        <th>NUMERO DE PERSONAS</th>
                        <th>NUMERO DE PLACA</th>
                        <th>FECHA Y HORA</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                  
            </table>
        </div>
        @else
            <div class="alert alert-danger">
                No tienes permisos para ver las Bitacora Visita.
            </div>
        @endif
    </div>


    <!-- Modal de nueva bitácora de visita -->
<div class="modal fade" id="nuevaVisitaModal" tabindex="-1" role="dialog" aria-labelledby="nuevaVisitaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevaVisitaModalLabel">Agregar Nueva Bitácora de Visita</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulario para agregar nueva bitácora de visita -->
                <form id="nueva-visita-form">
                    @csrf
                    <div class="form-group">
                        <label for="persona_descripcion">Residente:</label>
                        <input type="text" class="form-control" id="persona_descripcion" name="persona_descripcion" required>
                    </div>
                    <div class="form-group">
                        <label for="visita_descripcion">Visitante:</label>
                        <input type="text" class="form-control" id="visita_descripcion" name="visita_descripcion">
                    </div>
                    <div class="form-group">
                    <label for="visita_recurrente_descripcion">Visitante Recurrente:</label>
                    <input type="text" class="form-control" id="visita_recurrente_descripcion" name="visita_recurrente_descripcion">
                        </div>
                    <div class="form-group">
                        <label for="num_persona">Número de Persona:</label>
                        <input type="text" class="form-control" id="num_persona" name="num_persona" required>
                    </div>
                    <div class="form-group">
                        <label for="num_placa">Número de Placa:</label>
                        <input type="text" class="form-control" id="num_placa" name="num_placa">
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
    $('#bitacora-table').DataTable({ 
        "processing": true,
            "serverSide": true,
            "ajax": {
            "url": "{{ route('fetch.bitacora.visita') }}",
            "type": "GET"
        },
        "columns": [
            { "data": "ID_BITACORA_VISITA" },
            { "data": "PERSONA" },
            { "data": "VISITANTE" },
            { "data": "VISITANTE_RECURRENTE" },
            { "data": "NUM_PERSONA" },
            { "data": "NUM_PLACA" },
            { "data": "FECHA_HORA" },                    
            {
                "data": null,
                "orderable": false,
                "searchable": false,
                
         "render": function(data, type, row) {
                    return `
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editarVisitaModal${row.ID_BITACORA_VISITA}">Editar</button>
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#eliminarVisitaModal${row.ID_BITACORA_VISITA}">Eliminar</button>

                        <!-- Modal de editar visita -->
                       <div class="modal fade" id="editarVisitaModal${row.ID_BITACORA_VISITA}" tabindex="-1" role="dialog" aria-labelledby="editarVisitaModalLabel${row.ID_BITACORA_VISITA}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarVisitaModalLabel${row.ID_BITACORA_VISITA}">Editar Bitácora de Visita</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                    <form id="editarVisitaform" class="editarVisitaform" data-id="${row.ID_BITACORA_VISITA}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="persona_descripcion">Residente:</label>
                        <input type="text" class="form-control" id="persona_descripcion" name="persona_descripcion" value="${row.PERSONA}" required>
                    </div>
                    <div class="form-group">
                        <label for="visita_descripcion">Visitante:</label>
                        <input type="text" class="form-control" id="visita_descripcion" name="visita_descripcion" value="${row.VISITANTE}">
                    </div>
                    <div class="form-group">
                        <label for="visita_recurrente_descripcion">Visitante Recurrente:</label>
                        <input type="text" class="form-control" id="visita_recurrente_descripcion" name="visita_recurrente_descripcion" value="${row.VISITANTE_RECURRENTE}">
                    </div>
                    <div class="form-group">
                        <label for="num_persona">Número de Persona:</label>
                        <input type="text" class="form-control" id="num_persona" name="num_persona" value="${row.NUM_PERSONA}" required>
                    </div>
                    <div class="form-group">
                        <label for="num_placa">Número de Placa:</label>
                        <input type="text" class="form-control" id="num_placa" name="num_placa" value="${row.NUM_PLACA}">
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


                        <!-- Modal de eliminar visita -->
                        <div class="modal fade" id="eliminarVisitaModal${row.ID_BITACORA_VISITA}" tabindex="-1" role="dialog" aria-labelledby="eliminarVisitaModalLabel${row.ID_BITACORA_VISITA}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="eliminarVisitaModalLabel${row.ID_BITACORA_VISITA}">Eliminar Bitácora de Visita</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>¿Estás seguro de que deseas eliminar esta bitácora de visita?</p>
                                        <form class="eliminar-visita-form" data-id="${row.ID_BITACORA_VISITA}">
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

    const tituloFields = document.querySelectorAll('input[name="persona_descripcion"], input[name="visita_descripcion"],input[name="num_persona"],input[name="num_placa"]');
    tituloFields.forEach(function(input) {
        input.addEventListener('input', function() {
            validarInput(input);
        });
    });
    // Función para convertir a mayúsculas
 function convertirAMayusculas() {
        // Especificar los campos que deben ser convertidos a mayúsculas
        var fieldsToConvert = ['visita_descripcion','num_placa','persona_descripcion'];
        fieldsToConvert.forEach(function(fieldId) {
            var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
            inputs.forEach(function(input) {
                input.value = input.value.toUpperCase();
            });
        });
    }

    // Asignar evento input a los campos específicos
    ['visita_descripcion','num_placa','persona_descripcion'].forEach(function(fieldId) {
        document.querySelectorAll('input[id^="' + fieldId + '"]').forEach(function(input) {
            input.addEventListener('input', function() {
                input.value = input.value.toUpperCase();
            });
        });
    });
 

    
 // Función para restringir caracteres especiales
 function restringirCaracteres() {
        // Especificar los campos que deben ser restringidos
        var fieldsToRestrict = ['visita_descripcion','persona_descripcion'];
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
    var fieldsToRestrict = ['num_persona'];
    
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
            'visita_descripcion':70,
            'num_persona': 10,  
            'num_placa': 20
        
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


        // AJAX form submission for creating a bitácora de visita
        $('#nueva-visita-form').on('submit', function(event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: '{{ route("bitacora.guardar") }}',
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
      $(document).on('submit', '.editarVisitaform', function(event) {
            event.preventDefault();

            var id = $(this).data('id');
            var formData = new FormData(this);

            $.ajax({
                url: '{{ route("bitacora.actualizar", ":id") }}'.replace(':id', id),
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
       $(document).on('submit', '.eliminar-visita-form', function(event) {
            event.preventDefault();

            var visitaId = $(this).data('id');

            $.ajax({
                url: '{{ route("bitacora.eliminar", "") }}/' + visitaId,
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
            window.open('{{ route("bitacora.reporte") }}', '_blank');
        });
    });
     
</script>
@endsection
