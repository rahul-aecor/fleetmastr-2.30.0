@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal-bs3patch.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/vanilla-datetimerange-picker/vanilla-datetimerange-picker.css') }}" rel="stylesheet" type="text/css"/>
    <style>
        .report_download {
            color: #3598dc !important;
        }
        .daterangepicker .daterangepicker_start_input label,
        .daterangepicker .daterangepicker_end_input label {
          text-transform: capitalize;
        }
        .ui-jqgrid tr.jqgrow td, .ui-jqgrid tr.jqgrow td span {
            white-space: normal !important;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js" type="text/javascript"></script>
    <script src="{{ elixir('js/vanilla-datetimerange-picker/vanilla-datetimerange-picker.js') }}"></script>
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modal.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modalmanager.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script> 
@endsection

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <div class="tabbable-custom tabbable-rubine nav-justified margin-bottom0 js-custom-report-container">
        
        <ul class="nav nav-tabs nav-justified customreport_tabs">
            <li class="{{ showTelematicsSelectedTab($selectedTab, "reports_tab") }}" id="reports_tab">
                <a href="#reports" data-toggle="tab">
                Reports </a>
            </li>
            {{-- <li class="{{ showTelematicsSelectedTab($selectedTab, "custom_reports_tab") }}" id="custom_reports_tab">
                <a href="#custom_reports" data-toggle="tab">
                Custom Reports </a>
            </li> --}}
            <li class="{{ showTelematicsSelectedTab($selectedTab, "downloads_tab") }}" id="downloads_tab">
                <a href="#downloads" data-toggle="tab">
                Downloads </a>
            </li>

        </ul>
        <div class="tab-content" id="tabs">
            <div class="tab-pane {{ showTelematicsSelectedTab($selectedTab, "reports_tab") }}" id="reports">
                @include('_partials.custom_reports.search', ['formId' => 'reports-filter-form', 'searchId' => 'quickSearchInputReport', 'categoryId' => 'reportCategory', 'clearBtnId' => 'grid-report-clear-search-btn'])
                <div class="portlet box marginbottom0">
                    <div class="portlet-title bg-red-rubine">
                        <div class="caption">
                            Reports
                        </div>
                        {{-- <div class="actions new_btn">
                            <a href="{{ route('customreports.create') }}" class="config btn btn-sm gray_btn_border table-group-action-submit title_right_btn"><i class="jv-icon jv-plus"></i> Create custom report</a>
                        </div> --}}
                    </div>
                    <div class="portlet-body">
                        <div class="jqgrid-wrapper">
                            <table id="reportsJqGrid" class="table-striped table-hover"></table>
                            <div id="reportsJqGridPager"></div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- <div class="tab-pane {{ showTelematicsSelectedTab($selectedTab, "custom_reports_tab") }}" id="custom_reports">
                @include('_partials.custom_reports.search', ['formId' => 'custom-reports-filter-form', 'searchId' => 'quickSearchInput', 'categoryId' => 'category', 'clearBtnId' => 'grid-clear-search-btn'])
        		<div class="portlet box marginbottom0">
	                <div class="portlet-title bg-red-rubine">
                        <div class="caption">
                            Custom Reports
                        </div>
	                    <div class="actions new_btn align-self-end">
                            <a href="{{ route('customreports.create') }}" class="config btn btn-sm gray_btn_border table-group-action-submit title_right_btn"><i class="jv-icon jv-plus"></i> Create custom report</a>
                        </div>
	                </div>
	                <div class="portlet-body">
	                    <div class="jqgrid-wrapper">
                            <table id="jqGrid" class="table-striped table-hover"></table>
                            <div id="jqGridPager"></div>
                        </div>
                    </div>
				</div>
			</div> --}}
			<div class="tab-pane {{ showTelematicsSelectedTab($selectedTab, "downloads_tab") }}" id="downloads">
                @include('_partials.custom_reports.search', ['formId' => 'custom-reports-download-filter-form', 'searchId' => 'quickSearchInputForDownload', 'categoryId' => 'category-select', 'clearBtnId' => 'grid-clear-download-search-btn'])
				<div class="portlet box marginbottom0">
                    <div class="portlet-title bg-red-rubine">
                        <div class="caption">
                            Downloads
                        </div>
                        {{-- <div class="actions new_btn">
                            <a href="{{ route('customreports.create') }}" class="config btn btn-sm gray_btn_border table-group-action-submit title_right_btn"><i class="jv-icon jv-plus"></i> Create custom report</a>
                        </div> --}}
                    </div>
                    <div class="portlet-body">
                        <div class="jqgrid-wrapper">
                            <table id="jqGrid1" class="table-striped table-hover"></table>
                            <div id="jqGridPager1"></div>
                        </div>
                    </div>
                </div>
			</div>
        </div>
    </div>

    <div class="modal fade default-modal" id="download_report_modal" tabindex="-1" data-keyboard="false" role="dialog" aria-labelledby="downloadReportLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! BootForm::openHorizontal(['md' => [4, 8]])->addClass('form-bordered form-validation customreport-download-form')->action('/reports/save_download_report')->id('saveDownloadReport') !!}
                <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                  <h4 class="modal-title">Report Download</h4>
                    <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                        <i class="jv-icon jv-close"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="form-group row d-flex align-items-center margin-bottom-30">
                        <label class="col-md-4 control-label" for="date_range">Date range:</label>
                        <div class="col-md-8">
                            <input type="hidden" name="report_id" id="reportId">
                            <input type="hidden" name="report_slug" id="reportSlug">
                            <div class="input-group all_reports">
                                {!! Form::text('date_range', null, ['id' => 'date_range', 'class' => 'form-control bg-white cursor-pointer', 'placeholder' => 'Report date', 'readonly' => 'readonly']) !!}
                                <span class="input-group-btn">
                                    <button class="btn default date-set grey-gallery js-daterangepicker-button btn-h-45" type="button"><i class="jv-icon jv-calendar font-weight-700"></i></button>
                                </span>
                                <!-- <span class="input-group-addon js-daterangepicker-button" id="basic-addon1"><i class="jv-icon jv-calendar font-weight-700"></i></span> -->
                            </div>
                            <div class="input-group last_login_reports">
                                <span class="margin-0" type="text" readonly="readonly">Live report</span>
                            </div>
                        </div>
                    </div>

                    <div class="js-division-container"> 
                        @include('_partials.custom_reports.report_divisions_regions', ['labelTitle' => 'Select division/region:', 'type' => ''])
                    </div>

                    <div class="portlet box marginbottom0 js-show-report-data">
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-md-offset-2 col-md-8">
                            <div class="btn-group pull-left width100">
                                <button type="submit" class="btn red-rubine btn-padding col-md-12 text-center">Download report</button>
                            </div>
                        </div>    
                    </div>
                    <div class="row">
                        <div class="text-center col-md-10 col-md-offset-1">
                            <p class="margin-top-10">Your report may take few minutes to process. An email will be sent to you once your report is ready to view. Your report will be available on the Downloads tab.</p>
                        </div>
                    </div>
                </div>
                {!! BootForm::close() !!}
            </div>
        </div>
    </div>

    <div class="modal fade default-modal" id="download_report_criteria_modal" tabindex="-1" data-keyboard="false" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                  <h4 class="modal-title">Report Criteria</h4>
                    <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                        <i class="jv-icon jv-close"></i>
                    </a>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-md-offset-2 col-md-8">
                            <div class="btn-group pull-left width100">
                                <button type="button" class="btn red-rubine btn-padding col-md-12 text-center margin-bottom-40"data-dismiss="modal" aria-label="Close">Ok</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ elixir('js/custom-report-calendar.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/custom-reports.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/customreports-jqgrid.js') }}" type="text/javascript"></script>
@endpush