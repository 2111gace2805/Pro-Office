@extends('layouts.app')

@section('content')

<div class="row">
	<div class="col-md-12">
		<div class="card card-default no-export">
			<div class="card-header"><span class="card-title">{{ _lang('Transaction List') }}</span></div>

			<div class="card-body">
			    @php $currency = currency(); @endphp
				<table class="table table-bordered data-table">
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
							<td>{{ $transaction->account->account_title }}</td>
							<td>{{ isset($transaction->expense_type->name) ? $transaction->expense_type->name : _lang('Transfer') }}</td>
							<td class="text-right">{{ decimalPlace($transaction->amount, $currency) }}</td>
							<td>{{ isset($transaction->payment_method) ? $transaction->payment_method->name : '' }}</td>
							<td class="text-center"><a href="{{ route('client.view_transaction', $transaction->id) }}" data-title="{{ _lang('View Transaction Details') }}" class="btn btn-primary btn-sm ajax-modal">{{ _lang('View') }}</a></td>
						</tr>
						@endforeach
					</tbody>
			  </table>
			</div>
		</div>
	</div>
</div>

@endsection