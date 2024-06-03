@extends('layouts.app')

@section('content')

<style>
    .font_11 {
        font-size: 10px;
    }
    th{
        font-size: 10px;
    }
    #details table,
    #details th,
    #details td {
        border: 1px solid black;
        border-collapse: collapse;
    }
    
    #head{
        border-top: 1px solid black;
        border-left: 1px solid black;
        border-right: 1px solid black;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row g-2 mb-4">
                    <div class="col-md">
                        <div class="form-floating">
                            <a class="btn btn-primary btn-sm" href="{{ action('OrderNoteController@print', $order->id) }}" target="_blank">
                                <i class="ti-printer"></i>
                                Imprimir Nota
                            </a>
        
                            <a class="btn btn-warning btn-sm text-white" href="{{ action('OrderNoteController@edit', $order->id) }}">
                                <i class="ti-pencil-alt"></i>
                                {{ _lang('Edit') }}
                            </a>            
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="form-floating">
                            <a href="{{ action('OrderNoteController@create') }}" class="btn btn-primary">
                                <i class="fas fa-file-invoice"></i>
                                Nueva nota de pedido
                            </a> 
                            <a href="{{ action('OrderNoteController@index') }}" class="btn btn-primary">
                                <i class="fas fa-list"></i>
                                Lista de notas de pedido
                            </a> 
                        </div>
                    </div>
                </div>
                <div id="invoice-view">
                    <div class="table-responsive">
                        <table id="head" class="classic-table">
                            <tbody>
                                <tr>
                                    <td style="width:5%">
                                        <table>
                                            <td>
                                                <img src="{{ get_logo() }}" class="mh-80">
                                            </td>
                                        </table>
                                    </td>
                                    <td style="font-weight: bold;width:auto;" class="text-center">
                                        <table style="width:100%">
                                            <tbody>
                                                <tr>
                                                    <td style="width:7%"></td>
                                                    <td class="text-center font_11">NOTA DE PEDIDO</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-left font_11">CLIENTE</td>
                                                    <td class="text-center font_11">{{ $order->client->contact_name }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font_11 text-left" style="width:7%">GESTION DE COMPRA</td>
                                                    <td class="font_11 text-center">
                                                        <table style="width:100%">
                                                            <tbody>
                                                                <tr>
                                                                    <td>LICITACIÓN COMPETITIVA LC No.</td>
                                                                    
                                                                </tr>
                                                                <tr>
                                                                    <td>RESOLUCIÓN DE ADJUDICACIÓN:</td>
                                                                    
                                                                </tr>
                                                                <tr>
                                                                    <td>CONTRATO No. {{ $order->num_contract }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td style="font-weight: bold;width:14%;" class="text-center">
                                        <table style="width:100%">
                                            <tbody>
                                                <tr>
                                                    <div class="font_11">{{ $order->created_at->format('d-m-Y') }}</div>
                                                </tr>
                                                <tr>
                                                    <td class="text-left"><p class="font_11">FECHA DE ENTREGA</p></td>
                                                    @php
                                                        use Carbon\Carbon;
                                                    @endphp
                                                    <td><p class="font_11">{{ Carbon::createFromFormat('Y-m-d', $order->deliver_date_contract)->format('d-m-Y') }}</p></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table id="details" style="width:100%">
                            <thead>
                                <tr class="text-center">
                                    <th style="width:6%">{{ _lang('PAGO DE ANÁLISIS') }}</th>
                                    <th>{{ _lang('RENGLÓN') }}</th>
                                    <th>{{ _lang('CÓDIGO') }}</th>
                                    <th>{{ _lang('REFERENCIA') }}</th>
                                    <th style="width:20%">{{ _lang('DESCRIPCIÓN') }}</th>
                                    <th>{{ _lang('CANTIDAD') }}</th>
                                    <th>{{ _lang('MUESTRAS') }}</th>
                                    <th style="width:6%">{{ _lang('No. DE ENTREGA') }}</th>
                                    <th>{{ _lang('MARCA') }}</th>
                                    <th>{{ _lang('ORIGEN') }}</th>
                                    <th style="width:7%">{{ _lang('VENCIMIENTO OFERTADO') }}</th>
                                    <th>{{ _lang('LOTE') }}</th>
                                    <th>{{ _lang('VENCE') }}</th>
                                    <th>{{ _lang('MANUFACTURA') }}</th>
                                    <th>{{ _lang('CERTIFICADO DE ANÁLISIS') }}</th>
                                    <th>{{ _lang('EMPRESA') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font_11">
                                @foreach ($orderDetails as $detail)
                                    @php
                                        $product = $products[$detail->product_id] ?? null;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $detail->payment_analysis ?? '' }}</td>
                                        <td class="text-center">{{ $detail->line ?? '' }}</td>
                                        <td class="text-center">{{ $detail->code_product_institution ?? '' }}</td>
                                        <td class="text-center">{{ $product->product_code ?? '' }}</td>
                                        <td>{{ $detail->product_description ?? '' }}</td>
                                        <td class="text-center">{{ $detail->quantity ?? '' }}</td>
                                        <td class="text-center">{{ $detail->samples ?? '' }}</td>
                                        <td class="text-center">{{ $detail->delivery_number ?? '' }}</td>
                                        <td class="text-center">{{ $detail->product_brand ?? '' }}</td>
                                        <td class="text-center">{{ $detail->product_origin ?? '' }}</td>
                                        <td class="text-center">{{ $detail->offered_expiry ?? '' }}</td>
                                        <td class="text-center">{{ $detail->product_lot ?? '' }}</td>
                                        <td class="text-center">{{ $detail->expires ?? '' }}</td>
                                        <td class="text-center">{{ $detail->manufacture ?? '' }}</td>
                                        <td class="text-center">{{ $detail->analysis_certificate ?? '' }}</td>
                                        <td class="text-center">{{ $detail->product_delivery_company ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!--End Invoice Information-->
                </div>
                <!--End Invoice View-->

            </div>
        </div>
    </div>
</div>
@endsection