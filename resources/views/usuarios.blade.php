@extends('adminlte::page')

@section('title', 'Usuarios')

@section('content_header')
    <h1>Gestión de Usuarios</h1>
@stop

@section('content')

 <!-- Campo oculto para el parámetro de fecha de vencimiento -->
 <input type="hidden" id="diasVencimiento" value="{{ $diasVencimiento }}">

 <div class="card">
    <div class="card-header">
        <h3 class="card-title">Listado de Usuarios</h3>
        <div class="card-tools">
            <div class="d-flex justify-content-end">
                <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#modalAgregarUsuario">Nuevo</button>
                <form id="reporteUsuarioForm" method="GET" action="{{ route('usuarios.reporte') }}" target="_blank">
                    <input type="hidden" name="nombre" id="searchUsuarioInput">
                    <button type="submit" class="btn btn-success mt-2">Generar Reporte</button>
                </form>
            </div>
        </div>
    </div>
</div>       
        <div class="card-body">
        @if($hasPermission)
            <div class="table-container">
                <table id="usuarios" class="table table-striped table-bordered shadow-lg mt-4" style="width: 100%">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>ID DE USUARIO</th>
                            <th>ROL</th>
                            <th>NOMBRE DE USUARIO</th>
                            <th>ESTADO DE USUARIO</th>
                            <th>EMAIL</th>
                            <th>FECHA DE VENCIMIENTO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                    
                </table>
            </div>
            @else
            <div class="alert alert-danger">
                No tienes permisos para ver los usuarios.
            </div>
        @endif
    </div>

    <!-- Modal de Agregar Usuario -->
