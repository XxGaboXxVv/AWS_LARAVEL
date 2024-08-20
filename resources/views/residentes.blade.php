@extends('adminlte::page')

@section('title', 'Residentes')

@section('content_header')
    <h1>Gestión de Residentes</h1>
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
            <h3 class="card-title">Listado de Residentes</h3>
            <div class="card-tools">
            <div class="d-flex justify-content-end">
                <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#modalAgregarResidente">Nuevo</button>
                <form id="reporteForm" method="GET" action="{{ route('residentes.reporte') }}" target="_blank">
                    <input type="hidden" name="nombre" id="searchInput">
                    <button type="submit" class="btn btn-success mt-2">Generar Reporte</button>
                </form>
            </div>
        </div>
    </div>
</div>
          </div>
            </div>
            </form>
            <div class="card-body">
        @if($hasPermission)
            <div class="table-container">
                <table id="residentes" class="table table-striped table-bordered shadow-lg mt-4" style="width: 100%">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>ID DEL RESIDENTE</th>
                            <th>NOMBRE DEL RESIDENTE</th>
                            <th>DNI DEL RESIDENTE</th>
                            <th>TIPO DE CONTACTO DEL RESIDENTE</th>
                            <th>CONTACTO DEL RESIDENTE</th>
                            <th>TIPO DE RESIDENTE</th>
                            <th>CONDOMINIO</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($Residentes as $residente)
                            <tr>
                                <td>{{ $residente["ID_PERSONA"] }}</td>
                                <td>{{ $residente["NOMBRE_PERSONA"] }}</td>
                                <td>{{ $residente["DNI_PERSONA"] }}</td>
                                <td>{{ $residente["TIPO_CONTACTO"] }}</td>
                                <td>{{ $residente["CONTACTO"] }}</td>
                                <td>{{ $residente["ESTADO_PERSONA"] }}</td>
                                <td>{{ $residente["CONDOMINIO"] }}</td>

                                <td>
                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editarResidente{{ $residente['ID_PERSONA'] }}">Editar</button>
                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#eliminarResidente{{ $residente['ID_PERSONA'] }}">Eliminar</button>
                                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#mostrarMas{{ $residente['ID_PERSONA'] }}">Mostrar Mas</button>

                                </td>
                            </tr>
                            <!-- Modal de "Mostrar más" -->
                            <div class="modal fade" id="mostrarMas{{ $residente['ID_PERSONA'] }}" tabindex="-1" role="dialog" aria-labelledby="mostrarMas{{ $residente['ID_PERSONA'] }}Label" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="mostrarMas{{ $residente['ID_PERSONA'] }}Label">Detalles del Usuario</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>ID DEL RESIDENTE: {{ $residente["ID_PERSONA"] }}</p>
                                        <p>NOMBRE DEL RESIDENTE: {{ $residente["NOMBRE_PERSONA"] }}</p>
                                        <p>DNI DEL RESIDENTE: {{ $residente["DNI_PERSONA"] }}</p>
                                        <p>TIPO DE CONTACTO DEL RESIDENTE:{{ $residente["TIPO_CONTACTO"] }}</p>
                                        <p>CONTACTO DEL RESIDENTE: {{ $residente["CONTACTO"] }}</p>
                                        <p>TIPO DE RESIDENTE: {{ $residente["TIPO_PERSONA"]  }}</p>
                                         <p>CONDOMINIO: {{ $residente["CONDOMINIO"] }}</p>
                                        <p>ESTADO DEL RESIDENTE: {{ $residente["ESTADO_PERSONA"] }}</p>
                                        <p>PARENTESCO: {{ $residente["PARENTESCO"] }}</p>
                                       
                                        

                                    </div>
                                    <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <!-- Modal de edición de residente -->
