@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
			<div class="card-header">
				<h4 class="header-title">{{ _lang('Update Contact Group') }}</h4>
			</div>
            <div class="card-body">
                <form method="post" class="validate" autocomplete="off"
                    action="{{ action('ContactGroupController@update', $id) }}" enctype="multipart/form-data">
                    {{ csrf_field()}}
                    <input name="_method" type="hidden" value="PATCH">

                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label">{{ _lang('Group Name') }}</label>
                            <input type="text" class="form-control" name="name" value="{{ $contactgroup->name }}"
                                required>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label">{{ _lang('Note') }}</label>
                            <textarea class="form-control" name="note">{{ $contactgroup->note }}</textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Save Changes') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection