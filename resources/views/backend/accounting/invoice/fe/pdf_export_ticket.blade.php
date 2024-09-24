<!DOCTYPE html>
<html lang="es">

@php
    $topFooter = 80.7;
    $topHeader = 16.1;
    $currency = currency();
        $noSujeto = ''; $exento = ''; $gravado = '';
        $noSujetoSum = 0; $exentoSum = 0; $gravadoSum = 0;
        $top = 33;
@endphp
<head>
    <title>{{ get_option('site_title', 'Invoice') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <style>
            html{
                padding: 0;
                margin: 0;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12px;
            }

            .linea1{
                position: absolute;
                top: <?php echo ($topHeader+(2.6*1)).'%' ?>;
            }
            .linea2{
                position: absolute;
                top: <?php echo ($topHeader+(2.6*2)).'%' ?>;
            }
            .linea3{
                position: absolute;
                top: <?php echo ($topHeader+(2.6*3)).'%' ?>;
            }
            .linea4{
                position: absolute;
                top: <?php echo ($topHeader+(2.6*4)).'%' ?>;
            }

            .item{
                position: absolute;
            }

            .page_break { page-break-before: always; }

            .footer-linea1{
                position: absolute;
                top: <?php echo $topFooter.'%' ?>;
            }
            .text-center{
                text-align: center;
            }

            

            td {
                padding: 5px; /* Espaciado interno dentro de cada celda */
            }
    </style>
</head>

<body style="color: #636363; padding-left:10px; padding-right:10px; padding-top:10px">

    <div class="text-center">{{ strtoupper(get_option('company_name')) }}</div>
    <br/>
    <div class="text-center">{{ strtoupper($invoice->company->address) }}</div>
    <div class="text-center">N.I.T.: {{get_option('nit', '')}}</div>
    <div class="text-center">N.R.C.: {{get_option('nrc', '')}}</div>
    <div class="text-center">GIRO: {{ strtoupper(get_option('business_line')) }}</div>
    
    
    <div>-----------------------------------------------------------------------</div>
   
    {{-- <div><b>No. Ticket:  {{ $invoice->ticket_number }}</b></div>
    <div><b>Fecha: {{ $invoice->invoice_date }}</b></div>
    <div><b>Hora: {{ $invoice->invoice_time}} </b></div>
    <div><b>Cliente:  {{ $invoice->client->contact_name }}</b></div>
    <div><b>Dirección:  {{ $invoice->client->address??'' }}</b></div> --}}
    <table>
        <tr>
            <td><b>No. Ticket:</b></td>
            <td>{{ $invoice->ticket_number }}</td>
        </tr>
        <tr>
            <td><b>Fecha:</b></td>
            <td>{{ $invoice->invoice_date }}</td>
        </tr>
        <tr>
            <td><b>Hora:</b></td>
            <td>{{ $invoice->invoice_time }}</td>
        </tr>
        <tr>
            <td><b>Cliente:</b></td>
            <td>{{ $invoice->client->contact_name }}</td>
        </tr>
        <tr>
            <td><b>Dirección:</b></td>
            <td>{{ $invoice->client->address ?? '' }}</td>
        </tr>
    </table>
    

    <div>-----------------------------------------------------------------------</div>

    <table>
        <thead>
            <th>Cant.</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Total</th>
        </thead>
        <tbody>
            @for ($i = 0; $i < count($invoice->invoice_items); $i++)
        @php
            $noSujeto = ''; $exento = ''; $gravado = '';
            
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
            <td >{{ $invoice->invoice_items[$i]->quantity }}</td>
            <td >{{ $invoice->invoice_items[$i]->item->product->product_code }} - {{ $invoice->invoice_items[$i]->description }}</td>
            <td >{{ decimalPlace($invoice->invoice_items[$i]->unit_cost, $currency) }}</td>
            <td>{{ ($exento!=''?$exento:($noSujeto!=''?$noSujeto:$gravado)) }} </td>
        </tr>
    @endfor
        </tbody>
    </table>

    <div>-----------------------------------------------------------------------</div>

    <table style="width:100%">
        <tr>
            <td><b>GRAVADO</b></td>
            <td style="text-align:right"><b> {{ decimalPlace($gravadoSum, $currency) }}</b></td>
        </tr>
        <tr>
            <td><b>EXENTO</b></td>
            <td style="text-align:right"><b>{{ decimalPlace($exentoSum, $currency) }}</b></td>
        </tr>
        <tr>
            <td><b>VENTAS NO SUJETAS</b></td>
            <td style="text-align:right"><b>{{ decimalPlace($noSujetoSum, $currency) }}</b></td>
        </tr>
        <tr>
            <td><b>IVA RETENIDO</b></td>
            <td style="text-align:right"><b> {{ decimalPlace($invoice->iva_retenido, $currency) }}</b></td>
        </tr>
        <tr>
            <td><b>TOTAL</b></td>
            <td style="text-align:right"><b> {{ decimalPlace($invoice->grand_total, $currency) }}</b></td>
        </tr>
    </table>

    <div>-----------------------------------------------------------------------</div>

    <table style="width:100%; font-size: smaller;">
        <tr>
            <td> <b> Código de generación: </b></td>
        </tr>
        <tr>
            <td>{{$invoice->codigo_generacion}}</td>    
        </tr>
        <tr>
            <td><b>N° de control</b></td>
        </tr> 
        <tr>
            <td>{{$invoice->numero_control}}</td>
        </tr>
        <tr>
            <td><b>Sello de recepción</b></td>
        </tr>
        <tr>
            <td>{{$invoice->sello_recepcion}}</td>
        </tr>
        <tr>
            <td><b>Condición de operación</b></td>
        </tr>
        <tr>
            <td>{{ $invoice->condicion_operacion->conop_nombre }} {{ ( $invoice->conop_id == 2 && $invoice->plazo?->plazo_nombre != null ) ? $invoice->periodo . ' ' . $invoice->plazo->plazo_nombre : ''  }}</td>
        </tr>
    </table>
    
    <div>-----------------------------------------------------------------------</div>

    <div style="width: 100px; margin: 0 auto;">
        <img src="data:image/png;base64,{{ base64_encode($codigoQR) }}" alt="#" width="100" style="display: block;" />
    </div>
    
    
    

</body>

</html>
