@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            @php $currency = currency(); @endphp

            <div class="card-header d-flex align-items-center">
                <h4 class="header-title">Creación de caja</h4>
                <a class="btn btn-primary btn-sm ml-auto ajax-modal" data-title="{{ _lang('Crear Caja') }}" href="{{ route('cash.create') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
            </div>

            <div class="card-body">
                <table class="table table-bordered" id="tblCajas">
                    <thead>
                        <tr>
                            <th class="text-center">Nombre de caja</th>
                            <th class="text-center">Monto de apertura</th>
                            <th class="text-center">Sucursal</th>
                            <th class="text-center">Estado</th>
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
<script src="{{ asset('public/backend/assets/js/datatables/cash.js') }}"></script>
@endsection