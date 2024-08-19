<!DOCTYPE html>
<html>
<head>
    <title>Su nueva contraseña</title>
</head>
<body>
<h1>Feliz Dia Querido Usuario</h1>
    <p>El motivo del correo es para informarle que se ha generado una nueva contraseña para su cuenta. Aquí está su nueva contraseña:</p>
    <p><strong>{{ $nuevaContraseña }}</strong></p>
    <p>Le recomendamos que la cambie después de ingresar, si desea hacerlo en este momento, podra realizarlo pulsando el link</p>
    <p><a href="{{ $details['link'] }}">Restablecer Contraseña</a></p>
    <p>Saludos,</p>
    <p>Administracion Villa las Acacias</p>
</body>
</html>