<div class="modal fade" id="modalAgregarUsuario" tabindex="-1" role="dialog" aria-labelledby="modalAgregarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgregarUsuarioLabel">Agregar Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formAgregarUsuario" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="ID_ROL">Rol:</label>
                        <select class="form-control" id="ID_ROL" name="id_rol" required>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->ID_ROL }}">{{ $rol->ROL }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="NOMBRE_USUARIO">Nombre de Usuario:</label>
                        <input type="text" class="form-control" id="NOMBRE_USUARIO" name="nombre_usuario" oninput="this.value = this.value.toUpperCase()" maxlength="70" required>
                    </div>
                    <script>
                    // Este script convierte el texto del campo de entrada en mayúsculas automáticamente
                    document.getElementById('nombre').addEventListener('input', function(e) {
                        e.target.value = e.target.value.toUpperCase();
                    });
                </script>
                    
                    <div class="form-group">
                        <label for="EMAIL">Email:</label>
                        <input type="email" class="form-control" id="EMAIL" name="email" maxlength="70" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" title="Ingrese un correo electrónico válido" required>
                    </div>
                    <div class="form-group">
                        <label for="fechaVencimiento">Fecha de Vencimiento de la Contraseña</label>
                        <input type="text" class="form-control" id="fechaVencimiento" name="fechaVencimiento" value="{{ \Carbon\Carbon::now()->addDays($diasVencimiento)->format('Y-m-d H:i:s') }}" readonly>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
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
            .card-tools, .btn, .form-inline, .modal {
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.34/moment-timezone-with-data.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#usuarios').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
            "url": "{{ route('Usuarios-fetch') }}",
            "type": "GET"
        },
        "columns": [
            { "data": "ID_USUARIO" },
            { "data": "ROL" },
            { "data": "NOMBRE_USUARIO" },
            { "data": "ESTADO_USUARIO" },
            { "data": "EMAIL" },
            { "data": "FECHA_VENCIMIENTO" },
            {
                "data": null,
                "orderable": false,
                "searchable": false,
                
         "render": function(data, type, row) {
                    return `
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editarUsuario${row.ID_USUARIO}">Editar</button>
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#eliminarUsuario${row.ID_USUARIO}">Eliminar</button>
                        <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#mostrarMas${row.ID_USUARIO}">Mostrar Mas</button>
                        
                        
                        <!-- Modal de "Mostrar más" -->
                              <div class="modal fade" id="mostrarMas${row.ID_USUARIO}" tabindex="-1" role="dialog" aria-labelledby="mostrarMas${row.ID_USUARIO}Label" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="mostrarMas${row.ID_USUARIO}Label">Detalles del Usuario</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>ID Usuario: ${row.ID_USUARIO}</p>
                                        <p>Rol: ${row.ROL}</p>
                                        <p>Nombre de Usuario:${row.NOMBRE_USUARIO}</p>
                                        <p>Estado de Usuario: ${row.ESTADO_USUARIO}</p>
                                        <p>Email: ${row.EMAIL}</p>
                                        <p>Fecha de Vencimiento:${row.FECHA_VENCIMIENTO} </p>
                                        <p>Fecha Primer Ingreso: ${row.PRIMER_INGRESO}</p>
                                        <p>Fecha Ultima Conexion: ${row.FECHA_ULTIMA_CONEXION}</p>
                                        <p>Secreto de Google: ${row.google2fa_secret}</p>
                                        <p>Intentos Fallidos: ${row.INTENTOS_FALLIDOS}</p>
                                        <p>Intentos Fallidos OTP: ${row.INTENTOS_FALLIDOS_OTP}</p>
                                        <p>Ultimos Intentos Fallidos: ${row.ULTIMOS_INTENTOS_FALLIDOS}</p>


                                    </div>
                                    <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        
                    <!-- Modal de edición de usuario -->
                    <div class="modal fade" id="editarUsuario${row.ID_USUARIO}" tabindex="-1" role="dialog" aria-labelledby="editarUsuario${row.ID_USUARIO}Label" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                    <h5 class="modal-title" id="editarUsuario${row.ID_USUARIO}Label">Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <form id="editarUsuarioForm" class="editarUsuarioForm" data-id="${row.ID_USUARIO}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="ID_ROL">Rol:</label>
                        <select class="form-control" id="ID_ROL" name="id_rol" required>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->ID_ROL }}" ${row.ID_ROL == {{ $rol->ID_ROL }} ? 'selected' : ''}>{{ $rol->ROL }}</option>

                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="NOMBRE_USUARIO">Nombre de Usuario:</label>
                        <input type="text" class="form-control" id="NOMBRE_USUARIO" name="nombre_usuario" value="${row.NOMBRE_USUARIO}" oninput="this.value = this.value.toUpperCase()" maxlength="70"  required>
                    </div>
                  
                    <div class="form-group">
                        <label for="ID_ESTADO_USUARIO">Estado de Usuario:</label>
                        <select class="form-control" id="ID_ESTADO_USUARIO" name="id_estado_usuario" required>
                            @foreach ($estadosUsuario as $estado)
                                <option value="{{ $estado->ID_ESTADO_USUARIO }}"${row.ID_ESTADO_USUARIO== {{ $estado->ID_ESTADO_USUARIO}} ? 'selected' : '' }> {{ $estado->DESCRIPCION }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="EMAIL">Email:</label>
                        <input type="email" class="form-control" id="EMAIL" name="email" value="${row.EMAIL}" maxlength="70" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" title="Ingrese un correo electrónico válido" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
               
                    @csrf
                    <button type="button" class="btn btn-warning actualizarPasswordBtn" data-id="${row.ID_USUARIO}">Actualizar Contraseña</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                </form>
            </div>
        </div>
    </div>
</div>
            <!-- Modal de eliminación de usuario -->
                        <div class="modal fade" id="eliminarUsuario${row.ID_USUARIO}" tabindex="-1" role="dialog" aria-labelledby="eliminarUsuario${row.ID_USUARIO}Label" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="eliminarUsuario${row.ID_USUARIO}Label">Eliminar Usuario</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>¿Estás seguro de que deseas eliminar al Usuario "${row.NOMBRE_USUARIO}"?</p>
                                        <form action="{{ route('usuarios.eliminar') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="P_ID_USUARIO" value="${row.ID_USUARIO}">
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

            // Captura el evento de búsqueda en la tabla y actualiza el campo oculto
            table.on('search.dt', function() {
                var searchValue = table.search();  // Captura el valor de búsqueda actual
                $('#searchUsuarioInput').val(searchValue);  // Asigna el valor al campo oculto del formulario
            });

            // Verifica el valor antes de enviar el formulario
            $('#reporteUsuarioForm').on('submit', function(e) {
                var searchValue = $('#searchUsuarioInput').val();
            });
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
    $(document).on('input', 'input[name="nombre_usuario"], input[name="email"]', function() {
    validarInput(this);
});
    const tituloFields = document.querySelectorAll('input[name="nombre_usuario"], input[name="email"]');
    tituloFields.forEach(function(input) {
        input.addEventListener('input', function() {
            validarInput(input);
        });
    });

            $('#modalAgregarUsuario').on('show.bs.modal', function (event) {
                var modal = $(this);
                var today = moment().tz("America/Tegucigalpa");
                var diasVencimiento = $('#diasVencimiento').val();
                var expirationDate = today.clone().add(diasVencimiento, 'days');
                var formattedDate = expirationDate.format('YYYY-MM-DD HH:mm:ss');
                modal.find('#FECHA_VENCIMIENTO').val(formattedDate);
            });

            $('.modal').on('show.bs.modal', function (event) {
                var modal = $(this);
                var expirationField = modal.find('#FECHA_VENCIMIENTO');
                if (expirationField.length && expirationField.val() !== '') {
                    var expirationDate = moment(expirationField.val()).tz("America/Tegucigalpa");
                    var formattedDate = expirationDate.format('YYYY-MM-DD HH:mm:ss');
                    expirationField.val(formattedDate);
                }
            });

            $('#actualizarContraseña').on('click', function() {
                var diasVencimiento = $('#diasVencimiento').val();
                var today = new Date();
                today.setDate(today.getDate() + parseInt(diasVencimiento));
                var formattedDate = today.getFullYear() + '-' +
                                    ('0' + (today.getMonth() + 1)).slice(-2) + '-' +
                                    ('0' + today.getDate()).slice(-2) + ' ' +
                                    ('0' + today.getHours()).slice(-2) + ':' +
                                    ('0' + today.getMinutes()).slice(-2) + ':' +
                                    ('0' + today.getSeconds()).slice(-2);
                $('#FECHA_VENCIMIENTO').val(formattedDate);
            });

            // AJAX form submission for creating a user
            $('#formAgregarUsuario').on('submit', function(event) {
                event.preventDefault();
                
                $.ajax({
                    url: "{{ route('usuarios.crear') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.success,
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('Usuarios') }}";
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.error,
                            });
                        }
                    },
                    error: function(response) {
                        var errors = response.responseJSON.errors;
                        var errorMessage = '';
                        for (var key in errors) {
                            if (errors.hasOwnProperty(key)) {
                                errorMessage += errors[key][0] + '\n';
                            }
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage,
                        });
                    }
                });
            });
            
$(document).on('submit', '.editarUsuarioForm', function(event) {
    event.preventDefault();
    
    var id = $(this).data('id');
    var formData = new FormData(this);  // Corrige la referencia a formData
    
    $.ajax({
        url: '{{ route("usuarios.editar", ":id") }}'.replace(':id', id),
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

$(document).on('click', '.actualizarPasswordBtn', function(event) {
    event.preventDefault();

    // Obtén el ID del usuario del botón
    var id = $(this).data('id');

    // Confirmación antes de proceder
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esto actualizará la contraseña del usuario.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, actualizar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("usuarios.generarPassword", ":id") }}'.replace(':id', id),
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'  // Necesario para la protección CSRF
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
        }
    });
});

            // AJAX form submission for deleting a user
            $(document).on('submit', '.eliminar-usuario-form', function(event) {


                event.preventDefault();
                
                var formData = new FormData(this);

                $.ajax({
                    url: this.action,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
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
                                location.reload();  // Recarga la página después de cerrar SweetAlert
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

            // SweetAlert configurations for success and error messages
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: '{{ session('success') }}',
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ session('error') }}',
                });
            @endif
        });
    </script>
@stop
