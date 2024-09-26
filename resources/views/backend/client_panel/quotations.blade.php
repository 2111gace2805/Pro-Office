@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
				<h4 class="header-title">{{ _lang('Quotation List') }}</span>
            </div>

            <div class="card-body">
                @php $currency = currency() @endphp
                <table class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Quotation Number') }}</th>
                            <th>{{ _lang('Client') }}</th>
                            <th>{{ _lang('Quotation Date') }}</th>
                            <th class="text-right">{{ _lang('Grand Total') }}</th>
                            <th class="text-center">{{ _lang('View') }}</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($quotations as $quotation)
                        <tr id="row_{{ $quotation->id }}">
                            <td class='quotation_number'>{{ $quotation->quotation_number }}</td>
                            <td class='client_id'>{{ $quotation->client->contact_name }}</td>
                            <td class='due_date'>{{ $quotation->quotation_date }}</td>
                            <td class='grand_total text-right'>{{ decimalPlace($quotation->grand_total, $currency) }}</td>
                            <td class="view text-center">
                                <a class="btn btn-light btn-sm"
                                    href="{{ route('client.view_quotation', encrypt($quotation->id)) }}"><i
                                        class="fas fa-eye"></i> {{ _lang('View') }}</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection