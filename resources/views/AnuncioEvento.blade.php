@extends('adminlte::page')

@section('title', 'Gestión de Anuncios y Eventos')

@section('content_header')
    <h1>Anuncios y Eventos</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-11">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Gestión de Anuncios y Eventos</h3>
                    <button type="button" class="btn btn-primary ml-auto" data-toggle="modal" data-target="#crearEventoModal">Nuevo</button>
                </div>
                <div class="card-body">
                @if($hasPermission)
                    <div class="timeline">
                        @foreach($anunciosEventos as $evento)
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> {{ $evento['FECHA_HORA'] ? \Carbon\Carbon::parse($evento["FECHA_HORA"])->setTimezone('America/Tegucigalpa')->format('Y-m-d H:i') : '' }}</span>
                                <h3 class="timeline-header">
                                    <a href="#" class="titulo-evento" data-toggle="modal" data-target="#verEventoModal" data-titulo="{{ $evento['TITULO'] }}" data-descripcion="{{ $evento['DESCRIPCION'] }}" data-imagen="{{ asset('images/'.$evento['IMAGEN']) }}" data-fecha-hora="{{ \Carbon\Carbon::parse($evento["FECHA_HORA"])->setTimezone('America/Tegucigalpa')->format('Y-m-d H:i') }}">{{ $evento['TITULO'] }}</a>
                                </h3>
                                {{ $evento['DESCRIPCION'] }}
                                <div class="timeline-body">
                                    @if($evento['IMAGEN'])
                                        <img src="{{ asset('images/'.$evento['IMAGEN']) }}" alt="Imagen" class="img-ajustada">
                                    @endif
                                </div>
                                <div class="timeline-footer">
                                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#editarEventoModal" data-id="{{ $evento['ID_ANUNCIOS_EVENTOS'] }}" data-titulo="{{ $evento['TITULO'] }}" data-descripcion="{{ $evento['DESCRIPCION'] }}" data-estado-id="{{ $evento['ID_ESTADO_ANUNCIO_EVENTO'] }}" data-imagen="{{ asset('images/'.$evento['IMAGEN']) }}">Editar</button>
                                    <form action="{{ route('eliminar_anuncio_evento') }}" method="POST" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="ID_ANUNCIOS_EVENTOS" value="{{ $evento['ID_ANUNCIOS_EVENTOS'] }}">
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @else
            <div class="alert alert-danger">
                No tienes permisos para ver los Anuncios Y Eventos.
            </div>
        @endif
                </div>
                <div class="card-footer">
                    <div class="pagination justify-content-center">
                        {!! $paginador->links() !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para crear un evento -->
        <div class="modal fade" id="crearEventoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Nuevo Anuncio o Evento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('guardar_anuncio_evento') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="titulo">Título:</label>
                                <input type="text" name="titulo" id="titulo" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="ID_ESTADO_ANUNCIO_EVENTO">Estado del Anuncio:</label>
                                <select class="form-control" id="ID_ESTADO_ANUNCIO_EVENTO" name="ID_ESTADO_ANUNCIO_EVENTO" required>
                                    @foreach ($estadosAnuncio as $estado)
                                        <option value="{{ $estado->ID_ESTADO_ANUNCIO_EVENTO }}">{{ $estado->DESCRIPCION }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="descripcion">Descripción:</label>
                                <textarea name="descripcion" id="descripcion" class="form-control" rows="4"  maxlength="200" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="imagen">Imagen:</label>
                                <input type="file" name="imagen" id="imagen" class="form-control-file" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para editar un evento -->
        <div class="modal fade" id="editarEventoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Editar Anuncio o Evento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="" id="editarEventoForm" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" id="edit-id">
                            <div class="form-group">
                                <label for="edit-titulo">Título:</label>
                                <input type="text" name="titulo" id="edit-titulo" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-descripcion">Descripción:</label>
                                <textarea name="descripcion" id="edit-descripcion" class="form-control" rows="4"  maxlength="200" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="edit-ID_ESTADO_ANUNCIO_EVENTO">Estado del Anuncio:</label>
                                <select class="form-control" id="edit-ID_ESTADO_ANUNCIO_EVENTO" name="ID_ESTADO_ANUNCIO_EVENTO" required>
                                    @foreach ($estadosAnuncio as $estado)
                                        <option value="{{ $estado->ID_ESTADO_ANUNCIO_EVENTO }}">{{ $estado->DESCRIPCION }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit-imagen">Imagen:</label>
                                <input type="file" name="imagen" id="edit-imagen" class="form-control-file" accept="image/*">
                                <br>
                                <img id="imagen-actual" src="" alt="Imagen actual" class="img-ajustada" style="display: none;">
                            </div>
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para ver un evento -->
        <div class="modal fade" id="verEventoModal" tabindex="-1" role="dialog" aria-labelledby="verEventoLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="verEventoLabel">Detalle del Anuncio o Evento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="ver-fecha-hora"><i class="fas fa-clock"></i></p> <!-- Aquí se muestra la fecha y hora con el ícono de reloj -->
                        <h5 id="ver-titulo"></h5>
                        <p id="ver-descripcion"></p>
                        <img id="ver-imagen" src="" alt="Imagen del evento" class="img-ajustada" style="display: none;">
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('css')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<style>
    .pagination {
        display: flex;
        justify-content: center;
    }
    .pagination .page-item .page-link {
        font-size: 14px;
        padding: 5px 10px;
    }
    .pagination .page-item .page-link:hover {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }
</style>
    <link rel="stylesheet" href="/css/admin_custom.css">
    <style>
        .img-ajustada {
            max-width: 100%;
            max-height: 300px; /* Ajusta el valor según tus necesidades */
            object-fit: contain;
        }
    </style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.34/moment-timezone-with-data.min.js"></script>
<script>
$(document).ready(function() {
            $('#editarEventoModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var titulo = button.data('titulo');
                var descripcion = button.data('descripcion');
                var estadoId = button.data('estado-id');
                var imagen = button.data('imagen');

                var modal = $(this);
                modal.find('#edit-id').val(id);
                modal.find('#edit-titulo').val(titulo);
                modal.find('#edit-descripcion').val(descripcion);
                modal.find('#edit-ID_ESTADO_ANUNCIO_EVENTO').val(estadoId);
                modal.find('#imagen-actual').attr('src', imagen).show();
            });

            $('#verEventoModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var titulo = button.data('titulo');
                var descripcion = button.data('descripcion');
                var imagen = button.data('imagen');
                var fechaHora = button.data('fecha-hora');

                var modal = $(this);
                modal.find('#ver-titulo').text(titulo);
                modal.find('#ver-descripcion').text(descripcion);
                modal.find('#ver-fecha-hora').html('<i class="fas fa-clock"></i> ' + fechaHora);
                
                if (imagen) {
                    modal.find('#ver-imagen').attr('src', imagen).show();
                } else {
                    modal.find('#ver-imagen').hide();
                }
            });

           
    $('#editarEventoModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var titulo = button.data('titulo');
        var descripcion = button.data('descripcion');
        var estadoId = button.data('estado-id');
        var imagen = button.data('imagen'); // Obtén la URL de la imagen actual

        var modal = $(this);
        modal.find('#edit-id').val(id);
        modal.find('#edit-titulo').val(titulo);
        modal.find('#edit-descripcion').val(descripcion);
        modal.find('#edit-ID_ESTADO_ANUNCIO_EVENTO').val(estadoId);
        modal.find('#imagen-actual').attr('src', imagen).show(); // Muestra la imagen actual

        var formAction = '{{ route("actualizar_anuncio_evento", ["id" => ":id"]) }}';
        formAction = formAction.replace(':id', id);
        modal.find('form').attr('action', formAction);
    });

    // Previsualizar nueva imagen seleccionada
    $('#edit-imagen').on('change', function() {
        var input = this;
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagen-actual').attr('src', e.target.result).show();
            }
            reader.readAsDataURL(input.files[0]);
        }
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

        // Convertir texto a mayúsculas y evitar caracteres especiales no deseados
        input.value = input.value.toUpperCase().replace(/[<>(){}[\]=;%^&*,"'+-:]/g, '');
    }

    // Aplicar validaciones en los campos de descripción y título
    const descripcionFields = document.querySelectorAll('textarea[name="descripcion"]');
    descripcionFields.forEach(function(input) {
        input.addEventListener('input', function() {
            validarInput(input);
        });
    });

    const tituloFields = document.querySelectorAll('input[name="titulo"], input[name="edit-titulo"]');
    tituloFields.forEach(function(input) {
        input.addEventListener('input', function() {
            validarInput(input);
        });
    });

    // Función para convertir a mayúsculas
 function convertirAMayusculas() {
        // Especificar los campos que deben ser convertidos a mayúsculas
        var fieldsToConvert = ['titulo','edit-titulo'];
        fieldsToConvert.forEach(function(fieldId) {
            var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
            inputs.forEach(function(input) {
                input.value = input.value.toUpperCase();
            });
        });
    }

    // Asignar evento input a los campos específicos
    ['titulo','edit-titulo'].forEach(function(fieldId) {
        document.querySelectorAll('input[id^="' + fieldId + '"]').forEach(function(input) {
            input.addEventListener('input', function() {
                input.value = input.value.toUpperCase();
            });
        });
    });

// Función para limitar el tamaño de los caracteres en ciertos campos
function limitarTamañoCaracteres() {
        // Especificar los campos y sus tamaños máximos
        var fieldsWithMaxLength = {
            'titulo': 50,
            'edit-titulo':70
            
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
    
    limitarTamañoCaracteres();


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

    @error('email')
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ $message }}',
        });
    @enderror

    // AJAX form submission for creating an event
    $('#crearEventoModal form').on('submit', function(event) {
        event.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: '{{ route("guardar_anuncio_evento") }}',
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
                        window.location.reload();
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
                var response = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.error || 'Hubo un problema al guardar el evento.',
                });
            }
        });
    });

    // AJAX form submission for editing an event
    $('#editarEventoModal form').on('submit', function(event) {
        event.preventDefault();

        var formData = new FormData(this);
        var id = $('#edit-id').val();

        $.ajax({
            url: '{{ route("actualizar_anuncio_evento", ["id" => ":id"]) }}'.replace(':id', id),
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
                        window.location.reload();
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
                var response = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.error || 'Hubo un problema al actualizar el evento.',
                });
            }
        });
    });

    // Form submission for deleting an event
    $('form[action="{{ route("eliminar_anuncio_evento") }}"]').on('submit', function(event) {
        event.preventDefault();

        var form = this;

        Swal.fire({
            title: '¿Estás seguro?',
            text: '¡No podrás revertir esto!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

            $('#verEventoModal').on('hidden.bs.modal', function (e) {
                $(this).find('form')[0].reset();
            });
        });
        
    </script>
@stop


