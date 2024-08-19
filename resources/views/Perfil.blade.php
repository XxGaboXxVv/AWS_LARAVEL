@extends('adminlte::page')

@section('title', 'Perfil')

@section('content_header')
    <h1>Perfil</h1>
@stop

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">Información de Perfil</div>
            <div class="card-body">
                <div class="form-group">
                    <label for="NOMBRE_USUARIO">Nombre</label>
                    <input type="text" class="form-control" id="NOMBRE_USUARIO" name="NOMBRE_USUARIO" value="{{ $userData['NOMBRE_USUARIO'] }}" readonly>
                </div>

                <div class="form-group">
                    <label for="EMAIL">Email</label>
                    <input type="email" class="form-control" id="EMAIL" name="EMAIL" value="{{ $userData['EMAIL'] }}" readonly>
                </div>

                <div class="form-group">
                    <label for="ESTADO_USUARIO">Estado</label>
                    <input type="text" class="form-control" id="ESTADO_USUARIO" name="ESTADO_USUARIO" value="{{ $userData['ESTADO_USUARIO'] }}" readonly>
                </div>

                <form method="POST" action="{{ route('perfil.2fa') }}">
                    @csrf
                    <div class="form-group">
                        <label for="2fa">Autenticación de Dos Factores</label>
                        <input type="checkbox" id="2fa" name="2fa" {{ $userData['google2fa_secret'] ? 'checked' : '' }} onchange="this.form.submit()">
                        <span>{{ $userData['google2fa_secret'] ? 'Desactivar' : 'Activar' }} 2FA</span>
                    </div>
                </form>

                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editProfileModal">
                    Editar Perfil
                </button>

                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#changePasswordModal">
                    Cambiar Contraseña
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para editar el perfil -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Editar Perfil</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditProfile">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_NOMBRE_USUARIO">Nombre</label>
                            <input type="text" class="form-control" id="edit_NOMBRE_USUARIO" name="NOMBRE_USUARIO" value="{{ $userData['NOMBRE_USUARIO'] }}" pattern="[A-Z\s]+" title="Solo se permiten letras mayúsculas y espacios" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_EMAIL">Email</label>
                            <input type="email" class="form-control" id="edit_EMAIL" name="EMAIL" value="{{ $userData['EMAIL'] }}" maxlength="70" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" title="Ingrese un correo electrónico válido" required>
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

    <!-- Modal para cambiar contraseña -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formChangePassword">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="changePasswordModalLabel">Cambiar Contraseña</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="current_password">Contraseña Actual</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="contraseña_actual" required>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-eye-slash" id="toggle-current-password" style="cursor: pointer;"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="new_password">Nueva Contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="nueva_contraseña" required>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-eye-slash" id="toggle-new-password" style="cursor: pointer;"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="new_password_confirmation">Confirmar Nueva Contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password_confirmation" name="nueva_contraseña_confirmation" required>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-eye-slash" id="toggle-confirm-password" style="cursor: pointer;"></span>
                                    </div>
                                </div>
                            </div>
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
@stop

@section('css')
    <style>
        .input-group-text {
            cursor: pointer;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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

        document.addEventListener('DOMContentLoaded', function () {
            function togglePasswordVisibility(toggleElementId, passwordFieldId) {
                const toggleElement = document.getElementById(toggleElementId);
                const passwordField = document.getElementById(passwordFieldId);

                toggleElement.addEventListener('click', function () {
                    if (passwordField.type === 'password') {
                        passwordField.type = 'text';
                        toggleElement.classList.remove('fa-eye-slash');
                        toggleElement.classList.add('fa-eye');
                    } else {
                        passwordField.type = 'password';
                        toggleElement.classList.remove('fa-eye');
                        toggleElement.classList.add('fa-eye-slash');
                    }
                });
            }

            togglePasswordVisibility('toggle-current-password', 'current_password');
            togglePasswordVisibility('toggle-new-password', 'new_password');
            togglePasswordVisibility('toggle-confirm-password', 'new_password_confirmation');
           
            // AJAX para actualizar perfil
            $('#formEditProfile').on('submit', function (e) {
                e.preventDefault();
                let formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('perfil.actualizar') }}",
                    method: "POST",
                    data: formData,
                    success: function (response) {
                        $('#editProfileModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.success,
                        }).then(() => {
                            location.reload(); // Recargar la página para reflejar los cambios
                        });
                    },
                    error: function (response) {
                        let errors = response.responseJSON.errors;
                        let errorMessages = '';
                        for (let field in errors) {
                            errorMessages += `${errors[field][0]}<br>`;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMessages,
                        });
                    }
                });
            });

            // AJAX para cambiar contraseña
            $('#formChangePassword').on('submit', function (e) {
                e.preventDefault();
                let formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('perfil.cambiarContraseña') }}",
                    method: "POST",
                    data: formData,
                    success: function (response) {
                        $('#changePasswordModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.success,
                        }).then(() => {
                            location.reload(); // Recargar la página para reflejar los cambios
                        });
                    },
                    error: function (response) {
                        let errors = response.responseJSON.errors;
                        let errorMessages = '';
                        for (let field in errors) {
                            errorMessages += `${errors[field][0]}<br>`;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMessages,
                        });
                    }
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
    const tituloFields = document.querySelectorAll('input[name="NOMBRE_USUARIO"], input[name="EMAIL"]');
    tituloFields.forEach(function(input) {
        input.addEventListener('input', function() {
            validarInput(input);
        });
    });

            // Función para convertir a mayúsculas
 function convertirAMayusculas() {
        // Especificar los campos que deben ser convertidos a mayúsculas
        var fieldsToConvert = ['edit_NOMBRE_USUARIO'];
        fieldsToConvert.forEach(function(fieldId) {
            var inputs = document.querySelectorAll('input[id^="' + fieldId + '"]');
            inputs.forEach(function(input) {
                input.value = input.value.toUpperCase();
            });
        });
    }

    // Asignar evento input a los campos específicos
    ['edit_NOMBRE_USUARIO'].forEach(function(fieldId) {
        document.querySelectorAll('input[id^="' + fieldId + '"]').forEach(function(input) {
            input.addEventListener('input', function() {
                input.value = input.value.toUpperCase();
            });
        });
    });

 // Función para restringir caracteres especiales
 function restringirCaracteres() {
        // Especificar los campos que deben ser restringidos
        var fieldsToRestrict = ['edit_NOMBRE_USUARIO'];
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
        var fieldsToRestrict = ['edit_EMAIL'];
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
            'edit_NOMBRE_USUARIO': 70,
            'edit_EMAIL':70,
           
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
    restringirCaracteresContactoDNI();
    limitarTamañoCaracteres();

});
        
    </script>
@stop
