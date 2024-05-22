@extends('layouts.app')

@section('content')
<div class="row">

    <div class="col-md-12">
        <div class="btn-group pull-right">
            <a class="btn btn-info btn-sm ajax-modal" data-title="{{ _lang('Send Email') }}"
                href="{{ route('quotations.send_email', $quotation->id) }}"><i class="ti-email"></i>
                {{ _lang('Send Email') }}</a>
            @if($quotation->status == 0)
            <a class="btn btn-success btn-sm" href="#" onclick="convertirAFactura('{{$id}}')"><i
                    class="ti-exchange-vertical"></i> {{ _lang('Convert to Invoice') }}</a>
            {{-- <a class="btn btn-success btn-sm" href="{{ route('quotations.convert_invoice',$quotation->id) }}"><i
                    class="ti-exchange-vertical"></i> {{ _lang('Convert to Invoice') }}</a> --}}
            @else
            <a class="btn btn-success btn-sm disabled" href=""><i class="ti-exchange-vertical"></i>
                {{ _lang('Converted') }}</a>
            @endif
            <a class="btn btn-primary btn-sm print" href="#" data-print="invoice-view"><i class="ti-printer"></i>
                {{ _lang('Print or download') }}</a>
            {{-- <a class="btn btn-danger btn-sm" href="{{ route('quotations.download_pdf',$quotation->id) }}"><i
                    class="ti-file"></i> {{ _lang('Export PDF') }}</a> --}}
            <a class="btn btn-warning btn-sm" href="{{ action('QuotationController@edit', $quotation->id) }}"><i class="ti-pencil-alt"></i> {{ _lang('Edit') }}</a>
            <a class="btn btn-success btn-sm" href="{{ route('quotations.export_to_excel', $quotation->id) }}"><img width="15" height="15" src="https://img.icons8.com/metro/52/FFFFFF/ms-excel.png" alt="ms-excel"/> {{ _lang('Export to Excel') }}</a>
        </div>
        <div class="card card-default clear">
            <div class="card-body">
                <div id="invoice-view">
                    <div class="table-responsive">
                        <table class="classic-table">
                            <tbody>
                                <tr>
                                    <td>
                                        <h4><b>{{ get_option('company_name') }}</b></h4>
                                        {{ get_option('address') }}<br>
                                        {{ get_option('email') }}<br>
                                        {!! get_option('vat_id') != '' ? _lang('VAT ID').':
                                        '.xss_clean(get_option('vat_id')).'<br>' : '' !!}
                                    </td>
                                    <td>
                                        <img src="{{ get_logo() }}" class="mh-80">
                                    </td>
                                </tr>

                                <tr class="information">
                                    <td class="pt-4">
                                        <h5><b>{{ _lang('Quotation To') }}</b></h5>
                                        @if(isset($quotation->client))
                                        {{ $quotation->client->contact_name }}<br>
                                        {{ $quotation->client->contact_email }}<br>
                                        {!! $quotation->client->company_name != '' ?
                                        xss_clean($quotation->client->company_name).'<br>' : '' !!}
                                        {!! $quotation->client->address != '' ?
                                        xss_clean($quotation->client->address).'<br>' : '' !!}
                                        @endif
                                    </td>
                                    <td class="auto-column pt-4">
                                        <h5><b>{{ _lang('Quotation Details') }}</b></h5>

                                        <b>{{ _lang('Quotation') }} #:</b> {{ $quotation->quotation_number }}<br>

                                        <b>{{ _lang('Quotation Date') }}:</b>
                                        {{ $quotation->quotation_date }}<br>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--End quotation Information-->

                    @php $currency = currency(); @endphp

                    <!--quotation Product-->
                    <div class="table-responsive">
                        <table class="table table-bordered mt-2" id="quotation-item-table">
                            <thead class="base_color">
                                <tr>
                                    <th class="text-center wp-100">{{ _lang('Product Code') }}</th>
                                    <th>{{ _lang('Name') }}</th>
                                    {{-- <th>{{ _lang('Original') }}</th> --}}
                                    <th class="text-center wp-100">{{ _lang('Quantity') }}</th>
                                    <th class="text-right">{{ _lang('Unit Cost') }}</th>
                                    <th class="text-right wp-100 d-none">{{ _lang('Discount') }}</th>
                                    <th class="text-right d-none">{{ _lang('Tax') }}</th>
                                    <th class="text-right">{{ _lang('Sub Total') }}</th>
                                    <th class="text-right">{{ _lang('Delivery time') }}</th>
                                </tr>
                            </thead>
                            <tbody id="quotation">
                                @foreach($quotation->quotation_items as $item)
                                <tr id="product-{{ $item->item_id }}">
                                     <td class="text-center">{{ $item->item->product->product_code }}</td>
                                    <td>
                                        <b>{{ $item->item->item_name }}</b><br>{{ $item->description }}
                                    </td>
                                    {{-- <td class="text-center">{{ $item->item->product->original??'-' }}</td> --}}
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-right">{{ decimalPlace($item->unit_cost, $currency) }}</td>
                                    <td class="text-right d-none">{{ decimalPlace($item->discount, $currency) }}</td>
                                    <td class="text-right d-none">{!! xss_clean(object_to_tax($item->taxes, 'name')) !!}</td>
                                    <td class="text-right">{{ decimalPlace(($item->sub_total), $currency) }}</td>
                                    <td class="text-right">{{ $item->delivery_time }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!--End quotation Product-->

                    <!--Summary Table-->
                    <div class="quotation-summary-right">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="quotation-summary-table">
                                <tbody>
                                    {{-- <tr>
                                        <td><b>{{ _lang('Sub Total') }}</b></td>
                                        <td class="text-right">
                                            <b>{{ decimalPlace($quotation->grand_total - $quotation->tax_total, $currency) }}</b>
                                        </td>
                                    </tr>
                                    @foreach($quotation_taxes as $tax)
                                    <tr>
                                        <td>{{ $tax->name }}</td>
                                        <td class="text-right">
                                            <span>{{ decimalPlace($tax->tax_amount, $currency) }}</span>
                                        </td>
                                    </tr>
                                    @endforeach --}}
                                    <tr>
                                        <td>{{ _lang('Grand Total') }}</td>
                                        <td class="text-right">{{ decimalPlace($quotation->grand_total, $currency) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!--End Summary Table-->

                    <div class="clearfix"></div>

                    <!--quotation Note-->
                    @if($quotation->note != '')
                    <div class="invoice-note">{!! xss_clean($quotation->note) !!}</div>
                    @endif
                    <!--End quotation Note-->

                    <!--quotation Footer Text-->
                    @if(get_option('quotation_footer') != '')
                    <div class="invoice-note">{!! xss_clean(get_option('quotation_footer')) !!}</div>
                    @endif
                    <!--End quotation Note-->
                </div>
                <!--End Quotation View-->

            </div>
        </div>
    </div>
</div>
@endsection


@section('js-script')
    <script>
        function convertirAFactura(quotation_id){
                let options = "{{ create_option('tipo_documento', 'tipodoc_id', 'tipodoc_nombre', '11', ['tipodoc_estado='=>'Activo']) }}";
                Swal.fire({
                    title: '<strong>Convertir a</strong>',
                    icon: 'info',
                    html: `<select id="tipodoc_id" class="form-control">`+options+`</select>`,
                    showCancelButton: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Convertir',
                    cancelButtonText: 'No, cancelar'
                    }).then((result) => {
                        if (result.value) {
                            location.href = `{{route('invoices.create')}}`+`?quotation_id=${quotation_id}&tipodoc_id=${$('#tipodoc_id').val()}`;
                        }
                    });
            }
    </script>
@endsection