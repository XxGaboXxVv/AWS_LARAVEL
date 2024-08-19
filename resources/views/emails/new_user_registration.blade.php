<!DOCTYPE html>
<html>
<head>
    <title>Nuevo usuario pendiente de aprobación</title>
</head>
<body>
    <h1>Nuevo usuario registrado</h1>
    <p>Un nuevo usuario se ha registrado en el sistema y está pendiente de aprobación.</p>
    <p><strong>Nombre:</strong> {{ $nombre }}</p>
    <p><strong>Email:</strong> {{ $email }}</p>
    <p><strong>Id:</strong> {{ $userId }}</p>
    <p>Por favor, revisa el sistema y decida si aprueba o rechaza el acceso del usuario.</p>
    <a href="{{ route('approve.user', ['userId' => $userId]) }}"> click para aprobar acceso</a>
</body>
</html>
