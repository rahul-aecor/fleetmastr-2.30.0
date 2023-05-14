<form class="form-horizontal" role="form" id="addNewAssgignmentForm">
    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
        <h4 class="modal-title">Add Vehicle Assignment</h4>
        <a class="font-red-rubine" data-dismiss="modal" aria-label="Close" id="maintenanceHistoryClose">
            <i class="jv-icon jv-close"></i>
        </a>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group row gutters-tiny">
                    <label class="col-md-3 control-label">Registration:</label>
                    <div class="col-md-9 error-class">
                        <input type="text" class="form-control" name="registration_no" value="{{ $vehicle->registration }}" readonly>
                    </div>
                </div>

                <div class="form-group row gutters-tiny">
                    <label class="col-md-3 control-label" for="vehicle_division_id">Division*:</label>
                    <div class="col-md-9 error-class">
                        <select class="form-control select2me vehicle-division-value js-assignment-division-value" id="add_vehicle_division_id" name="add_vehicle_division_id" placeholder="Select">
                            @foreach ($vehicleDivisions as $key => $division)
                                <option {{ $vehicle->vehicle_division_id == $key ? 'selected': '' }} value="{{ $key }}">{{ $division}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="vehicle-region-value">
                    <div class="form-group row gutters-tiny">
                        <label class="col-md-3 control-label" for="vehicle_region_id">Region*:</label>
                        <div class="col-md-9 error-class">
                            <select class="form-control select2me select2me vehicle-region js-assignment-region-value" id="add_vehicle_region_id" name="add_vehicle_region_id" placeholder="Select">
                                <option></option>
                                @if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'))
                                    @foreach ($vehicleRegions as $divisionId => $regions)
                                        @if($regions != '')
                                            @foreach ($regions as $regionId => $region)
                                                <option {{ $vehicle->vehicle_region_id == $regionId ? 'selected': '' }} value="{{ $regionId }}">{{ $region}}</option>
                                            @endforeach
                                        @endif
                                    @endforeach
                                @else
                                    @foreach ($vehicleRegions as $key => $region)
                                        <option {{ $vehicle->vehicle_region_id == $key ? 'selected': '' }} value="{{ $key }}">{{ $region}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                <div class="vehicle-location">
                    <div class="form-group row gutters-tiny">
                        <label class="col-md-3 control-label">Location:</label>
                        <div class="col-md-9 error-class">
                            <select class="form-control select2me js-assignment-location-value" id="add_vehicle_location_id" name="add_vehicle_location_id" placeholder="Select">
                                @foreach ($vehicleLocation as $key => $location)
                                    <option {{ $vehicle->vehicle_location_id == $key ? 'selected': '' }} value="{{ $key }}">{{ $location}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="add_assignment_vehicle_id" id="add_assignment_vehicle_id" value="{{$vehicle->id}}">

            </div>
                
        </div>
    </div>
    <div class="modal-footer">
        <div class="col-md-offset-2 col-md-8 ">
            <div class="btn-group pull-left width100">
                <button type="button" class="btn white-btn btn-padding col-md-6" id="maintenanceHistoryCancle" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn red-rubine btn-padding submit-button col-md-6" id="addAssignmentBtn">Save</button>
            </div>
        </div>
    </div>
</form>