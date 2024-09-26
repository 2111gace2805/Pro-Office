@extends('layouts.public')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="btn-group mb-2">
            <a class="btn btn-primary print" href="#" data-print="invoice-view"><i class="ti-printer"></i>
                {{ _lang('Print Invoice') }}</a>
            <a class="btn btn-danger" href="{{ route('client.download_pdf_invoice', encrypt($invoice->id)) }}"><i
                    class="ti-file"></i> {{ _lang('PDF Export') }}</a>
            @auth
            <a class="btn btn-secondary" href="{{ route('dashboard') }}"><i class="ti-dashboard"></i>
                {{ _lang('Dashboard') }}</a>
            @endauth
        </div>

        <div class="float-md-right mb-2">
        @if($invoice->status != 'Paid')
            <select Class="form-control bg-dark text-white" id="select_payment_method">
                <option value="">{{ _lang('Payment Method') }}</option>
                @if(get_option('paypal_active') == 'yes')
                <option value="paypal">{{ _lang('PayPal') }}</option>
                @endif

                @if(get_option('stripe_active') == 'yes')
                <option value="stripe">{{ _lang('Credit Card') }}</option>
                @endif
            </select>
        @endif
        </div>

        <div class="clearfix"></div>

        <div class="card">
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
                                        <h5><b>{{ _lang('Invoice To') }}</b></h5>
                                        @if(isset($invoice->client))
                                        {{ $invoice->client->contact_name }}<br>
                                        {{ $invoice->client->contact_email }}<br>
                                        {!! $invoice->client->company_name != '' ?
                                        xss_clean($invoice->client->company_name).'<br>' : '' !!}
                                        {!! $invoice->client->address != '' ?
                                        xss_clean($invoice->client->address).'<br>' : '' !!}
                                        @endif
                                    </td>
                                    <td class="auto-column pt-4">
                                        <h5><b>{{ _lang('Invoice Details') }}</b></h5>

                                        <b>{{ _lang('Invoice') }} #:</b> {{ $invoice->invoice_number }}<br>

                                        <b>{{ _lang('Invoice Date') }}:</b>
                                        {{ $invoice->invoice_date }}<br>

                                        <b>{{ _lang('Due Date') }}:</b>
                                        {{ $invoice->due_date }}<br>

                                        <b>{{ _lang('Payment Status') }}:</b>
                                        {{ _dlang(str_replace('_',' ',$invoice->status)) }}<br>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--End Invoice Information-->

                    @php $currency = currency(); @endphp

                    <!--Invoice Product-->
                    <div class="table-responsive">
                        <table class="table table-bordered mt-2" id="invoice-item-table">
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
                            <tbody id="invoice">
                                @foreach($invoice->invoice_items as $item)
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
                    <!--End Invoice Product-->

                    <!--Summary Table-->
                    <div class="invoice-summary-right">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="invoice-summary-table">
                                <tbody>
                                    <tr>
                                        <td><b>{{ _lang('Sub Total') }}</b></td>
                                        <td class="text-right">
                                            <b>{{ decimalPlace($invoice->grand_total - $invoice->tax_total, $currency) }}</b>
                                        </td>
                                    </tr>
                                    @foreach($invoice_taxes as $tax)
                                    <tr>
                                        <td>{{ $tax->name }}</td>
                                        <td class="text-right">
                                            <span>{{ decimalPlace($tax->tax_amount, $currency) }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                    <tr>
                                        <td>{{ _lang('Grand Total') }}</td>
                                        <td class="text-right">{{ decimalPlace($invoice->grand_total, $currency) }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Total Paid') }}</td>
                                        <td class="text-right">{{ decimalPlace($invoice->paid, $currency) }}</td>
                                    </tr>
                                    @if($invoice->status != 'Paid')
                                    <tr>
                                        <td>{{ _lang('Amount Due') }}</td>
                                        <td class="text-right">
                                            {{ decimalPlace($invoice->grand_total - $invoice->paid, $currency) }}</td>
                                    </tr>

                                    <tr>
                                        <td colspan="2">
                                            <div id="payment-button">
                                                <div id="paypal-container" class="d-none p-button">
                                                    @include('backend.client_panel.payment_gateways.paypal')
                                                </div>

                                                <div id="stripe-container" class="d-none p-button">
                                                    @include('backend.client_panel.payment_gateways.stripe')
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endif


                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!--End Summary Table-->

                    <div class="clearfix"></div>

                    <!--Related Transaction-->
                    @if( ! $transactions->isEmpty() )
                    <div class="table-responsive">
                        <table class="table table-bordered" id="invoice-payment-history-table">
                            <thead class="base_color">
                                <tr>
                                    <td colspan="4" class="text-center"><b>{{ _lang('Payment History') }}</b></td>
                                </tr>
                                <tr>
                                    <th>{{ _lang('Date') }}</th>
                                    <th>{{ _lang('Account') }}</th>
                                    <th class="text-right">{{ _lang('Amount') }}</th>
                                    <th>{{ _lang('Payment Method') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                <tr id="transaction-{{ $transaction->id }}">
                                    <td>{{ $transaction->trans_date }}</td>
                                    <td>{{ $transaction->account->account_title }}
                                    </td>
                                    <td class="text-right">{{ decimalPlace($transaction->amount, $currency) }}</td>
                                    <td>{{ $transaction->payment_method->name }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                    <!--END Related Transaction-->

                    <!--Invoice Note-->
                    @if($invoice->note != '')
                    <div class="invoice-note">{!! xss_clean($invoice->note) !!}</div>
                    @endif
                    <!--End Invoice Note-->

                    <!--Invoice Footer Text-->
                    @if(get_option('invoice_footer') != '')
                    <div class="invoice-note">{!! xss_clean(get_option('invoice_footer')) !!}</div>
                    @endif
                    <!--End Invoice Note-->
                </div>
                <!--End Invoice View-->
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-script')
<script>
(function($) {
    "use strict";

    $(document).on('change', '#select_payment_method', function() {
        $("#payment-button .p-button").addClass('d-none');
        $(this).val() != "" ? $(`#payment-button #${$(this).val()}-container`).removeClass('d-none') : '';
    });

})(jQuery);
</script>
@endsection