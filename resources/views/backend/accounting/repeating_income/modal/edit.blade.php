<form method="post" class="ajax-submit" autocomplete="off" action="{{ action('RepeatingIncomeController@update', $id) }}"
    enctype="multipart/form-data">
    {{ csrf_field()}}
    <input name="_method" type="hidden" value="PATCH">

    <div class="row p-2">
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Date') }}</label>
                <input type="text" class="form-control datepicker" name="trans_date"
                    value="{{ $transaction->getRawOriginal('trans_date') }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Account') }}</label>
                <select class="form-control select2" name="account_id" required>
                    <option value="">{{ _lang('Select One') }}</option>
                    {{ create_option("accounts","id","account_title",$transaction->account_id,array("company_id="=>company_id())) }}
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Income Type') }}</label>
                <select class="form-control select2" name="chart_id" required>
                    <option value="">{{ _lang('Select One') }}</option>
                    {{ create_option("chart_of_accounts","id","name",$transaction->chart_id,array("type="=>"income","AND company_id="=>company_id())) }}
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Amount')." ".currency() }}</label>
                <input type="text" class="form-control float-field" name="amount" value="{{ $transaction->amount }}"
                    required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Payer') }}</label>
                <select class="form-control select2" name="payer_payee_id">
                    <option value="">{{ _lang('Select One') }}</option>
                    {{ create_option("contacts","id","contact_name",$transaction->payer_payee_id,array("company_id="=>company_id())) }}
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Payment Method') }}</label>
                <select class="form-control select2" name="payment_method_id" required>
                    <option value="">{{ _lang('Select One') }}</option>
                    {{ create_option("payment_methods","id","name",$transaction->payment_method_id,array("company_id="=>company_id())) }}
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Status') }}</label>
                <select class="form-control select2" name="status" required>
                    <option value="0" {{ $transaction->status == 0 ? "selected" : "" }}>{{ _lang('Pending') }}</option>
                    <option value="1" {{ $transaction->status == 1 ? "selected" : "" }}>{{ _lang('Completed') }}
                    </option>
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Reference') }}</label>
                <input type="text" class="form-control" name="reference" value="{{ $transaction->reference }}">
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Note') }}</label>
                <textarea class="form-control" name="note">{{ $transaction->note }}</textarea>
            </div>
        </div>

		<div class="col-md-12">
        	<div class="form-group">   
                <button type="submit" class="btn btn-primary">{{ _lang('Update') }}</button>
            </div>
        </div>
    </div>
</form>