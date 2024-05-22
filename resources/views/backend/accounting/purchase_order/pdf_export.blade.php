<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ get_option('site_title', 'ElitKit Purchase Order') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style type="text/css">
	@php include public_path('backend/assets/css/styles.css') @endphp
    body {
        -webkit-print-color-adjust: exact !important;
        background: #FFF;
        font-size: 12px;
		font-family: DejaVu Sans;
    }
    </style>
</head>

<body>
    @php $date_format = get_option('date_format','Y-m-d'); @endphp
    @php
        $lstCompany = GetCompanies();
        $currentCompany = Session::get('company');
    @endphp
    <div id="invoice-view" class="pdf">
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
                        <img src="{{ ($currentCompany->logo=='' || $currentCompany->logo == null) ? asset('public/backend/images/avatar.png'):asset($currentCompany->logo) }}" 
                            alt="logo-company" height="60" class="shadow-sm">
                    </td>
                </tr>

                <tr class="information">
                    <td class="pt-4">
                        <h5><b>{{ _lang('Supplier Details') }}</b></h5>
                        @if(isset($purchase->supplier))
                        <b>{{ _lang('Name') }}</b> : {{ $purchase->supplier->supplier_name }}<br>
                        <b>{{ _lang('Email') }}</b> : {{ $purchase->supplier->email }}<br>
                        <b>{{ _lang('Phone') }}</b> : {{ $purchase->supplier->phone }}<br>
                        <b>{{ _lang('VAT Number') }}</b> :
                        {{ $purchase->supplier->vat_number == '' ? _lang('N/A') : $purchase->supplier->vat_number }}<br>
                        @endif
                    </td>
                    <td class="auto-column pt-4">
                        <h5><b>{{ _lang('Purchase Order') }}</b></h5>
                        <b>{{ _lang('Order ID') }} #:</b> {{ $purchase->id }}<br>
                         <b>{{ _lang('Import number') }} :</b> {{ $purchase->import_number }}<br>
                        <b>{{ _lang('Order Date') }}:</b> {{ $purchase->order_date }}<br>

                        <b>{{ _lang('Order Status') }}:</b>

                        @if($purchase->order_status == 1)
                        <span class="badge badge-info">{{ _lang('Ordered') }}</span><br>
                        @elseif($purchase->order_status == 2)
                        <span class="badge badge-danger">{{ _lang('Pending') }}</span><br>
                        @elseif($purchase->order_status == 3)
                        <span class="badge badge-success">{{ _lang('Received') }}</span><br>
                        @elseif($purchase->order_status == 4)
                        <span class="badge badge-danger">{{ _lang('Canceled') }}</span><br>
                        @endif

                        <b>{{ _lang('Payment') }}:</b>

                        @if($purchase->payment_status == 0)
                        <span class="badge badge-danger">{{ _lang('Due') }}</span>
                        @else
                        <span class="badge badge-success">{{ _lang('Paid') }}</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
        <!--End Invoice Information-->

        <div class="clearfix"></div>

        @php $currency = currency(); @endphp

        <!--Invoice Product-->
        <div class="table-responsive">
            <table class="table table-bordered mt-2" id="invoice-item-table">
                <thead>
                    <tr>
                        <th>{{ _lang('Name') }}</th>
                        <th class="text-center wp-100">{{ _lang('Quantity') }}</th>
                        <th class="text-right">{{ _lang('Unit Cost') }}</th>
                        <th class="text-right wp-100">{{ _lang('Discount')}}</th>
                        <th>{{ _lang('Tax') }}</th>
                        <th class="text-right">{{ _lang('Line Total') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($purchase->purchase_items as $item)
                    <tr id="product-{{ $item->product_id }}">
						<td>
                            <b>{{ $item->item->item_name }}</b><br>{{ $item->description }}
                        </td>
                        <td class="text-center quantity">{{ $item->quantity }}</td>
                        <td class="text-right unit-cost">{{ decimalPlace($item->unit_cost, $currency) }}</td>
                        <td class="text-right discount">{{ decimalPlace($item->discount, $currency) }}</td>
                        <td>{!! xss_clean(object_to_tax($item->taxes, 'name')) !!}</td>
                        <td class="text-right sub-total">{{ decimalPlace($item->sub_total, $currency) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!--End Invoice Product-->


        <!--Summary Table-->
        <div class="invoice-summary-right">
            <table class="table table-bordered" id="invoice-summary-table">
                <tbody>
                    <tr>
                        <td>{{ _lang('Sub Total') }}</td>
                        <td class="text-right">
                            <span>{!! strip_tags(decimalPlace($purchase->product_total, $currency)) !!}</span>
                        </td>
                    </tr>
                    @foreach($purchase_taxes as $tax)
                    <tr>
                        <td>{{ $tax->name }}</td>
                        <td class="text-right">
                            <span>{!! strip_tags(decimalPlace($tax->tax_amount, $currency)) !!}</span>
                        </td>
                    </tr>
                    @endforeach
                    <tr>
                        <td>{{ _lang('Shipping Cost') }}</td>
                        <td class="text-right">
                            <span>+ {!! strip_tags(decimalPlace($purchase->shipping_cost, $currency)) !!}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Discount') }}</td>
                        <td class="text-right">
                            <span>- {!! strip_tags(decimalPlace($purchase->order_discount, $currency)) !!}</span>
                        </td>
                    </tr>
                    <tr>
                        <td><b>{{ _lang('Grand Total') }}</b></td>
                        <td class="text-right">
                            <b>{!! strip_tags(decimalPlace($purchase->grand_total, $currency)) !!}</b>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Total Paid') }}</td>
                        <td class="text-right">
                            <span>{!! strip_tags(decimalPlace($purchase->paid, $currency)) !!}</span>
                        </td>
                    </tr>
                    @if($purchase->payment_status == 0)
                    <tr>
                        <td>{{ _lang('Amount Due') }}</td>
                        <td class="text-right">
                            <span>{!! strip_tags(decimalPlace(($purchase->grand_total - $purchase->paid), $currency))
                                !!}</span>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <!--End Summary Table-->

        <div class="clearfix"></div>

        <!--Related Transaction-->
        @if( ! $transactions->isEmpty() )
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
                    <td>{{ date($date_format, strtotime($transaction->trans_date)) }}</td>
                    <td>{{ $transaction->account->account_title }}</td>
                    <td class="text-right">{{ decimalPlace($transaction->amount, $currency) }}</td>
                    <td>{{ $transaction->payment_method->name }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        <!--END Related Transaction-->

        <!--Invoice Note-->
        @if($purchase->note != '')
        <div class="invoice-note border-top pt-4">{{ $purchase->note }}</div>
        @endif
        <!--End Invoice Note-->
    </div>
</body>

</html>