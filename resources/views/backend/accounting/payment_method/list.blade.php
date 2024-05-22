@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('Payment Methods') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto ajax-modal" data-title="{{ _lang('Add Payment Method') }}"
                    href="{{ route('payment_methods.create') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
            </div>

            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Name') }}</th>
                            <th class="action-col">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($paymentmethods as $paymentmethod)
                        <tr id="row_{{ $paymentmethod->id }}">
                            <td class='name'>{{ $paymentmethod->name }}</td>
                            <td class="text-center">
                                <form action="{{ action('PaymentMethodController@destroy', $paymentmethod['id']) }}"
                                    method="post">
                                    <a href="{{ action('PaymentMethodController@edit', $paymentmethod['id']) }}"
                                        data-title="{{ _lang('Update Payment Method') }}"
                                        class="btn btn-warning btn-sm ajax-modal"><i class="ti-pencil-alt"></i></a>
                                    {{ csrf_field() }}
                                    <input name="_method" type="hidden" value="DELETE">
                                    <button class="btn btn-danger btn-sm btn-remove"
                                        type="submit"><i class="ti-trash"></i></button>
                                </form>
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