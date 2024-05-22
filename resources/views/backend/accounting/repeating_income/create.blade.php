@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
				<h4 class="header-title">{{ _lang('Add Repeating Income') }}</h4>
			</div>

            <div class="card-body">
                <form method="post" class="validate" autocomplete="off" action="{{ route('repeating_income.store') }}"
                    enctype="multipart/form-data">
                    {{ csrf_field() }}

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Date') }}</label>
                                <input type="text" class="form-control datepicker" name="trans_date"
                                    value="{{ old('trans_date') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Account') }}</label>
                                <select class="form-control select2" name="account_id" required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("accounts","id","account_title",old('account_id'),array("company_id="=>company_id())) }}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Income Type') }}</label>
                                <select class="form-control select2" name="chart_id" required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("chart_of_accounts","id","name",old('chart_id'),array("type="=>"income","AND company_id="=>company_id())) }}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Rotation') }}</label>
                                <select name="rotation" class="form-control select2" id="rotation" required>
                                    <option value="1 month">{{ _lang('Monthly') }}</option>
                                    <option value="7 days">{{ _lang('Weekly') }}</option>
                                    <option value="14 days">{{ _lang('Bi Weekly') }}</option>
                                    <option value="1 day">{{ _lang('Everyday') }}</option>
                                    <option value="30 days">{{ _lang('Every 30 Days') }}</option>
                                    <option value="2 month">{{ _lang('Every 2 Month') }}</option>
                                    <option value="3 month">{{ _lang('Quarterly') }}</option>
                                    <option value="6 month">{{ _lang('Every 6 Month') }}</option>
                                    <option value="1 year">{{ _lang('Yearly') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-6 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Number Of Rotation') }}</label>
                                <input type="number" min="0" class="form-control" name="num_of_rotation"
                                    id="num_of_rotation" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Amount')." ".currency() }}</label>
                                <input type="text" class="form-control float-field" name="amount"
                                    value="{{ old('amount') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Payer') }}</label>
                                <select class="form-control select2" name="payer_payee_id">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("contacts","id","contact_name",old('payer_payee_id'),array("company_id="=>company_id())) }}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Payment Method') }}</label>
                                <select class="form-control select2" name="payment_method_id" required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {{ create_option("payment_methods","id","name",old('payment_method_id'),array("company_id="=>company_id())) }}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Reference') }}</label>
                                <input type="text" class="form-control" name="reference" value="{{ old('reference') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Note') }}</label>
                                <textarea class="form-control" name="note">{{ old('note') }}</textarea>
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

@section('js-script')



@endsection