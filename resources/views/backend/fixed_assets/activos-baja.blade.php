@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('Activos fijos (De baja)') }}</h4>
                                              
            </div>

            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Codigo') }}</th>
                            <th>{{ _lang('Nombre') }}</th>
                            <th>{{ _lang('Tipo') }}</th>
                            <th>{{ _lang('Marca') }}</th>
                            <th>{{ _lang('Sucursal') }}</th>
                            <th>{{ _lang('Motivos de la baja') }}</th>
                            <th class="text-center">{{ _lang('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assets as $key => $value)
                        <tr>
                            <td>{{$value->assettag}}</td>
                            <td>{{$value->name}}</td>
                            <td>{{$value->asset_type_name}}</td>
                            <td>{{$value->brand_name}}</td>
                            <td>{{$value->company_name}}</td>
                            <td>{{$value->explicacion}}</td>
                            <td>
                                <a href="{{ route('asset.show', $value->id) }}" class="btn btn-info"><i class="fas fa-info-circle"></i> </a>
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