<div class="modal fade" id="editarResidente{{ $residente['ID_PERSONA'] }}" tabindex="-1" role="dialog" aria-labelledby="editarResidente{{ $residente['ID_PERSONA'] }}Label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarResidente{{ $residente['ID_PERSONA'] }}Label">Editar Residente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulario de edición de residente --> 
                <form class="editar-residente-form" data-id="{{$residente['ID_PERSONA'] }}" method="POST" enctype="multipart/form-data">
                @csrf
                    <div class="form-group">
                        <label for="nombre">NOMBRE DEL RESIDENTE:</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="{{ $residente['NOMBRE_PERSONA'] }}" required>
                    </div>
                    <div class="form-group">
                        <label for="dni">DNI DEL RESIDENTE:</label>
                        <input type="text" class="form-control" id="dni" name="dni" value="{{ $residente['DNI_PERSONA'] }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="id_estado_persona">ESTADO DEL RESIDENTE:</label>
                        <select class="form-control" id="id_estado_persona" name="id_estado_persona" required>
                        @foreach($estadopersona as $estado)
                        <option value="{{ $estado->ID_ESTADO_PERSONA }}"{{ $residente['ID_ESTADO_PERSONA'] == $estado->ID_ESTADO_PERSONA ? 'selected' : '' }}>{{ $estado->DESCRIPCION }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="id_tipo_contacto">TIPO DE CONTACTO DEL RESIDENTE:</label>
                        <select class="form-control" id="id_tipo_contacto" name="id_tipo_contacto" required>
                            @foreach($TipoContacto as $tipoContacto)
                                <option value="{{ $tipoContacto->ID_TIPO_CONTACTO }}"{{ $residente['ID_TIPO_CONTACTO'] == $tipoContacto->ID_TIPO_CONTACTO ? ' selected' : '' }}>
                                    {{ $tipoContacto->DESCRIPCION }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="contacto_descripcion">CONTACTO DEL RESIDENTE:</label>
                        <input type="text" class="form-control" id="contacto_descripcion" name="contacto_descripcion" value="{{ $residente['CONTACTO'] }}" required>
                    </div>

                    <div class="form-group">
                        <label for="ID_TIPO_PERSONA">TIPO DE RESIDENTE:</label>
                        <select class="form-control" id="ID_TIPO_PERSONA" name="ID_TIPO_PERSONA" required>
                        @foreach($tipopersona as $tipo)
                        <option value="{{ $tipo->ID_TIPO_PERSONA }}"{{ $residente['ID_TIPO_PERSONA'] == $tipo->ID_TIPO_PERSONA ? 'selected' : '' }}>{{ $tipo->DESCRIPCION }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="form-group">
                        <label for="id_parentesco">PARENTESCO DEL RESIDENTE:</label>
                        <select class="form-control" id="id_parentesco" name="id_parentesco" required>
                        @foreach($Parentesco as $parentesco)
                        <option value="{{ $parentesco->ID_PARENTESCO }}"{{ $residente['ID_PARENTESCO'] == $parentesco->ID_PARENTESCO ? 'selected' : '' }}>{{ $parentesco->DESCRIPCION }}</option>
                            @endforeach
                        </select>
                    </div>

                   
                    <div class="form-group">
    <label for="condominio_descripcion">CONDOMINIO:</label>
    <select class="form-control" id="condominio_descripcion" name="condominio_descripcion" required style="height: 40px; overflow-y: auto;">
        @foreach($Condominio as $condominio)
            <option value="{{ $condominio->DESCRIPCION }}" 
                {{ $condominio->DESCRIPCION == $residente['CONDOMINIO'] ? 'selected' : '' }}>
                {{ $condominio->DESCRIPCION }}
            </option>
        @endforeach
    </select>
</div>

                        

                    <div class="form-group">
                        <label for="id_padre">ID PADRE:</label>
                        <input type="number" class="form-control" id="id_padre" name="id_padre" value="{{ $residente['ID_PADRE'] }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>

                            <!-- Modal de eliminación de residente -->
<div class="modal fade" id="eliminarResidente{{ $residente['ID_PERSONA'] }}" tabindex="-1" role="dialog" aria-labelledby="eliminarResidente{{ $residente['ID_PERSONA'] }}Label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eliminarResidente{{ $residente['ID_PERSONA'] }}Label">Eliminar Residente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar al residente "{{ $residente['NOMBRE_PERSONA'] }}"?</p>
            </div>
            <div class="modal-footer">
            <form class="eliminar-residente-form" data-id="{{  $residente['ID_PERSONA'] }}">
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
                No tienes permisos para ver los residentes.
            </div>
        @endif
        </div>
    </div>

<!-- Modal para agregar residente -->
<div class="modal fade" id="modalAgregarResidente" tabindex="-1" role="dialog" aria-labelledby="modalAgregarResidenteLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgregarResidenteLabel">Agregar Residente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulario para agregar residente -->
                <form id="nuevo-residente-form" method="POST" enctype="multipart/form-data">
                @csrf
                    <div class="form-group">
                        <label for="P_NOMBRE_PERSONA">Nombre:</label>
                        <input type="text" class="form-control" id="P_NOMBRE_PERSONA" name="P_NOMBRE_PERSONA" required>
                    </div>
                    <div class="form-group">
                        <label for="P_DNI_PERSONA">DNI:</label>
                        <input type="text" class="form-control" id="P_DNI_PERSONA" name="P_DNI_PERSONA" required>
                    </div>

                    <div class="form-group">
                        <label for="id_estado_persona">ESTADO DE LA PERSONA:</label>
                        <select class="form-control" id="id_estado_persona" name="id_estado_persona" required>
                            @foreach($estadopersona as $estado)
                                <option value="{{ $estado->ID_ESTADO_PERSONA }}">{{ $estado->DESCRIPCION }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="id_tipo_contacto">TIPO DE CONTACTO:</label>
                        <select class="form-control" id="id_tipo_contacto" name="id_tipo_contacto" required>
                            @foreach($TipoContacto as $tipo)
                                <option value="{{ $tipo->ID_TIPO_CONTACTO }}">{{ $tipo->DESCRIPCION }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="contacto_descripcion">CONTACTO DEL RESIDENTE:</label>
                        <input type="text" class="form-control" id="contacto_descripcion" name="contacto_descripcion" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="P_ID_TIPO_PERSONA">TIPO DE RESIDENTE:</label>
                        <select class="form-control" id="P_ID_TIPO_PERSONA" name="P_ID_TIPO_PERSONA" required>
                        @foreach($tipopersona as $tipo)
                        <option value="{{ $tipo->ID_TIPO_PERSONA }}">{{ $tipo->DESCRIPCION }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="P_ID_PARENTESCO">PARANTESCO DEL RESIDENTE:</label>
                        <select class="form-control" id="P_ID_PARENTESCO" name="P_ID_PARENTESCO" required>
                            @foreach($Parentesco as $parentesco)
                                <option value="{{ $parentesco->ID_PARENTESCO }}">{{ $parentesco->DESCRIPCION }}</option>
                            @endforeach
                        </select>
                    </div>
                   
                    <div class="form-group">
    <label for="condominio_descripcion">CONDOMINIO:</label>
    <select class="form-control" id="condominio_descripcion" name="condominio_descripcion" required style="height: 40px; overflow-y: auto;">
        @foreach($Condominio as $condominio)
            <option value="{{ $condominio->DESCRIPCION }}">{{ $condominio->DESCRIPCION }}</option>
        @endforeach
    </select>
</div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
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
        var table = $('#residentes').DataTable({
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
        var fieldsToConvert = ['nombre', 'parentesco', 'condominio','P_NOMBRE_PERSONA'];
        fieldsToConvert.forEach(function(fieldId) {
            var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
            inputs.forEach(function(input) {
                input.value = input.value.toUpperCase();
            });
        });
    }

    // Asignar evento input a los campos específicos
    ['nombre', 'parentesco', 'condominio','P_NOMBRE_PERSONA'].forEach(function(fieldId) {
        document.querySelectorAll('input[id^="' + fieldId + '"]').forEach(function(input) {
            input.addEventListener('input', function() {
                input.value = input.value.toUpperCase();
            });
        });
    });
 

    
 // Función para restringir caracteres especiales
 function restringirCaracteres() {
        // Especificar los campos que deben ser restringidos
        var fieldsToRestrict = ['nombre', 'parentesco', 'condominio','P_NOMBRE_PERSONA'];
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

    // Función para restringir ciertos caracteres en campos de contacto y DNI
    function restringirCaracteresContactoDNI() {
        // Especificar los campos que deben ser restringidos
        var fieldsToRestrict = ['contacto_descripcion', 'P_DNI_PERSONA','dni'];
        var invalidChars = /[<>(){}[\]=;%^&*,"':]/g;
        fieldsToRestrict.forEach(function(fieldId) {
            var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
            inputs.forEach(function(input) {
                input.addEventListener('input', function(e) {
                    if (invalidChars.test(input.value)) {
                        input.value = input.value.replace(invalidChars, '');
                    }
                });
            });
        });
    }
    // Función para limitar el tamaño de los caracteres en ciertos campos
    function limitarTamañoCaracteres() {
        // Especificar los campos y sus tamaños máximos
        var fieldsWithMaxLength = {
            'nombre': 70,
            'P_NOMBRE_PERSONA':70,
            'parentesco': 30,
            'condominio': 100,
            'contacto_descripcion': 60,
            'dni': 20,
            'P_DNI_PERSONA':20
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
      // Función para restringir la entrada a solo números positivos
function permitirSoloNumerosPositivos() {
    // Especificar los campos que deben ser restringidos
    var fieldsToRestrict = ['P_ID_PADRE', 'id_padre','P_DNI_PERSONA'];
    
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


    const tituloFields = document.querySelectorAll('input[name="P_NOMBRE_PERSONA"], input[name="P_DNI_PERSONA"], input[name="contacto_descripcion"], input[name="condominio_descripcion"], input[name="nombre"], input[name="dni"], input[name="contacto_descripcion"], input[name="condominio_descripcion"]');
    tituloFields.forEach(function(input) {
        input.addEventListener('input', function() {
            validarInput(input);
        });
    });
    // Asignar evento input a los campos específicos
    restringirCaracteres();
    restringirCaracteresContactoDNI();
    limitarTamañoCaracteres();
    permitirSoloNumerosPositivos();


        // AJAX form submission for creating a resident
$('#nuevo-residente-form').on('submit', function(event) {
    event.preventDefault();

    var formData = new FormData(this);

    $.ajax({
        url: '{{ route("residentes.crear") }}',
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
// AJAX form submission for editing a resident
$('.editar-residente-form').on('submit', function(event) {
    event.preventDefault();

    var residenteId = $(this).data('id');
    var formData = new FormData(this);

    $.ajax({
        url: '{{ route("residentes.editar", ":id") }}'.replace(':id', residenteId),
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
// AJAX form submission for deleting a resident
$('.eliminar-residente-form').on('submit', function(event) {
    event.preventDefault();

    var residenteId = $(this).data('id');

    $.ajax({
        url: '{{ route("residentes.eliminar", ":id") }}'.replace(':id', residenteId),
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
    window.open('{{ route("residentes.reporte") }}', '_blank');
});
 
    });
    
    
    </script>
@stop
