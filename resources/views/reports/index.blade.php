@extends('layouts.default')

@section('plugin-scripts')
@endsection

@section('scripts')
@endsection

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="portlet box user-list-portlet marginbottom0">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">
                        Reports&nbsp;
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="custom-responsive-table">
                        <table class="ui-jqgrid-htable table table-condensed table-striped table-hover custom-table-striped table-hmrc-info">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Download</th>
                                    <th>&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($flag)
                                    <tr>
                                        <td>Month To Date Defect Report (All regions)</td>
                                        <td>This report keeps a track of all the defects recorded within a calendar month as they accumulate.</td>
                                        <td>
                                            {!! Form::select('topLevelMonthToDateDefect', config('config-variables.monthReportOptionForSelect'), null, ['id' => 'topLevelMonthToDateDefect', 'class' => 'form-control']) !!}
                                        </td>
                                        <td>
                                            <a href="/reports/download/a" class="btn btn-block btn-padding-big red-rubine" id="top_level_month_to_date_defect_btn" style="margin-left: 0">Download</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Week To Date VOR Defect Report (All regions)</td>
                                        <td>This report keeps a track of all the defects recorded within a week as they accumulate that are rendering vehicles VOR.</td>
                                        <td>
                                            {!! Form::select('topLevelWeekToDateVorDefect', config('config-variables.weekReportOptionForSelect'), null, ['id' => 'topLevelWeekToDateVorDefect', 'class' => 'form-control']) !!}
                                        </td>
                                        <td>
                                            <a href="/reports/download/b" class="btn btn-block btn-padding-big red-rubine" id="top_level_week_to_date_vor_defect_btn" style="margin-left: 0">Download</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Week To Date VOR Report (All regions)</td>
                                        <td>This report keeps a track of all the vehicles as the week progresses that have been VOR’d and confirms when the vehicles are estimated to be back on the road.</td>
                                        <td>
                                            {!! Form::select('topLevelWeekToDateVorVehicle', config('config-variables.weekReportOptionForSelect'), null, ['id' => 'topLevelWeekToDateVorVehicle', 'class' => 'form-control']) !!}
                                        </td>
                                        <td>
                                            <a href="{{ url('/reports/download/d') }}" class="btn btn-block btn-padding-big red-rubine" id="topLevelWeekToDateVorVehicleBtn" style="margin-left: 0">
                                                Download
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Week To Date Activity Report (All regions)</td>
                                        <td>This report keeps a track of all the vehicle checks recorded within a week by users as they accumulate.</td>
                                        <td>
                                            {!! Form::select('allWeekToDateActivity', config('config-variables.weekReportOptionForSelect'), null, ['id' => 'allWeekToDateActivity', 'class' => 'form-control']) !!}
                                        </td>
                                        <td>
                                            <a href="{{ url('/reports/download/j') }}" class="btn btn-block btn-padding-big red-rubine" id="allWeekToDateActivityBtn" style="margin-left: 0">
                                                Download
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                                @foreach($userAccessibleRegions as $key => $region)
                                <tr>
                                    <td>Week To Date VOR Defect Report ({{$region}})</td>
                                    <td>This report keeps a track of all the defects recorded within a week as they accumulate that are rendering vehicles VOR for a specific region.</td>
                                    <td>
                                        {!! Form::select('weekToDateVorDefect', config('config-variables.weekReportOptionForSelect'), null, ['class' => 'form-control weekToDateVorDefect','data-user-region-id' => $key ]) !!}
                                    </td>
                                    <td>
                                        <a href="{{ url('/reports/regionwise/download')}}/{{$key}}" class="btn btn-block btn-padding-big red-rubine" id="weekToDateVorDefectBtn{{$key}}" style="margin-left: 0">
                                            Download
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td>P11D Benefits in Kind Report</td>
                                    <td>This report documents the vehicle usage of each driver during the tax year and calculates the value of any benefits in kind.</td>
                                    <td>
                                        {!! Form::select('p11DBenefitsInKind', $taxYearList, null, ['id' => 'p11DBenefitsInKind', 'class' => 'form-control']) !!}
                                    </td>
                                    <td>
                                        <a href="" class="btn btn-block btn-padding-big red-rubine" id="P11DBenefitsInKindBtn" style="margin-left: 0">
                                            Download
                                        </a>
                                    </td>
                                </tr>
                                @if(setting('is_fleetcost_enabled'))
                                    <tr>
                                        <td>Monthly Fleet Costs (All regions)</td>
                                        <td>This report keeps track of all the monthly vehicle costs.</td>
                                        <td>
                                            {!! Form::select('fleetCostSelectMonth', config('config-variables.monthReportOptionForSelect'), null, ['id' => 'fleetCostSelectMonth', 'class' => 'form-control']) !!}
                                        </td>
                                        <td>
                                            <a href="/reports/fleetCost/thisMonth" class="btn btn-block btn-padding-big red-rubine" id="fleet_cost_report_btn" style="margin-left: 0">Download</a>
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td>Last Login Report</td>
                                    <td>This report shows the last login day and time for each user.</td>
                                    <td>
                                        <select class="disabled form-control" disabled="disabled">
                                            <option value="N/A">Not applicable</option>
                                        </select>
                                    </td>
                                    <td>
                                        <a href="/reports/download/lastlogin" class="btn btn-block btn-padding-big red-rubine" id="fleet_cost_report_btn" style="margin-left: 0">Download</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
<script src="{{ elixir('js/reports.js') }}" type="text/javascript"></script>
@endpush