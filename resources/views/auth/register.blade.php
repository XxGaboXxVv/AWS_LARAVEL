@extends('adminlte::auth.register')

@section('auth_body')
<p class="login-box-msg">{{ __('Registrate') }}</p>

<form method="POST" action="{{ route('register') }}">
    @csrf

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="form-group">
        <label for="NOMBRE_USUARIO">{{ __('Nombre de Usuario') }}</label>
        <input id="NOMBRE_USUARIO" type="text" class="form-control @error('NOMBRE_USUARIO') is-invalid @enderror" name="NOMBRE_USUARIO" value="{{ old('NOMBRE_USUARIO') }}" maxlength="70" pattern="[A-Z\s]+" title="Solo se permiten letras mayúsculas y espacios" oninput="this.value = this.value.toUpperCase()" onpaste="return false" oncopy="return false" oncut="return false" required autofocus>

        @error('NOMBRE_USUARIO')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <script>
    // Este script convierte el texto del campo de entrada en mayúsculas automáticamente
    document.getElementById('nombre').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });
</script>

    <div class="form-group">
        <label for="EMAIL">{{ __('Email') }}</label>
        <input id="EMAIL" type="email" class="form-control @error('EMAIL') is-invalid @enderror" name="EMAIL" value="{{ old('EMAIL') }}" maxlength="70" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$" title="Ingrese un correo electrónico válido" onpaste="return false" oncopy="return false" oncut="return false" required>

        @error('EMAIL')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="form-group">
        <label for="CONTRASEÑA">{{ __('Contraseña') }}</label>
        <div class="input-group mb-3">
            <input id="CONTRASEÑA" type="password" class="form-control @error('CONTRASEÑA') is-invalid @enderror" name="CONTRASEÑA" onpaste="return false" oncopy="return false" oncut="return false" required> 
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-eye-slash" id="toggle-password" style="cursor: pointer;"></span>
                </div>
            </div>
        </div>

        @error('CONTRASEÑA')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="form-group">
        <label for="CONTRASEÑA-confirm">{{ __('Confirmar Contraseña') }}</label>
        <div class="input-group mb-3">
            <input id="CONTRASEÑA-confirm" type="password" class="form-control" name="CONTRASEÑA_confirmation" onpaste="return false" oncopy="return false" oncut="return false" required>                   
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-eye-slash" id="toggle-password-confirm" style="cursor: pointer;"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- /.col -->
    <div class="col-5">
        <button type="submit" class="btn btn-primary btn-block">{{ __('Registrarse') }}</button>
    </div>
    <!-- /.col -->
</form>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Restricción para evitar caracteres especiales no deseados en el campo de EMAIL
        const email = document.querySelector('input[name="EMAIL"]');
        email.addEventListener('keypress', function(e) {
            const invalidChars = ['<', '>', '(', ')', '{', '}', '[', ']', '=', ';'];
            if (invalidChars.includes(e.key)) {
                e.preventDefault();
            }
        });

        // Restricción para evitar caracteres especiales no deseados en el campo de NOMBRE_USUARIO
        document.getElementById('NOMBRE_USUARIO').addEventListener('input', function (e) {
            const input = e.target;
            // Solo permitimos letras mayúsculas y espacios
            const validChars = /^[a-zA-Z\s]*$/;
            if (!validChars.test(input.value)) {
                input.value = input.value.replace(/[^A-Z\s]/g, '');
            }
        });

        // Mostrar/ocultar contraseña
        document.getElementById('toggle-password').addEventListener('click', function () {
            const passwordInput = document.getElementById('CONTRASEÑA');
            const icon = this;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });

        document.getElementById('toggle-password-confirm').addEventListener('click', function () {
            const passwordConfirmInput = document.getElementById('CONTRASEÑA-confirm');
            const icon = this;
            if (passwordConfirmInput.type === 'password') {
                passwordConfirmInput.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                passwordConfirmInput.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
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

    }

    // Aplicar validaciones en los campos de descripción y título
    const descripcionFields = document.querySelectorAll('textarea[name="descripcion"]');
    descripcionFields.forEach(function(input) {
        input.addEventListener('input', function() {
            validarInput(input);
        });
    });

    const tituloFields = document.querySelectorAll('input[name="NOMBRE_USUARIO"], input[name="EMAIL"], input[name="CONTRASEÑA"], input[name="CONTRASEÑA_confirmation"]');
    tituloFields.forEach(function(input) {
        input.addEventListener('input', function() {
            validarInput(input);
        });
    });
    });
</script>
@stop
