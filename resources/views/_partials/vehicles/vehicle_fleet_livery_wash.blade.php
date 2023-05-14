 <form class="form-horizontal js-vehicle-cost-field js-fleet-livery-wash-adjustment" role="form" id="vehicleFleetLiveryForm">
    <div class="form-group row d-flex align-items-center margin-bottom-30">
        <label for="cost" class="col-md-2 control-label padding-top-0">Cost*:</label>
        <div class="col-md-10 error-class">
            <div class="input-group">
                <span class="input-group-addon">&pound;</span>
                <input type="text" name="vehicle_fleet_livery_cost" id="vehicle_fleet_livery_cost" class="form-control" value="">
            </div>
        </div>
    </div>
    <div class="form-group row d-flex align-items-center js-fleet-livery-cost-date-picker margin-bottom-30 position-relative gutters-tiny">
        <label for="from" class="col-md-2 control-label padding-top-0">From*:</label>
        <div class="col-md-4 error-class">
            <div class='input-group date form_date vehicleCostFromDate'>
                <input type='text' name="vehicle_fleet_livery_from_date" readonly id="vehicle_fleet_livery_from_date" class="form-control js-vehicle-fleet-livery-from-date">
                <span class="input-group-btn">
                    <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                </span>
            </div>
            <div class="hide overlapping-date modal-date-validation" id="fleetLiveryOverlappingDateValidation">
                <span class="date">Overlapping date not allowed</span>
            </div>
        </div>
        <label for="to" class="col-md-2 control-label padding-top-0" style="text-align: center;">To*:</label>
        <div class="col-md-4 error-class">
            <div class='input-group date form_date vehicleCostToDate'>
                <input type='text' name="vehicle_fleet_livery_to_date" readonly id="vehicle_fleet_livery_to_date" class="form-control">
                <span class="input-group-btn">
                    <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                </span>
            </div>
        </div>
        
    </div>
    <input type="hidden" name="vehicle_fleet_livery_data_id" id="vehicle_fleet_livery_data_id" value="">

    <div class="btn-group width100 margin-top-20">
        <button type="button" class="btn white-btn btn-padding col-md-6 vehicleFleetLiveryCancle" data-dismiss="modal">Cancel</button>
        {{-- <button id="fleetCostAreaFormSave" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Save</button>   --}}
        <button id="vehicleFleetLiverySave" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Save</button>   
    </div>
</form>