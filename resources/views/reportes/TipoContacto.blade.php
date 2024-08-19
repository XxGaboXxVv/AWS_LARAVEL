html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Tipo de Contacto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .logo {
            height: 80px; /* Tamaño del logo ajustado */
        }
        .title {
            text-align: left;
            margin-left: 20px;
        }
        h1 {
            margin: 0;
            font-size: 25px; /* Título principal más pequeño */
            line-height: 1.2;
        }
        h2 {
            margin: 0;
            font-size: 16px; /* Subtítulo más pequeño */
            line-height: 1.2;
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
            width: 90%; /* Ajusta el ancho de la tabla */
            border-collapse: collapse;
            margin: 0 auto; /* Centra la tabla */
            font-size: 12px; /* Reduce el tamaño de la fuente */
        }
        th, td {
            border: 1px solid black;
            padding: 6px; /* Reduce el padding */
            text-align: left;
        }
        th {
            background-color: #f2f2f2; /* Color de fondo para los encabezados */
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('vendor/adminlte/dist/img/LasAcacias.png') }}" alt="Logo" class="logo">
        <div class="title">
            <h1>VILLA LAS ACACIAS</h1>
            <h1>RESIDENCIAL EL SAUCE</h1>
            <h2>Reporte de Tipo de Contacto</h2>
        </div>
    </div>


    <table>
        <thead>
            <tr>
                <th>#</th> <!-- Cambiado el encabezado a "#" -->
                <th>DESCRIPCION DE TIPO DE CONTACTO</th>
                </tr>
        </thead>
        <tbody>
        
        @foreach($tipoContactos as $index => $tipoContacto)
            <tr>
                <td>{{ $index + 1 }}</td> <!-- Mostrar el índice del bucle como número de orden -->
                <td>{{ $tipoContacto["DESCRIPCION"] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
    <p>Fecha y Hora: {{ \Carbon\Carbon::now()->setTimezone('America/Tegucigalpa')->format('Y-m-d H:i:s') }}</p>
        <script type="text/php">
            if (isset($pdf)) {
                $pdf->page_script('
                    if ($PAGE_COUNT >= 1) {
                        $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
                        $size = 10;
                        $pageText = "Página " . $PAGE_NUM . " de " . $PAGE_COUNT;
                        $pdf->text(270, 800, $pageText, $font, $size);
                    }
                ');
            }
        </script>
    </div>
</body>
</html>
