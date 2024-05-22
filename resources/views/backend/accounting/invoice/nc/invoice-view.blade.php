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


<div id="invoice-view" style="height:100%;">
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
                        <div style="font-size: 14px">{{ mb_strtoupper($invoice->tipo_documento->tipodoc_nombre, 'UTF-8') }}</div>
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
                    <td>{{ $invoice->seller_code->seller_code ?? '-' }}</td>
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
            </table>
        </div>
    </div>
    <div class="row_">
        <div class="col-md6 text-center">
            <b>EMISOR</b>
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
            </table>
        </div>
        <div style="border: 2px solid #565656; border-radius: 10px;width:50% !important;" class="table-responsive col-md6">
            <table class="classic-table" style="font-size: 15px">
                <tr>
                    <td class="font-weight-bold pl-2" style="width: 28%;">{{ _lang('Nombre o razón social:') }}</td>
                    <td>{{ $invoice->name_invoice }}</td>                    
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('NIT:') }}</td>
                    <td style="width: 370px">{{ $invoice->client->nit }}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('NRC:') }}</td>
                    @php
                        $nrc = $invoice->client->nrc;
                        $parte1 = substr($nrc, 0, 6);
                        $parte2 = substr($nrc, 6);
                        $format_nrc = $parte1 . '-' . $parte2;
                    @endphp
                    <td style="width: 370px">{{ $format_nrc }}</td>
                </tr>
                 <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('Actividad económica:') }}</td>
                    <td class="">{{ $invoice->client->descActividad ?? '-' }}</td>                    
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('Dirección:') }}</td>
                    <td class="">{{ $invoice->client->address.', '.mb_convert_case( $invoice->client->municipio->muni_nombre, MB_CASE_TITLE,  'UTF-8').', '.$invoice->client->departamento->depa_nombre }}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('Número de teléfono:') }}</td>
                    <td class="">{{ $invoice->client->contact_phone }}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold pl-2">{{ _lang('Correo electrónico:') }}</td>
                    <td class="">{{ $invoice->client->contact_email }}</td>
                </tr>
            </table>
        </div>
    </div>
    <!--End Invoice Information-->

    @php $currency = currency(); @endphp

    <div class="table-responsive invoice-header" style="">
        <table class="classic-table">
            <tbody>
                <tr>
                    <td style="width:35%">
                    </td>
                    <td style="font-weight: bold;width:auto;" class="text-center">
                        <div style="font-size: 14px">DOCUMENTOS RELACIONADOS</div>
                    </td>
                    <td class="text-right" style="width:30%">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <table class="classic-table" style="font-size: 15px;" id="invoice-docs-table">
        <thead>
            <tr>
                <th style="border-top: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656;" class="text-center">{{ _lang('Tipo de Documento') }}</th>
                <th style="border-top: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656; width: 43%" class="text-center">{{ _lang('N° de Documento') }}</th>
                <th style="border-top: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-right: 2px solid #565656;" class=" text-center">{{ _lang('Fecha de Documento') }}</th>
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
                            style="border-left: 2px solid #565656; border-right: 0px solid #565656; border-bottom: 2px solid #565656; border-top: 0px solid #565656;" class="text-center">
                            <b>{{ $invoice->invoice_items[$i]->tipoDocumento->tipodoc_nombre }}</b>
                        </td>
                        <td style="border-left: 2px solid #565656; border-right: 0px solid #565656; border-bottom: 2px solid #565656; border-top: 0px solid #565656;"
                            class="text-center ">{{ $invoice->invoice_items[$i]->cod_dte_rel }}</td>
                        <td style="border-left: 2px solid #565656; border-right: 2px solid #565656; border-bottom: 2px solid #565656; border-top: 0px solid #565656;"
                            class="text-center">{{ \Carbon\Carbon::parse($invoice->invoice_items[$i]->date_dte_rel)->format('d-m-Y') }}
                        </td>
                    </tr>
                    @php
                        $cod_dte_rel_array[] = $invoice->invoice_items[$i]->cod_dte_rel;
                    @endphp
                @endif
            @endfor
        </tbody>
    </table>

    {{-- <div style="border: 2px solid #565656; border-radius: 10px; height: 80%" class=" p-0"> --}}
    <!--Invoice Product-->
    <table class="classic-table" style="font-size: 15px; margin-top:10px;" id="invoice-item-table">
        <thead>
            <tr>
                <th class=" text-center" style="border-top: 2px solid #565656; border-left: 2px solid #565656;padding: 8px;">
                    {{ _lang('Quantity') }}
                    {{-- <div
                        style="border-top: 1px solid #565656; border-radius: 10px 0px 1px 0px; height: 100%; width: 100%; margin: 0; border-right: 0px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656; margin-top: 0px; margin-left: -1px; box-sizing: content-box;" class="d-flex justify-content-center align-items-center">
                        
                    </div> --}}
                </th>
                <th style="border-top: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656;" class="text-center">Código</th>
                <th style="border-top: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656; width: 43%" class="text-center">{{ _lang('Description') }}</th>
                <th style="border-top: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-right: 0;" class=" text-center">{{ _lang('Unit Price') }}</th>
                <th style="border-top: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-right: 0;" class=" text-center">{{ _lang('Ventas no sujetas') }}</th>
                <th style="border-top: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-right: 2px solid #565656;" class=" text-center">{{ _lang('Ventas exentas') }}</th>
                {{-- <th class=" text-right wp-100">{{ _lang('Discount') }}</th> --}}
                {{-- <th class=" text-right">{{ _lang('Tax') }}</th> --}}
                <th style="border-top: 2px solid #565656; border-left: 2px solid #565656;padding: 8px;" class="text-center">
                    {{ _lang('Affected sales') }}
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
                <tr id="product-{{ $invoice->invoice_items[$i]->item_id }}">
                    <td
                        style="border-left: 2px solid #565656; border-right: 0px solid #565656; border-bottom: 0px solid #565656; border-top: 0px solid #565656;" class="text-center">
                        <b>{{ $invoice->invoice_items[$i]->quantity }}</b>
                    </td>
                    <td style="border-left: 2px solid #565656; border-right: 0px solid #565656; border-bottom: 0px solid #565656; border-top: 0px solid #565656;"
                        class="text-center ">{{ $invoice->invoice_items[$i]->item->product->product_code }}</td>
                    <td style="border-left: 2px solid #565656; border-right: 0px solid #565656; border-bottom: 0px solid #565656; border-top: 0px solid #565656;"
                        class="">{!! nl2br($invoice->invoice_items[$i]->description) !!}/td>
                    <td style="border-left: 2px solid #565656; border-right: 0px solid #565656; border-bottom: 0px solid #565656; border-top: 0px solid #565656;"
                        class="text-center">{{ decimalPlace($invoice->invoice_items[$i]->unit_cost, $currency) }}</td>
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
                    <td style="border-left: 2px solid #565656; border-right: 2px solid #565656; border-bottom: 0px solid #565656; border-top: 0px solid #565656;"
                        class="text-center">
                        {{ $noSujeto }}</td>
                    {{-- VENTAS EXENTAS --}}
                    <td style="border-left: 2px solid #565656; border-right: 2px solid #565656; border-bottom: 0px solid #565656; border-top: 0px solid #565656;" class="text-center">
                        {{ $exento }}
                    </td>
                    {{-- VENTAS GRAVADAS --}}
                    <td style="border-left: 2px solid #565656; border-right: 2px solid #565656; border-bottom: 0px solid #565656; border-top: 0px solid #565656;" class="text-center">{{ $gravado }}</td>
                </tr>
            @endfor
            <tr class="tr-space-white-ccf">
                <td></td><td></td><td></td><td></td><td></td><td></td><td style="border-right: 2px solid #565656 !important;"></td>
            </tr>
            <tr>
                <td colspan="3" style="border-top: 2px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0; border-radius: 5px !important; padding: 0px;"
                    class="p-1" colspan="3">
                    {{ _lang('It is') }} {{dollarToText($invoice->grand_total)}}
                </td>
                <td class="p-1" style="border-top: 2px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0px solid #565656; border-radius: 5px !important; padding: 0px;">SUMAS
                </td>
                <td class="p-1" style="border-top: 2px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-radius: 5px !important; padding: 0px;">
                {{ decimalPlace($noSujetoSum, $currency) }}
                </td>
                <td class="p-1" style="border-top: 2px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-radius: 5px !important; padding: 0px;">
                {{ decimalPlace($exentoSum, $currency) }}
                </td>
                <td class="p-1" style="border-top: 2px solid #565656; border-right: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-radius: 5px !important; padding: 0px; text-align: right;">
                {{ decimalPlace($gravadoSum, $currency) }}
                </td>
               
            </tr>
            <tr>
                <td colspan="3" style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0; border-radius: 5px !important;"
                    class="">
                    NOTA: {{ $invoice->note }}
                </td>
                <td style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0; border-radius: 5px !important;" colspan="2">13 % IVA</td>
                <td style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0; border-radius: 5px !important;"></td>
                <td style="border-top: 0px solid #565656; border-right: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-radius: 5px !important;" class="text-right pr-1">{{decimalPlace($invoice->tax_total,$currency)}}</td>
            </tr>
            <tr>
                <td colspan="3" style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0; border-radius: 5px !important; padding: 0px;"
                    class="">
                </td>
                <td style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0; border-radius: 5px !important; padding: 0px;" colspan="2">Sub-Total</td>
                <td style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0; border-radius: 5px !important; padding: 0px;"></td>
                <td style="border-top: 0px solid #565656; border-right: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-radius: 5px !important; padding: 0px;" class="text-right pr-1">{{ decimalPlace($gravadoSum+$invoice->tax_total, $currency) }}</td>
            </tr>
            <tr>
                <td colspan="3" style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0 !important; border-radius: 5px !important; padding: 0px;"
                    class="">
                </td>
                <td style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0; border-radius: 5px !important; padding: 0px;" colspan="2">(-) IVA Retenido</td>
                <td style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0; border-radius: 5px !important; padding: 0px;"></td>
                <td style="border-top: 0px solid #565656; border-right: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-radius: 5px !important; padding: 0px;" class="text-right">{{ decimalPlace($invoice->iva_retenido, $currency) }}&nbsp;</td>
            </tr>
            <tr>
                <td colspan="3" style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0; border-radius: 5px !important; padding: 0px;"
                    class="">
                </td>
                <td style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0; border-radius: 5px !important; padding: 0px;" colspan="2">(-) IVA Retenido</td>
                <td style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0; border-radius: 5px !important; padding: 0px;"></td>
                <td style="border-top: 0px solid #565656; border-right: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 1px solid #565656 !important; border-radius: 5px !important; padding: 0px;" class="text-right">{{ decimalPlace($invoice->iva_retenido, $currency) }}&nbsp;</td>
            </tr>
            <tr>
                <td colspan="3" rowspan="2" style="border-top: 0px solid #565656;border-right: 0;border-left: 2px solid #565656;border-bottom: 2px solid #565656;/* border-radius: 5px !important; */padding: 0px;"
                    class="">
                    {{-- <div
                        style="border-top: 0px solid #565656;border-radius: 0px 0px 0px 10px;height: 100%;width: 100%;margin: 0;border-right: 0px solid #565656;border-left: 2px solid black; margin-top: -1px;margin-left: -1px;box-sizing: content-box;border-bottom: 2px solid black;" class="pl-2">
                        
                    </div> --}}
                </td>
                <td style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0; border-radius: 5px !important; padding: 0px;" colspan="2">Vtas. Extentas</td>
                <td style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 0; border-radius: 5px !important; padding: 0px;"></td>
                <td style="border-top: 0px solid #565656; border-right: 2px solid #565656; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-radius: 5px !important; padding: 0px;" class="text-right pr-1">{{ decimalPlace($exentoSum, $currency) }}</td>
            </tr>
            <tr>
                {{-- <td colspan="3" style="border-top: 2px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-radius: 5px !important; padding: 0px;"
                    class="">
                </td> --}}
                <td style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-radius: 5px !important; padding: 0px;" colspan="2">Total</td>
                <td style="border-top: 0px solid #565656; border-right: 0; border-left: 2px solid #565656; border-bottom: 2px solid #565656; border-radius: 5px !important; padding: 0px;"></td>
                <td style="border: 0; padding: 0px; border-left: 2px solid #565656;">
                    <div
                        style="border-top: 0px solid #565656; height: 100%; width: 100%; margin: 0; border-right: 2px solid black; border-left: 0px solid #565656; margin-top: -1px; margin-left: -1px; box-sizing: content-box; border-bottom: 2px solid black; border-radius: 0px 0px 10px 0;" class="text-right">
                        {{ decimalPlace($invoice->grand_total, $currency) }}&nbsp;
                        {{-- {{ decimalPlace($gravadoSum+$invoice->tax_total-$invoice->iva_retenido+$noSujetoSum+$exentoSum, $currency) }}&nbsp; --}}
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
