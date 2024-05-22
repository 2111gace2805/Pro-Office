<form method="post" class="ajax-submit" autocomplete="off" action="{{ action('PaymentMethodController@update', $id) }}"
    enctype="multipart/form-data">
    {{ csrf_field()}}
    <input name="_method" type="hidden" value="PATCH">

    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label">{{ _lang('Name') }}</label>
            <input type="text" class="form-control" name="name" value="{{ $paymentmethod->name }}" required>
        </div>
    </div>

	<div class="col-md-12">
    	<div class="form-group">
            <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i>
                {{ _lang('Update') }}</button>
        </div>
    </div>
</form>