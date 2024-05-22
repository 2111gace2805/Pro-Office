@extends('layouts.app')

@section('content')
@php $currency = currency() @endphp

<div class="row">
    <!-- Panel 1 -->
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Recent Invoices') }}</h4>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ _lang('Invoice Number') }}</th>
                                <th>{{ _lang('Client') }}</th>
                                <th>{{ _lang('Due Date') }}</th>
                                <th class="text-right">{{ _lang('Grand Total') }}</th>
                                <th class="text-center">{{ _lang('Status') }}</th>
                                <th class="text-center">{{ _lang('View Details') }}</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach($invoices as $invoice)
                            <tr id="row_{{ $invoice->id }}">
                                <td class='invoice_number'>{{ $invoice->invoice_number }}</td>
                                <td class='client_id'>{{ $invoice->client->contact_name }}</td>
                                <td class='due_date'>{{ $invoice->due_date }}</td>
                                <td class='grand_total text-right'>{{ decimalPlace($invoice->grand_total, $currency) }}</td>
                                <td class='status text-center'>{!! xss_clean(invoice_status($invoice->status)) !!}</td>
                                <td class='view text-center'><a class="btn btn-light btn-sm"
                                        href="{{ route('client.view_invoice', encrypt($invoice->id)) }}"
                                        data-title="{{ _lang('View Invoice') }}" data-fullscreen="true"><i
                                            class="fas fa-eye"></i> {{ _lang('View') }}</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- End Panel 1 -->
    <!-- Panel 2 -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Recent Transactions') }}</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ _lang('Date') }}</th>
                                <th>{{ _lang('Account') }}</th>
                                <th>{{ _lang('Category') }}</th>
                                <th class="text-right">{{ _lang('Amount') }}</th>
                                <th>{{ _lang('Payment Method') }}</th>
                                <th class="action-col">{{ _lang('View Details') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->trans_date }}</td>
                                <td>{{ isset($transaction->account) ? $transaction->account->account_title : '' }}</td>
                                <td>{{ isset($transaction->expense_type->name) ? $transaction->expense_type->name : _lang('Transfer') }}
                                </td>
                                <td class="text-right">{{ decimalPlace($transaction->amount, $currency) }}</td>
                                <td>{{ isset($transaction->payment_method) ? $transaction->payment_method->name : '' }}
                                </td>
                                <td class="text-center"><a href="{{ route('client.view_transaction', $transaction->id) }}"
                                        data-title="{{ _lang('View Transaction Details') }}"
                                        class="btn btn-light btn-sm ajax-modal"><i class="fas fa-eye"></i>
                                        {{ _lang('View') }}</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- End Panel 2 -->

</div>
@endsection