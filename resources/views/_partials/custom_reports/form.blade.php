<div class="portlet box">
    <div class="portlet-title bg-red-rubine">
        <div class="caption">
            Report Details
        </div>
    </div>
    <div class="portlet-body">
        <div class="form-group row gutters-tiny js-report-form">
            <label class="col-md-4 control-label" for="report_name">Name of report*:</label>
            <div class="col-md-8">
                <input type="text" name="report_name" id="report_name" class="form-control" value="{{ isset($report) ? $report->name : null }}">
            </div>
        </div>

        <div class="form-group row gutters-tiny js-report-display d-none">
            <label class="col-md-4 control-label" for="report_name">Name of report*:</label>
            <div class="col-md-8">
                <div class="has-label js-report-name pt15"></div>
            </div>
        </div>

        <div class="form-group row gutters-tiny js-report-form">
            <label for="comments" class="col-md-4 control-label">Report description*:</label>
            <div class="col-md-8 error-class">
                <textarea type="text" name="report_description" id="report_description" class="
                manual-cost-adjustment-textarea form-control" rows="5" maxlength="200">{{ isset($report) ? $report->description : null }}</textarea>
                <div class="comment-manual-cost text-right"><span class="js-fleetcost-manual-cost-comment">{{ isset($report) ? 200-strlen($report->description) : 200 }}</span>/200 remaining characters</div>
                <div class="form-control-focus"></div>
            </div>
        </div>

        <div class="form-group row gutters-tiny js-report-display d-none">
            <label class="col-md-4 control-label" for="report_name">Report description*:</label>
            <div class="col-md-8">
                <div class="has-label js-report-desc pt15"></div>
            </div>
        </div>

        <input type="hidden" name="report_id" value="{{ $report['id'] }}">

    </div>
</div>

<div class="portlet box">
    <div class="portlet-title bg-red-rubine">
        <div class="caption">
            Data Set
        </div>
        <div class="actions">
            <a href="javascript:void(0);" class="js-reset-dataset btn red-rubine btn-padding">Reset dataset</a>
        </div>
    </div>
    <div class="portlet-body select_accordion report-section-accordion js-show-category-dataset">
            <div class="js-report-form">
                @include('_partials.custom_reports.set_category_dataset')
            </div>
            <input type="hidden" name="report_slug" id="reportSlug" value="{{ $report['slug'] }}">
            <div class="form-group row d-flex margin-bottom-30{{ in_array( $report['slug'], ['standard_last_login_report', 'standard_user_details_report', 'standard_vehicle_profile_report'] ) ? ' d-none' : '' }}">
                <label class="col-md-4 control-label" for="date_range">Date range*:</label>
                <div class="col-md-8 js-date-range">
                    <div class="input-group all_reports js-report-form">
                        {!! Form::text('date_range', null, ['id' => 'date_range', 'class' => 'form-control bg-white cursor-pointer', 'placeholder' => 'Report date', 'readonly' => 'readonly']) !!}
                        <span class="input-group-btn">
                            <button class="btn default date-set grey-gallery js-daterangepicker-button btn-h-45" type="button"><i class="jv-icon jv-calendar font-weight-700"></i></button>
                        </span>
                    </div>
                    <div class="has-label js-report-display d-none js-report-desc pt15 js-report-daterange"></div>
                </div>
            </div>
            <div class="js-division-container">
                @include('_partials.custom_reports.report_divisions_regions', ['labelTitle' => 'Select division/region:', 'type' => 'edit'])
            </div>

        <!-- @if($reportColumns) -->
        <!-- @endif -->
    </div>
</div>

<div class="form-group row">
    <div class="col-md-4"></div>
    <div class="col-md-8 btn-group js-report-form">
        <a href="/reports" type="button" class="btn white-btn btn-padding col-md-6">Cancel</a>
        <button type="submit" class="btn red-rubine btn-padding col-md-6" id="btnSaveCustomReport">Next</button>
    </div>
    <div class="col-md-8 btn-group js-report-display d-none">
        <button type="button" class="btn white-btn btn-padding col-md-6" id="customiseData">Customise</button>
        <button type="button" class="btn red-rubine btn-padding col-md-6" id="btnSubmitCustomReport">Generate Report</button>
    </div>
</div>