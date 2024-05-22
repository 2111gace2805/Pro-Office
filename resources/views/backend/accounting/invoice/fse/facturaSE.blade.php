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
                                <div>FACTURA</div>
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
                            <td style="width: 25%;">Sucursal:</td>
                            <td>{{ $invoice->company->company_name }}</td>
                            <td style="width: 25%;padding-left:10px;">Correlativo N°:</td>
                            <td>{{ $invoice->invoice_number }}</td>
                        </tr>
                        <tr>
                            <td>Código de Generación:</td>
                            <td>{{ $invoice->codigo_generacion }}</td>
                            <td style="padding-left:10px;">Modelo de Facturación:</td>
                            <td>{{ ucfirst($invoice->modelo_facturacion) }}</td>
                        </tr>
                        <tr>
                            <td>Número de Control:</td>
                            <td>{{ $invoice->numero_control }}</td>
                            <td style="padding-left:10px;">Tipo de Transmisión:</td>
                            <td>{{ ucfirst($invoice->transmision) }}</td>
                        </tr>
                        <tr>
                            <td>Sello de Recepción:</td>
                            <td>{{ $invoice->sello_recepcion }}</td>
                            <td style="padding-left:10px;">Fecha y Hora de Generación:</td>
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
                                        <td>{{ $invoice->num_documento }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Número de teléfono:') }}</td>
                                        <td>{{ $invoice->client->contact_phone }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Dirección:') }}</td>
                                        <td>{{ $invoice->client->address.', '.mb_convert_case( $invoice->client->municipio->muni_nombre, MB_CASE_TITLE, 'UTF-8').', '.$invoice->client->departamento->depa_nombre }}.</td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Correo electrónico:') }}</td>
                                        <td>{{ $invoice->client->contact_email }}</td>
                                    </tr>
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
                            <th class="item borders right" style="width: 8%;">P. Unitario</th>
                            <th class="item borders" style="width: 8%;">Descuento por item</th>
                            <th class="item borders" style="width: 8%;">Ventas</th>
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
                                <td class="item borders">{!! nl2br($invoice->invoice_items[$i]->description) !!}</td>
                                <td class="item borders right">{{ decimalPlace($invoice->invoice_items[$i]->unit_cost, $currency) }}</td>
                                <td class="item borders {{ $exento == '' ? 'center' : 'right' }}">-</td>
                                <td class="item borders right">{{ $gravado == '' ? '-' : $gravado }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
                <!-- Contenido del pie de página aquí -->
                <table class="borders" style="width: 100%;border-collapse: collapse;border-top:0;">
                    <tr>
                        <td style="width: 55.2%;">{{ _lang('It is') }} {{ dollarToText($invoice->grand_total) }}</td>
                        <td class="right" style="width: 35%;border-left: 2px solid #565656;">Sumatoria de ventas:</td>
                        <td class="right" style="border-left: 2px solid #565656;">{{ decimalPlace($gravadoSum, $currency) }}</td>
                    </tr>
                </table>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="border-top:0;">
                        <td style="width: 22%;border-left: 2px solid #565656;">Condición de la operación:</td>
                        <td style="width: 33.2%;">{{ $invoice->condicion_op }}</td>
                        <td class="right" style="width: 35%;border-left: 2px solid #565656;border-right: 2px solid #565656;border-bottom: 2px solid #565656;">Monto global Desc., Rebajas y otros a ventas:</td>
                        <td class="right" style="border-bottom: 2px solid #565656;border-right: 2px solid #565656;">{{ decimalPlace(0, $currency) }}</td>
                    </tr>
                    <tr style="border-top:0;">
                        <td style="width: 22%;border-left: 2px solid #565656;"></td>
                        <td style="width: 33.2%;"></td>
                        <td class="right" style="width: 35%;border-left: 2px solid #565656;border-right: 2px solid #565656;border-bottom: 2px solid #565656;">Sub-Total</td>
                        <td class="right" style="border-bottom: 2px solid #565656;border-right: 2px solid #565656;">{{ decimalPlace($gravadoSum-$invoice->iva_retenido, $currency) }}</td>
                    </tr>
                    <tr style="border-top:0;">
                        <td style="width: 22%;border-left: 2px solid #565656;"></td>
                        <td style="width: 33.2%;"></td>
                        <td class="right" style="width: 35%;border-left: 2px solid #565656;border-right: 2px solid #565656;border-bottom: 2px solid #565656;">IVA Retenido:</td>
                        <td class="right" style="border-bottom: 2px solid #565656;border-right: 2px solid #565656;">{{ decimalPlace($invoice->iva_retenido, $currency) }}</td>
                    </tr>
                    <tr style="border-top:0;">
                        <td style="width: 22%;border-left: 2px solid #565656;"></td>
                        <td style="width: 33.2%;"></td>
                        <td class="right" style="width: 35%;border-left: 2px solid #565656;border-right: 2px solid #565656;border-bottom: 2px solid #565656;">Retención Renta:</td>
                        <td class="right" style="border-bottom: 2px solid #565656;border-right: 2px solid #565656;">{{ decimalPlace(0.0, $currency) }}</td>
                    </tr>
                    <tr class="borders" style="border-top:0;">
                        <td style="width: 22%;">NOTA:</td>
                        <td style="width: 33.2%;">{{ ( $invoice->note != '' ) ? $invoice->note : '' }} </td>
                        <td class="right" style="width: 35%;border-left: 2px solid #565656;border-right: 2px solid #565656;">Monto Total de la Operación:</td>
                        <td class="right">{{ decimalPlace($invoice->grand_total, $currency) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>