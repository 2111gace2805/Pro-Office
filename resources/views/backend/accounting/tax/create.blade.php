@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Add New Tax') }}</h4>
            </div>

            <div class="card-body">
                <form method="post" class="validate" autocomplete="off" action="{{ route('taxs.store') }}"
                    enctype="multipart/form-data">
                    {{ csrf_field() }}

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Tax Name') }}</label>
                                <input type="text" class="form-control" name="tax_name" value="{{ old('tax_name') }}"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Rate') }}</label>
                                <input type="text" class="form-control float-field" name="rate"
                                    value="{{ old('rate') }}" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Type') }}</label>
                                <select class="form-control" name="type" required>
                                    <option value="fixed">{{ _lang('Fixed') }}</option>
                                    <option value="percent">{{ _lang('Percentage %') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i>
                                    {{ _lang('Save') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection