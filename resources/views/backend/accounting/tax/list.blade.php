@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">

            <div class="card-header d-flex align-items-center">
                <h4 class="header-title">{{ _lang('Taxes') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto ajax-modal" data-title="{{ _lang('Add Payment Method') }}"
                    href="{{ route('taxs.create') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
            </div>

            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Tax Name') }}</th>
                            <th>{{ _lang('Rate') }}</th>
                            <th class="text-center">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $currency = currency(); @endphp
                        @foreach($taxs as $tax)
                        <tr id="row_{{ $tax->id }}">
                            <td class='tax_name'>{{ $tax->tax_name }}</td>
                            <td class='rate'>{{ $tax->type == 'fixed' ? decimalPlace($tax->rate, $currency) : $tax->rate.'%' }}</td>
                            <td class="text-center">
                                <form action="{{action('TaxController@destroy', $tax['id'])}}" method="post">
                                    <a href="{{action('TaxController@edit', $tax['id'])}}"
                                        data-title="{{ _lang('Update Tax') }}"
                                        class="btn btn-warning btn-sm ajax-modal"><i class="ti-pencil-alt"></i></a>
                                    {{ csrf_field() }}
                                    <input name="_method" type="hidden" value="DELETE">
                                    <button class="btn btn-danger btn-sm btn-remove" type="submit"><i
                                            class="ti-trash"></i></button>
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