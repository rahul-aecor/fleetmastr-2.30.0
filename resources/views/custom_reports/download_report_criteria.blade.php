<div class="form-group row d-flex align-items-center">
    <label class="col-md-4 control-label" for="date_range">Date range:</label>
    <div class="col-md-8">
        <div class="input-group">
            {{ $dateRange }}
        </div>
    </div>
</div>

<input type="hidden" name="is_custom_report" value="{{ $report->is_custom_report }}" id="isCustomReportFlag">
<input type="hidden" name="is_auto_download" value="{{ $downloadReport->is_auto_download }}" id="isAutoDownloadFlag">

<?php
    if(in_array($report->slug, ['standard_last_login_report', 'standard_driver_behaviour_report'])) {
        $title = 'User division/region:';
    } else {
        $title = 'Vehicle division/region:';
    }
?>

@include('_partials.custom_reports.report_divisions_regions', ['labelTitle' => $title, 'type' => ''] )

<div class="portlet box marginbottom0">
    @include('custom_reports.report_columns', ['type' => 'download'])
</div>