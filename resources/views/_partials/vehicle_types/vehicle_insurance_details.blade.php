@if ($fromPage == "edit")
    <input type="hidden" class="vehicle_type_id" name="vehicle_type_id" value="{{$vehicleType->id}}"/>
@endif
@if(isset($vehicleType->annual_insurance_cost))
    <?php $vehicle_insurance_costs = json_decode($vehicleType->annual_insurance_cost, true); ?>
    @foreach ($vehicle_insurance_costs as $vehicleInsuranceCost)
        <div class="row gutters-tiny js-vehicle-insurance-cost-fields-wrapper d-flex align-items-center margin-bottom-25" data-repeater-item>
            <div class="col-md-3">
                <label>Monthly cost*:</label>
                <div class="error-class">
                    <div class="input-group">
                        <span class="input-group-addon">&pound;</span>
                        <input type="text" name="edit_vehicle_insurance_cost" class="form-control vehicle_insurance_cost vehicleInsuranceCurrentCost" value="{{ number_format($vehicleInsuranceCost['cost_value'], 2) }}">
                    </div>
                    <span class="help-block help-block-error edit_vehicle_insurance_cost_error" style="display:none;">This field is required</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row gutters-tiny position-relative">
                    <div class="col-md-6">
                        <label>From*:</label>
                        <div class="error-class">
                            <div class="input-group date form_date insuranceCostFromDate">
                                <input type="text" class="form-control vehicle_insurance_cost_from_date datepicker-pointer-events-none" readonly name="edit_vehicle_insurance_cost_from_date" value="{{ $vehicleInsuranceCost['cost_from_date'] }}">
                                <span class="input-group-btn">
                                    <button class="btn default date-set grey-gallery btn-h-45" type="button">   <i class="jv-icon jv-calendar"></i></button>
                                </span>
                            </div>
                            <span class="help-block help-block-error edit_vehicle_insurance_cost_from_date_error" style="display:none;">This field is required</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="vehicle_insurance_cost_end_date">
                            <label>To*:</label>
                            <div class="error-class">
                                <div class="input-group date form_date insuranceCostToDate">
                                    <input type="text" class="form-control vehicle_insurance_cost_to_date datepicker-pointer-events-none" readonly name="edit_vehicle_insurance_cost_to_date" value="{{ $vehicleInsuranceCost['cost_to_date'] }}">
                                    <span class="input-group-btn">
                                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                    </span>
                                </div>
                                <span class="help-block help-block-error edit_vehicle_insurance_cost_to_date_error" style="display:none;">This field is required</span>
                            </div>
                        </div>
                    </div>


                </div>
            </div>

            <div class="col-md-2">
                <div id="insurance_cost_continuous_block" style="display: none;">
                    <label class="invisible">Cost</label>
                    <div class="d-flex align-items-center">
                        <div>
                            <input type="checkbox" name="edit_vehicle_insurance_cost_continuous" class="form-check-input edit_vehicle_insurance_cost_continuous"  value="{{isset($vehicleInsuranceCost['cost_continuous']) && $vehicleInsuranceCost['cost_continuous'] == "true" ? 1 : 0 }}" {{isset($vehicleInsuranceCost['cost_continuous']) && $vehicleInsuranceCost['cost_continuous'] == "true" ? 'checked' : '' }} >Cost is continuous
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-1">
                <label class="invisible">Delete</label>
                <div class="d-flex align-items-center justify-content-center" style="height: 45px;">
                    <a title="Details" data-repeater-delete class="btn btn-xs grey-gallery tras_btn js-vehicle-insurance-cost-delete"><i class="jv-icon jv-dustbin icon-big"></i></a>
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class="row gutters-tiny d-flex align-items-center js-vehicle-insurance-cost-fields-wrapper margin-bottom-25" data-repeater-item>
        <div class="col-md-3">
            <label>Monthly cost*:</label>
            <div class="error-class">
                <div class="input-group">
                    <span class="input-group-addon">&pound;</span>
                    <input type="text" name="edit_vehicle_insurance_cost" class="form-control vehicle_insurance_cost" value="">
                </div>
                <span class="help-block help-block-error edit_vehicle_insurance_cost_error" style="display:none;">This field is required</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row gutters-tiny position-relative">
                <div class="col-md-6">
                    <label>From*:</label>
                    <div class="error-class">
                        <div class="input-group date form_date insuranceCostFromDate">
                            <input type="text" class="form-control vehicle_insurance_cost_from_date datepicker-pointer-events-none" readonly name="edit_vehicle_insurance_cost_from_date" value="{{ Carbon\Carbon::parse()->format('d M Y') }}">
                            <span class="input-group-btn">
                                <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                            </span>
                        </div>
                        <span class="help-block help-block-error edit_vehicle_insurance_cost_from_date_error" style="display:none;">This field is required</span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="vehicle_insurance_cost_end_date">
                        <label>To*:</label>
                        <div class="error-class">
                            <div class="input-group date form_date insuranceCostToDate">
                                <input type="text" class="form-control vehicle_insurance_cost_to_date datepicker-pointer-events-none" readonly name="edit_vehicle_insurance_cost_to_date" value="">
                                <span class="input-group-btn">
                                    <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                </span>
                            </div>
                            <span class="help-block help-block-error edit_vehicle_insurance_cost_insurance_date_error" style="display:none;">This field is required</span>
                        </div>
                    </div>  
                </div>
                <div class="hide overlapping-date modal-date-validation" id="vehicleInsuranceCostDateValidation">
                    <span class="date">Overlapping date not allow</span>
                </div>
            </div>
        </div>
        
        <div class="col-md-2">
            <div id="insurance_cost_continuous_block" style="display: none;">
                <label class="invisible">Cost</label>
                <div class="d-flex align-items-center">
                    <div>
                        <input type="checkbox" name="edit_vehicle_insurance_cost_continuous" class="form-check-input edit_vehicle_insurance_cost_continuous"  value="1">Cost is continuous
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-1">
            <label class="invisible">Delete</label>
            <div class="d-flex align-items-center justify-content-center" style="height: 45px;">
                <a title="Details" data-repeater-delete class="btn btn-xs grey-gallery tras_btn js-vehicle-insurance-cost-delete"><i class="jv-icon jv-dustbin icon-big"></i></a>
            </div>
        </div>
    </div>
@endif