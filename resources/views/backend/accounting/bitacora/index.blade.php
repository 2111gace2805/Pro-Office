@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4 class="header-title">{{ _lang('BITACORA') }}</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="logs-table" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ID  Usuario</th>
                                <th>Nombre de la tabla</th>
                                <th>Acción realizada</th>
                                <th>Fecha de creación</th>
                                <th>Fecha de actualizacion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>{{ $log->user->name }}</td>
                                <td>{{ $log->table_name }}</td>
                                <td>{{ $log->action_description }}</td>
                                <td>{{ $log->created_at }}</td>
                                <td>{{ $log->updated_at }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js-script')
<script>
    $(document).ready(function() {
        $('#logs-table').DataTable();
    });
</script>
@endsection
