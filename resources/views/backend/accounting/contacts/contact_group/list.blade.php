@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4 class="header-title">{{ _lang('Contact Group List') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto ajax-modal" data-title="{{ _lang('Add Contact Group') }}"
                    href="{{ route('contact_groups.create') }}"><i class="ti-plus"></i>
                    {{ _lang('Add New') }}</a>
            </div>

            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Group') }}</th>
                            <th>{{ _lang('Note') }}</th>
                            <th class="text-center">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contactgroups as $contactgroup)
                        <tr id="row_{{ $contactgroup->id }}">
                            <td class='group'>{{ $contactgroup->name }}</td>
                            <td class='note'>{{ $contactgroup->note }}</td>
                            <td class="text-center">
                                <form action="{{action('ContactGroupController@destroy', $contactgroup['id'])}}"
                                    method="post">
                                    <a href="{{action('ContactGroupController@edit', $contactgroup['id'])}}"
                                        data-title="{{ _lang('Update Contact Group') }}"
                                        class="btn btn-warning btn-sm ajax-modal"><i class="ti-pencil-alt"></i></a>
                                    <a href="{{action('ContactGroupController@show', $contactgroup['id'])}}"
                                        data-title="{{ _lang('View Contact Group') }}"
                                        class="btn btn-primary btn-sm ajax-modal"><i class="ti-eye"></i></a>
                                    {{ csrf_field() }}
                                    <input name="_method" type="hidden" value="DELETE">
                                    <button class="btn btn-danger btn-sm btn-remove"
                                        type="submit"><i class="ti-trash"></i></button>
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