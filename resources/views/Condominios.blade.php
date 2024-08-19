@extends('adminlte::page')

@section('title', 'Gestión de Condominios')

@section('content_header')
    <h1>Gestión de Condominios</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Condominios</h3>
            <div class="card-tools">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#nuevoCondominioModal">Nuevo</button>
                    <form id="reporteForm" method="GET" action="{{ route('condominios.reporte') }}" target="_blank">
                    <input type="hidden" name="condominio" id="searchInput">
                    <button type="submit" class="btn btn-success mt-2">Generar Reporte</button>
                </form>
                </div>
            </div>
        </div>
        <div class="card-body">
        @if($hasPermission)
            <div class="table-container">
                <table id="condominios-table" class="table table-striped table-bordered shadow-lg mt-4" style="width: 100%">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>ID CONDOMINIO</th>
                            <th>TIPO CONDOMINIO</th>
                            <th>DESCRIPCION</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($Condominios as $Condominio)
                            <tr>
                                <td>{{ $Condominio["ID_CONDOMINIO"] }}</td>
                                <td>{{ $Condominio["TIPOCONDOMINIO"] }}</td>
                                <td>{{ $Condominio["DESCRIPCION"] }}</td>
                                <td>
                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editarCondominioModal{{ $Condominio['ID_CONDOMINIO'] }}">Editar</button>
                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#eliminarCondominioModal{{ $Condominio['ID_CONDOMINIO'] }}">Eliminar</button>
                                </td>
                            </tr>

                            <!-- Modal de editar condominio -->
                            <div class="modal fade" id="editarCondominioModal{{ $Condominio['ID_CONDOMINIO'] }}" tabindex="-1" role="dialog" aria-labelledby="editarCondominioModalLabel{{ $Condominio['ID_CONDOMINIO'] }}" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editarCondominioModalLabel{{ $Condominio['ID_CONDOMINIO'] }}">Editar Condominio</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form class="editar-condominio-form" data-id="{{ $Condominio['ID_CONDOMINIO'] }}">
                                                @csrf
                                               
                                                <div class="form-group">
                                                <label for="tipo_condominio">TIPO DE CONDOMINIO:</label>
                                                <select class="form-control" id="tipo_condominio" name="tipo_condominio" required>
                                                @foreach($tipodecondominios as $tipo)
                                                <option value="{{ $tipo->ID_TIPO_CONDOMINIO }}"{{ $Condominio['ID_TIPO_CONDOMINIO'] == $tipo->ID_TIPO_CONDOMINIO ? 'selected' : '' }}>{{ $tipo->DESCRIPCION }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                                <div class="form-group">
                                                    <label for="descripcion">Descripción:</label>
                                                    <input type="text" class="form-control" id="descripcion" name="descripcion"value="{{ $Condominio['DESCRIPCION'] }}" required>
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

                            <!-- Modal de eliminar condominio -->
                            <div class="modal fade" id="eliminarCondominioModal{{ $Condominio['ID_CONDOMINIO'] }}" tabindex="-1" role="dialog" aria-labelledby="eliminarCondominioModalLabel{{ $Condominio['ID_CONDOMINIO'] }}" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="eliminarCondominioModalLabel{{ $Condominio['ID_CONDOMINIO'] }}">Eliminar Condominio</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p>¿Estás seguro de que deseas eliminar el condominio "{{ $Condominio['ID_CONDOMINIO'] }}"?</p>
                                            <form class="eliminar-condominio-form" data-id="{{ $Condominio['ID_CONDOMINIO'] }}">
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
        </div>
        @else
            <div class="alert alert-danger">
                No tienes permisos para ver los Condominios.
            </div>
        @endif
    </div>

    <!-- Modal de nuevo condominio -->
    <div class="modal fade" id="nuevoCondominioModal" tabindex="-1" role="dialog" aria-labelledby="nuevoCondominioModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nuevoCondominioModalLabel">Nuevo Condominio</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="nuevo-condominio-form">
                        @csrf
                    <div class="form-group">
                        <label for="TIPOCONDOMINIO">Tipo de condominio:</label>
                        <select class="form-control" id="TIPOCONDOMINIO" name="TIPOCONDOMINIO" required>
                            @foreach($tipodecondominios as $tipo)
                                <option value="{{ $tipo->ID_TIPO_CONDOMINIO }}">{{ $tipo->DESCRIPCION }}</option>
                            @endforeach
                        </select>
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
        var table = $('#condominios-table').DataTable({
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
                "thousands": ","
            }
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

    const tituloFields = document.querySelectorAll('input[name="descripcion"], input[name="tipo_condominio"]');
    tituloFields.forEach(function(input) {
        input.addEventListener('input', function() {
            validarInput(input);
        });
    });
// Función para convertir a mayúsculas
function convertirAMayusculas() {
        // Especificar los campos que deben ser convertidos a mayúsculas
        var fieldsToConvert = ['tipo_condominio', 'descripcion'];
        fieldsToConvert.forEach(function(fieldId) {
            var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
            inputs.forEach(function(input) {
                input.value = input.value.toUpperCase();
            });
        });
    }

    // Asignar evento input a los campos específicos
    ['descripcion', 'tipo_condominio'].forEach(function(fieldId) {
        document.querySelectorAll('input[id^="' + fieldId + '"]').forEach(function(input) {
            input.addEventListener('input', function() {
                input.value = input.value.toUpperCase();
            });
        });
    });
 
     
 // Función para restringir caracteres especiales
 function restringirCaracteres() {
        // Especificar los campos que deben ser restringidos
        var fieldsToRestrict = ['tipo_condominio', 'descripcion'];
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

    // Función para limitar el tamaño de los caracteres en ciertos campos
    function limitarTamañoCaracteres() {
        // Especificar los campos y sus tamaños máximos
        var fieldsWithMaxLength = {
            'tipo_condominio': 30,
            'descripcion':50
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

       // AJAX form submission for creating a condominios
     $('#nuevo-condominio-form').on('submit', function(event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: '{{ route("condominios.store") }}',
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

        // AJAX form submission for updating a condominios
        $('.editar-condominio-form').on('submit', function(event) {
            event.preventDefault();

            var id = $(this).data('id');
            var formData = new FormData(this);

            $.ajax({
                url: '{{ route("condominios.actualizar", ":id") }}'.replace(':id', id),
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
        $('.eliminar-condominio-form').on('submit', function(event) {
            event.preventDefault();

            var id = $(this).data('id');

            $.ajax({
                url: '{{ route("condominios.eliminar", ":id") }}'.replace(':id', id),
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
    window.open('{{ route("condominios.reporte") }}', '_blank');
});
});   
 
</script>
@stop
