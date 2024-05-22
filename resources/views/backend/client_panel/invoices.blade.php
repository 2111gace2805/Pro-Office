@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card card-default no-export">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Invoice List') }}</h4>
            </div>

            <div class="card-body">
                @php $currency = currency() @endphp
                <table class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Invoice Number') }}</th>
                            <th>{{ _lang('Client') }}</th>
                            <th>{{ _lang('Due Date') }}</th>
                            <th class="text-right">{{ _lang('Grand Total') }}</th>
                            <th class="text-center">{{ _lang('Status') }}</th>
                            <th class="text-center">{{ _lang('View') }}</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($invoices as $invoice)
                        <tr id="row_{{ $invoice->id }}">
                            <td class='invoice_number'>{{ $invoice->invoice_number }}</td>
                            <td class='client_id'>{{ $invoice->client->contact_name }}</td>
                            <td class='due_date'>{{ $invoice->due_date }}</td>
                            <td class='grand_total text-right'>{{ decimalPlace($invoice->grand_total, $currency) }}</td>
                            <td class='status text-center'>{!! invoice_status($invoice->status) !!}</td>
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

@endsection