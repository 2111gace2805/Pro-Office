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
		    <img src="{{ get_logo() }}" style="max-height: 100px;" width="200" height="100" alt="Logo NT">
                </td>
                <td class="center" style="width: 50%; ">
                    <div>DOCUMENTO TRIBUTARIO ELECTRÓNICO</div>
                    <div>COMPROBANTE DE CRÉDITO FISCAL</div>
                    <br>
                    <div> <p style="font-weight: bold; text-align: center;margin:0;"> {{ get_option('company_name') }}</p></div>
                    <div>{{ strtoupper(get_option('business_line')) }}</div>
                </td>
		<td class="center" style="width: 25%;">
			
            <img src="data:image/png;base64,{{ base64_encode($codigoQR) }}" alt="Código QR">
                </td>
            </tr>
        </table>
        <!-- Encabezado de la Factura -->
    <table  style="margin-left: 0px; border-collapse: collapse; width: 100%;">
        <tr>
            <td style="width: 8.2%; padding-left: 6px;">Código vendedor:</td>
            <td style="width: 15%; padding-left: 6px;">{{ $invoice->seller_code->seller_code ?? '-' }} {{ $invoice->formatted_seller_code2 }}</td>
            <td style="width: 3%;">&nbsp;</td>
            <td style="width: 10%; padding-left: 10px;">Correlativo N°:</td>
            <td style="width: 15%; padding-left: 6px;">{{ $invoice->invoice_number }}</td>
        </tr>
        <tr>
            <td style="padding-left: 6px;">Sucursal:</td>
            <td style="padding-left: 6px;">{{ $invoice->company->company_name }}</td>
            <td>&nbsp;</td>
            <td style="padding-left: 10px;">Modelo de Facturación:</td>
            <td style="padding-left: 6px;">{{ ucfirst($invoice->modelo_facturacion) }}</td>
        </tr>
        <tr>
            <td style="background-color: #e0e0e0; padding: 4px 6px; font-weight: bold; border-top-left-radius: 10px;">Código de Generación:</td>
            <td style="background-color: #e0e0e0; padding: 4px 6px;border-top-right-radius: 10px;">{{ $invoice->codigo_generacion }}</td>
            <td >&nbsp;</td>
            <td style="padding-left: 10px;">Tipo de Transmisión:</td>
            <td style="padding-left: 6px;">{{ ucfirst($invoice->transmision) }}</td>
        </tr>
        <tr>
            <td style="background-color: #e0e0e0; padding: 4px 6px; font-weight: bold;">Número de Control:</td>
            <td style="background-color: #e0e0e0; padding: 4px 6px;">{{ $invoice->numero_control }}</td>
            <td>&nbsp;</td>
            <td style="padding-left: 10px;">Fecha y Hora de Emisión:</td>
            <td style="padding-left: 6px;"s>{{ $invoice->getInvoiceDateTime()->format('d-m-Y H:i:s') }}</td>
        </tr>
        <tr>
            <td style="background-color: #e0e0e0; padding: 4px 6px; font-weight: bold; border-bottom-left-radius: 10px;">Sello de Recepción:</td>
            <td style="background-color: #e0e0e0; padding: 4px 6px; border-bottom-right-radius: 10px; ">{{ $invoice->sello_recepcion }}</td>
            <td >&nbsp;</td>
            <td style="padding-left: 10px;">Fecha y Hora de Generación:</td>
            <td style="padding-left: 6px;">{{ $invoice->created_at->format('d-m-Y H:i:s') }}</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td >&nbsp;</td>
            <td style="padding-left: 10px;">Condición de operación:</td>
            <td style="padding-left: 6px;">{{ $invoice->condicion_operacion->conop_nombre }} 
                {{ ( $invoice->conop_id == 2 && $invoice->plazo?->plazo_nombre != null ) ? $invoice->periodo . ' ' . $invoice->plazo->plazo_nombre : ''  }}
            </td>
        </tr>
    </table>


    <table style="width: 100%;margin:0;">
        <tr>
            <td style="width: 50%; padding: 5px;">
                <p style="font-weight: bold; text-align: center;margin:0;">EMISOR</p>
                <div style="border: 2px solid #565656; border-radius: 10px; min-height: 170px; ">
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
                            <td>
                                {{ $invoice->company->address }}<br>
                                {{ mb_convert_case($invoice->company->municipio->muni_nombre, MB_CASE_TITLE, 'UTF-8') }}, {{ $invoice->company->departamento->depa_nombre }}
                            </td>
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
                            <td>
                                {{ $invoice->client->address }}<br>
                                {{ mb_convert_case($invoice->client->municipio->muni_nombre, MB_CASE_TITLE, 'UTF-8') }}, {{ $invoice->client->departamento->depa_nombre }}
                            </td>
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
    <!-- contenido del cuerpo aquí -->
    <table style="width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 10px; border: 0px solid #565656; border-radius: 10px; overflow: hidden;">
        <thead>
            <tr style="background-color: black; color: white; ">
                <th style="padding: 8px; border-top-left-radius: 10px; width: 6.8%;">CANTIDAD</th>
                <th style="padding: 8px; width: 43%;">DESCRIPCIÓN</th>
                <th style="padding: 8px; width: 8%;">PRECIO UNITARIO</th>
                <th style="padding: 8px; width: 8%;">VENTAS NO SUJETAS</th>
                <th style="padding: 8px; width: 8%;">VENTAS EXENTAS</th>
                <th style="padding: 8px; border-top-right-radius: 10px; width: 8%;">VENTAS GRAVADAS</th>
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
                    $isLast = ($i == count($invoice->invoice_items) - 1);
                @endphp
                <tr>
                    <td style="text-align: center; padding: 5px; border-left: 1px solid #565656; border-right: 1px solid #565656; {{ $isLast ? 'border-bottom: 1px solid #565656; border-bottom-left-radius: 10px;' : 'border-bottom: 0px solid #565656;' }}">
                        {{ number_format($invoice->invoice_items[$i]->quantity, 2, '.', ',') }}
                    </td>
                    {{-- <td class="item borders center">{{ $invoice->invoice_items[$i]->item->product->product_code }}</td> --}}
                    <td style="padding: 5px; border-right: 1px solid #565656; {{ $isLast ? 'border-bottom: 1px solid #565656;' : 'border-bottom: 0px solid #565656;' }}">
                        {!! nl2br($invoice->invoice_items[$i]->description) !!}
                    </td>
                    <td style="text-align: center; padding: 5px; border-right: 1px solid #565656; {{ $isLast ? 'border-bottom: 1px solid #565656;' : 'border-bottom: 0px solid #565656;' }}">
                        {{ $currency }} {{ number_format($invoice->invoice_items[$i]->unit_cost, 2, '.', '') }}
                    </td>
                    <td style="text-align: center; padding: 5px; border-right: 1px solid #565656; {{ $isLast ? 'border-bottom: 1px solid #565656;' : 'border-bottom: 0px solid #565656;' }}">
                        {{ $noSujeto == '' ? '-' : $noSujeto }}
                    </td>
                    <td style="text-align: center; padding: 5px; border-right: 1px solid #565656; {{ $isLast ? 'border-bottom: 1px solid #565656;' : 'border-bottom: 0px solid #565656;' }}">
                        {{ $exento == '' ? '-' : $exento }}
                    </td>
                    <td style="text-align: center; padding: 5px; border-left: 1px solid #565656; border-right: 1px solid #565656; {{ $isLast ? 'border-bottom: 1px solid #565656; border-bottom-right-radius: 10px;' : 'border-bottom: 0px solid #565656;' }}">
                        {{ $gravado == '' ? '-' : $gravado }}
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>


     <!-- Resumen de Totales --> 
      <!-- Resumen de Totales --> 
   <table style="width: 100%;border-collapse: separate; border-spacing: 0; border-top:0;border-bottom:0;">
    <tr>
        <td style="border-top-left-radius: 10px; padding-left: 5px; width: 64.5%; border-left: 1px solid #565656; border-top: 1px solid #565656;">{{ _lang('It is') }} {{ dollarToText($invoice->grand_total) }} DÓLARES</td>
        <td style="padding-left: 5px; width: 6.6%;border-left: 1px solid #565656;border-bottom: 1px solid #565656; border-top: 1px solid #565656;">SUMAS</td>
        <td class="center" style="width: 9.8%;border-left: 1px solid #565656;border-bottom: 1px solid #565656; border-top: 1px solid #565656;">{{ decimalPlace($noSujetoSum, $currency) }}</td>
        <td class="center" style="width: 9.8%;border-left: 1px solid #565656;border-bottom: 1px solid #565656; border-top: 1px solid #565656;">{{ decimalPlace($exentoSum, $currency) }}</td>
        <td class="right"  style="padding-right: 5px; border-top-right-radius: 10px; border-left: 1px solid #565656;border-bottom: 1px solid #565656; border-top: 1px solid #565656;  border-right: 1px solid #565656;">{{ decimalPlace($gravadoSum, $currency) }}</td>
    </tr>
