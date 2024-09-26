@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('Codigos arancelarios') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto ajax-modal" data-title="{{ _lang('Add New') }}"
                    href="{{ route('codigo_arancelarios.create') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
            </div>

            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Código') }}</th>
                            <th>{{ _lang('Description') }}</th>
                            <th class="text-center">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($codigo_arancelarios as $item)
                        <tr id="row_{{ $item->codaran_id }}">
                            <td class='codaran_codigo'>{{ $item->codaran_codigo }}</td>
                            <td class='codaran_descripcion'>{{ $item->codaran_descripcion }}</td>
                            <td class="text-center">
                                <form action="{{action('CodigoArancelarioController@destroy', $item['codaran_id'])}}"
                                    method="post">
                                    <a href="{{action('CodigoArancelarioController@edit', $item['codaran_id'])}}"
                                        data-title="{{ _lang('Update') }}"
                                        class="btn btn-warning btn-sm ajax-modal"><i class="ti-pencil-alt"></i></a>
                                    {{ csrf_field() }}
                                    {{-- <input name="_method" type="hidden" value="DELETE">
                                    <button class="btn btn-danger btn-sm btn-remove"
                                        type="submit"><i class="ti-trash"></i></button> --}}
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