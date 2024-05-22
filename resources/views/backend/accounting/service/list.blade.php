@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('Service List') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto ajax-modal" data-title="{{ _lang('Add New Service') }}"
                    href="{{ route('services.create') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
            </div>

            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Service') }}</th>
                            <th>{{ _lang('Cost') }}</th>
                            <th class="text-center">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>

                        @php $currency = currency(); @endphp
                        @foreach($items as $item)
                        <tr id="row_{{ $item->id }}">
                            <td class='item_id'>{{ $item->item_name }}</td>
                            <td class='cost'>{{ decimalPlace($item->service->cost, $currency) }}</td>
                            <td class="text-center">
                                <form action="{{ action('ServiceController@destroy', $item['id']) }}" method="post">
                                    <a href="{{ action('ServiceController@edit', $item['id']) }}"
                                        data-title="{{ _lang('Update Service') }}"
                                        class="btn btn-warning btn-sm ajax-modal"><i class="ti-pencil-alt"></i></a>
                                    <a href="{{ action('ServiceController@show', $item['id']) }}"
                                        data-title="{{ _lang('View Service') }}"
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