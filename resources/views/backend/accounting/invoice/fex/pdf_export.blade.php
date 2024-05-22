<!DOCTYPE html>
<html lang="es">

@php 
    $topHeader = 10.5;
    $top = 28;
        $currency = currency();
        $noSujeto = ''; $exento = ''; $gravado = '';
        $noSujetoSum = 0; $exentoSum = 0; $gravadoSum = 0;
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
                /* font-size: 1.5em; */
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
            .linea5{
                position: absolute;
                top: <?php echo ($topHeader+(2.6*5)).'%' ?>;
            }

            .item{
                position: absolute;
            }

            .page_break { page-break-before: always; }

            .footer-linea1{
                position: absolute;
                top: 89.5%;
            }
    </style>
</head>

<body style="/* background-image: url('{{asset('backend/images/fex.jpg')}}'); */  background-position: center; background-repeat: no-repeat; background-size: contain;">

    <div class="linea1" style="left: 13.5%; width: 40%">{{ $invoice->invoice_date }}</div>
    <div class="linea2" style="left: 13.5%; width: 40%">{{ $invoice->client->contact_name }}</div>
    <div class="linea3" style="left: 13.5%; width: 50%">{{ $invoice->client->address??'' }}</div>
    <div class="linea4" style="left: 25%; width: 40%">{{Auth::user()->name}}</div>
    <div style="position: absolute; top: {{$topHeader+14}}%; left: 21%; width: 40%">LÍNEA</div>

    @for ($i = 0; $i < count($invoice->invoice_items); $i++)
        @php
            $noSujeto = ''; $exento = ''; $gravado = '';
            /*
            if($invoice->exento_iva == 'si'){
                $exento = decimalPlace($invoice->invoice_items[$i]->sub_total, $currency);
                $exentoSum += $invoice->invoice_items[$i]->sub_total;
            }else if($invoice->nosujeto_iva == 'si'){
                $noSujeto = decimalPlace($invoice->invoice_items[$i]->sub_total, $currency);
                $noSujetoSum += $invoice->invoice_items[$i]->sub_total;
            }else{
                */
                $gravado = decimalPlace($invoice->invoice_items[$i]->sub_total, $currency);
                $gravadoSum += $invoice->invoice_items[$i]->sub_total;
            /*
            }
            */
        @endphp
        <div class="item" style="top: {{$top+($i*4)}}%; left: 5%; width: 40%">{{ $invoice->invoice_items[$i]->quantity }}</div>
        <div class="item" style="top: {{$top+($i*4)}}%; left: 11.3%; width: 40%">{{ $invoice->invoice_items[$i]->item->product->product_code }}</div>
        <div class="item" style="top: {{$top+($i*4)}}%; left: 23%; width: 5%">{{ $invoice->invoice_items[$i]->line }}</div>
        <div class="item" style="top: {{$top+($i*4)}}%; left: 27%; width: 44%; height: 3.5em; overflow: hidden;">{{ $invoice->invoice_items[$i]->description }} (Garantia: {{$invoice->invoice_items[$i]->item->product->warranty_value}} {{$invoice->invoice_items[$i]->item->product->warranty_type}})</div>
        <div class="item" style="top: {{$top+($i*4)}}%; left: 74%; width: 10%; text-align:center">{{ decimalPlace($invoice->invoice_items[$i]->unit_cost, $currency) }}</div>
        {{-- <div class="item" style="top: {{$top+($i*4)}}%; left: 75.5%; width: 10%;">{{ $exento==''?'-':$exento }}</div> --}}
        {{-- <div class="item" style="top: {{$top+($i*4)}}%; left: 81.5%; width: 10%;">{{ $noSujeto==''?'-':$noSujeto }}</div> --}}
        <div class="item" style="top: {{$top+($i*4)}}%; left: 88%; width: 8%; text-align:right;">{{ $gravado==''?'-':$gravado }}</div>
    @endfor

    <div class="footer-linea1" style="left: 10%; width: 40%">{{dollarToText($invoice->grand_total)}}</div>
    <div class="footer-linea1" style="left: 88%; width: 8%;text-align:right;">{{ decimalPlace($invoice->grand_total, $currency) }}</div>
    {{-- <div class="footer-linea1" style="left: 75.5%; width: 8%">{{ decimalPlace($noSujetoSum, $currency) }}</div>
    <div class="footer-linea1" style="left: 81.5%; width: 8%">{{ decimalPlace($exentoSum, $currency) }}</div> --}}
    {{-- <div class="footer-linea1" style="left: 88%; width: 8%;text-align:right;">{{ decimalPlace($gravadoSum, $currency) }}</div>
    <div style="position: absolute; top: 83%; left: 88%; width: 8%;text-align:right;">{{ decimalPlace($invoice->iva_retenido, $currency) }}</div>
    <div style="position: absolute; top: 85.1%; left: 88%; width: 8%;text-align:right;">{{ decimalPlace($gravadoSum-$invoice->iva_retenido, $currency) }}</div>
    <div style="position: absolute; top: 87.1%; left: 88%; width: 8%;text-align:right;">{{ decimalPlace($noSujetoSum, $currency) }}</div>
    <div style="position: absolute; top: 89.4%; left: 88%; width: 8%;text-align:right;">{{ decimalPlace($exentoSum, $currency) }}</div> --}}
    {{-- <div style="position: absolute; top: 80%; left: 88%; width: 8%;text-align:right;">{{ decimalPlace($invoice->grand_total, $currency) }}</div> --}}
</body>

</html>