 <form class="form-horizontal js-vehicle-cost-field js-manual-cost-adjustment" role="form" id="vehicleManualCostForm">
    <div class="form-group row d-flex align-items-center margin-bottom-30">
        <label for="cost" class="col-md-2 control-label padding-top-0">Cost*:</label>
        <div class="col-md-10 error-class">
            <div class="input-group">
                <span class="input-group-addon">&pound;</span>
                <input type="text" name="vehicle_manual_cost" id="vehicle_manual_cost" class="form-control" value="">
            </div>
        </div>
    </div>
    <div class="form-group row js-manual-cost-date-picker d-flex align-items-center margin-bottom-30 position-relative gutters-tiny">
        <label for="from" class="col-md-2 control-label padding-top-0">From*:</label>
        <div class="col-md-4 error-class">
            <div class='input-group date form_date vehicleCostFromDate'>
                <input type="text" name="vehicle_manual_cost_from_date" readonly id="vehicle_manual_cost_from_date" class="form-control js-vehicle-manual-cost-from-date" value="">
                <span class="input-group-btn">
                    <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                </span>
            </div>
        </div>
        <label for="to" class="col-md-2 control-label padding-top-0" style="text-align: center;">To*:</label>
        <div class="col-md-4 error-class">
            <div class='input-group date form_date vehicleCostToDate'>
                <input type="text" name="vehicle_manual_cost_to_date" readonly id="vehicle_manual_cost_to_date" class="form-control">
                <span class="input-group-btn">
                    <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                </span>
            </div>
        </div>
    </div>
    <div class="form-group row d-flex align-items-center align-items-center margin-bottom-30">
        <label for="comments" class="col-md-2 control-label padding-top-0">Comments*:</label>
        <div class="col-md-10 error-class">
            <textarea type="text" name="vehicle_manual_cost_comment" id="vehicle_manual_cost_comment" class="
            manual-cost-adjustment-textarea form-control" rows="2" maxlength="100"></textarea>
            <span class="position-absolute comment-manual-cost"><span class="js_manual_cost_comment">100</span>/100 remaining characters</span>
            <div class="form-control-focus"></div>
        </div>
    </div>
    <input type="hidden" name="vehicle_manual_cost_id" id="vehicle_manual_cost_id" value="">

    <div class="btn-group width100 margin-top-20">
        <button type="button" class="btn white-btn btn-padding col-md-6 manualCostCancle" data-dismiss="modal">Cancel</button>
        {{-- <button id="fleetCostAreaFormSave" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Save</button>   --}}
        <button id="vehicleManualCostAdjustment" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Save</button>   
    </div>
</form>
