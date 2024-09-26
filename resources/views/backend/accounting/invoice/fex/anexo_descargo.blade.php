<!DOCTYPE html>
<html lang="es">

<head>
    <title>Anexo de descargo</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <link href="{{ asset('public/auth/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        th, tr td{
            padding: 2px !important;
            margin: 2px !important;
        }
        .table-bordered th, .table-bordered tr td{
            border: 1px solid grey !important;
        }
    </style>
</head>

<body style="font-size: 11px">
    <div class="text-right">
        <span><u>{{ get_option('anexo_i') }}</u></span><br><br>
        <b>Anexo #</b>{{$invoice->no_anexo_descargo}} &nbsp;&nbsp;&nbsp;<span>{{date('d-m-Y')}}</span>
    </div>
    <table class="w-100">
        <tr>
            <td style="width: 120px">Empresa envía:</td>
            <td><b>{{ get_option('company_name') }}</b></td>
            <td class="w-25">NIT: {{ get_option('nit') }}</td>
        </tr>
        <tr>
            <td>Dirección:</td>
            <td>{{ get_option('address') }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Empresa receptora:</td>
            <td><b>{{ $invoice->client->company_name }}</b></td>
            <td>NIT: {{ $invoice->client->nit }}</td>
        </tr>
        <tr>
            <td>Dirección:</td>
            <td>{{ $invoice->client->address.'. '.($invoice->client->municipio->muni_nombre??'').', '.($invoice->client->departamento->depa_nombre??'') }}</td>
            <td></td>
        </tr>
    </table>
    <div class="text-right">
        DM: {{get_option('dm')}}<br>
    </div>

    <div class="text-center mb-4 mt-2">DETALLE DE BIENES DESCARGADOS EN TRASLADOS DEFINITIVOS Y DEVOLUCIONES DE TRASLADO TEMPORALES</div>

    <table class="table table-bordered">
            <tr>
                <th rowspan="2" class="align-middle" style="width: 10px">DECLARACIONES DE DESCARGO</th>
                <th rowspan="2" class="align-middle">ADUANA DE REGISTRO</th>
                <th rowspan="2" class="align-middle">FECHA DE REGISTRO</th>
                <th rowspan="2" class="align-middle">ÍTEM DEL DESCARGO</th>
                <th rowspan="2" class="align-middle">CÓDIGO ARANCELARIA</th>
                <th rowspan="2" class="align-middle">DESCRIPCIÓN DE LOS BIENES</th>
                <th colspan="2">SALDOS PENDIENTES DE DEVOLUCIÓN</th>
                <th rowspan="2" class="align-middle">OBSERVACIONES</th>
            </tr>
            <tr>
                {{-- <td ></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td> --}}
                <td>Cantidad</td>
                <td >Unidad de medida</td>
            </tr>
            @for ($i = 0; $i < count($invoice->invoice_items); $i++)
                <tr>
                    <td>{{ $invoice->invoice_items[$i]->no_declaracion }}</td>
                    <td>{{ $invoice->invoice_items[$i]->aduana_registro }}</td>
                    <td>{{ $invoice->invoice_items[$i]->fecha_registro }}</td>
                    <td>{{ $invoice->invoice_items[$i]->line }}</td>
                    <td>{{ $invoice->invoice_items[$i]->codigo_arancelario }}</td>
                    <td>{{ $invoice->invoice_items[$i]->item->item_name }}</td>
                    <td>{{ $invoice->invoice_items[$i]->quantity }}</td>
                    <td>{{ $invoice->invoice_items[$i]->item->product->unidad_medida->unim_nombre }}</td>
                    <td>{{ $invoice->invoice_items[$i]->observacion }}</td>
                </tr>
            @endfor
    </table>
    <p>No. De Factura de Exportación: {{$invoice->invoice_number}}</p>
</body>

</html>