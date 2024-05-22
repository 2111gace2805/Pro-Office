@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('Brands') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto ajax-modal" data-title="{{ _lang('Add Brand') }}"
                    href="{{ route('brands.create') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
            </div>

            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Brand Name') }}</th>
                            <th>{{ _lang('Brand Status') }}</th>
                            <th class="text-center">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($brands as $item)
                        <tr id="row_{{ $item->id }}">
                            <td class='brand_name'>{{ $item->brand_name }}</td>
                            <td class='brand_name'>{{ _lang($item->brand_status) }}</td>
                            <td class="text-center">
                                <form action="{{action('BrandController@destroy', $item['brand_id'])}}"
                                    method="post">
                                    <a href="{{action('BrandController@edit', $item['brand_id'])}}"
                                        data-title="{{ _lang('Update Brand') }}"
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