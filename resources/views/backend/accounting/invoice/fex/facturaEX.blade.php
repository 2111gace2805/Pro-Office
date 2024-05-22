<!--
<!DOCTYPE html>
<html lang="es">

@php
    $topFooter = 85.7;
    $topHeader = 0.5;
    $topHeaders = 3.1;
    $topHeader_ = 6;
    $currency = currency();
    $noSujeto = '';
    $exento = '';
    $gravado = '';
    $noSujetoSum = 0;
    $exentoSum = 0;
    $gravadoSum = 0;
    $top = 41.5;
    $topH       = 0;
@endphp

<head>
    <title>{{ get_option('site_title', 'Invoice') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    {{-- <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"> --}}
    {{-- <link rel="stylesheet" href="{{ asset('public/backend/assets/css/invoice.css') }}"> --}}

    <style>
        html {
            padding: 0;
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .linea0 {
            position: absolute;
            top: <?php echo $topHeaders + 0.3 * 0.1 . '%'; ?>;
        }

        .linea_qr{
            position: absolute;
            top: <?php echo $topHeaders + 3.3 * 3.6 . '%'; ?>;
        }

        .linea1 {
            position: absolute;
            top: <?php echo $topHeader + 2.6 * 3.8 . '%'; ?>;
        }

        .linea2 {
            position: absolute;
            top: <?php echo $topHeader + 2.6 * 4.8 . '%'; ?>;
        }

        .linea3 {
            position: absolute;
            top: <?php echo $topHeader + 2.6 * 6 . '%'; ?>;
        }

        .linea4 {
            position: absolute;
            top: <?php echo $topHeader + 2.6 * 6.7 . '%'; ?>;
        }

        .linea5 {
            position: absolute;
            top: <?php echo $topHeader + 2.6 * 8.01 . '%'; ?>;
        }

        .item {
            position: absolute;
        }

        .page_break {
            page-break-before: always;
        }

        .footer-linea1 {
            position: absolute;
            top: <?php echo $topFooter . '%'; ?>;
        }

        .borders {
            border: 2px solid #565656;
        }

        .center {
            text-align: center;
        }

        .left {
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .mh-80 {
            max-height: 80px;
        }

        .items-div {
            border-left: 2px solid #565656;
            border-right: 2px solid #565656;
            height: 540px;
            width: 91%;
            left: 5%;
            top: 43%;
        }
        .linea{
            position: absolute;
            top: <?php echo ($topHeader+(1.5*1)).'%' ?>;
        }
        .linea1{
            position: absolute;
            top: <?php echo ($topHeader+(2.5*1)).'%' ?>;
        }
        .linea2{
            position: absolute;
            top: <?php echo ($topHeader_+(2.5*2)).'%' ?>;
        }
        .linea3{
            position: absolute;
            top: <?php echo ($topHeader_+(2.5*2.6)).'%' ?>;
        }
        .linea4{
            position: absolute;
            top: <?php echo ($topHeader_+(2.3*3.6)).'%' ?>;
        }
        .linea5{
            position: absolute;
            top: <?php echo ($topHeader_+(2.2*4.6)).'%' ?>;
        }
        .linea5_{
            position: absolute;
            top: <?php echo ($topHeader_+(2.65*4.6)).'%' ?>;
        }
        .linea6{
            position: absolute;
            top: <?php echo ($topHeader_+(3.2*4.7)).'%' ?>;
        }
        .linea7{
            position: absolute;
            top: <?php echo ($topHeader_+(6.5*5)).'%' ?>;
        }
        .linea_e_1{
            position: absolute;
            top: <?php echo (0+(0*0)).'%' ?>;
        }
        .linea_e_2{
            position: absolute;
            top: <?php echo ($topH+(1.4*1)).'%' ?>;
        }
        .linea_e_3{
            position: absolute;
            top: <?php echo ($topH+(2.7*1)).'%' ?>;
        }
        .linea_e_4{
            position: absolute;
            top: <?php echo ($topH+(3.9*1)).'%' ?>;
        }
        .linea_e_5{
            position: absolute;
            top: <?php echo ($topH+(7.4*1.1)).'%' ?>;
        }
        .linea_e_6{
            position: absolute;
            top: <?php echo ($topH+(10.3*1.07)).'%' ?>;
        }
        .linea_e_7{
            position: absolute;
            top: <?php echo ($topH+(11.8*1.05)).'%' ?>;
        }
        .linea_e_8{
            position: absolute;
            top: <?php echo ($topH+(13.3*1.05)).'%' ?>;
        }
        .watermark {
            position: fixed;
            top: 45%;
            transform: rotate(-45deg);
            font-size: 72px;
            color: rgba(254, 0, 0, 0.4);
            width: 100%;
            text-align: center;
            z-index: -1000;
            letter-spacing: 30px;
            /* border: 2px solid rgba(254, 0, 0, 0.4);
            border-radius: 30px;
            padding: 5px;
            display: inline; */
        }
    </style>
</head>

<body>
    @if( $invoice->status == 'Canceled' )
        <div class="watermark">ANULADO</div>
    @endif
    <div class="linea text-center" style="left: 7%">
        <img src="{{ get_logo() }}" style="max-height: 100px;" width="100" height="100" alt="Código QR">
    </div>
    <div class="linea center" style="left: 31%;margin-top:50px;font-weight: bold;width:auto;">
        <div style="font-size: 12px">{{ get_option('company_name') }}</div>
        <div style="font-size: 12px">{{ strtoupper(get_option('business_line')) }}</div>
    </div>
    <div class="linea center" style="left: 35%;font-weight: bold;width:auto;">
        <div style="font-size: 12px">DOCUMENTO TRIBUTARIO ELECTRÓNICO</div>
        <div style="font-size: 12px">FACTURA DE EXPORTACIÓN</div>
    </div>
    <div class="linea text-center" style="left: 80%;"">
        <img src="data:image/png;base64,{{ base64_encode($codigoQR) }}" alt="Código QR">
    </div>
    <div class="linea2" style="left: 5%;">
        <p>Sucursal:</p>
    </div>
    <div class="linea2" style="left: 21%;">
        <p >{{ $invoice->company->company_name }}</p>
    </div>
    <div class="linea3" style="left: 5%;">
        <p >{{ _lang('Código de Generación:') }}</p>
    </div>
    <div class="linea3" style="left: 21%;">
        <p >{{ $invoice->codigo_generacion }}</p>
    </div>
    <div class="linea4" style="left: 5%;">
        <p >{{ _lang('Número de Control:') }}</p>
    </div>
    <div class="linea4" style="left: 21%;">
        <p >{{ $invoice->numero_control }}</p>
    </div>
    <div class="linea5" style="left: 5%;">
        <p >{{ _lang('Sello de Recepción:') }} </p>
    </div>
    <div class="linea5" style="left: 21%;">
        <p >{{ $invoice->sello_recepcion }}</p>
    </div>
    <div class="linea2" style="left: 60%;">
        <p>Correlativo N°:</p>
    </div>
    <div class="linea2" style="left: 76%;">
        <p >{{ $invoice->invoice_number }}</p>
    </div>
    <div class="linea3" style="left: 60%;">
        <p >{{ _lang('Modelo de Facturación:') }}</p>
    </div>
    <div class="linea3" style="left: 76%;">
        <p >{{ ucfirst($invoice->modelo_facturacion) }}</p>
    </div>
    <div class="linea4" style="left: 60%;">
        <p >{{ _lang('Tipo de Transmisión:') }}</p>
    </div>
    <div class="linea4" style="left: 76%;">
        <p >{{ ucfirst($invoice->transmision) }}</p>
    </div>
    <div class="linea5" style="left: 60%;">
        <p >{{ _lang('Fecha y Hora de Generación:') }} {{ $invoice->created_at->format('d-m-Y H:i:s') }} </p>
    </div>
    <div class="linea5_" style="left: 20%;">
        <p style="font-weight: bold;">EXPORTADOR (Emisor)</p>
    </div>
    <div class="linea6" style="left: 5%;width: 91%;">
        <div style="border: 2px solid #565656; border-radius: 10px;width:50%; height:176px;">
            <div class="linea_e_1" style="left: 1%;">
                <p style="text-align: right; margin-top:3px; ">{{ _lang('Nombre o razón social:') }}</p>
            </div>
            <div class="linea_e_1" style="left: 21%;text-align: right;">
                <p style="margin-top:3px;">{{ get_option('tradename') }}</p>
            </div>
            <div class="linea_e_2" style="left: 1%;">
                <p style="text-align: right;margin-top:5px; ">{{ _lang('NIT:') }}</p>
            </div>
            <div class="linea_e_2" style="left: 21%;text-align: right;">
                <p style="text-align: right;margin-top:5px; ">{{ get_option('nit') }}</p>
            </div>
            <div class="linea_e_3" style="left: 1%;">
                <p style="text-align: right; margin-top:7px;">{{ _lang('NRC:') }}</p>
            </div>
            <div class="linea_e_3" style="left: 21%;">
                <p style="margin-top:7px;">{{ get_option('nrc') }}</p>
            </div>
            <div class="linea_e_4" style="left: 1%;">
                <p >{{ _lang('Actividad económica:') }}</p>
            </div>
            <div class="linea_e_4" style="left: 21%;">
                <p style=" max-width: 190px;">{{ get_option('desc_actividad') }}</p>
            </div>
            <div class="linea_e_5" style="left: 1%;">
                <p >{{ _lang('Dirección:') }}</p>
            </div>
            <div class="linea_e_5" style="left: 21%;">
                <p style=" max-width: 190px;">{{ $invoice->company->address.', '.mb_convert_case( $invoice->company->municipio->muni_nombre, MB_CASE_TITLE, 'UTF-8').', '.$invoice->company->departamento->depa_nombre }}</p>
            </div>
            <div class="linea_e_6" style="left: 1%;">
                <p >{{ _lang('Número de teléfono:') }}</p>
            </div>
            <div class="linea_e_6" style="left: 21%;">
                <p >{{ $invoice->company->cellphone }}</p>
            </div>
            <div class="linea_e_7" style="left: 1%;">
                <p >{{ _lang('Correo electrónico:') }}</p>
            </div>
            <div class="linea_e_7" style="left: 21%;">
                <p >{{ $invoice->company->email }}</p>
            </div>
            <div class="linea_e_8" style="left: 1%;">
                <p >{{ _lang('Tipo de establecimiento:') }}</p>
            </div>
            <div class="linea_e_8" style="left: 21%;">
                <p >{{ $invoice->company->tipoEstablecimiento->tipoest_nombre }}</p>
            </div>
        </div>
    </div>
    <div class="linea5_" style="left: 69%;">
        <p style="font-weight: bold;">RECEPTOR</p>
    </div>
    <div class="linea6" style="left: 52%;width: 88%">
        <div style="border: 2px solid #565656; border-radius: 10px;width:50%;height:176px;">

            <div class="linea_e_1" style="left: 1%;">
                <p style="text-align: right;margin-top:3px; ">{{ _lang('Nombre o razón social:') }}</p>
            </div>
            <div class="linea_e_1" style="left: 21%;text-align: right;">
                <p style="margin-top:3px;">{{ $invoice->name_invoice }}</p>
            </div>
            <div class="linea_e_2" style="left: 1%;">
                @php
                    $tipoDoc = '';
                    if( $invoice->tdocrec_id == 36 ){
                        $tipoDoc = 'NIT';
                    }
                    else if( $invoice->tdocrec_id == 13 ){
                        $tipoDoc = 'DUI';
                    }
                    else{
                        $tipoDoc = 'OTRO';
                    }
                @endphp
                <p style="text-align: right; ">{{ $tipoDoc }}</p>
            </div>
            <div class="linea_e_2" style="left: 21%;text-align: right;">
                <p style="text-align: right; ">{{ $invoice->num_documento }}</p>
            </div>
            <div class="linea_e_4" style="left: 1%;">
                <p >{{ _lang('Actividad económica:') }}</p>
            </div>
            <div class="linea_e_4" style="left: 21%;">
                <p style=" max-width: 190px;">{{ $invoice->client->descActividad ?? '-' }}</p>
            </div>
            <div class="linea_e_5" style="left: 1%;">
                <p >{{ _lang('País destino:') }}</p>
            </div>
            <div class="linea_e_5" style="left: 21%;">
                <p >{{ $invoice->client->pais->pais_nombre }}</p>
            </div>
            <div class="linea_e_6" style="left: 1%;">
                <p >{{ _lang('Dirección:') }}</p>
            </div>
            <div class="linea_e_6" style="left: 21%;">
                <p style=" max-width: 200px;">{{ $invoice->client->address.', '.mb_convert_case( $invoice->client->municipio->muni_nombre, MB_CASE_TITLE,  'UTF-8').', '.$invoice->client->departamento->depa_nombre }}</p>
            </div>
        </div>
    </div>

    {{-- <div class="linea7 borders center" style=" left: 5%; width: 6.8%;height: 2.5em;border-top-left-radius:10px;">
        Cantidad</div>
    <div class="linea7 borders center" style="left: 12%; width: 7.7%;height: 2.5em;">Código</div>
    <div class="linea7 borders center" style="left: 20%; width: 43%; height: 2.5em; overflow: hidden;">Descripción</div>
    <div class="linea7 borders center" style="left: 63.2%; width: 8%;height: 2.5em;">P.Unitario</div>
    <div class="linea7 borders center" style="left: 71.5%; width: 8%;">Descuento por item</div>
    <div class="linea7 borders center" style="left: 79.8%; width: 8%;">Ots. montos no afectos</div>
    <div class="linea7 borders center" style="left: 88%; width: 8%;border-top-right-radius:10px">Ventas afectas</div> --}}

    <div class="item items-div">
    </div>

    {{-- @for ($i = 0; $i < count($invoice->invoice_items); $i++)
        @php
            $noSujeto = '';
            $exento = '';
            $gravado = '';

            if($invoice->exento_iva == 'si'){
                $exento = decimalPlace($invoice->invoice_items[$i]->sub_total, $currency);
                $exentoSum += $invoice->invoice_items[$i]->sub_total;
            }else if($invoice->nosujeto_iva == 'si'){
                $noSujeto = decimalPlace($invoice->invoice_items[$i]->sub_total, $currency);
                $noSujetoSum += $invoice->invoice_items[$i]->sub_total;
            }else{
                $gravado = decimalPlace($invoice->invoice_items[$i]->sub_total, $currency);
                $gravadoSum += $invoice->invoice_items[$i]->sub_total;
            }
        @endphp
        <div class="item borders center" style="top: {{ $top + $i * 4 }}%; left: 5%; width: 6.8%;height: 3.5em;">
            {{ $invoice->invoice_items[$i]->quantity }}
        </div>
        <div class="item borders center" style="top: {{ $top + $i * 4 }}%; left: 12%; width: 7.7%;height: 3.5em;">
            {{ $invoice->invoice_items[$i]->item->product->product_code }}
        </div>
        <div class="item borders"
            style="top: {{ $top + $i * 4 }}%; left: 20%; width: 43%; height: 3.5em; overflow: hidden;">
            {{ $invoice->invoice_items[$i]->description }} (Garantia:
            {{ $invoice->invoice_items[$i]->item->product->warranty_value }}
            {{ $invoice->invoice_items[$i]->item->product->warranty_type }})
        </div>
        <div class="item borders right" style="top: {{ $top + $i * 4 }}%; left: 63.2%; width: 8%;height: 3.5em;">
            {{ decimalPlace($invoice->invoice_items[$i]->unit_cost, $currency) }}
        </div>
        <div class="item borders {{ $exento == '' ? 'center' : 'right' }}"
            style="top: {{ $top + $i * 4 }}%; left: 71.5%; width: 8%;height: 3.5em;"> -
        </div>
        <div class="item borders {{ $exento == '' ? 'center' : 'right' }}"
            style="top: {{ $top + $i * 4 }}%; left: 79.8%; width: 8%;height: 3.5em;"> -
        </div>
        <div class="item borders" style="top: {{ $top + $i * 4 }}%; left: 88%; width: 8%; text-align:right;height: 3.5em;">
            {{ $gravado == '' ? '-' : $gravado }}
        </div>
    @endfor --}}

    <div class="item" style="top: 38.5%;width: 91.4%;left: 5%;">
        <table style="width: 100%;border-collapse: collapse;">
            <thead>
                <tr>
                    <th class="item borders center" style="width: 6.8%;">Cantidad</th>
                    <th class="item borders center" style="width: 7.7%;">Código</th>
                    <th class="item borders" style="width: 43%;">Descripción</th>
                    <th class="item borders center" style="width: 8%;">Precio Unitario</th>
                    <th class="item borders" style="width: 8%;">Descuento por item</th>
                    <th class="item borders" style="width: 8%;">Otros montos no afectos</th>
                    <th class="item borders" style="width: 8%;">Ventas afectas</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 0; $i < count($invoice->invoice_items); $i++)
                    @php
                        $noSujeto = '';
                        $exento = '';
                        $gravado = '';

                        if($invoice->exento_iva == 'si'){
                            $exento = decimalPlace($invoice->invoice_items[$i]->sub_total, $currency);
                            $exentoSum += $invoice->invoice_items[$i]->sub_total;
                        }else if($invoice->nosujeto_iva == 'si'){
                            $noSujeto = decimalPlace($invoice->invoice_items[$i]->sub_total, $currency);
                            $noSujetoSum += $invoice->invoice_items[$i]->sub_total;
                        }else{
                            $gravado = decimalPlace($invoice->invoice_items[$i]->sub_total, $currency);
                            $gravadoSum += $invoice->invoice_items[$i]->sub_total;
                        }
                    @endphp
                    <tr>
                        <td class="item borders center">{{ $invoice->invoice_items[$i]->quantity }}</td>
                        <td class="item borders center">{{ $invoice->invoice_items[$i]->item->product->product_code }}</td>
                        <td class="item borders">{{ $invoice->invoice_items[$i]->description }} (Garantia: {{ $invoice->invoice_items[$i]->item->product->warranty_value }} {{ $invoice->invoice_items[$i]->item->product->warranty_type }})</td>
                        <td class="item borders right">{{ decimalPlace($invoice->invoice_items[$i]->unit_cost, $currency) }}</td>
                        <td class="item borders center">-</td>
                        <td class="item borders center">-</td>
                        <td class="item borders right">{{ $gravado == '' ? '-' : $gravado }}</td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>


    <div class="footer-linea1 left" style="border-top: 2px solid #565656;border-bottom: 2px solid #565656;border-left: 2px solid #565656; left: 5%; width: 91%;">
        {{ _lang('It is') }} {{ dollarToText($invoice->grand_total) }}
    </div>

    <div class="left" style="border-top: 2px solid #565656;border-bottom: 0px solid #565656;border-left: 2px solid #565656;position: absolute; top: {{ $topFooter + 2.4 * 0.67 }}%; left: 5%; width: 58%;">
        Condición de la operación: {{ $invoice->condicion_op }}
    </div>
    <div class="left" style="border-top: 0px solid #565656;border-bottom: 0px solid #565656;border-left: 2px solid #565656;position: absolute; top: {{ $topFooter + 4.8 * 0.67 }}%; left: 5%; width: 58%;">
        Descripción Incoterms: {{ ( $invoice->id_incoterms != '' ) ? $invoice->incoterm->nombre_incoterms : '----' }}
    </div>

    
    <div class="left" style="border-left: 2px solid #565656;border-bottom: 2px solid #565656;position: absolute; top: {{ $topFooter + 7.4 * 0.67 }}%; left: 5%; width: 58%;height:49px;border-bottom-left-radius:10px;">
        NOTA: {{ ( $invoice->note != '' ) ? $invoice->note : '' }}
    </div>

    <div class="borders left" style="border-left: 2px solid #565656;position: absolute; top: {{ $topFooter + 0.8 * 2.01 }}%; left: 63.2%; text-align:right; width: 24.5%; height:26px;">
        Total de Operaciones Afectas:
    </div>
    <div class="borders" style="position: absolute; top: {{ $topFooter + 0.8 * 2.01 }}%; left: 88%; width: 8%;text-align:right;height:26px;">
        {{ decimalPlace($gravadoSum-$invoice->iva_retenido, $currency) }}
    </div>
    <div class="borders left" style="border-left: 2px solid #565656;position: absolute; top: {{ $topFooter + 1.6 * 2.66 }}%; left: 63.2%; width: 24.5%;height:26px;text-align:right;">
        Rebajas de operaciones afectas:
    </div>
    <div class="borders" style="position: absolute; top: {{ $topFooter + 1.6 * 2.66 }}%; left: 88%; width: 8%;text-align:right;height:26px;">
        {{ decimalPlace(0.0, $currency) }}
    </div>
    <div class="borders left" style="border-left: 2px solid #565656; border-bottom: 2px solid #565656;position: absolute; top: {{ $topFooter + 2.08 * 3.32 }}%; left: 63.2%; width: 24.5%;height:26px;text-align:right;">
        Monto Total de la Operación:
    </div>
    <div class="borders" style="position: absolute; top: {{ $topFooter + 2.08 * 3.32}}%; left: 88%; width: 8%;text-align:right;border-bottom-right-radius:6px;height:26px;">
        {{ decimalPlace($invoice->grand_total, $currency) }}
    </div>

</body>

</html>
-->

<!DOCTYPE html>
@php
    $currency = currency();
    $noSujeto = '';
    $exento = '';
    $gravado = '';
    $noSujetoSum = 0;
    $exentoSum = 0;
    $gravadoSum = 0;
    $style = '';
    if( $invoice->status == 'Canceled' ){
        $style = 'background-image: url('.asset("backend/images/invalidado.png").');background-position: center; background-repeat: no-repeat; background-size: contain;opacity: 0.5;background-size: 70%;';
    }

    $nrc = $invoice->client->nrc;
    $parte1 = substr($nrc, 0, 6);
    $parte2 = substr($nrc, 6);
    $format_nrc = $parte1 . '-' . $parte2;
@endphp
<html lang="es">
<head>
    <title>{{ get_option('site_title', 'Invoice') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        /* Estilos CSS */
        body {
            padding: 0;
            margin: 0 !important;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }
        .container {
            width: 100%;
        }
        .header, .content, .footer {
            width: 100%;
        }
        .center {
            text-align: center;
        }

        .left {
            text-align: left;
        }

        .right {
            text-align: right;
        }
        .borders {
            border: 2px solid #565656;
        }
        .watermark {
            position: fixed;
            top: 45%;
            transform: rotate(-45deg);
            font-size: 72px;
            color: rgba(254, 0, 0, 0.4);
            width: 100%;
            text-align: center;
            z-index: 1000;
            letter-spacing: 30px;
        }
        /* Agrega otros estilos según sea necesario */
    </style>
</head>
<body style="margin: 0; padding: 0; {{ $style }}">
    <table class="container" style="margin: 0; padding: 0;">
        <tr>
            <td>
                <table class="header">
                    <!-- Contenido del encabezado aquí -->
                    <table style="width: 100%;">
                        <tr>
                            <td class="center" style="width: 25%;">
                                <img src="{{ get_logo() }}" style="max-height: 100px;" width="100" height="100" alt="Código QR">
                            </td>
                            <td class="center" style="width: 50%; font-weight: bold;">
                                <div>DOCUMENTO TRIBUTARIO ELECTRÓNICO</div>
                                <div>FACTURA DE EXPORTACIÓN</div>
                                <br>
                                <div>{{ get_option('company_name') }}</div>
                                <div>{{ strtoupper(get_option('business_line')) }}</div>
                            </td>
                            <td class="center" style="width: 25%;">
                                <img src="data:image/png;base64,{{ base64_encode($codigoQR) }}" alt="Código QR">
                            </td>
                        </tr>
                    </table>
                    <table class="info-table">
                        <tr>
                            <td style="width: 25%;">Código vendedor:</td>
                            <td>{{ $invoice->seller_code->seller_code ?? '-' }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="width: 18%;">Sucursal:</td>
                            <td>{{ $invoice->company->company_name }}</td>
                            <td style="width: 25%;padding-left:7px;">Correlativo N°:</td>
                            <td>{{ $invoice->invoice_number }}</td>
                        </tr>
                        <tr>
                            <td>Código de Generación:</td>
                            <td>{{ $invoice->codigo_generacion }}</td>
                            <td style="padding-left:7px;">Modelo de Facturación:</td>
                            <td>{{ ucfirst($invoice->modelo_facturacion) }}</td>
                        </tr>
                        <tr>
                            <td>Número de Control:</td>
                            <td>{{ $invoice->numero_control }}</td>
                            <td style="padding-left:7px;">Tipo de Transmisión:</td>
                            <td>{{ ucfirst($invoice->transmision) }}</td>
                        </tr>
                        <tr>
                            <td>Sello de Recepción:</td>
                            <td>{{ $invoice->sello_recepcion }}</td>
                            <td style="padding-left:7px;">Fecha y Hora de Generación:</td>
                            <td>{{ $invoice->created_at->format('d-m-Y H:i:s') }}</td>
                        </tr>
                    </table>
                </table>
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 50%; padding: 5px;">
                            <p style="font-weight: bold; text-align: center;">EMISOR</p>
                            <div style="border: 2px solid #565656; border-radius: 10px; padding: 10px;">
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="width: 45%;">{{ _lang('Nombre o razón social:') }}</td>
                                        <td>{{ get_option('tradename') }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('NIT:') }}</td>
                                        <td>{{ get_option('nit') }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('NRC:') }}</td>
                                        <td>{{ get_option('nrc') }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Actividad económica:') }}</td>
                                        <td>{{ get_option('desc_actividad') }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Dirección:') }}</td>
                                        <td>{{ $invoice->company->address.', '.mb_convert_case( $invoice->company->municipio->muni_nombre, MB_CASE_TITLE, 'UTF-8').', '.$invoice->company->departamento->depa_nombre }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Número de teléfono:') }}</td>
                                        <td>{{ $invoice->company->cellphone }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Correo electrónico:') }}</td>
                                        <td>{{ $invoice->company->email }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Tipo de establecimiento:') }}</td>
                                        <td>{{ $invoice->company->tipoEstablecimiento->tipoest_nombre }}</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                        <td style="width: 50%; padding: 5px;">
                            <p style="font-weight: bold; text-align: center;">RECEPTOR</p>
                            <div style="border: 2px solid #565656; border-radius: 10px; padding: 10px;min-height:180px;">
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="width: 45%;">{{ _lang('Nombre o razón social:') }}</td>
                                        <td>{{ $invoice->name_invoice }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('NIT:') }}</td>
                                        <td>{{ $invoice->client->nit }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Actividad económica:') }}</td>
                                        <td>{{ $invoice->client->descActividad ?? '-' }}.</td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('País destino:') }}</td>
                                        <td>{{ $invoice->client->pais->pais_nombre }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Dirección:') }}</td>
                                        <td>{{ $invoice->client->address.', '.mb_convert_case( $invoice->client->municipio->muni_nombre, MB_CASE_TITLE, 'UTF-8').', '.$invoice->client->departamento->depa_nombre }}.</td>
                                    </tr>
                                    @for ($i = 0; $i < 7; $i++)
                                            <tr>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                    @endfor
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
                <table style="width: 100%;border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th class="borders center" style="width: 6.8%;">Cantidad</th>
                            <th class="item borders center" style="width: 7.7%;">Código</th>
                            <th class="item borders" style="width: 43%;">Descripción</th>
                            <th class="item borders center" style="width: 8%;">Precio Unitario</th>
                            <th class="item borders" style="width: 8%;">Descuento por item</th>
                            <th class="item borders" style="width: 8%;">Otros montos no afectos</th>
                            <th class="item borders" style="width: 8%;">Ventas afectas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < count($invoice->invoice_items); $i++)
                            @php
                                $noSujeto = '';
                                $exento = '';
                                $gravado = '';
                                $subTotalItem = $invoice->invoice_items[$i]->product_price * $invoice->invoice_items[$i]->quantity;
                                $subTotalItem = $subTotalItem - $invoice->invoice_items[$i]->discount;
                                if ($invoice->exento_iva == 'si') {
                                    $exento = decimalPlace($subTotalItem, $currency);
                                    $exentoSum += $subTotalItem;
                                } elseif ($invoice->nosujeto_iva == 'si') {
                                    $noSujeto = decimalPlace($subTotalItem, $currency);
                                    $noSujetoSum += $subTotalItem;
                                } else {
                                    $gravado = decimalPlace($subTotalItem, $currency);
                                    $gravadoSum += $subTotalItem;
                                }
                            @endphp
                            <tr>
                                <td class="item borders center">{{ $invoice->invoice_items[$i]->quantity }}</td>
                                <td class="item borders center">{{ $invoice->invoice_items[$i]->item->product->product_code }}</td>
                                <td class="item borders">{!! nl2br($invoice->invoice_items[$i]->description) !!}</td>
                                <td class="item borders right">{{ decimalPlace($invoice->invoice_items[$i]->unit_cost, $currency) }}</td>
                                <td class="item borders {{ $exento == '' ? 'center' : 'right' }}">{{ $exento == '' ? '-' : $exento }}</td>
                                <td class="item borders {{ $exento == '' ? 'center' : 'right' }}">{{ $noSujeto == '' ? '-' : $noSujeto }}</td>
                                <td class="item borders right">{{ $gravado == '' ? '-' : $gravado }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
                <!-- Contenido del pie de página aquí -->
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="borders" style="width: 64.2%; padding: 5px;border-top:0;">
                            <table style="width: 100%;border-collapse: collapse;">
                                <tr>
                                    <td>{{ _lang('It is') }} {{ dollarToText($invoice->grand_total) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="border-top:0;">
                        <td style="width: 22%;border-left: 2px solid #565656;">Condición de la operación:</td>
                        <td style="width: 42.5%;">{{ $invoice->condicion_op }}</td>
                        <td class="right" style="width: 27%;border-left: 2px solid #565656;border-right: 2px solid #565656;border-bottom: 2px solid #565656;">Total de Operaciones Afectas:</td>
                        <td class="right" style="border-bottom: 2px solid #565656;border-right: 2px solid #565656;">{{ decimalPlace($gravadoSum + $invoice->tax_total, $currency) }}</td>
                    </tr>
                    <tr style="border-top:0;">
                        <td style="width: 22%;border-left: 2px solid #565656;">Descripción Incoterms:</td>
                        <td style="width: 42.5%;">{{ ( $invoice->id_incoterms != '' ) ? $invoice->incoterm->nombre_incoterms : '----' }}</td>
                        <td class="right" style="width: 27%;border-left: 2px solid #565656;border-right: 2px solid #565656;border-bottom: 2px solid #565656;">Rebajas de operaciones afectas:</td>
                        <td class="right" style="border-bottom: 2px solid #565656;border-right: 2px solid #565656;">{{ decimalPlace(0, $currency) }}</td>
                    </tr>
                    <tr class="borders" style="border-top:0;">
                        <td style="width: 22%;">NOTA:</td>
                        <td style="width: 42.5%;">{{ ( $invoice->note != '' ) ? $invoice->note : '' }} </td>
                        <td class="right" style="width: 27%;border-left: 2px solid #565656;border-right: 2px solid #565656;">Monto Total de la Operación:</td>
                        <td class="right">{{ decimalPlace($invoice->grand_total, $currency) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>