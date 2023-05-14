<div class="col-md-4" style="position: sticky; top: 68px">
    <div class="portlet box">
        <div class="portlet-title bg-red-rubine">
            <div class="caption">
                Report Summary
            </div>
        </div>
        <div class="portlet-body report-summary">
            <label class="control-label" for="report_name">This report includes the following data:</label>
            <!-- <h4>This report includes the following data:</h4> -->
            <table class="table table-striped table-hover custom-table-striped" id="reportSummary">
                <tbody>
                </tbody>
            </table>

            <label class="control-label{{ in_array( $report['slug'], ['standard_last_login_report', 'standard_user_details_report', 'standard_vehicle_profile_report'] ) ? ' d-none' : '' }}">Date range:
                <span id="reportDateSummary" style="margin-left:10px;">
                </span>
            </label>

            <div class="reportDivisionRegionSummary">
                <label class="control-label" for="region_name">Selected division/region:</label>
                <table class="table" id="reportDivisionRegionSummary">
                    <tbody>
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</div>