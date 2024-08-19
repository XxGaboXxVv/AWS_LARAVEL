@extends('adminlte::auth.login')

@section('auth_body')
    <p class="login-box-msg">{{ __('Ingresa para Iniciar Sesion ') }}</p>
    
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulario de inicio de sesión -->
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control" placeholder="{{ __('Email') }}" required autofocus 
                   maxlength="70" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$" onpaste="return false" oncopy="return false" oncut="return false">
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
        </div>

        <div class="input-group mb-3">
            <input type="password" name="password" id="password" class="form-control" placeholder="{{ __('Contraseña') }}" required 
                   onpaste="return false" oncopy="return false" oncut="return false">
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
                <div class="input-group-text">
                    <span class="fas fa-eye-slash" id="toggle-password" style="cursor: pointer;"></span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block">{{ __('Iniciar Sesion ') }}</button>
            </div>
        </div>
    </form>
@endsection

@section('auth_footer')
<p class="mb-1">
    <a href="{{ route('password.request') }}">{{ __('¿Olvido Su Contraseña?') }}</a>
</p>
<p class="mb-0">
    <a href="{{ route('register') }}" class="text-center">{{ __('Crear Una Cuenta Nueva') }}</a>
</p>

@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.querySelector('#toggle-password');
        const password = document.querySelector('#password');
        const email = document.querySelector('input[name="email"]');

        togglePassword.addEventListener('click', function(e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            this.classList.toggle('fa-eye-slash');
            this.classList.toggle('fa-eye');
        });

        email.addEventListener('keypress', function(e) {
            const invalidChars = ['<', '>', '(', ')', '{', '}', '[', ']', '=', ';'];
            if (invalidChars.includes(e.key)) {
                e.preventDefault();
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

    const tituloFields = document.querySelectorAll('input[name="email"], input[name="password"]');
    tituloFields.forEach(function(input) {
        input.addEventListener('input', function() {
            validarInput(input);
        });
    });
    });
</script>
@endsection
