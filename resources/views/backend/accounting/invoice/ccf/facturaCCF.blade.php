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
            font-size: 10px;
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
        @page {
            margin: 20px;
        }
        /* Agrega otros estilos según sea necesario */
    </style>
</head>
<body style="margin: 0; padding: 0; {{ $style }}">
    <table class="header">
        <!-- Contenido del encabezado aquí -->
        <table style="width: 100%;">
            <tr>
		<td class="center" style="width: 25%;">
		    <img src="data:image/png;base64,{{ base64_encode($codigoQR) }}" alt="Código QR">
                </td>
                <td class="center" style="width: 50%; font-weight: bold;">
                    <div>--DOCUMENTO TRIBUTARIO ELECTRÓNICO--</div>
                    <div>COMPROBANTE DE CRÉDITO FISCAL</div>
                    <br>
                    <div>{{ get_option('company_name') }}</div>
                    <div>{{ strtoupper(get_option('business_line')) }}</div>
                </td>
		<td class="center" style="width: 25%;">
			<img src="{{ get_logo() }}" style="max-height: 100px;" width="200" height="100" alt="Logo NT">
                </td>
            </tr>
        </table>
        <table class="info-table" style="margin-left:40px;">
            <tr>
                <td style="width: 25%;">Código vendedor:</td>
                <td>{{ $invoice->seller_code->seller_code ?? '-' }} {{ $invoice->formatted_seller_code2 }}</td>
                <td style="width: 25%;padding-left:10px;">Correlativo N°:</td>
                <td>{{ $invoice->invoice_number }}</td>
            </tr>
            <tr>
                <td style="width: 25%;">Sucursal:</td>
                <td>{{ $invoice->company->company_name }}</td>
                <td style="padding-left:10px;">Modelo de Facturación:</td>
                <td>{{ ucfirst($invoice->modelo_facturacion) }}</td>
            </tr>
            <tr>
                <td>Código de Generación:</td>
                <td>{{ $invoice->codigo_generacion }}</td>
                <td style="padding-left:10px;">Tipo de Transmisión:</td>
                <td>{{ ucfirst($invoice->transmision) }}</td>
            </tr>
            <tr>
                <td>Número de Control:</td>
		<td>{{ $invoice->numero_control }}</td>
		<td style="padding-left:10px;">Fecha y Hora de Emisión:</td>
                <td>{{ $invoice->getInvoiceDateTime()->format('d-m-Y H:i:s') }}</td>

            </tr>
	    <tr>
		<td>&nbsp;</td>
                <td>&nbsp;</td>
		<td style="padding-left:10px;">Fecha y Hora de Generación:</td>
                <td>{{ $invoice->created_at->format('d-m-Y H:i:s') }}</td>
	    </tr>
	    <tr>
		<td>Sello de Recepción:</td>
                <td>{{ $invoice->sello_recepcion }}</td>

		<td style="padding-left:10px;">Condición de operación:</td>
                <td>{{ $invoice->condicion_operacion->conop_nombre }} {{ ( $invoice->conop_id == 2 && $invoice->plazo?->plazo_nombre != null ) ? $invoice->periodo . ' ' . $invoice->plazo->plazo_nombre : ''  }}</td>
	   </tr>
        </table>
    </table>
    <table style="width: 100%;margin:0;">
        <tr>
            <td style="width: 50%; padding: 5px;">
                <p style="font-weight: bold; text-align: center;margin:0;">EMISOR</p>
                <div style="border: 2px solid #565656; border-radius: 10px; min-height: 170px;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 35%;">{{ _lang('Nombre o razón social:') }}</td>
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
            <td style="width: 50%;">
                <p style="font-weight: bold; text-align: center;margin:0;">RECEPTOR</p>
                <div style="border: 2px solid #565656; border-radius: 10px;min-height:170px;">
                    <table style="width: 100%;table-layout:fixed;">
                        <tr>
                            <td style="width: 35%;">{{ _lang('Nombre o razón social:') }}</td>
                            <td style="word-break:break-all; word-wrap:break-word;">{{ $invoice->name_invoice }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('NIT:') }}</td>
                            <td>{{ $invoice->client->nit }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('NRC:') }}</td>
                            <td>{{ $nrc }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Actividad económica:') }}</td>
                            <td>{{ $invoice->client->descActividad ?? '-' }}.</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Dirección:') }}</td>
                            <td>{{ $invoice->client->address.', '.mb_convert_case( $invoice->client->municipio->muni_nombre, MB_CASE_TITLE, 'UTF-8').', '.$invoice->client->departamento->depa_nombre }}.</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Número de teléfono:') }}</td>
                            <td>{{ $invoice->client->contact_phone }}</td>
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
    @if( $invoice->id_nr_rel > 0 )
        <p class="center" style="margin:0"><b>DOCUMENTOS RELACIONADOS</b></p>
        <table style="width: 100%;border-collapse: collapse;margin-bottom:10px;">
            <thead>
                <tr>
                    <th style="border-top: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656;" class="center">{{ _lang('Tipo de Documento') }}</th>
                    <th style="border-top: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656; width: 43%" class="center">{{ _lang('N° de Documento') }}</th>
                    <th style="border-top: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-right: 2px solid #565656;" class="center">{{ _lang('Fecha de Documento') }}</th>
                </tr>
            </thead>
            <tbody id="docs">
                @php
                    $cod_dte_rel_array = [];
                @endphp
                @for ($i = 0; $i < count($invoice->invoice_items); $i++)
                    @if (!in_array($invoice->invoice_items[$i]->cod_dte_rel, $cod_dte_rel_array))
                        <tr id="product-{{ $invoice->invoice_items[$i]->item_id }}">
                            <td
                                style="border-left: 2px solid #565656; border-right: 0px solid #565656; border-bottom: 2px solid #565656; border-top: 0px solid #565656;" class="center">
                                <b>{{ $invoice->invoice_items[$i]->tipoDocumento->tipodoc_nombre }}</b>
                            </td>
                            <td style="border-left: 2px solid #565656; border-right: 0px solid #565656; border-bottom: 2px solid #565656; border-top: 0px solid #565656;"
                                class="center">{{ $invoice->invoice_items[$i]->cod_dte_rel }}</td>
                            <td style="border-left: 2px solid #565656; border-right: 2px solid #565656; border-bottom: 2px solid #565656; border-top: 0px solid #565656;"
                                class="center">{{ \Carbon\Carbon::parse($invoice->invoice_items[$i]->date_dte_rel)->format('d-m-Y') }}
                            </td>
                        </tr>
                        @php
                            $cod_dte_rel_array[] = $invoice->invoice_items[$i]->cod_dte_rel;
                        @endphp
                    @endif
                @endfor
            </tbody>
        </table>
    @endif
    <table style="width: 100%;border-collapse: collapse;">
        <thead>
            <tr>
                <th class="borders center" style="width: 6.8%;">Cantidad</th>
                {{-- <th class="item borders center" style="width: 7.7%;">Código</th> --}}
                <th class="item borders" style="width: 43%;">Descripción</th>
                <th class="item borders right" style="width: 8%;">P. Unitario</th>
                <th class="item borders" style="width: 8%;">Ventas No Sujetas</th>
                <th class="item borders" style="width: 8%;">Ventas Exentas</th>
                <th class="item borders" style="width: 8%;">Ventas gravadas</th>
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
                    <td class="item borders center">{{ number_format($invoice->invoice_items[$i]->quantity, 2, '.', ',') }}</td>
                    {{-- <td class="item borders center">{{ $invoice->invoice_items[$i]->item->product->product_code }}</td> --}}
                    <td class="item borders">{!! nl2br($invoice->invoice_items[$i]->description) !!}</td>
                    <td class="item borders right">{{ $currency }} {{ number_format($invoice->invoice_items[$i]->unit_cost, 6, '.', '') }}</td>
                    <td class="item borders {{ $exento == '' ? 'center' : 'right' }}">{{ $noSujeto == '' ? '-' : $noSujeto }}</td>
                    <td class="item borders {{ $exento == '' ? 'center' : 'right' }}">{{ $exento == '' ? '-' : $exento }}</td>
                    <td class="item borders right">{{ $gravado == '' ? '-' : $gravado }}</td>
                </tr>
            @endfor
        </tbody>
    </table>
    <table class="borders" style="width: 100%;border-collapse: collapse;border-top:0;border-bottom:0;">
        <tr>
            <td style="width: 64.4%;">{{ _lang('It is') }} {{ dollarToText($invoice->grand_total) }} DÓLARES</td>
            <td class="center" style="width: 8.9%;border-left: 2px solid #565656;border-bottom: 2px solid #565656;">SUMAS</td>
            <td class="center" style="width: 8.9%;border-left: 2px solid #565656;border-bottom: 2px solid #565656;">{{ decimalPlace($noSujetoSum, $currency) }}</td>
            <td class="center" style="width: 8.9%;border-left: 2px solid #565656;border-bottom: 2px solid #565656;">{{ decimalPlace($exentoSum, $currency) }}</td>
            <td class="right" style="border-left: 2px solid #565656;border-bottom: 2px solid #565656;">{{ decimalPlace($gravadoSum, $currency) }}</td>
        </tr>
    </table>
    <table style="width: 100%; border-collapse: collapse;">
        <tr style="border-top:0;">
            <td style="width: 64.4%;border-left: 2px solid #565656;"></td>
            <td class="left" style="width: 26.7%;border-left: 2px solid #565656;border-right: 2px solid #565656;border-bottom: 2px solid #565656;">Descuento general</td>
            <td class="right" style="border-bottom: 2px solid #565656;border-right: 2px solid #565656;">{{ decimalPlace($invoice->general_discount, $currency) }}</td>
        </tr>
        <tr style="border-top:0;">
            <td style="width: 64.4%;border-left: 2px solid #565656;"></td>
            <td class="left" style="width: 26.7%;border-left: 2px solid #565656;border-right: 2px solid #565656;border-bottom: 2px solid #565656;">13 % IVA</td>
            <td class="right" style="border-bottom: 2px solid #565656;border-right: 2px solid #565656;">{{ decimalPlace($invoice->tax_total, $currency) }}</td>
        </tr>
        <tr style="border-top:0;">
            <td style="width: 64.4%;border-left: 2px solid #565656;"></td>
            <td class="left" style="width: 26.7%;border-left: 2px solid #565656;border-right: 2px solid #565656;border-bottom: 2px solid #565656;">Sub-Total</td>
            <td class="right" style="border-bottom: 2px solid #565656;border-right: 2px solid #565656;">{{ decimalPlace($gravadoSum -( $invoice->general_discount )+$invoice->tax_total, $currency) }}</td>
        </tr>
        <tr style="border-top:0;">
            <td style="width: 64.4%;border-left: 2px solid #565656;"></td>
            <td class="left" style="width: 26.7%;border-left: 2px solid #565656;border-right: 2px solid #565656;border-bottom: 2px solid #565656;">(-) IVA Retenido</td>
            <td class="right" style="border-bottom: 2px solid #565656;border-right: 2px solid #565656;">{{ decimalPlace($invoice->iva_retenido, $currency) }}</td>
        </tr>
        <tr style="border-top:0;">
            <td style="width: 64.4%;border-left: 2px solid #565656;"></td>
            <td class="left" style="width: 26.7%;border-left: 2px solid #565656;border-right: 2px solid #565656;border-bottom: 2px solid #565656;">Retención Renta</td>
            <td class="right" style="border-bottom: 2px solid #565656;border-right: 2px solid #565656;">{{ decimalPlace($invoice->retencion_renta, $currency) }}</td>
        </tr>
        <tr style="border-top:0;">
            <td style="width: 64.4%;border-left: 2px solid #565656;"></td>
            <td class="left" style="width: 26.7%;border-left: 2px solid #565656;border-right: 2px solid #565656;border-bottom: 2px solid #565656;">Vtas. No sujetas</td>
            <td class="right" style="border-bottom: 2px solid #565656;border-right: 2px solid #565656;">{{ decimalPlace($noSujetoSum, $currency) }}</td>
        </tr>
        <tr style="border-top:0;">
            <td style="width: 64.4%;border-left: 2px solid #565656;"></td>
            <td class="left" style="width: 26.7%;border-left: 2px solid #565656;border-right: 2px solid #565656;border-bottom: 2px solid #565656;">Vtas. Exentas</td>
            <td class="right" style="border-bottom: 2px solid #565656;border-right: 2px solid #565656;">{{ decimalPlace($exentoSum, $currency) }}</td>
        </tr>
        <tr class="borders" style="border-top:0;">
            <td style="width: 64.4%;">NOTA: {!! nl2br($invoice->note) !!} </td>
            <td class="left" style="width: 8.9%;border-left: 2px solid #565656;border-right: 2px solid #565656;">Total</td>
            <td class="right" style="width:8.9%;">{{ decimalPlace($invoice->grand_total, $currency) }}</td>
        </tr> 
    </table>
</body>
</html>
