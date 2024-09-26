@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Create New Service') }}</h4>
            </div>
            <div class="card-body">
                <form method="post" class="validate" autocomplete="off" action="{{ route('services.store') }}"
                    enctype="multipart/form-data">
                    {{ csrf_field() }}
                    
                    <div class="form-group row">
                        <label class="col-xl-3 col-form-label">{{ _lang('Service Name') }} *</label>
                        <div class="col-xl-9">
                            <input type="text" class="form-control" name="item_name" value="{{ old('item_name') }}"
                                required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-xl-3 col-form-label">{{ _lang('Service Cost') }} *</label>
                        <div class="col-xl-9">
                            <input type="text" class="form-control" name="cost" value="{{ old('cost') }}" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-xl-3 col-form-label">{{ _lang('Description') }}</label>
                        <div class="col-xl-9">
                            <textarea class="form-control" name="description">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-xl-9 offset-xl-3">
                            <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Save Changes') }}</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection