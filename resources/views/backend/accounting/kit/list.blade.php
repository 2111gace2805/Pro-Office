@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('Order Notes List') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto" href="{{ route('kits.create') }}"><i class="ti-plus"></i> {{ _lang('Create Kit') }}</a>
            </div>

            <div class="card-body">
                <div class="row">
					<div class="col-lg-3 mb-2">
                     	<label>{{ _lang('Code Kit') }}</label>
                     	<input type="text" class="form-control select-filter" name="code" id="code">
                    </div>
					<div class="col-lg-3 mb-2">
                     	<label>{{ _lang('Name') }}</label>
                     	<input type="text" class="form-control select-filter" name="name" id="name">
                    </div>
                </div>

                <hr>
                <table class="table table-bordered" id="kit-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Code') }}</th>
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('Products') }}</th>
                            <th>{{ _lang('Price') }}</th>
                            <th class="text-center">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js-script')
<script src="{{ asset('public/backend/assets/js/datatables/kit-table.js') }}"></script>
<script src="{{ asset('public/backend/assets/js/list_order_notes.js') }}"></script>

@endsection