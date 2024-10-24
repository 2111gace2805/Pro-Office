<style>
    .row_ {
    display: flex;
    flex-wrap: wrap;
    }
    .col-md6 {
        flex: 0 0 50%;
        box-sizing: border-box;
        padding: 10px;
    }
    .col-md5 {
        flex: 0 0 41.66667%;
    }
    .col-md1 {
        flex: 0 0 8.33333%;
    }
    .col-md2 {
        flex: 0 0 16.66667%;
    }
</style>
@php
    $style = '';
    if( $invoice->status == 'Canceled' ){
        $style = 'background-image: url('.asset("backend/images/invalidado.png").');background-position: center; background-repeat: no-repeat; background-size: contain;opacity: 0.5;background-size: 70%;';
    }
@endphp
<div id="invoice-view" style="height:100%;{{ $style }}">
    <div class="table-responsive invoice-header" style="">
        <table class="classic-table">
            <tbody>
                <tr>
                    <td style="width:35%">
                        <table>
                            <td>
                                <img src="{{ get_logo() }}" class="mh-80">
                            </td>
                            <td style="padding-left: 5px;">
                                <h3 style="">{{ get_option('company_name') }}</h3>
                                <p style="font-size: 13px"><b>{{ strtoupper(get_option('business_line')) }}</b></p>
                            </td>
                        </table>
                    </td>
                    <td style="font-weight: bold;width:auto;" class="text-center">
                        <div style="font-size: 14px">DOCUMENTO TRIBUTARIO ELECTRÓNICO</div>
                        <div style="font-size: 14px">FACTURA DE EXPORTACIÓN</div>
                    </td>
                    <td class="text-right" style="width:30%">
                        {!! QrCode::size(150)->generate($url) !!}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="row_">
        <div class="table-responsive col-md6" >
            <table class="classic-table" style="font-size: 15px">
                <tr>
                    <td class="font-weight-bold pl-2" style="width: 25%;">Código vendedor</td>
                    <td>{{ $invoice->seller_code->seller_code ?? '-' }} {{ $invoice->formatted_seller_code2 }}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2" style="width: 25%;">{{ _lang('Sucursal:') }}</td>
                    <td>{{ $invoice->company->company_name }}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2" style="width: 25%;">{{ _lang('Código de Generación:') }}</td>
                    <td>{{ $invoice->codigo_generacion }}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('Número de Control:') }}</td>
                    <td style="width: 370px">{{ $invoice->numero_control }}</td>
                </tr>
                 <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('Sello de Recepción:') }}</td>
                    <td class="">{{ $invoice->sello_recepcion }}</td>                    
                </tr>
            </table>
        </div>
        <div class="table-responsive col-md6" >
            <table class="classic-table" style="font-size: 15px">
                <tr>
                    <td class="font-weight-bold pl-2" style="width: 32%;">{{ _lang('Correlativo N°:') }}</td>
                    <td>{{ $invoice->invoice_number }}</td>                    
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2" style="width: 32%;">{{ _lang('Modelo de Facturación:') }}</td>
                    <td>{{ ucfirst($invoice->modelo_facturacion) }}</td>                    
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('Tipo de Transmisión:') }}</td>
                    <td style="width: 370px">{{ ucfirst($invoice->transmision) }}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('Fecha y Hora de Generación:') }}</td>
                    <td class="">{{ $invoice->created_at->format('d-m-Y H:i:s') }}</td>                    
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('Condición de operación:') }}</td>
                    <td class="">{{ $invoice->condicion_operacion->conop_nombre }} {{ ( $invoice->conop_id == 2 && $invoice->plazo?->plazo_nombre != null ) ? $invoice->periodo . ' ' . $invoice->plazo->plazo_nombre : ''  }}</td>            
                </tr>
            </table>
        </div>
    </div>
    <div class="row_">
        <div class="col-md6 text-center">
            <b>EXPORTADOR (Emisor)</b>
        </div>
        <div class="col-md6 text-center">
            <b>RECEPTOR</b>
        </div>
        <div style="border: 2px solid #565656; border-radius: 10px;width:50% !important;" class="table-responsive col-md6" >
            <table class="classic-table" style="font-size: 15px">
                <tr>
                    <td class="font-weight-bold pl-2" style="width: 28%;">{{ _lang('Nombre o razón social:') }}</td>
                    <td>{{ get_option('tradename') }}</td>               
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('NIT:') }}</td>
                    <td style="width: 370px">{{ get_option('nit') }}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('NRC:') }}</td>
                    <td style="width: 370px">{{ get_option('nrc') }}</td>
                </tr>
                 <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('Actividad económica:') }}</td>
                    <td class="">{{ get_option('desc_actividad') }}</td>                    
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('Dirección:') }}</td>
                    <td class="">{{ $invoice->company->address.', '.mb_convert_case( $invoice->company->municipio->muni_nombre, MB_CASE_TITLE, 'UTF-8').', '.$invoice->company->departamento->depa_nombre }}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('Número de teléfono:') }}</td>
                    <td class="">{{ $invoice->company->cellphone }}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('Correo electrónico:') }}</td>
                    <td class="">{{ $invoice->company->email }}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('Tipo de establecimiento:') }}</td>
                    <td class="">{{ $invoice->company->tipoEstablecimiento->tipoest_nombre }}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('Recinto fiscal:') }}</td>
                    <td class="">{{ ( $invoice->nombre_recinto != '' ) ? $invoice->nombre_recinto : '----' }}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('Regimen de exportación:') }}</td>
                    <td class="">{{ ( $invoice->nombre_regimen != '' ) ? $invoice->nombre_regimen : '----' }}</td>
                </tr>
            </table>
        </div>
        <div style="border: 2px solid #565656; border-radius: 10px;width:50% !important;" class="table-responsive col-md6">
            <table class="classic-table" style="font-size: 15px">
                <tr>
                    <td class="font-weight-bold pl-2" style="width: 28%;">{{ _lang('Nombre o razón social:') }}</td>
                    <td style="word-break: break-all !important;">{{ $invoice->name_invoice }}</td>                    
                </tr>
                <tr>
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
                    <td class="font-weight-bold pl-2">{{ $invoice->tipo_documento_receptor->tdocrec_nombre??'' }}</td>
                    <td style="width: 370px">{{$invoice->num_documento }}</td>
                </tr>
                 <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('Actividad económica:') }}</td>
                    <td class="">{{ $invoice->client->descActividad ?? '-' }}</td>                    
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('País destino:') }}</td>
                    <td class="">{{ $invoice->client->pais->pais_nombre }}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('Dirección:') }}</td>
                    <td class="">{{ $invoice->client->address.', '.mb_convert_case( ($invoice->client->municipio->muni_nombre??''), MB_CASE_TITLE,  'UTF-8').', '.($invoice->client->departamento->depa_nombre??'') }}</td>
                </tr>
            </table>
        </div>
    </div>
    <!--End Invoice Information-->

    @php $currency = currency(); @endphp


    {{-- <div style="border: 2px solid black; border-radius: 10px; height: 80%" class=" p-0"> --}}
    <!--Invoice Product-->
    <table class="classic-table" style="font-size: 15px; height:80%" id="invoice-item-table">
        <thead>
            <tr>
                <th style="border-top: 0px solid #565656;border-left: 0px solid #565656; border-radius: 5px !important;padding: 0px;" class="">
                    <div style="border-top: 1px solid #565656; border-radius: 10px 0px 1px 0px; height: 100%; width: 100%; margin: 0; border-right: 0px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656; margin-top: 0px; margin-left: -1px; box-sizing: content-box;" class="d-flex justify-content-center align-items-center">
                        {{ _lang('Quantity') }}
                    </div>
                </th>
                <th style="border-top: 0; border-left: 2px solid #565656; border-bottom: 2px solid #565656; width: 43%" class="text-center">{{ _lang('Description') }}</th>
                <th style="border-top: 0; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-right: 0;width: 12%" class=" text-center">{{ _lang('Unit Price') }}</th>
                <th style="border-top: 0; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-right: 0;" class=" text-center">{{ _lang('Descuento por item') }}</th>
                <th style="border-top: 0; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-right: 2px solid #565656;" class=" text-center">{{ _lang('Otros montos no afectos') }}</th>
                <th style="border-top: 0px solid #565656; border-right: 0; border-left: 0px solid #565656; border-radius: 5px !important; padding: 0px;" class="">
                    <div
                        style="border-top: 1px solid #565656; border-radius: 0px 10px 1px 0px; height: 100%; width: 100%; margin: 0; border-right: 2px solid #565656; border-left: 0px solid #565656; margin-top: 0px; margin-left: -1px; box-sizing: content-box; border-bottom: 2px solid #565656;" class="d-flex justify-content-center align-items-center text-center">
                        {{ _lang('Affected sales') }}
                    </div>
                </th>
            </tr>
        </thead>
        @php
                        $noSujeto = '';
                        $exento = '';
                        $gravado = '';
                        
                        $noSujetoSum = 0;
                        $exentoSum = 0;
                        $gravadoSum = 0;
        @endphp
        <tbody id="invoice">
            @for ($i = 0; $i < count($invoice->invoice_items); $i++)

                @php
                    $sumTaxes = array_sum(array_column($invoice->invoice_items[$i]->taxes()->get()->toArray(), 'amount')); 
                @endphp
                <tr id="product-{{ $invoice->invoice_items[$i]->item_id }}">
                    <td
                        style="border-left: 2px solid #565656; border-right: 0px solid #565656; border-bottom: 0px solid #565656; border-top: 0px solid #565656; width: 70px;" class="text-center pb-0">
                        <b>{{ number_format($invoice->invoice_items[$i]->quantity, 0, '.', ',') }}</b>
                    </td>
                    <td style="border-left: 2px solid #565656; border-right: 0px solid #565656; border-bottom: 0px solid #565656; border-top: 0px solid #565656;"
                        class=" pb-0">{!! nl2br($invoice->invoice_items[$i]->description) !!}</td>
                    <td style="border-left: 2px solid #565656; border-right: 0px solid #565656; border-bottom: 0px solid #565656; border-top: 0px solid #565656;"
                        class="text-center pb-0">{{ $currency }} {{ number_format($invoice->invoice_items[$i]->unit_cost, 6, '.', '') }}</td>

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
                    {{-- VENTAS NO SUJETAS --}}
                    <td style="border-left: 2px solid #565656; border-right: 2px solid #565656; border-bottom: 0px solid #565656; border-top: 0px solid #565656; width: 85px;"
                        class="text-center pb-0">
                        {{ $noSujeto }}</td>
                    {{-- VENTAS EXENTAS --}}
                    <td style="border-left: 2px solid #565656; border-right: 2px solid #565656; border-bottom: 0px solid #565656; border-top: 0px solid #565656; width: 85px;" class="text-center pb-0">
                        {{ $exento }}
                    </td>
                    {{-- VENTAS GRAVADAS --}}
                    <td style="border-left: 2px solid #565656; border-right: 2px solid #565656; border-bottom: 0px solid #565656; border-top: 0px solid #565656; width: 110px;" class="text-right pb-0">{{ $gravado }}</td>
                </tr>
            @endfor
            <tr class="tr-space-white-ccf">
                <td></td><td></td><td></td><td></td><td></td><td style="border-right: 2px solid #565656 !important;"></td>
            </tr>
            <tr>
                <td colspan="6" style="border-top: 2px solid #565656; border-right: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 0; border-radius: 5px !important; padding: 0px;"
                    class="p-1">
                    {{ _lang('It is') }} {{dollarToText($invoice->grand_total)}} DÓLARES
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border-top: 2px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0; border-radius: 5px !important; padding: 0px;"
                    class="p-1">
                    Condición de la operación: {{ $invoice->condicion_op }}
                </td>
                <td colspan="3"  class="p-1 text-right" style="border-top: 2px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-radius: 5px !important; padding: 0px;">Total de Operaciones Afectas:
                </td>
                <td class="p-1 text-right" style="border-top: 2px solid #565656; border-right: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-radius: 5px !important; padding: 0px;">
                {{ decimalPlace($gravadoSum, $currency) }}
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0; border-radius: 5px !important;"
                    class="p-1">
                    Descripción Incoterms: {{ ( $invoice->id_incoterms != '' ) ? $invoice->incoterm->nombre_incoterms : '----' }}
                </td>
                <td class="p-1 text-right" style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-radius: 5px !important;" colspan="3">Rebajas de operaciones afectas:</td>
                <td style="border-top: 0px solid #565656; border-right: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-radius: 5px !important;" class="text-right">{{ decimalPlace($invoice->general_discount, $currency) }}</td>
            </tr>
            <tr>
                <td colspan="2" style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0; border-radius: 5px !important; padding: 0px;"
                    class="">
                    <div
                        style="height: 100%; width: 100%; border-bottom: 0px solid black; border-right: 2px solid black; border-radius: 0px 0 0px 10px; border-top: 0; border-left: 0; margin-left: -1px; margin-top: -1px; box-sizing: content-box;" class="d-flex justify-content-end align-items-center">
                    </div>
                </td>
            </tr>            
            <tr>
                <td colspan="2" rowspan="2" style="border-top: 0px solid #565656;border-right: 0;border-left: 2px solid #565656;border-bottom: 2px solid #565656;/* border-radius: 5px !important; */padding: 0px;"
                    class="p-1">
                    <div
                        style="height: 100%; width: 100%; box-sizing: content-box;border-radius: 0px 0 0px 10px;" class="">
                        NOTA: {{ ( $invoice->note != '' ) ? $invoice->note : '------' }}
                    </div>
                </td>
            </tr>
            <tr>
                <td class="p-1 text-right" style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-radius: 5px !important; padding: 0px;" colspan="3" class="pl-1">Monto Total de la Operación:</td>
                <td style="border-top: 0px solid #565656; border-left: 2px solid #565656; border-radius: 5px !important; padding: 0px; border-bottom: 0px; border-right: 0px;"
                    class="">
                    <div
                        style="height: 100%; width: 100%; border-bottom: 2px solid black; border-right: 2px solid black; border-radius: 0px 0 10px 0px; border-top: 0; border-left: 0; margin-left: -1px; margin-top: -1px; box-sizing: content-box;" class="d-flex justify-content-end align-items-center">
                        {{ decimalPlace($invoice->grand_total, $currency) }}&nbsp;
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <!--End Invoice Product-->
    {{-- </div> --}}

    <div class="clearfix"></div>

    <!--Invoice Footer Text-->
    {{-- @if (get_option('invoice_footer') != '')
        <div class="invoice-note">{!! xss_clean(get_option('invoice_footer')) !!}</div>
    @endif --}}
    <!--End Invoice Note-->
</div>
<!--End Invoice View-->
