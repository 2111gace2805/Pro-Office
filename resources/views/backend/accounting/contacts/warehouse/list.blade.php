@extends('layouts.app')

@section('content')
<h4 class="page-title">{{ _lang('Contact Management') }}</h4>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <input type="hidden" id="client_id" value="{{ $id_client }}">
            <div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('Warehouses List') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto ajax-modal" href="{{ route('warehouses.create', ['id_client' => $id_client]) }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
            </div>

            <div class="card-body">
                <table id="warehouses-table" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ _lang('Name') }}</th>
                            <th class="text-center">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-script')
<script src="{{ asset('public/backend/assets/js/datatables/warehouses.js') }}"></script>
@endsection