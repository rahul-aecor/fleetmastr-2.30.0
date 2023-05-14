@if($insuranceValueDisplay)
    @foreach ($insuranceValueDisplay as $fleetCost)
        <div class="row gutters-tiny d-flex align-items-center js-insurance-fields-wrapper margin-bottom-25" data-repeater-item>
            <div class="col-md-3">
                <label>Monthly cost*:</label>
                <div class="error-class">
                    <div class="input-group">
                        <span class="input-group-addon">&pound;</span>
                        <input type="text" name="edit_annual_insurance_cost" class="form-control annual_insurance edit_annual_insurance_cost" value="{{ number_format($fleetCost['cost_value'], 2) }}">
                    </div>
                    <span class="help-block help-block-error insurance_cost_error" style="display:none;">This field is required</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row gutters-tiny position-relative">
                    <div class="col-md-6">
                        <label>From*:</label>
                        <div class="error-class">
                            <div class="input-group date form_date insuranceCostFromDate">
                                <input type="text" class="form-control annual_insurance_from_date edit_annual_insurance_from_date datepicker-pointer-events-none" readonly name="edit_annual_insurance_from_date" value="{{ $fleetCost['cost_from_date'] }}">
                                <span class="input-group-btn">
                                    <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                </span>
                            </div>
                            <span class="help-block help-block-error insurance_cost_from_date_error" style="display:none;">This field is required</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="insurance-to-date">
                            <label>To*:</label>
                            <div class="error-class">
                                <div class="input-group date form_date insuranceCostToDate">
                                    <input type="text" class="form-control annual_insurance_to_date edit_annual_insurance_to_date datepicker-pointer-events-none" readonly name="edit_annual_insurance_to_date" value="{{ $fleetCost['cost_to_date'] }}">
                                    <span class="input-group-btn">
                                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                    </span>
                                </div>
                                <span class="help-block help-block-error insurance_cost_to_date_error" style="display:none;">This field is required</span>
                            </div>
                        </div>  
                    </div>
                </div>
            </div>
            
            <div class="col-md-2">
                <div id="cost_continuous_block">
                    <label class="invisible">Cost</label>
                    <div class="d-flex align-items-center">
                        <div>
                            <input type="checkbox" name="edit_insurance_cost_continuous" class="form-check-input edit-annual-checkbox edit_insurance_cost_continuous annual_insurance_cost_continuous" value="{{isset($fleetCost['cost_continuous']) && $fleetCost['cost_continuous'] == "true" ? 1 : 0 }}" {{isset($fleetCost['cost_continuous']) && $fleetCost['cost_continuous'] == "true" ? 'checked' : '' }}>Cost is continuous
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-1">
                <label class="d-block">&nbsp;</label>
                <a title="Details" data-repeater-delete class="btn btn-xs grey-gallery tras_btn js-insurance-delete"><i class="jv-icon jv-dustbin icon-big"></i></a>
            </div>
        </div>
    @endforeach
@else
    <div class="row gutters-tiny d-flex align-items-center js-insurance-fields-wrapper margin-bottom-25" data-repeater-item>
        <div class="col-md-3">
            <label>Monthly cost*:</label>
            <div class="error-class">
                <div class="input-group">
                    <span class="input-group-addon">&pound;</span>
                    <input type="text" name="edit_annual_insurance_cost" class="form-control annual_insurance edit_annual_insurance_cost" value="">
                </div>
                <span class="help-block help-block-error insurance_cost_error" style="display:none;">This field is required</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row gutters-tiny position-relative">
                <div class="col-md-6">
                    <label>From*:</label>
                    <div class="error-class">
                        <div class="input-group date form_date insuranceCostFromDate">
                            <input type="text" class="form-control annual_insurance_from_date edit_annual_insurance_from_date datepicker-pointer-events-none" readonly name="edit_annual_insurance_from_date" value="{{ Carbon\Carbon::parse()->format('d M Y') }}">
                            <span class="input-group-btn">
                                <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                            </span>
                        </div>
                        <span class="help-block help-block-error insurance_cost_from_date_error" style="display:none;">This field is required</span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="insurance-to-date">
                        <label>To*:</label>
                        <div class="error-class">
                            <div class="input-group date form_date insuranceCostToDate">
                                <input type="text" class="form-control annual_insurance_to_date edit_annual_insurance_to_date datepicker-pointer-events-none" readonly name="edit_annual_insurance_to_date" value="">
                                <span class="input-group-btn">
                                    <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                </span>
                            </div>
                            <span class="help-block help-block-error insurance_cost_to_date_error" style="display:none;">This field is required</span>
                        </div>
                    </div>  
                </div>

            </div>
        </div>
        
        <div class="col-md-2">
            <div id="cost_continuous_block">
                <label class="invisible">Cost</label>
                <div class="d-flex align-items-center">
                    <div>
                        <input type="checkbox" name="edit_insurance_cost_continuous" class="form-check-input edit-annual-checkbox edit_insurance_cost_continuous annual_insurance_cost_continuous" value="1">Cost is continuous
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-1">
            <label class="invisible">Delete</label>
            <div class="d-flex align-items-center justify-content-center" style="height: 45px;">
                <a title="Details" data-repeater-delete class="btn btn-xs grey-gallery tras_btn js-insurance-delete"><i class="jv-icon jv-dustbin icon-big"></i></a>
            </div>
        </div>
    </div>
@endif