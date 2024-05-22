@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('Email Templates') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto" data-title="{{ _lang('Create Email Template') }}"
                    href="{{ route('email_template.create') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
            </div>

            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('Subject') }}</th>
                            <th>{{ _lang('Related') }}</th>
                            <th class="text-center">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($companyemailtemplates as $companyemailtemplate)
                        <tr id="row_{{ $companyemailtemplate->id }}">
                            <td class='name'>{{ $companyemailtemplate->name }}</td>
                            <td class='subject'>{{ $companyemailtemplate->subject }}</td>
                            <td class='related_to'>{{ ucwords($companyemailtemplate->related_to) }}</td>
                            <td class="text-center">
                                <form
                                    action="{{ action('CompanyEmailTemplateController@destroy', $companyemailtemplate['id']) }}"
                                    method="post">
                                    <a href="{{ action('CompanyEmailTemplateController@edit', $companyemailtemplate['id']) }}"
                                        class="btn btn-warning btn-sm"><i class="ti-pencil-alt"></i></a>
                                    <a href="{{ action('CompanyEmailTemplateController@show', $companyemailtemplate['id']) }}"
                                        class="btn btn-primary btn-sm ajax-modal"
                                        data-title="{{ _lang('View Email Template') }}"><i class="ti-eye"></i></a>
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