<form class="form-horizontal" role="form" id="editAssignmentHistory" data-upload-template-id="template-upload-1" data-download-template-id="template-download-1">
    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
        <h4 class="modal-title">Edit Vehicle Assignment</h4>
        <a class="font-red-rubine" data-dismiss="modal" aria-label="Close" id="editMaintenanceHistoryClose">
            <i class="jv-icon jv-close"></i>
        </a>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group row d-flex align-items-center">
                    <label class="col-md-3 col-form-label">Registration:</label>
                    <div class="col-md-9 error-class">
                        <input type="text" size="16" readonly class="form-control" name="edit_assignment_registration_number" id="edit_assignment_registration_number" value="{{$vehicleRegistration}}">
                    </div>
                </div>
                <div class="form-group row d-flex align-items-center">
                    <label class="col-md-3 col-form-label">Division*:</label>
                    <div class="col-md-9 error-class">
                        <select class="form-control select2me select2-edit-division-assignement-type assignment-region assignment-division js-assignment-division-value" id="edit_assignment_division" name="edit_assignment_division" placeholder="Select">
                            @foreach($vehicleDivisions as $key => $division)
                                <option {{ $vehicleAssignmentHistory->vehicle_division_id === $key ? 'selected': '' }} value="{{ $key }}">{{ $division}}</option>
                            @endforeach
                      </select>
                    </div>
                </div>
                <div class="form-group row d-flex align-items-center">
                    <label class="col-md-3 col-form-label">Region*:</label>
                    <div class="col-md-9 error-class">
                        <select class="form-control select2me select2-edit-region-assignement-type js-assignment-region-value" id="edit_assignment_region" name="edit_assignment_region" placeholder="Select">
                            @if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'))
                                @foreach ($vehicleRegions as $divisionId => $regions)
                                    @if($regions != '')
                                        @foreach ($regions as $regionId => $region)
                                            <option {{ $vehicleAssignmentHistory->vehicle_region_id === $regionId ? 'selected': '' }} value="{{ $regionId }}">{{ $region}}</option>
                                        @endforeach
                                    @endif
                                @endforeach
                            @else
                                @foreach ($vehicleRegions as $key => $region)
                                    <option {{ $vehicleAssignmentHistory->vehicle_region_id === $key ? 'selected': '' }} value="{{ $key }}">{{ $region}}</option>
                                @endforeach
                            @endif
                      </select>
                    </div>
                </div>
                <div class="form-group row d-flex align-items-center">
                    <label class="col-md-3 col-form-label">Location:</label>
                    <div class="col-md-9 error-class">
                        <select class="form-control select2me select2-edit-location-assignement-type js-assignment-location-value" id="edit_assignment_location" name="edit_assignment_location" placeholder="Select">
                            @if(env('IS_REGION_LOCATION_LINKED_IN_VEHICLE'))
                                @foreach ($vehicleLocation as $divisionId => $locations)
                                    @if($locations != '')
                                        @foreach ($locations as $locationId => $location)
                                            <option {{ $vehicleAssignmentHistory->vehicle_location_id === $locationId ? 'selected': '' }} value="{{ $locationId }}">{{ $location}}</option>
                                        @endforeach
                                    @endif
                                @endforeach
                            @else
                                @foreach ($vehicleLocation as $key => $location)
                                    <option {{ $vehicleAssignmentHistory->vehicle_location_id === $key ? 'selected': '' }} value="{{ $key }}">{{ $location}}</option>
                                @endforeach
                            @endif

                      </select>
                    </div>
                </div>
                <div class="form-group row d-flex align-items-center">
                    <label class="col-md-3 col-form-label">From date:</label>
                    <div class="col-md-9 error-class">
                        <div class="input-group" style="width: 100%">
                            <input type="text" value="{{isset($vehicleAssignmentHistory->from_date) ? $vehicleAssignmentHistory->from_date : ''}}" class="form-control" readonly id="edit_assignment_from_date" style="width:100%">
                        </div>
                    </div>
                </div>
                <div class="form-group row d-flex">
                    <label class="col-md-3 col-form-label pt15">To date:</label>
                    <div class="col-md-9 error-class">
                        <div class="input-group date edit_assignment_to_date">
                            <input type="text" size="16" class="form-control" name="edit_assignment_to_date" id="edit_assignment_to_date" value="{{isset($vehicleAssignmentHistory->to_date) ? $vehicleAssignmentHistory->to_date : 'N/A'}}">
                            <span class="input-group-btn">
                            <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                            </span>
                        </div>
                        <div class="form-text text-muted"><b>Note:</b> Changing this date will also update the "From date" of the next entry.</div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="assignment_history_edit_id" id="assignment_history_edit_id" value="{{$vehicleAssignmentHistory->id}}">
        <input type="hidden" name="edit_assignment_vehicle_id" id="edit_assignment_vehicle_id" value="{{$vehicle->id}}">
    </div>
    <div class="modal-footer">
        <div class="col-md-offset-2 col-md-8 ">
            <div class="btn-group pull-left width100">
                <button type="button" class="btn white-btn btn-padding col-md-6" 
                id="editMaintenanceHistoryCancle" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn red-rubine btn-padding submit-button col-md-6" id="editAssignmentHistorySave">Save</button>
            </div>
        </div>
    </div>
</form>