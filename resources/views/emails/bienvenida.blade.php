<!DOCTYPE html>
<html>
<head>
    <title>{{ $details['title'] }}</title>
</head>
<body>
    <h1>{{ $details['title'] }}</h1>
    <p>{{ $details['body'] }}</p>
    <p><a href="{{ $details['link'] }}">Restablecer Contraseña</a></p>
    @if(isset($details['temporaryPassword']))
        <p>Su contraseña temporal es: <strong> {{ $details['temporaryPassword'] }}</strong> </p>
    @endif
</body>
</html>
