@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('Transfers List') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto" data-title="{{ _lang('Add Transfer') }}"
                    href="{{ route($send ? 'passes.create' : 'passes.incoming') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
            </div>

            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Company') }}</th>
                            <th>{{ _lang('Transfer Code') }}</th>
                            <th>{{ _lang('Transfer Date') }}</th>
                            <th class="text-center">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $currency = currency(); @endphp
                        @foreach($items as $item)
                        <tr id="row_{{ $item->id }}">
                            <td class='item_id'>{{ $item->company_name }}</td>
                            <td class='product_cost'>{{ $item->transfer_code }}</td>
                            <td class='product_price'>{{ $item->transfer_datesend }}</td>
                            <td class="text-center">
                                <form action="{{action('PassController@destroy', $item['transfer_id'])}}" method="post">
                                    <a href="{{ route('transfer.show', [$item->transfer_id, $send]) }}"
                                        class="btn btn-primary btn-sm"><i class="ti-eye"></i></a>
                                    @if ($item->estado == 0 && $send == 0)
                                        {{ csrf_field() }}
                                        <input name="_method" type="hidden" value="DELETE">
                                        <button class="btn btn-danger btn-sm btn-remove"
                                            type="submit"><i class="ti-trash"></i></button>                                        
                                    @endif
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