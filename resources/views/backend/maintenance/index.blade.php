@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('Mantenimientos') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto ajax-modal" data-title="{{ _lang('Añadir Mantenimiento') }}" href="{{ route('maintenance.create') }}">
                    <i class="ti-plus"></i> {{ _lang('Add New') }}
                </a>                               
            </div>

            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Activo</th>
                            <th>Proveedor</th>
                            <th>Tipo</th>
                            <th>Costo</th>
                            <th>Estado</th>
                            <th>Fecha de inicio</th>
                            <th>Fecha de finalización</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($maintenance as $key => $value)
                        <tr>
                            <td>{{$value->assettag}}</td>
                            <td>{{$value->asset_name}}</td>
                            <td>{{$value->supplier_name}}</td>
                            <td>{{$value->type}}</td>
                            <td>{{$value->cost}}</td>
                            <td>{{$value->status}}</td>
                            <td>{{$value->startdate}}</td>
                            <td>{{$value->enddate}}</td>
                            <td>
                                <div style="display: flex;">
                                    <a href="{{ route('maintenance.edit', ['id' => $value->id]) }}" class="btn btn-primary mr-2 btn-sm ml-auto ajax-modal" data-title="{{ _lang('Update Mantenimiento') }}"><i class="fas fa-edit"></i> </a>
                                    <form action="{{ route('delete.maintenance', ['id' => $value->id]) }}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm ml-auto"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>
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