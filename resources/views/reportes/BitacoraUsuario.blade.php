<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Bitácoras de Usuario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .page {
            page-break-after: always;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            height: 100px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .details {
            text-align: right;
            margin-bottom: 10px;
        }
        .title {
            text-align: center;
            margin-bottom: 20px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: right;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    @foreach($pages as $pageIndex => $bitacoraPage)
        <div class="page">
            <div class="header">
                <img src="{{ public_path('vendor/adminlte/dist/img/LasAcacias.png') }}" alt="Logo" class="logo">
                <h2>VILLA LAS ACACIAS</h2>
                <h3>RESIDENCIAL EL SAUCE</h3>
            </div>

            <div class="details">
                <p>Fecha y Hora: {{ \Carbon\Carbon::now()->setTimezone('America/Tegucigalpa')->format('Y-m-d H:i:s') }}</p>
            </div>

            <div class="title">
                <h1>Reporte de Bitácoras de Usuarios</h1>
                <p>Página {{ $pageIndex + 1 }} de {{ count($pages) }}</p>
            </div>

            <table>
                <thead>
                    <tr>
                    <th>#</th> <!-- Cambiado el encabezado a "#" -->
                    <th>ID USUARIO</th>
                        <th>ID OBJETO</th>
                        <th>FECHA</th>
                        <th>ACCION</th>
                        <th>DESCRIPCION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bitacoraPage  as $index => $visita)
                        <tr>
                        <td>{{ $index + 1 }}</td> <!-- Mostrar el índice del bucle como número de orden -->
                        <td>{{ $visita["ID_USUARIO"] }}</td>
                            <td>{{ $visita["ID_OBJETO"] }}</td>
                            <td>{{ $visita["FECHA"] ? \Carbon\Carbon::parse($visita["FECHA"])->setTimezone('America/Tegucigalpa')->format('Y-m-d H:i:s') : '' }}</td>
                            <td>{{ $visita["ACCION"] }}</td>
                            <td>{{ $visita["DESCRIPCION"] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="footer">
                Página {{ $pageIndex + 1 }} de {{ count($pages) }}
            </div>
        </div>
    @endforeach
</body>
</html>
