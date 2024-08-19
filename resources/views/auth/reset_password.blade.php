@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('auth_header', __('adminlte::adminlte.reset_password'))

@section('auth_body')
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

<form action="{{ route('password.update') }}" method="post">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div class="input-group mb-3">
        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror"
               placeholder="Contraseña Temporal" id="current_password">
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
            </div>
            <div class="input-group-text toggle-password" style="cursor: pointer;">
                <span class="fas fa-eye-slash"></span>
            </div>
        </div>
        @error('current_password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="input-group mb-3">
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
               placeholder="{{ __('adminlte::adminlte.password') }}" id="password">
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
            </div>
            <div class="input-group-text toggle-password" style="cursor: pointer;">
                <span class="fas fa-eye-slash"></span>
            </div>
        </div>
        @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="input-group mb-3">
        <input type="password" name="password_confirmation" class="form-control"
               placeholder="{{ __('adminlte::adminlte.retype_password') }}" id="password_confirmation">
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
            </div>
            <div class="input-group-text toggle-password" style="cursor: pointer;">
                <span class="fas fa-eye-slash"></span>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-block {{ config('adminlte.classes_auth_btn', 'btn-flat btn-primary') }}">
        <span class="fas fa-sync-alt"></span>
        {{ __('adminlte::adminlte.reset_password') }}
    </button>
</form>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const togglePasswordIcons = document.querySelectorAll('.toggle-password');
    togglePasswordIcons.forEach(function(togglePasswordIcon) {
        togglePasswordIcon.addEventListener('click', function () {
            const input = this.parentElement.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.querySelector('span').classList.toggle('fa-eye');
            this.querySelector('span').classList.toggle('fa-eye-slash');
        });
    });
});
</script>
@stop
