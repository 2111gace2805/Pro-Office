@extends('layouts.app')

@section('content')
<h4 class="page-title">{{ _lang('Database Backups') }}</h4>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4 class="header-title">{{ _lang('Database Backups') }}</h4>
                <a class="btn btn-primary btn-sm ml-auto" href="{{ route('database_backups.create') }}"><i
                        class="ti-plus"></i> {{ _lang('Create New Backup') }}</a>
            </div>
            <div class="card-body">
                <table id="database_backups_table" class="table data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Created At') }}</th>
                            <th>{{ _lang('File') }}</th>
                            <th>{{ _lang('Created By') }}</th>
                            <th class="text-center">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($databasebackups as $databasebackup)
                        <tr data-id="row_{{ $databasebackup->id }}">
                            <td class='created_at'>{{ $databasebackup->created_at }}</td>
                            <td class='file'>{{ $databasebackup->file }}</td>
                            <td class='user_id'>{{ $databasebackup->created_by->name }}</td>

                            <td class="text-center">
                                <span class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle btn-sm" type="button"
                                        id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                        {{ _lang('Action') }}
                                        <i class="fas fa-angle-down"></i>
                                    </button>
                                    <form
                                        action="{{ action('UtilityController@destroy_database_backup', $databasebackup['id']) }}"
                                        method="post">
                                        @csrf
                                        <input name="_method" type="hidden" value="DELETE">

                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a href="{{ action('UtilityController@download_database_backup', $databasebackup['id']) }}"
                                                class="dropdown-item dropdown-view"><i class="mdi mdi-eye"></i>
                                                {{ _lang('Download') }}</a>
                                            <button class="btn-remove dropdown-item" type="submit"><i
                                                    class="mdi mdi-delete"></i> {{ _lang('Delete') }}</button>
                                        </div>
                                    </form>
                                </span>
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