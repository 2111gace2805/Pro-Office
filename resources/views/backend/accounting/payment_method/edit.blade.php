@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-default">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Update Payment Method') }}</h4>
            </div>

            <div class="card-body">
                <div class="col-md-6">
                    <form method="post" class="validate" autocomplete="off"
                        action="{{ action('PaymentMethodController@update', $id) }}" enctype="multipart/form-data">
                        {{ csrf_field()}}
                        <input name="_method" type="hidden" value="PATCH">

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Name') }}</label>
                                <input type="text" class="form-control" name="name" value="{{ $paymentmethod->name }}"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i>
                                    {{ _lang('Update') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection