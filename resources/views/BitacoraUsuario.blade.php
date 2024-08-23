@extends('adminlte::page')

@section('title', 'Bitácora de Usuario')

@section('content_header')
    <h1>Bitácora de Usuario</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Bitácoras de Usuario</h3>
            <div class="card-tools">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#crearBitacoraModal">Nuevo</button>
                    <form id="reporteForm" method="GET" action="{{ route('bitacoraUsuario.reporte') }}" target="_blank">
                    <input type="hidden" name="id_usuario" id="searchInput">
                    <button type="submit" class="btn btn-success mt-2">Generar Reporte</button>
                </form>
                </div>

                </div>
            </div>
        </div>

        <div class="card-body">
                @if($hasPermission)
            <div class="table-container">
                <table id="bitacora-usuario-table" class="table table-striped table-bordered shadow-lg mt-4" style="width: 100%">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>ID BITACORA DE USUARIO</th>
                            <th>USUARIO</th>
                            <th>OBJETO</th>
                            <th>FECHA</th>
                            <th>ACCION</th>
                            <th>DESCRIPCION</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los datos se cargarán a través de DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

   <!-- Modal para Crear Bitácora -->
<div class="modal fade" id="crearBitacoraModal" tabindex="-1" aria-labelledby="crearBitacoraLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="crearBitacoraLabel">Crear Nueva Bitácora</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="nuevo-bitacora-form">
                <div class="modal-body">
                    @csrf
                    <div class="form-group">
                        <label for="nombre_usuario">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" required>
                    </div>
                    <div class="form-group">
                        <label for="ID_OBJETO">ID Objeto</label>
                        <input type="text" class="form-control" id="ID_OBJETO" name="ID_OBJETO" required>
                    </div>
                    <div class="form-group">
                        <label for="ACCION">Acción</label>
                        <input type="text" class="form-control" id="ACCION" name="ACCION" required>
                    </div>
                    <div class="form-group">
                        <label for="DESCRIPCION">Descripción</label>
                        <input type="text" class="form-control" id="DESCRIPCION" name="DESCRIPCION">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

   <!-- Modal para editar una nueva bitácora -->

<<!-- Modal para Editar Bitácora -->
<div class="modal fade" id="editarBitacoraModal" tabindex="-1" aria-labelledby="editarBitacoraLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarBitacoraLabel">Editar Bitácora</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editar-bitacora-form">
                <div class="modal-body">  @csrf 
                    <input type="hidden" id="editar_ID_BITACORA" name="P_ID_BITACORA">
                    <div class="form-group">
                        <label for="editar_ID_USUARIO">ID Usuario</label>
                        <input type="text" class="form-control" id="editar_ID_USUARIO" name="P_ID_USUARIO" required>
                    </div>
                    <div class="form-group">
                        <label for="editar_ID_OBJETO">ID Objeto</label>
                        <input type="text" class="form-control" id="editar_ID_OBJETO" name="P_ID_OBJETO" required>
                    </div>
                    <div class="form-group">
                        <label for="editar_ACCION">Acción</label>
                        <input type="text" class="form-control" id="editar_ACCION" name="P_ACCION" required>
                    </div>
                    <div class="form-group">
                        <label for="editar_DESCRIPCION">Descripción</label>
                        <input type="text" class="form-control" id="P_DESCRIPCION" name="P_DESCRIPCION" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal para eliminar una bitácora -->
<div class="modal fade" id="eliminarBitacoraModal" tabindex="-1" aria-labelledby="eliminarBitacoraLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eliminarBitacoraLabel">Eliminar Bitácora</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="eliminar-bitacora-form">
                <div class="modal-body">  
                    <input type="hidden" id="eliminar_ID_BITACORA" name="P_ID_BITACORA">
                    <p>¿Estás seguro de que deseas eliminar esta bitácora?</p>
                </div>
                <div class="modal-footer">
                    @csrf 
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>
     @else
            <div class="alert alert-danger">
                No tienes permisos para ver los la bitacora de usuario.
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
        var table = $('#bitacora-usuario-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "/get-bitacora-usuario",
            "type": "GET"
        },
        "columns": [
            { "data": "ID_BITACORA" },
            { "data": "ID_USUARIO" },
            { "data": "ID_OBJETO" },
            { "data": "FECHA" },
            { "data": "ACCION" },
            { "data": "DESCRIPCION" },
            {
                "data": null,
                "render": function(data, type, row) {
                    return `
                        <button type="button" class="btn btn-info editar-bitacora-btn" data-id="${row.ID_BITACORA}" data-toggle="modal" data-target="#editarBitacoraModal">Editar</button>
                        <button type="button" class="btn btn-danger eliminar-bitacora-btn" data-id="${row.ID_BITACORA}" data-toggle="modal" data-target="#eliminarBitacoraModal">Eliminar</button>`;
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
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]]
        }).ajax.reload();
 
// Captura y manejo de búsqueda en DataTables
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

            // Manejo de la creación de bitácora
            $('#nuevo-bitacora-form').on('submit', function(event) {
    event.preventDefault();
    $.ajax({
        url: '/bitacora-usuario/create',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            Swal.fire('Éxito', response.message, 'success');
            $('#crearBitacoraModal').modal('hide');
            $('#bitacora-usuario-table').DataTable().ajax.reload();
        },
        error: function(response) {
            Swal.fire('Error', response.responseJSON.message, 'error');
        }
    });
});
      // Abrir modal de edición con datos

      $(document).on('click', '.editar-bitacora-btn', function() {
    var rowData = $('#bitacora-usuario-table').DataTable().row($(this).parents('tr')).data();
    
    $('#editar_ID_BITACORA').val(rowData.ID_BITACORA);
    $('#editar_ID_USUARIO').val(rowData.ID_USUARIO);
    $('#editar_ID_OBJETO').val(rowData.ID_OBJETO);
    $('#editar_ACCION').val(rowData.ACCION);
    $('#editar_DESCRIPCION').val(rowData.DESCRIPCION);

    $('#editarBitacoraModal').modal('show');
});
      //  edición con datos
      $('#editar-bitacora-form').on('submit', function(event) {
    event.preventDefault();
    var bitacoraId = $('#editar_ID_BITACORA').val(); // Obteniendo el ID desde el campo oculto
    $.ajax({
        url: '/bitacora-usuario/update/' + bitacoraId,
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            Swal.fire('Éxito', response.message, 'success');
            $('#editarBitacoraModal').modal('hide');
            $('#bitacora-usuario-table').DataTable().ajax.reload();
        },
        error: function(response) {
            Swal.fire('Error', response.responseJSON.message, 'error');
        }
    });
});

// Abrir modal de eliminación con ID
$(document).on('click', '.eliminar-bitacora-btn', function() {
    var rowData = $('#bitacora-usuario-table').DataTable().row($(this).parents('tr')).data();
    $('#eliminar_ID_BITACORA').val(rowData.ID_BITACORA);
    $('#eliminarBitacoraModal').modal('show');
});
// Manejo de la eliminación de bitácora
$('#eliminar-bitacora-form').on('submit', function(event) {
    event.preventDefault();
    var bitacoraId = $('#eliminar_ID_BITACORA').val();
    console.log("ID de Bitácora a eliminar:", bitacoraId);
    $.ajax({
        url: '/bitacora-usuario/delete/' + bitacoraId,
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            Swal.fire('Éxito', response.message, 'success');
            $('#eliminarBitacoraModal').modal('hide');
            $('#bitacora-usuario-table').DataTable().ajax.reload();
        },
        error: function(response) {
            Swal.fire('Error', response.responseJSON.message, 'error');
            
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

    const tituloFields = document.querySelectorAll('input[name="P_ACCION"], input[name="P_DESCRIPCION"], input[name="editar_ACCION"], input[name="P_ID_USUARIO"], input[name="P_ID_OBJETO"]');
    tituloFields.forEach(function(input) {
        input.addEventListener('input', function() {
            validarInput(input);
        });
    });
// Función para convertir a mayúsculas
function convertirAMayusculas() {
        // Especificar los campos que deben ser convertidos a mayúsculas
        var fieldsToConvert = ['P_ID_USUARIO','P_ID_OBJETO','P_ACCION','P_DESCRIPCION','editar_ID_USUARIO','editar_ID_OBJETO','editar_ACCION'];
        fieldsToConvert.forEach(function(fieldId) {
            var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
            inputs.forEach(function(input) {
                input.value = input.value.toUpperCase();
            });
        });
    }

    // Asignar evento input a los campos específicos
    ['P_ID_USUARIO','P_ID_OBJETO','P_ACCION','P_DESCRIPCION','editar_ID_USUARIO','editar_ID_OBJETO','editar_ACCION'].forEach(function(fieldId) {
        document.querySelectorAll('input[id^="' + fieldId + '"]').forEach(function(input) {
            input.addEventListener('input', function() {
                input.value = input.value.toUpperCase();
            });
        });
    });
 
     
 // Función para restringir caracteres especiales
 function restringirCaracteres() {
        // Especificar los campos que deben ser restringidos
        var fieldsToRestrict = ['P_ID_USUARIO','P_ID_OBJETO','P_ACCION','P_DESCRIPCION','editar_ID_USUARIO','editar_ID_OBJETO','editar_ACCION'];
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
            'P_ID_USUARIO': 20,
            'P_ID_OBJETO':20,
            'P_ACCION':50,
            'P_DESCRIPCION':50,
            'editar_ID_USUARIO': 20,
            'editar_ID_OBJETO': 20,
            'editar_ACCION':50
            
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
</script>
@stop
