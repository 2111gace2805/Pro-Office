@extends('layouts.app')

@section('content')
<style>
.btn {
    margin-bottom: 2px !important;
}
</style>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Report By Payer') }}</h4>
            </div>
            <div class="card-body">
                <div class="report-params">
                    <form class="validate" method="post" action="{{ route('reports.report_by_payer','view') }}">
                        <div class="row">
                            {{ csrf_field() }}

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('From') }}</label>
                                    <input type="text" class="form-control datepicker" name="date1" id="date1"
                                        value="{{ isset($date1) ? $date1 : old('date1') }}" readOnly="true" required>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('To') }}</label>
                                    <input type="text" class="form-control datepicker" name="date2" id="date2"
                                        value="{{ isset($date2) ? $date2 : old('date2') }}" readOnly="true" required>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Payer') }}</label>
                                    <select class="form-control select2" name="payer_id">
                                        <option value="">{{ _lang('Select One') }}</option>
                                        {{ create_option("contacts","id","contact_name",isset($payer_id) ? $payer_id : "") }}
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary btn-sm">{{ _lang('View Report') }}</button>
                            </div>

                        </div>
                    </form>
                </div>
                <!--End Report param-->

                @php $date_format = get_date_format(); @endphp

                <div class="report-header">
                    <h5>{{ _lang('Report By Payer') }}</h5>
                    <h6>{{ isset($date1) ? date($date_format, strtotime($date1)).' '._lang('to').' '.date($date_format,strtotime($date2)) : '-------------  '._lang('to').'  -------------' }}
                    </h6>
                </div>

                <table class="table table-bordered report-table">
                    <thead>
                        <th>{{ _lang('Date') }}</th>
                        <th>{{ _lang('Income Type') }}</th>
                        <th>{{ _lang('Note') }}</th>
                        <th>{{ _lang('Account') }}</th>
                        <th>{{ _lang('Payer') }}</th>
                        <th class="text-right">{{ _lang('Amount') }}</th>
                    </thead>
                    <tbody>

                        @if(isset($report_data))
                        @php $currency = currency(); @endphp

                        @foreach($report_data as $report)
                        <tr>
                            <td>{{ date($date_format,strtotime($report->trans_date)) }}</td>
                            <td>{{ $report->c_type }}</td>
                            <td>{{ $report->note }}</td>
                            <td>{{ $report->account }}</td>
                            <td>{{ $report->payer }}</td>
                            <td class="text-right">{{ decimalPlace($report->amount, $currency) }}</td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection