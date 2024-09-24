@extends('layouts.app')
@section('styles')
<style>
    table.dataTable tbody td {
    word-break: break-word;
    vertical-align: top;
}
</style>
@endsection
@section('content')
<h4 class="page-title">{{ _lang('Contact Management') }}</h4>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('Contact List') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto" href="{{route('contacts.create')}}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
            </div>

            <div class="card-body">
                <table id="contacts-table" class="table table-bordered">
                    <thead>
                        <tr>
                            {{-- <th>{{ _lang('Image') }}</th> --}}
                            <th>{{ _lang('Profile Type') }}</th>
                            <th style="word-break: break-word;">{{ _lang('Nombre o razón social') }}</th>
                            <th>{{ _lang('Email') }}</th>
                            <th>{{ _lang('Phone') }}</th>
                            <th>{{ _lang('Group') }}</th>
                            <th class="d-none">{{ _lang('NIT') }}</th>
                            <th class="d-none">{{ _lang('NRC') }}</th>
                            <th class="d-none">{{ _lang('No Documento') }}</th>
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
<script src="{{ asset('public/backend/assets/js/datatables/contacts.js') }}"></script>
@endsection