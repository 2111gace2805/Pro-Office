@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card panel-default">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Income VS Expense Report') }}</h4>
            </div>

            <div class="card-body">

                <div class="report-params">
                    <form class="validate" method="post" action="{{ url('reports/income_vs_expense/view') }}">
                        <div class="row">
                            {{ csrf_field() }}

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('End Date') }}</label>
                                    <input type="text" class="form-control datepicker" name="date1" id="date1"
                                        value="{{ isset($date1) ? $date1 : old('date1') }}" readOnly="true" required>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('End Date') }}</label>
                                    <input type="text" class="form-control datepicker" name="date2" id="date2"
                                        value="{{ isset($date2) ? $date2 : old('date2') }}" readOnly="true" required>
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
                    <h5>{{ _lang('Income VS Expense Report') }}</h5>
                    <h6>{{ isset($date1) ? date($date_format,strtotime($date1)).' '._lang('to').' '.date($date_format,strtotime($date2)) : '-------------  '._lang('to').'  -------------' }}</h6>
                </div>

                <table class="table table-bordered report-table">
                    <thead>
                        <th>{{ _lang('Income Date') }}</th>
                        <th>{{ _lang('Income Type') }}</th>
                        <th class="text-right">{{ _lang('Amount') }}</th>
                        <th>{{ _lang('Expense Date') }}</th>
                        <th>{{ _lang('Expense Type') }}</th>
                        <th class="text-right">{{ _lang('Amount') }}</th>
                    </thead>
                    <tbody>

                        @if(isset($report_data))
                        @php
                        $currency = currency();
                        $income_total = 0;
                        $expense_total = 0;
                        @endphp

                        @foreach($report_data as $report)
                        <tr>
                            <td>{{ $report->income_date != '' ? date($date_format,strtotime($report->income_date)) : '' }}</td>
                            <td>{{ $report->income_type }}</td>
                            <td class="text-right">{{ decimalPlace($report->income_amount, $currency) }}</td>
                            <td>{{ $report->expense_date != '' ? date($date_format,strtotime($report->expense_date)) : '' }}</td>
                            <td>{{ $report->expense_type }}</td>
                            <td class="text-right">{{ decimalPlace($report->expense_amount, $currency) }}</td>
                        </tr>

                        @php
                        $income_total += $report->income_amount;
                        $expense_total += $report->expense_amount;
                        @endphp
                        @endforeach
                        <tr>
                            <td></td>
                            <td>{{ _lang('Total Income') }}</td>
                            <td class="text-right">{{ decimalPlace($income_total, $currency) }}</td>
                            <td></td>
                            <td>{{ _lang('Total Expense') }}</td>
                            <td class="text-right">{{ decimalPlace($expense_total, $currency) }}</td>
                        </tr>

                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection