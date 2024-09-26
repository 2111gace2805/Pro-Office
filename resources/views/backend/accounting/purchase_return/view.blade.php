@extends('layouts.app')

@section('content')
  <div class="row">
	<div class="col-12">
		<div class="btn-group group-buttons">
			<a class="btn btn-primary btn-sm print" href="#" data-print="invoice-view"><i class="ti-printer"></i> {{ _lang('Print') }}</a>
			<a class="btn btn-warning btn-sm" href="{{ action('PurchaseReturnController@edit', $purchase_return->id) }}"><i class="ti-pencil-alt"></i> {{ _lang('Edit') }}</a>
		</div>

		<div class="card clearfix">

			<div class="card-body">
				<div id="invoice-view">
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
                                    <h5><b>{{ _lang('Supplier Details') }}</b></h5>
									@if(isset($purchase_return->supplier))	
										<b>{{ _lang('Name') }}</b> : {{ $purchase_return->supplier->supplier_name }}<br>
										<b>{{ _lang('Email') }}</b> : {{ $purchase_return->supplier->email }}<br>
										<b>{{ _lang('Phone') }}</b> : {{ $purchase_return->supplier->phone }}<br>
										<b>{{ _lang('VAT Number') }}</b> : {{ $purchase_return->supplier->vat_number == '' ? _lang('N/A') : $purchase_return->supplier->vat_number }}<br>
									@endif    
                                </td>
                                <td class="auto-column pt-4">
                                    <h5><b>{{ _lang('Purchase Return') }}</b></h5>

                                    <b>{{ _lang('Return ID') }} #:</b> {{ $purchase_return->id }}<br>
									<b>{{ _lang('Return Date') }}:</b> {{ $purchase_return->return_date }}<br>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!--End Information-->
					
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
								@foreach($purchase_return->purchase_return_items as $item)
									<tr id="product-{{ $item->product_id }}">
										<td>
											<b>{{ $item->item->item_name }}</b><br>
											{{ $item->description }}
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
											<span>{{ decimalPlace($purchase_return->product_total, $currency) }}</span>
										</td>
									</tr>
									@foreach($purchase_return_taxes as $tax)
									<tr>
										<td>{{ $tax->name }}</td>
										<td class="text-right">
											<span>{{ decimalPlace($tax->tax_amount, $currency) }}</span>
										</td>
									</tr>
									@endforeach
									<tr>
										<td><b>{{ _lang('Grand Total') }}</b></td>
										<td class="text-right">
											 <b>{{ decimalPlace($purchase_return->grand_total, $currency) }}</b>
										</td>
									</tr>
									<tr>
										<td>{{ _lang('Total Paid') }}</td>
										<td class="text-right">
											<span>{{ decimalPlace($purchase_return->paid, $currency) }}</span>
										</td>
									</tr>
									@if($purchase_return->payment_status == 0)
										<tr>
											<td>{{ _lang('Amount Due') }}</td>
											<td class="text-right">
												<span>{{ decimalPlace(($purchase_return->grand_total - $purchase_return->paid), $currency) }}</span>
											</td>
										</tr>
									@endif
							 </tbody>
						</table>
					</div>
					<!--End Summary Table-->
					 
					<div class="clearfix"></div>				 

					<!--Invoice Note-->
					@if($purchase_return->note  != '')
						<div class="invoice-note border-top pt-4">{{ $purchase_return->note }}</div> 
					@endif
					<!--End Invoice Note-->	
					 
				</div>
			</div>
		</div>
    </div><!--End Classic Invoice Column-->
</div><!--End Classic Invoice Row-->
@endsection