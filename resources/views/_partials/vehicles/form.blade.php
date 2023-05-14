<div class="form-body vehicle-edit-body">
    <div class="alert alert-danger display-hide bg-red-rubine">
        <button class="close" data-close="alert"></button>
        <!-- You have some form errors. Please check below. -->
        Please complete the errors highlighted below.
    </div>
    <div class="alert alert-success display-hide">
        <button class="close" data-close="alert"></button>
        Your form validation is successful!
    </div>

    <div class="portlet box">
        <div class="portlet-title bg-red-rubine">
            <div class="caption">
                {{ $fromPage == 'add' ? 'Vehicle ' : ''}}Summary
            </div>
        </div>
        <div class="portlet-body">
            {!! BootForm::text('Registration*:', 'registration') !!}

            <div class="form-group{{ $errors->has('dt_added_to_fleet') ? ' has-error' : '' }}" id="dt_added_to_fleet">
                <label class="control-label col-md-3">Date added to fleet*:</label>
                <div class="col-md-9">
                    <div class="input-group date form_date">
                        <?php
                            if ($vehicle->dt_added_to_fleet == "") {
                                $value = date('d M Y');
                            } else {
                                $value = $vehicle->dt_added_to_fleet;
                            }
                        ?>
                        <input type="text" size="16" readonly class="form-control" name="dt_added_to_fleet" value="{{ $value }}">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div>

            {!! BootForm::text('Type*:', 'vehicle_type_id')->placeholder('Select') !!}

            <div class="last-odometer">
                <div class="form-group @if($errors->first('last_odometer_reading')) has-error @endif">
                    {!! Form::label('last_odometer_reading', 'Odometer:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        <div class="input-group">
                            {!! Form::text('last_odometer_reading', $vehicle->last_odometer_reading, ['class' => 'form-control','id' => 'last_odometer_reading']) !!}
                            <span class="input-group-addon" id="odometer_reading_unit_display">Miles</span>
                        </div>
                        <small class="text-danger">{{ $errors->first('last_odometer_reading') }}</small>
                    </div>
                </div>
            </div>

            @if (strtolower($vehicle->status) === 'archived')
                @can('archived.vehicle')
                    {!! BootForm::select('Vehicle status*:', 'status')->options($vehicleStatusList)->addClass('select2me vehicle-status-edit')->placeholder('Select') !!}
                @else
                    {!! BootForm::hidden('status', $vehicle->status) !!}
                    {!! BootForm::select('Vehicle status*:', 'status')->options(['Archived'])->addClass('select2me vehicle-status-edit')->disabled()->placeholder('Select') !!}
                @endcan
            @else
                {!! BootForm::select('Vehicle status*:', 'status')->options($vehicleStatusList)->addClass('select2me vehicle-status-edit')->placeholder('Select') !!}
            @endif

            <div class="form-group{{ $errors->has('vor_date') ? ' has-error' : '' }}" id="vor_date" style="{{ !starts_with($vehicle->status,'VOR') ? 'display: none' : '' }}">
                @if($from == 'edit')
                    <label class="control-label col-md-3" >VOR date*:</label>
                    <div class="col-md-9">
                        <div class="input-group date vor-date-datepicker">
                            @if($isSuperAdmin)
                            <input type="text" size="16" readonly class="form-control" name="vor_date"
                            value="{{$vehicleDateOffRoad}}" id="vor_date">
                            @else
                            <input type="text" size="16" readonly {{$vehicleDateOffRoad ? 'disabled' : '' }} class="form-control" name="vor_date" id="vor_date" value="{{$vehicleDateOffRoad}}">
                            @endif
                            <span class="input-group-btn">
                            <button class="btn default date-set grey-gallery btn-h-45" {{!$isSuperAdmin &&
                            $vehicleDateOffRoad ? 'disabled' : '' }} type="button"><i class="jv-icon jv-calendar"></i></button>
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            {!! BootForm::select('Usage override*:', 'usage_type')->options($usageTypeList)->addClass('select2') !!}

            <span id="administration"></span>

            {!! BootForm::select('Ownership status*:', 'staus_owned_leased')->options($ownershipStatusList)->addClass('select2me vehicle-ownership-edit')->placeholder('Select') !!}

            @if($from == 'edit')
            <input type="hidden" class="prevVehicleUsageType" value="{{ !empty($vehicle->usage_type)?$vehicle->usage_type:$vehicle->type->usage_type }}">
            <input type="hidden" class="vehicleUsageType" value="{{ $vehicle->usage_type }}">
            <input type="hidden" class="globalVehicleUsageType" value="{{ $vehicle->type->usage_type }}">
            @else
            <input type="hidden" class="prevVehicleUsageType" value="">
            @endif
        </div>
    </div>

    <div class="portlet box">
        <div class="portlet-title bg-red-rubine">
            <div class="caption">
                {{ $fromPage == 'add' ? 'Vehicle ' : ''}}Administration
            </div>
        </div>
        <div class="portlet-body">
            {!! BootForm::text('Nominated driver:', 'nominated_driver')->placeholder('Select')->addClass('form-control') !!}
            {{-- @if($from == 'edit')  --}}
            <input type="hidden" class="js_hmrc_tax_year" value="{{ $hmrcTaxYear }}">
            <div class="form-group">
                <label class="control-label col-md-3">Private use:</label>
                <div class="col-md-9">
                    <label class="control-label margin-0">
                        <input type="checkbox" id="private_use" name="private_use" @if($privateUseLogs != null) checked="true" @endif>
                    </label>
                    <input type="hidden" name="start_date" id="start_date">
                    <input type="hidden" name="end_date" id="end_date">
                    <input type="hidden" name="privateuse_entry_flag" id="privateuse_entry_flag" value="0">
                </div>
            </div>
            {{-- @endif --}}
            <div class="form-group{{ $errors->has('dt_registration') ? ' has-error' : '' }}" id="dt_registration">
                <label class="control-label col-md-3">Registration date:</label>
                <div class="col-md-9">
                    <div class="input-group date maintenance_history_registration_form_date">
                        <input type="text" size="16" readonly class="form-control registration-value" name="dt_registration" value="{{ $vehicle->dt_registration ? $vehicle->dt_registration : old('dt_registration')}}">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45 registration-date" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group{{ $errors->has('dt_first_use_inspection') ? ' has-error' : '' }}" id="dt_first_use_inspection">
                <label class="control-label col-md-3">First use inspection date:</label>
                <div class="col-md-9">
                    <div class="input-group date form_date">
                        <input type="text" size="16" readonly class="form-control" name="dt_first_use_inspection" value="{{ $vehicle->dt_first_use_inspection ? $vehicle->dt_first_use_inspection : old('dt_first_use_inspection')}}">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group{{ $errors->has('lease_expiry_date') ? ' has-error' : '' }}" id="lease_expiry_date">
                <label class="control-label col-md-3">Vehicle lease expiry date:</label>
                <div class="col-md-9">
                    <div class="input-group date form_date">
                        <input type="text" size="16" readonly class="form-control" name="lease_expiry_date" value="{{ $vehicle->lease_expiry_date ? $vehicle->lease_expiry_date : old('lease_expiry_date')}}">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div>

            <div class="list_price">
                <div class="form-group">
                    {!! Form::label('P11D_list_price', 'P11D list price or benefit charge:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::text('P11D_list_price', $vehicle->P11D_list_price, ['class' => 'form-control']) !!}
                        <span class="P11D_list_price" id="errmsg"></span>
                    </div>
                </div>
            </div>

            <div class="operator_license" style="display:none">
            {!! BootForm::text('Operator license:', 'operator_license')->addClass('operator_license') !!}
            </div>


            {!! BootForm::text('Chassis number:', 'chassis_number') !!}
            {!! BootForm::text('Contract ID:', 'contract_id') !!}

            <span id="assignment"></span>

            {!! BootForm::text('Notes:', 'notes') !!}
            <div id="vehicle-assignment"></div>
            {!! BootForm::hidden('form_status')->value($from)->id('form_status') !!}


        </div>
    </div>

    <div class="portlet box">
        <div class="portlet-title bg-red-rubine">
            <div class="caption">
                {{ $fromPage == 'add' ? 'Vehicle ' : ''}}Assignment
            </div>
        </div>
        <div class="portlet-body">
            <div class="form-group">
                <label class="col-md-3 control-label" for="vehicle_division_id">Vehicle division*:</label><div class="col-md-9">
                    <select class="form-control select2me vehicle-division-value" id="vehicle_division_id" name="vehicle_division_id" placeholder="Select">
                        @foreach ($vehicleDivisions as $key => $division)
                            <option {{ $vehicle->vehicle_division_id == $key ? 'selected': '' }} value="{{ $key }}">{{ $division}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="vehicle-region-value">
                <div class="form-group">
                    <label class="col-md-3 control-label" for="vehicle_region_id">Vehicle region*:</label>
                    <div class="col-md-9">
                        <select class="form-control select2me select2me vehicle-region" id="vehicle_region_id" name="vehicle_region_id" placeholder="Select">
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
                @if($from == 'edit')
                    <input type="hidden" id="selectd_region" value="{{ $vehicle->vehicle_region }}">
                    <input type="hidden" id="selectd_base_location" value="{{ $vehicle->vehicle_location_id }}">
                @endif
            </div>
            <div class="vehicle-location">{{--
            {!! BootForm::text('Vehicle location:', 'vehicle_location_id')->placeholder('Select') !!} --}}

            <span id="telematics"></span>

            {!! BootForm::select('Vehicle location:', 'vehicle_location_id')->options($vehicleLocationsList)->addClass('select2me')->placeholder('Select') !!}
            </div>
        </div>
    </div>

    <div class="portlet box">
        <div class="portlet-title bg-red-rubine">
            <div class="caption">
                Telematics
            </div>
        </div>
        <div class="portlet-body">
            @if($isConfigurationTabEnabled == 1)
                <!-- {!! BootForm::select('Telematics*:', 'is_telematics_enabled')->options([""=>"", 0 => 'No', 1=> 'Yes'])->addClass('select2me') !!} -->
                <div class="form-group" id="is_telematics_enabled_conf">
                    <label class="col-md-3 control-label" for="is_telematics_enabled">Telematics*:
                    </label>
                    <div class="col-md-9">
                        <select name="is_telematics_enabled" id="is_telematics_enabled" class="form-control select2me">
                            <option value="" selected></option>
                            <option value="0" {{ 0 == $vehicle->is_telematics_enabled ? 'selected' : '' }}>No</option>
                            <option value="1" {{ 1 == $vehicle->is_telematics_enabled ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                </div>
                <input type="hidden" name="telematics_provider" value="{{env('TELEMATICS_PROVIDER')}}">
                @if(env('TELEMATICS_PROVIDER') == 'webfleet')
                    <div class="form-group{{ $errors->has('webfleet_registration') ? ' has-error' : '' }}" id="webfleet_registration">
                            <label class="control-label col-md-3" >Webfleet registration*:</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="webfleet_registration" value="{{$vehicle->webfleet_registration}}">
                            </div>
                    </div>
                @endif
                <div class="form-group" id="supplier">
                    <label class="col-md-3 control-label" for="supplier">Supplier*:</label>
                    <div class="col-md-9">
                        <select class="form-control select2me select2" id="supplier_id" name="supplier" placeholder="Select">
                            @foreach ($supplierTelematics as $key => $supplier)
                                <option {{ $vehicle->supplier == $key ? 'selected': '' }} value="{{ $key }}">{{ $supplier}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group" id="device">
                    <label class="col-md-3 control-label" for="device">Device*:</label>
                    <div class="col-md-9">
                        <select class="form-control select2me select2" id="device_id" name="device" placeholder="Select">
                            @foreach ($deviceTelematics as $key => $device)
                                <option {{ $vehicle->device == $key ? 'selected': '' }} value="{{ $key }}">{{ $device}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group" id="serial_id">
                    <label class="col-md-3 control-label" for="serial_id">Serial ID*:</label>
                    <div class="col-md-9">
                        <input type="text" value="{{ $vehicle->serial_id ? $vehicle->serial_id : old('serial_id') }}" name="serial_id" class="form-control">
                    </div>
                </div>

                <div class="form-group{{ $errors->has('installation_date') ? ' has-error' : '' }}" id="installation_date">
                    <label class="control-label col-md-3">Installation date*:</label>
                    <div class="col-md-9">
                        <div class="input-group date form_date">
                            <input type="text" size="16" readonly class="form-control" name="installation_date" value="{{ $installationDate ? $installationDate : old('installation_date') }}">
                            <span class="input-group-btn">
                            <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-group" id="last_date_update">
                    <label class="col-md-3 control-label" for="last_date_update">Last data update:</label>
                    <div class="col-md-9">
                        <input type="text" readonly class="form-control" value="{{ $lastDateUpdateDevice }}">
                    </div>
                </div>
            @else
                    <div class="form-group" id="is_telematics_enabled_tab">
                        <label class="col-md-3 control-label" for="is_telematics_enabled">Telematics*:
                        </label>
                        <div class="col-md-9">
                            <select name="is_telematics_enabled" id="is_telematics_enabled" class="form-control select2me">
                                <option value="" selected></option>
                                <option value="0" {{ 0 == $vehicle->is_telematics_enabled ? 'selected' : '' }}>No</option>
                                <option value="1" {{ 1 == $vehicle->is_telematics_enabled ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>
                    </div>
                    <!-- {!! BootForm::select('Telematics*:', 'is_telematics_enabled')->options([""=>"", 0 => 'No', 1=> 'Yes'])->addClass('select2me') !!} -->
                    <input type="hidden" name="telematics_provider" value="{{env('TELEMATICS_PROVIDER')}}">
                    @if(env('TELEMATICS_PROVIDER') == 'webfleet')
                        
                        <div class="form-group{{ $errors->has('webfleet_registration') ? ' has-error' : '' }}" id="webfleet_registration">
                                <label class="control-label col-md-3" >Webfleet registration*:</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="webfleet_registration" value="{{$vehicle->webfleet_registration}}">
                                </div>
                        </div>
                    @endif
                
                @if($from == 'edit')
                <div class="form-group" id="supplier_edit">
                    <label class="col-md-3 control-label" for="supplier">Supplier:</label>
                    <div class="col-md-9">
                        <div class="form-control text-capitalize has-label">{{ ($vehicle->supplier) ? $vehicle->supplier:'N/A' }}</div>
                    </div>
                </div>
                <div class="form-group" id="device_edit">
                    <label class="col-md-3 control-label" for="device">Device:</label>
                    <div class="col-md-9">
                        <div class="form-control text-capitalize has-label">{{ ($vehicle->device) ? $vehicle->device:'N/A' }}</div>
                    </div>
                </div>
                <div class="form-group" id="serial_id_edit">
                    <label class="col-md-3 control-label" for="serial_id">Serial ID:</label>
                    <div class="col-md-9">
                        <div class="form-control text-capitalize has-label">{{ ($vehicle->serial_id) ? $vehicle->serial_id:'N/A' }}</div>
                    </div>
                </div>

                <div class="form-group" id="installation_date_edit">
                    <label class="control-label col-md-3">Installation date:</label>
                    <div class="col-md-9">
                        <div class="form-control text-capitalize has-label">{{ ($installationDate) ? $installationDate:'N/A' }}</div>
                    </div>
                </div>

                <div class="form-group" id="last_date_update_edit">
                    <label class="col-md-3 control-label" for="last_date_update">Last data update:</label>
                    <div class="col-md-9">
                        <div class="form-control text-capitalize has-label">{{ $lastDateUpdateDevice }}</div>
                    </div>
                </div>
                @endif
            @endif
            


        </div>
    </div>

    <div class="portlet box">
        <div class="portlet-title bg-red-rubine">
            <div class="caption">
                {{ $fromPage == 'add' ? 'Vehicle ' : ''}}Planning
            </div>
        </div>
        <div class="portlet-body">
            <div class="form-group{{ $errors->has('dt_repair_expiry') ? ' has-error' : '' }}">
                <label class="control-label col-md-3" for="vehicle_repair_location_id">Repair/Maintenance location:</label>
                <div class="col-md-9">
                    <div class="d-flex justify-content-between">
                        <div class="flex-grow-1 margin-right-10">
                            <select class="form-control select2me" name="vehicle_repair_location_id" id="vehicle_repair_location_id" placeholder="Select">
                                @if($from == 'edit')
                                    @foreach ($vehicleRepairLocationsList as $key => $vehicleRepairLocations)
                                        @if(in_array($key, $vehicleRepairLocationsList) && $key != $vehicle->vehicle_repair_location_id)
                                           <option disabled="disabled" value="{{ $key }}">{{ $vehicleRepairLocations }}</option>
                                        @else
                                            <option value="{{ $key }}" {{ $key == $vehicle->vehicle_repair_location_id ? 'selected' : '' }}>{{ $vehicleRepairLocations }}</option>
                                        @endif
                                    @endforeach
                                @else
                                    @foreach ($vehicleRepairLocationsList as $key => $vehicleRepairLocations)
                                        <option value="{{ $key }}" {{ (old("vehicle_repair_location_id") == $key ? "selected":"") }}>{{ $vehicleRepairLocations }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="desboard_thumbnail">
                            <a href="#add_vehicle_repair_location" id="add_vehicle_location_view" data-toggle="modal" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center" style="margin-right: 0">
                                <i class="jv-icon jv-plus"></i>
                            </a>
                        </div>
                        <div class="desboard_thumbnail">
                            <a href="#view-repair-maintenance" id="view_repair_maintenance" data-path="vehicles" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center" style="margin-right: 0">
                                <i class="jv-icon jv-edit"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group{{ $errors->has('adr_test_date') ? ' has-error' : '' }}" id="adr_test_date">
                <label class="control-label col-md-3">ADR test date:</label>
                <div class="col-md-9">
                    <div class="input-group date form_date">
                        <input type="text" size="16" readonly class="form-control" name="adr_test_date" value="{{ $vehicle->adr_test_date ? $vehicle->adr_test_date : old('adr_test_date') }}">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group{{ $errors->has('dt_annual_service_inspection') ? ' has-error' : '' }}" id="dt_annual_service_inspection">
                <label class="control-label col-md-3">Annual service date:</label>
                <div class="col-md-9">
                    <div class="input-group date form_date">
                        <input type="text" size="16" readonly class="form-control" name="dt_annual_service_inspection" value="{{ $vehicle->dt_annual_service_inspection ? $vehicle->dt_annual_service_inspection : old('dt_annual_service_inspection') }}">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group {{ $errors->has('next_compressor_service') ? ' has-error' : '' }}">
                <label class="control-label col-md-3">Compressor service date:</label>
                <div class="col-md-9">
                    <div class="input-group date form_date" id="next_compressor_service">
                        <input type="text" size="16" readonly class="form-control" name="next_compressor_service" value="{{ $vehicle->next_compressor_service ? $vehicle->next_compressor_service : old('next_compressor_service') }}">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div>
             <div class="form-group{{ $errors->has('next_invertor_service_date') ? ' has-error' : '' }}">
                <label class="control-label col-md-3">Invertor service date:</label>
                <div class="col-md-9">
                    <div class="input-group date form_date">
                        <input type="text" size="16" readonly class="form-control" name="next_invertor_service_date" value="{{ $vehicle->next_invertor_service_date ? $vehicle->next_invertor_service_date : old('next_invertor_service_date') }}">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group{{ $errors->has('dt_loler_test_due') ? ' has-error' : '' }}" id="dt_loler_test_due">
                <label class="control-label col-md-3">LOLER test date:</label>
                <div class="col-md-9">
                    <div class="input-group date form_date">
                        <input type="text" size="16" readonly class="form-control" name="dt_loler_test_due" value="{{ $vehicle->dt_loler_test_due ? $vehicle->dt_loler_test_due : old('dt_loler_test_due') }}">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group{{ $errors->has('dt_repair_expiry') ? ' has-error' : '' }}" id="dt_repair_expiry">
                <label class="control-label col-md-3">Maintenance expiry date:</label>
                <div class="col-md-9">
                    <div class="input-group date form_date">
                        <input type="text" size="16" readonly class="form-control" name="dt_repair_expiry" value="{{ $vehicle->dt_repair_expiry ? $vehicle->dt_repair_expiry : old('dt_repair_expiry') }}">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group{{ $errors->has('dt_mot_expiry') ? ' has-error' : '' }}" id="dt_mot_expiry">
                <label class="control-label col-md-3">MOT expiry date:</label>
                <div class="col-md-9">
                    <div class="input-group date form_date">
                        <input type="text" size="16" readonly class="form-control mot-expiry-date" name="dt_mot_expiry" value="{{ $vehicle->dt_mot_expiry ? $vehicle->dt_mot_expiry : old('dt_mot_expiry') }}">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div>

            @if (($from == 'edit' && $vehicle->type->service_interval_type == 'Time') || ($from == 'edit' && $vehicle->type->service_interval_type == ''))
            <div class="form-group{{ $errors->has('dt_next_service_inspection') ? ' has-error' : '' }} next_service_inspection" id="dt_next_service_inspection">
                <label class="control-label col-md-3">Next service date:</label>
                <div class="col-md-9">
                    <div class="input-group date form_date">
                        <input type="text" size="16" readonly class="form-control" name="dt_next_service_inspection" value="{{ $vehicle->dt_next_service_inspection ? $vehicle->dt_next_service_inspection : old('dt_next_service_inspection')}}">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div>
            @else
            <div class="form-group{{ $errors->has('dt_next_service_inspection') ? ' has-error' : '' }} next_service_inspection">
                <label class="control-label col-md-3">Next service date:</label>
                <div class="col-md-9">
                    <div class="input-group date form_date">
                        <input type="text" size="16" readonly class="form-control" name="dt_next_service_inspection">
                        <span class="input-group-btn">
                    <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                    </span>
                    </div>
                </div>
            </div>
            @endif

            @if($from == 'edit' && $vehicle->type->service_interval_type == 'Distance')
            <div class="form-group{{ $errors->has('next_service_inspection_distance') ? ' has-error' : '' }} next_service_inspection_distance" id="dt_next_service_inspection">
                <label class="control-label col-md-3">Next service distance:</label>
                <div class="col-md-9">
                    <input type="text" size="16" readonly class="form-control" @if($calculateNextServiceOdometer) id="next_service_inspection_distance" @endif name="next_service_inspection_distance" value="{{ $vehicle->next_service_inspection_distance ? number_format($vehicle->next_service_inspection_distance,0) : ''}}">
                </div>
            </div>
            @else
                <div class="form-group{{ $errors->has('next_service_inspection_distance') ? ' has-error' : '' }} next_service_inspection_distance">
                    <label class="control-label col-md-3">Next service distance:</label>
                    <div class="col-md-9">
                        <input type="text" size="16" readonly class="form-control" id="next_service_inspection_distance" name="next_service_inspection_distance" value="">
                    </div>
                </div>
            @endif

            <div class="vehicle_pmi form-group{{ $errors->has('next_pmi_date') ? ' has-error' : '' }}">
                <div class="col-md-12">
                    <div class="row gutters-tiny">
                        <div class="col-md-6">
                            <div class="row gutters-tiny">
                                <label class="control-label col-md-6">First PMI date:</label>
                                @if($from == 'edit' && $vehicle->first_pmi_date)
                                    {{-- <div class="col-md-6 input-group first-pmi-date-change js-first-pmi-date date" id="firstPmiDate">
                                        <input type="text" size="16" id="js_first_pmi_interval" readonly class="form-control first-pmi-date" name="first_pmi_date" value="{{ $vehicle->first_pmi_date }}">
                                        <span class="input-group-btn">
                                        <button class="btn default date-set grey-gallery btn-h-45 edit-first-pmi-date" type="button"><i class="jv-icon jv-lock"></i></button>
                                        </span>
                                    </div> --}}
                                    <div class="col-md-6 input-group" id="firstPmiDate">
                                        <input type="text" size="16" id="js_first_pmi_interval" readonly class="form-control first-pmi-date" name="first_pmi_date" value="{{ $vehicle->first_pmi_date }}">
                                    </div>
                                @else
                                    <div class="col-md-6 input-group first-pmi-date-change js-first-pmi-date date form_date">
                                        <input type="text" size="16" id="js_first_pmi_interval" readonly class="form-control first-pmi-date" name="first_pmi_date" value="{{ $vehicle->first_pmi_date }}">
                                        <span class="input-group-btn">
                                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                        </span>
                                    </div>
                                @endif
                                <!-- <div class="col-md-3 js-first-pmi-date-not-applicable">
                                    <input type="text" size="16" disabled class="form-control" value="NA">
                                </div> -->
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row gutters-tiny">
                                <label class="col-md-6 control-label">Next PMI date:</label>
                                <div class="col-md-6 js-next-pmi-date">
                                    <input type="text" id="nextPmiDateCalculation" size="16" readonly class="form-control next_pmi_date" name="next_pmi_date" value="{{ $vehicle->next_pmi_date }}">
                                </div>
                                <!-- <div class="col-md-3 js-next-pmi-date-not-applicable">
                                    <input type="text" size="16" disabled class="form-control" value="NA">
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group{{ $errors->has('next_pto_service_date') ? ' has-error' : '' }}">
                <label class="control-label col-md-3">PTO service date:</label>
                <div class="col-md-9">
                    <div class="input-group date form_date">
                        <input type="text" size="16" readonly class="form-control" name="next_pto_service_date" value="{{ $vehicle->next_pto_service_date ? $vehicle->next_pto_service_date : old ('next_pto_service_date') }}">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3">Tacho calibration date:</label>
                <div class="col-md-9">
                    <div class="input-group date form_date" id="dt_tacograch_calibration_due">
                        <input type="text" size="16" readonly class="form-control" name="dt_tacograch_calibration_due" value="{{ $vehicle->dt_tacograch_calibration_due ? $vehicle->dt_tacograch_calibration_due : old('dt_tacograch_calibration_due')}}">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                    <div id="dt_tacograch_calibration_due_not_applicable">
                        <input type="text" size="16" disabled class="form-control" value="NA">
                    </div>
                </div>
            </div>

            <div class="form-group{{ $errors->has('tank_test_date') ? ' has-error' : '' }}" id="tank_test_date">
                <label class="control-label col-md-3">Tank test date:</label>
                <div class="col-md-9">
                    <div class="input-group date form_date">
                        <input type="text" size="16" readonly class="form-control" name="tank_test_date" value="{{ $vehicle->tank_test_date ? $vehicle->tank_test_date : old('tank_test_date') }}">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div>

            <span id="costs"></span>
            <div class="form-group{{ $errors->has('dt_tax_expiry') ? ' has-error' : '' }}" id="dt_tax_expiry">
                <label class="control-label col-md-3">Tax expiry date:</label>
                <div class="col-md-9">
                    <div class="input-group date form_date">
                        <input type="text" size="16" readonly class="form-control" name="dt_tax_expiry" value="{{ $vehicle->dt_tax_expiry ? $vehicle->dt_tax_expiry : old('dt_tax_expiry') }}">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div>
            {!! BootForm::hidden('on_road')->value(0) !!}
        </div>
    </div>

    <?php
        $display=(setting('is_fleetcost_enabled') ? 'block' : 'none')
    ?>
    <div class="portlet box" style='display: {{ $display }};'>
        <div class="portlet-title bg-red-rubine">
            <div class="caption">
                {{ $fromPage == 'add' ? 'Vehicle ' : ''}}Costs
            </div>
        </div>
        <div class="portlet-body vehicle-cost-wrapper">
            {{--
            <div id="owned_vehicel_hide">
                @include('_partials.vehicles.owned_vehicle')
            </div>
            <div id="leased_vehicle_hide">
                @include('_partials.vehicles.leased_vehicle')
            </div>
            --}}
            <div id="leased_vehicle_hide">
                @include('_partials.vehicles.leased_vehicle')
            </div>
        </div>

        {{-- Owned & leased hidden id --}}
        <input type="hidden" id="vehicle_fleet_cost_adjustments" name="vehicle_fleet_cost_adjustments"  value="{{ $vehicle['manual_cost_adjustment'] }}">
        <input type="hidden" id="vehicle_fuel_use_value" name="vehicle_fuel_use_value" value="{{$vehicle['fuel_use']}}">
        <input type="hidden" id="vehicle_oil_cost_adjustments" name="vehicle_oil_cost_adjustments"  value="{{$vehicle['oil_use']}}">
        <input type="hidden" id="vehicle_ad_blue_adjustments" name="vehicle_ad_blue_adjustments"  value="{{$vehicle['adblue_use']}}">
        <input type="hidden" id="vehicle_screen_wash" name="vehicle_screen_wash"  value="{{$vehicle['screen_wash_use']}}">
        <input type="hidden" id="vehicle_fleet_livery" name="vehicle_fleet_livery"  value="{{$vehicle['fleet_livery_wash']}}">
    </div>
</div>
<span id="documents"></span>
<div class="form-actions row">
    <div class="col-md-12 btn-group">
        <a href="{{ url('/vehicles/') }}" type="button" class="btn white-btn btn-padding col-md-6">Cancel</a>
        <button type="submit" class="btn red-rubine btn-padding col-md-6" id="saveVehicleBtn">Save</button>
    </div>
</div>

<div class="modal fade" id="vehicles_status_modal" tabindex="-1" data-keyboard="false" role="dialog" aria-labelledby="defect-info-modal-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Important Message</h4>
            </div>
            <div class="modal-body">
                <p style="font-size: 15px;">Before you can change the status of this vehicle you will need to un-archive the vehicle profile type being used by this vehicle. To edit the vehicle profile, click <a href="{{ url('profiles/' . $vehicleType->id . '/edit') }}"><u>here</u></a>.</p>
            </div>

            <div class="modal-footer">
                <div class="btn-group pull-left width100">
                    <button type="button" class="btn white-btn btn-padding col-md-12" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@if($from == 'edit')
 <div class="modal fade" id="vehicle-status-modal" tabindex="-1" data-keyboard="false" role="dialog" aria-labelledby="">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine">
                <button type="button" class="close profile" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Confirm Vehicle Status</h4>
            </div>
            <form>
                <div class="modal-body">
                    <div class="form-group mb15">
                        <label for="comment" class="control-label">The list of defects below are unresolved for this vehicle. Would you like to continue to change the vehicle status?</label>
                    </div>
                    <table class="table no-wrap-header">
                        <thead>
                          <tr>
                            <th>Registration</th>
                            <th>Defect ID</th>
                            <th>Category</th>
                            <th>Defect</th>
                            <th>Defect Status</th>
                            <!-- <th>VOR Defect?</th> -->
                          </tr>
                        </thead>
                        <tbody>
                        @foreach($vehicleStatusRecords as $vehicleStatus)
                            <tr>
                                <td>{{$vehicleStatus->vehicle->registration}}</td>
                                <td>{{$vehicleStatus->id}}</td>
                                <td>{{$vehicleStatus->defectMaster->page_title}}</td>
                                <td>{{$vehicleStatus->defectMaster->defect}}</td>
                                @if($vehicleStatus->status == 'Reported')
                                <td class="label-danger label-results">{{$vehicleStatus->status}}</td>
                                @elseif($vehicleStatus->status == 'Acknowledged' || $vehicleStatus->status == 'Under repair' || $vehicleStatus->status == 'Discharged' || $vehicleStatus->status == 'Allocated')
                                <td class="label-warning label-results">{{$vehicleStatus->status}}</td>
                                @elseif($vehicleStatus->status == 'Resolved')
                                <td class="label-success label-results">{{$vehicleStatus->status}}</td>
                                @elseif($vehicleStatus->status == 'Repair rejected')
                                <td class="label-danger label-results">{{$vehicleStatus->status}}</td>
                                @endif
                                <!-- <td>{{$vehicleStatus->defectMaster->is_prohibitional == 1 ? 'Yes' : 'No'}}</td> -->
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <div class="btn-group pull-left width100">
                        <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal" id="vehicleStatusClose">Cancel</button>
                        <button type="button" class="btn red-rubine btn-padding col-md-6" data-dismiss="modal" id="vehicleStatusChange">Confirm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- maintenance cost view modal --}}
<div id="maintenance_cost_history" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
            <h4 class="modal-title">Maintenance Cost History</h4>
            <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
            </a>
           </div>
            <div class="modal-body">
                @include('_partials.vehicles.maintenance_cost_history')
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

{{-- lease cost view modal --}}
<div id="lease_cost_history" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
            <h4 class="modal-title">Lease Cost History</h4>
            <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
            </a>
           </div>
            <div class="modal-body">
                @include('_partials.vehicles.lease_cost_history')
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

{{-- manual cost adjustment delete modal --}}
<div id="vehicle_manual_cost_delete_pop_up" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Confirmation</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                Are you sure you would like to delete this entry?
            </div>
            <div class="modal-footer">
                <div class="btn-group pull-left width100">
                    <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                    <button id="vehicle_manual_cost_adjustment_delete" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Yes</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- fuel use delete modal --}}
<div id="vehicle_fuel_use_delete_pop_up" class="modal fade default-modal vehicle_fuel_use_delete_pop_up" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Confirmation</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                Are you sure you would like to delete this entry?
            </div>
            <div class="modal-footer">
                <div class="btn-group pull-left width100">
                    <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                    <button id="vehicle_fuel_use_delete" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Yes</button>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- oil used delete modal --}}
<div id="vehicle_oil_use_delete_pop_up" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Confirmation</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                Are you sure you would like to delete this entry?
            </div>
            <div class="modal-footer">
                <div class="btn-group pull-left width100">
                    <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                    <button id="vehicle_oil_use_adjustment_delete" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Yes</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- AdBlue delete modal --}}
<div id="vehicle_adblue_delete_pop_up" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Confirmation</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                Are you sure you would like to delete this entry?
            </div>
            <div class="modal-footer">
                <div class="btn-group pull-left width100">
                    <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                    <button id="vehicle_adblue_delete_save_button" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Yes</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Screen Wash delete modal --}}
<div id="vehicle_screen_wash_delete_pop_up" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Confirmation</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                Are you sure you would like to delete this entry?
            </div>
            <div class="modal-footer">
                <div class="btn-group pull-left width100">
                    <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                    <button id="vehicle_screen_wash_delete_save_button" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Yes</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Fleet livery delete modal --}}
<div id="vehicle_fleet_livery_delete_pop_up" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Confirmation</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                Are you sure you would like to delete this entry?
            </div>
            <div class="modal-footer">
                <div class="btn-group pull-left width100">
                    <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                    <button id="vehicle_fleet_livery_delete_save_button" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Yes</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="view-repair-maintenance" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <form class="" role="form" id="vehicleRepairLocation">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                    <h4 class="modal-title">All Repair/Maintenance Locations</h4>
                    <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                        <i class="jv-icon jv-close"></i>
                    </a>
                </div>
                <div class="table-wrapper-scroll-y my-custom-scrollbar">
                  <table class="table table-hover table-striped table-repair-maintenance">
                    <thead class="thead-dark">
                      <tr>
                        <th scope="col" width="70%">Repair/Maintenance Name</th>
                        <th scope="col" class="text-center">Action</th>
                      </tr>
                    </thead>
                    <tbody id="view_all_repair_maintenance">
                    </tbody>
                  </table>
                </div>
              </div>
        </form>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->