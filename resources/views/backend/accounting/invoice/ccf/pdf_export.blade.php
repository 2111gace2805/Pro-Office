<!DOCTYPE html>
<html lang="es">

 @php 
        $topFooter = 77.5;
        $topHeader = 12.7;
        $currency = currency();
        $noSujeto = ''; $exento = ''; $gravado = '';
        $noSujetoSum = 0; $exentoSum = 0; $gravadoSum = 0;
        $top = 33;
@endphp

<head>
    <title>{{ get_option('site_title', 'Invoice') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    {{-- <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"> --}}
    {{-- <link rel="stylesheet" href="{{ asset('public/backend/assets/css/invoice.css') }}"> --}}

    <style>
            html{
                padding: 0;
                margin: 0;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12px;
            }

            .linea1{
                position: absolute;
                top: <?php echo ($topHeader+(2.5*1)).'%' ?>;
            }
            .linea2{
                position: absolute;
                top: <?php echo ($topHeader+(2.5*2)).'%' ?>;
            }
            .linea3{
                position: absolute;
                top: <?php echo ($topHeader+(2.5*3)).'%' ?>;
            }
            .linea4{
                position: absolute;
                top: <?php echo ($topHeader+(2.5*4)).'%' ?>;
            }
            .linea5{
                position: absolute;
                top: <?php echo ($topHeader+(2.5*5)).'%' ?>;
            }

            .item{
                position: absolute;
            }

            .page_break { page-break-before: always; }

            .footer-linea1{
                position: absolute;
                top: <?php echo $topFooter.'%' ?>;
            }
    </style>
</head>

<body style="/* background-image: url('{{asset('backend/images/ccf.jpg')}}'); */ background-position: center; background-repeat: no-repeat; background-size: contain;">

    <div class="linea1" style="left: 10%; width: 40%">{{ $invoice->client->contact_name }}</div>
    <div class="linea1" style="left: 60%; width: 40%">{{ $invoice->invoice_date }}</div>
    <div class="linea2" style="left: 12%; width: 40%">{{ $invoice->client->address??'' }}</div>
    <div class="linea2" style="left: 60%; width: 40%">{{ $invoice->client->nit??'' }}</div>
    <div class="linea3" style="left: 60%; width: 40%">{{ $invoice->client->nrc??'' }}</div>
    <div class="linea4" style="left: 10%; width: 40%">{{ $invoice->client->business_line??'' }}</div>
    <div class="linea5" style="left: 22%; width: 40%">{{$invoice->periodo.' '.($invoice->plazo->plazo_nombre??'')}}</div>
    <div class="linea5" style="left: 62%; width: 40%">{{Auth::user()->name}}</div>

   

    @for ($i = 0; $i < count($invoice->invoice_items); $i++)
        @php
            $noSujeto = ''; $exento = ''; $gravado = '';
            $subTotalItem = $invoice->invoice_items[$i]->product_price*$invoice->invoice_items[$i]->quantity;
            $subTotalItem = $subTotalItem-$invoice->invoice_items[$i]->discount;
            if($invoice->exento_iva == 'si'){
                $exento = decimalPlace($subTotalItem, $currency);
                $exentoSum += $subTotalItem;
            }else if($invoice->nosujeto_iva == 'si'){
                $noSujeto = decimalPlace($subTotalItem, $currency);
                $noSujetoSum += $subTotalItem;
            }else{
                $gravado = decimalPlace($subTotalItem, $currency);
                $gravadoSum += $subTotalItem;
            }
        @endphp
        <div class="item" style="top: {{$top+($i*4)}}%; left: 6%; width: 40%">{{ $invoice->invoice_items[$i]->quantity }}</div>
        <div class="item" style="top: {{$top+($i*4)}}%; left: 13.5%; width: 40%">{{ $invoice->invoice_items[$i]->item->product->product_code }}</div>
        <div class="item" style="top: {{$top+($i*4)}}%; left: 23%; width: 34%; height: 3.5em; overflow: hidden;">{{ $invoice->invoice_items[$i]->description }} (Garantia: {{$invoice->invoice_items[$i]->item->product->warranty_value}} {{$invoice->invoice_items[$i]->item->product->warranty_type}})</div>
        <div class="item" style="top: {{$top+($i*4)}}%; left: 62%; width: 10%;">{{ decimalPlace($invoice->invoice_items[$i]->unit_cost, $currency) }}</div>
        <div class="item" style="top: {{$top+($i*4)}}%; left: 73%; width: 10%;">{{ $exento==''?'-':$exento }}</div>
        <div class="item" style="top: {{$top+($i*4)}}%; left: 81%; width: 10%;">{{ $noSujeto==''?'-':$noSujeto }}</div>
        <div class="item" style="top: {{$top+($i*4)}}%; left: 87%; width: 8%; text-align:right;">{{ $gravado==''?'-':$gravado }}</div>
    @endfor

    <div class="footer-linea1" style="left: 10%; width: 40%">{{ _lang('It is') }} {{ dollarToText($invoice->grand_total) }}</div>
    <div class="footer-linea1" style="left: 73%; width: 8%">{{ decimalPlace($noSujetoSum, $currency) }}</div>
    <div class="footer-linea1" style="left: 81%; width: 8%">{{ decimalPlace($exentoSum, $currency) }}</div>
    <div class="footer-linea1" style="left: 87%; width: 8%;text-align:right;">{{ decimalPlace($gravadoSum, $currency) }}</div>
    <div style="position: absolute; top: {{$topFooter+(2.4*1)}}%; left: 10%; width: 30%;">NOTA: {{ $invoice->note }}</div>
    <div style="position: absolute; top: {{$topFooter+(2.4*1)}}%; left: 87%; width: 8%;text-align:right;">{{ decimalPlace($invoice->tax_total, $currency) }}</div>
    <div style="position: absolute; top: {{$topFooter+(2.4*2)}}%; left: 87%; width: 8%;text-align:right;">{{ decimalPlace($gravadoSum+$invoice->tax_total, $currency) }}</div>
    <div style="position: absolute; top: {{$topFooter+(2.4*3)}}%; left: 87%; width: 8%;text-align:right;">{{ decimalPlace($invoice->iva_retenido, $currency) }}</div>
    <div style="position: absolute; top: {{$topFooter+(2.4*4)}}%; left: 87%; width: 8%;text-align:right;">{{ decimalPlace($noSujetoSum, $currency) }}</div>
    <div style="position: absolute; top: {{$topFooter+(2.4*5)}}%; left: 87%; width: 8%;text-align:right;">{{ decimalPlace($exentoSum, $currency) }}</div>
    <div style="position: absolute; top: {{$topFooter+(2.4*6)}}%; left: 87%; width: 8%;text-align:right;">{{ decimalPlace($invoice->grand_total, $currency) }}</div>
</body>

</html>