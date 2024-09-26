@extends('layouts.public')

@section('content')
<div class="row">

    <div class="col-md-12">
        <div class="btn-group pull-right">
            <a class="btn btn-primary btn-sm print" href="#" data-print="quotation-view"><i class="ti-printer"></i>
                {{ _lang('Print') }}</a>
            <a class="btn btn-danger btn-sm"
                href="{{ route('client.download_pdf_quotation', encrypt($quotation->id)) }}"><i
                    class="ti-file"></i> {{ _lang('PDF Export') }}</a>
            @auth
            <a class="btn btn-secondary btn-sm" href="{{ route('dashboard') }}"><i class="ti-dashboard"></i>
                {{ _lang('Dashboard') }}</a>
            @endauth
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
                                    <th>{{ _lang('Name') }}</th>
                                    <th class="text-center wp-100">{{ _lang('Quantity') }}</th>
                                    <th class="text-right">{{ _lang('Unit Cost') }}</th>
                                    <th class="text-right wp-100">{{ _lang('Discount') }}</th>
                                    <th class="text-right">{{ _lang('Tax') }}</th>
                                    <th class="text-right">{{ _lang('Sub Total') }}</th>
                                </tr>
                            </thead>
                            <tbody id="quotation">
                                @foreach($quotation->quotation_items as $item)
                                <tr id="product-{{ $item->item_id }}">
                                    <td>
                                        <b>{{ $item->item->item_name }}</b><br>{{ $item->description }}
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-right">{{ decimalPlace($item->unit_cost, $currency) }}</td>
                                    <td class="text-right">{{ decimalPlace($item->discount, $currency) }}</td>
                                    <td class="text-right">{!! xss_clean(object_to_tax($item->taxes, 'name')) !!}</td>
                                    <td class="text-right">{{ decimalPlace($item->sub_total, $currency) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!--End quotation Product-->

                    <!--Summary Table-->
                    <div class="invoice-summary-right">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="invoice-summary-table">
                                <tbody>
                                    <tr>
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
                                    @endforeach
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
            </div>
        </div>
    </div>
</div>
@endsection