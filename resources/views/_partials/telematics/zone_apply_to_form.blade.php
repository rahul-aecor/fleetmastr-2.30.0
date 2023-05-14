{!! BootForm::openHorizontal($columnSizes)->addClass('form-validation form')->id('applyToZoneForm')->action($url)->multipart()->post() !!}
<div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
    <h4 class="modal-title">Zones</h4>
    <a class="font-red-rubine" data-dismiss="modal" aria-label="Close" id="editMaintenanceHistoryClose">
        <i class="jv-icon jv-close"></i>
    </a>
</div>

<div class="modal-body form-bordered">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label class="col-md-3 control-label toggle_switch" for="apply_to_select">Apply to:</label>
                <div class="col-md-7">
                    <select class="form-control select2me apply_to_select" id="apply_to_select" name="apply_to_select" placeholder="Please select">
                        <option value=""></option>
                        <option value="division">All vehicles within a division</option>
                        <option value="vehicle_type">Specific vehicle types</option>
                        <option value="registration">Specific vehicle registrations</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row DivisionDiv" id='divisionDiv' style="display:none;">
        <div class="col-md-12">
            @if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'))
                <div class="form-group align-items-start">
                    <label class="col-md-4 control-label mt-22" for="accessible_regions[]">Select division(s):</label>
                    <div class="col-md-12 report-section-accordion accessible-regions-checkbox-wrapper">
                        @foreach ($allVehicleDivisionsList as $division => $regions)
                            <div id="accordion{{ $division }}" class="panel-group accordion all_division_list">
                                <div class="panel panel-default">
                                    <div class="panel-heading bg-red-rubine">
                                        <h4 class="d-flex panel-title">
                                            <label>
                                                <input type="checkbox" name="accessible_divisions[]" class="accessible-divisions-checkbox divisions-group division-{{ $division }}" value="{{ $division }}">
                                            </label>
                                            <a class="accordion-toggle flex-grow-1 accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion{{ $division }}" href="#nested-regions{{ $division }}">
                                               {{ $vehicleDivisions[$division] }}
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="nested-regions{{ $division }}" class="panel-body panel-collapse collapse">
                                        <div class="row margin-top-10">
                                            <div class="col-md-12">
                                                <label class="margin-bottom-5">
                                                    <input type="checkbox" class="all_division_region" value="{{ $division }}" disabled="disabled">
                                                    All
                                                </label>
                                                <div class="row marginbottom0">
                                                    @foreach(array_chunk($regions, 2, true) as $chunk)
                                                        @foreach($chunk as $region_id => $region_name)
                                                            <div class="col-md-12">
                                                                <div class="all_regions margin-bottom-5">
                                                                    <label>
                                                                        <input type="checkbox" name="accessible_regions[]" class="accessible-regions-checkbox-{{ $division }} regions-group" value="{{ $region_id }}" disabled="disabled">
                                                                        {{ $region_name }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="form-group align-items-start">
                    <label class="col-md-3 control-label mt-22" for="accessible_regions[]">Select division(s):</label>
                    <div class="col-md-9 report-section-accordion">
                        <div class="row">
                            <div class="col-md-12 accessible-regions-checkbox-wrapper">
                                <div class="checkbox-list">
                                    <div class="row gutters-tiny">
                                        <div class="col-md-12">
                                            <label>
                                                <input type="checkbox" id="all_accessible_region" value="">
                                                All
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row gutters-tiny">
                                        @foreach(array_chunk($allVehicleDivisionsList, 2, true) as $chunk)
                                            @foreach($chunk as $division => $regions)
                                                <div class="col-md-4">
                                                    <div class="all_regions">
                                                        <label>
                                                            <input type="checkbox" name="accessible_regions[]" class="accessible-regions-checkbox regions-group" value="{{ $division }}">
                                                            {{ $regions }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 accessible-regions-checkbox-wrapper-error"></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="row VehicleTypeDiv form-bordered" style="display:none;">
        <div class="col-md-12">
            <div id="accordion_vehicletype" class="panel-group all_vehicleType_list">
                <div class="panel panel-default">
                    <div class="panel-heading bg-red-rubine">
                        <h4 class="panel-title">
                            <div class="checkbox-list">
                                <label class="mb-0">
                                    <input type="checkbox"  id="all_vehicle_types" class="accessible-divisions-checkbox divisions-group  division-24" value="24">
                                    Select all vehicle types
                                </label>
                            </div>
                        </h4>
                    </div>
                    <div id="nested-vehicletypes" class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    @foreach(array_chunk($allVehicleTypesList, 1, true) as $chunk)
                                        @foreach($chunk as $id => $text)
                                            <div class="col-md-6">
                                                <div class="all_regions">
                                                    <label>
                                                        <input type="checkbox" name="vehicle_types[]" class="vehicle-type-checkbox vehicle-type-group" value="{{ $id }}">
                                                        {{ $text }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div class="form-group row margin-bottom-10">
                <label class="col-md-4 control-label" for="vehicle_types[]">Select vehicle type(s):</label>
                <div class="col-md-8 report-section-accordion">
                    <div class="form-group row">
                        <div class="col-md-12 accessible-regions-checkbox-wrapper">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="checkbox-list">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label>
                                                    <input type="checkbox" id="all_vehicle_types" value="">
                                                    All
                                                </label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            @foreach(array_chunk($allVehicleTypesList, 1, true) as $chunk)
                                                @foreach($chunk as $id => $text)
                                                    <div class="col-md-6">
                                                        <div class="all_regions">
                                                            <label>
                                                                <input type="checkbox" name="vehicle_types[]" class="vehicle-type-checkbox vehicle-type-group" value="{{ $id }}">
                                                                {{ $text }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 vehicle-type-checkbox-wrapper-error"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
    <div class="row registerationDiv gutters-tiny" style="display:none;">
        <div class='col-md-3'>&nbsp;</div>
        <div class="col-md-7">
            <div class="d-flex">
                <div class="flex-grow-1 margin-right-10 js-zone-registration">
                    <input type="text" class="form-control" name="zoneRegistration" id="zoneRegistration" placeholder="Enter registration">
                </div>
                <div class="" style="flex-shrink: 0">
                    <button type="button" class="btn red-rubine btn-block btn-h-45" id="regListAddBtn">Add</button>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="portlet box user-list-portlet marginbottom0 js-list-vehicle-registration d-none">
                <div class="portlet-body margin-top-10">
                    <input type="text" id="zoneRegList" name="zoneRegList" class="d-none">
                    <table class="ui-jqgrid-btable ui-common-table table">
                        <thead><th>List of vehicles:</th></thead>
                    </table>
                    <table id="regList" class="table-striped table-hover check-table ui-jqgrid-btable ui-common-table table">
                        <tbody><tr></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <div class="col-md-offset-2 col-md-8 ">
        <div class="btn-group pull-left width100">
            <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn red-rubine btn-padding submit-button col-md-6">Save</button>
        </div>
    </div>
</div>
{!! BootForm::close() !!}