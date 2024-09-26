@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Cierre de caja') }}</h4>
            </div>

            <div class="card-body">
                <div class="col-md-12">
                    <form method="post" class="validate" autocomplete="off" action="{{ route('cash_movement.store') }}"
                        enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <input type="hidden" name="cashmov_type" value="{{$cashmov_type}}"/>

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Date') }}</label>
                                    <input type="date" readonly class="form-control" name="cashmov_date" id="cashmov_date"
                                        value="{{ $cash_movement->cashmov_date }}" required>
                                </div>
                            </div>
                            
                            {{-- <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Hora') }}</label>
                                    <input type="time" class="form-control" name="cashmov_time"
                                        value="{{ old('cashmov_time') }}" required>
                                </div>
                            </div> --}}

                            <div class="col-12 col-md-4">
                                <div class="form-group" style="pointer-events: none;">
                                    <label class="control-label">{{ _lang('Sucursal') }}</label>
                                    <select readonly class="form-control select-filter" name="company_id" id="company_id">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("companies", "id", "company_name", $cash_movement->company_id, ['id='=> $cash_movement->company_id]) }}
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Caja') }}</label>
                                    <select class="form-control" readonly name="cash_id" id="cash_id" style="pointer-events: none;">
                                    <option value="">{{ _lang('- Select one -') }}</option>
                                    {{ create_option("cashs", "cash_id", "cash_name", old('cash_id'), ['cash_id=' => old('cash_id')]) }}
                                </select>
                                </div>
                            </div>

                            <div class="col-12 mt-3">

                                <div class="col-12 d-flex justify-content-between align-items-center">
                                <h5>Ventas en efectivo</h5>
                                <div class="form-group">
                                    {{-- <label class="control-label">{{ _lang('Tipo') }}</label> --}}
                                    <select class="form-control select-filter" name="prodgrp_id" id="prodgrp_id">
                                    <option value="" selected>{{ _lang('Todos') }}</option>
                                    {{ create_option("product_groups", "prodgrp_id", "prodgrp_name", old('prodgrp_id')) }}
                                    </select>
                                </div>
                            </div>
                            </div>

                            
                            
                            
                            
                            


                            <div class="col-12">
                                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                    <li class="nav-item">
                                      <a class="nav-link active" id="pills-home-tab" data-toggle="pill" href="#pills-home" role="tab" aria-controls="pills-home" aria-selected="true">Productos</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="#pills-profile" role="tab" aria-controls="pills-profile" aria-selected="false">Ventas</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-groups-tab" data-toggle="pill" href="#pills-groups" role="tab" aria-controls="pills-groups" aria-selected="false">Ventas por grupo</a>
                                    </li>
                                </ul>
                                <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab" tabindex="0">
                                            <div class="col-12"><div class="table-responsive">
                                                <table class="table table-bordered" id="closing_cash_table">
                                                    <thead>
                                                        <th>Cantidad</th>
                                                        <th>Producto</th>
                                                        <th>Efectivo</th>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                </table>
                                            </div>
                                </div>
                                  </div>
                                  <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab" tabindex="0">
                                        <div class="col-12"><div class="table-responsive">
                                            <table class="table table-bordered" id="closing_cash_invoices_table">
                                                <thead>
                                                    <th>#Factura</th>
                                                    <th>Cliente</th>
                                                    <th>Total</th>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    </div>
                                    <div class="tab-pane fade" id="pills-groups" role="tabpanel" aria-labelledby="pills-groups-tab" tabindex="0">
                                        <div class="col-12"><div class="table-responsive">
                                         <table class="table table-bordered" id="groups_table">
                                             <thead>
                                            <th>Grupo</th>
                                            <th>Cantidad</th>
                                            <th>Efectivo</th>
                                            </thead>
                                            <tbody>
                                            <tr><td colspan="100%" class="text-center">Sin registros</td></tr>
                                            </tbody>
                                         </table>
                                        </div>
                                    </div>
                                  </div>
                                </div>
                            </div>

                            <div class="col-12 px-4 mt-4">
                                <h5>Resúmen de ventas por forma de pago</h5>
                                <div class="table-responsive">
                                <table class="table table-bordered" id="formas_pago">
                                    <thead>
                                        <th>Forma de pago</th>
                                        <th>Total</th>
                                   </thead>
                                   <tbody>
                                        <tr><td colspan="100%" class="text-center">Sin registros</td></tr>
                                   </tbody>
                                </table>
                            </div>
                            
                            
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('js-script')
<script src="{{ asset('public/backend/assets/js/cash_movement.js') }}"></script>
<script src="{{ asset('public/backend/assets/js/datatables/closing-cash-table.js') }}"></script>

@endsection
