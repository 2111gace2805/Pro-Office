@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Service Details') }}</h4>
            </div>

            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <td>{{ _lang('Item ID') }}</td>
                        <td>{{ $item->id }}</td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Service Name') }}</td>
                        <td>{{ $item->item_name }}</td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Service Cost') }}</td>
                        <td>{{ decimalPlace($item->service->cost, currency()) }}</td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Description') }}</td>
                        <td>{{ $item->service->description }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection