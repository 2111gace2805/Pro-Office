@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('Product Groups') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto ajax-modal" data-title="{{ _lang('Add Product Group') }}"
                    href="{{ route('product_group.create') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
            </div>

            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th class="text-center">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($product_groups as $item)
                        <tr id="row_{{ $item->prodgrp_id }}">
                            <td class='brand_name'>{{ $item->prodgrp_name }}</td>
                            <td class='brand_name'>{{ _lang($item->prodgrp_status) }}</td>
                            <td class="text-center">
                                <form action="{{action('ProductGroupController@destroy', $item['prodgrp_id'])}}"
                                    method="post">
                                    <a href="{{action('ProductGroupController@edit', $item['prodgrp_id'])}}"
                                        data-title="{{ _lang('Update Product Group') }}"
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