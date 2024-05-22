@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('List Account') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto ajax-modal" data-title="{{ _lang('Add Account') }}"
                    href="{{route('accounts.create')}}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
            </div>

            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Account Title') }}</th>
                            <th>{{ _lang('Opening Date') }}</th>
                            <th>{{ _lang('Account Number') }}</th>
                            <th class="text-right">{{ _lang('Opening Balance') }}</th>
                            <th class="action-col">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $currency = currency(); @endphp
						@php $date_format = get_date_format(); @endphp
                        @foreach($accounts as $account)
                        <tr id="row_{{ $account->id }}">
                            <td class='account_title'>{{ $account->account_title }}</td>
                            <td class='opening_date'>{{ date("$date_format", strtotime($account->opening_date)) }}</td>
                            <td class='account_number'>{{ $account->account_number }}</td>
                            <td class='opening_balance text-right'>
                                {{ decimalPlace($account->opening_balance, $currency) }}</td>
                            <td class="text-center">
                                <form action="{{ action('AccountController@destroy', $account->id) }}" method="post">
                                    <a href="{{ action('AccountController@edit', $account->id) }}"
                                        data-title="{{ _lang('Update Account') }}"
                                        class="btn btn-warning btn-sm ajax-modal"><i class="ti-pencil-alt"></i></a>
                                    <a href="{{ action('AccountController@show', $account->id) }}"
                                        data-title="{{ _lang('View Account') }}"
                                        class="btn btn-primary btn-sm ajax-modal"><i class="ti-eye"></i></a>
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