</table>
<table style="width: 100%; border-collapse: separate; border-spacing: 0;">
    <tr style="border-top:0;">
        <td style="width: 64.5%;border-left: 1px solid #565656;"></td>
        <td class="left" style="width: 26.3%;padding-left: 5px; border-left: 1px solid #565656;border-right: 1px solid #565656;">Descuento general</td>
        <td class="right" style="padding-right: 5px; border-right: 1px solid #565656;">{{ decimalPlace($invoice->general_discount, $currency) }}</td>
    </tr>
    <tr style="border-top:0;">
        <td style="width: 64.4%;border-left: 1px solid #565656;"></td>
        <td class="left" style="padding-left: 5px; border-left: 1px solid #565656;border-right: 1px solid #565656;">13 % IVA</td>
        <td class="right" style="padding-right: 5px; border-right: 1px solid #565656;">{{ decimalPlace($invoice->tax_total, $currency) }}</td>
    </tr>
    <tr style="border-top:0;">
        <td style="width: 64.4%;border-left: 1px solid #565656;"></td>
        <td class="left" style=" padding-left: 5px; border-left: 1px solid #565656;border-right: 1px solid #565656;">Sub-Total</td>
        <td class="right" style="padding-right: 5px; border-right: 1px solid #565656;">{{ decimalPlace($gravadoSum -( $invoice->general_discount )+$invoice->tax_total, $currency) }}</td>
    </tr>
    <tr style="border-top:0;">
        <td style="width: 64.4%;border-left: 1px solid #565656;"></td>
        <td class="left" style="padding-left: 5px; border-left: 1px solid #565656;border-right: 1px solid #565656;">(-) IVA Retenido</td>
        <td class="right" style="padding-right: 5px; border-right: 1px solid #565656;">{{ decimalPlace($invoice->iva_retenido, $currency) }}</td>
    </tr>
    <tr style="border-top:0;">
        <td style="width: 64.4%;border-left: 1px solid #565656;"></td>
        <td class="left" style="padding-left: 5px; border-left: 1px solid #565656;border-right: 1px solid #565656;">Retención Renta</td>
        <td class="right" style="padding-right: 5px; border-right: 1px solid #565656;">{{ decimalPlace($invoice->retencion_renta, $currency) }}</td>
    </tr>
    <tr style="border-top:0;">
        <td style="width: 64.4%;border-left: 1px solid #565656;"></td>
        <td class="left" style="padding-left: 5px; border-left: 1px solid #565656;border-right: 1px solid #565656;">Vtas. No sujetas</td>
        <td class="right" style="padding-right: 5px; border-right: 1px solid #565656;">{{ decimalPlace($noSujetoSum, $currency) }}</td>
    </tr>
    <tr style="border-top:0;">
        <td style="width: 64.4%;border-left: 1px solid #565656;"></td>
        <td class="left" style="padding-left: 5px; border-left: 1px solid #565656;border-right: 1px solid #565656;">Vtas. Exentas</td>
        <td class="right" style=" padding-right: 5px; border-right: 1px solid #565656;">{{ decimalPlace($exentoSum, $currency) }}</td>
    </tr>
    <tr style="border-top:0;">
        <td style="padding-left: 5px; border-bottom-left-radius: 10px; border-left: 1px solid #565656;border-bottom: 1px solid #565656; width: 64.4%; " >NOTA: {!! nl2br($invoice->note) !!} </td>
        <td class="left" style="padding-left: 5px; border-left: 1px solid #565656;border-bottom: 1px solid #565656; border-right: 1px solid #565656;">Total</td>
        <td class="right" style="border-bottom-right-radius: 10px; padding-right: 5px; border-bottom: 1px solid #565656; border-right: 1px solid #565656;">{{ decimalPlace($invoice->grand_total, $currency) }}</td>
    </tr> 
</table>

</body>
</html>
