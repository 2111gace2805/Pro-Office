<!DOCTYPE html>
<html>

<head>
    <title>{{ $content['subject'] }}</title>
    <style type="text/css">
    .g-container {
        padding: 15px 30px;
    }
    </style>
</head>

<body>
    <div class="g-container">
        <h1>{{ $content['subject'] }}</h1>
        <p>{{ $content['body'] }}</p>
        @if( $anulacion )
            <p>Por este medio enviamos su factura electrónica JSON y PDF, que registra la anulación del DTE realizado en {{ get_option('company_name') }}</p>
        @else
            <p>Por este medio enviamos su factura electrónica JSON y PDF, que registra su compra realizada en {{ get_option('company_name') }}</p>
        @endif
        <p>Los documentos adjuntos cuentan con las especificaciones requeridas por el Ministerio de Hacienda, por lo que tienen el mismo respaldo tributario legal que los documentos físicos.</p>
        @if( $anulacion )
            <p>Adjunto encontrará el archivo JSON de la factura anulada.</p>
        @else
            <p>Adjunto encontrará el archivo JSON de la factura.</p>
        @endif
        {{-- <a href="{{ route('download.json', ['invoice' => $invoice, 'download' => 1]) }}">Descargar JSON</a> <!-- Enlace para descargar el JSON -->
        <br>
        <a href="{{ route('download.pdf', ['invoice' => $invoice, 'download' => 1]) }}">Descargar PDF</a> --}}
    </div>
</body>

</html>