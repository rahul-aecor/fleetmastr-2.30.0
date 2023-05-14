{{ csrf_field() }}
<div class="row">
    <div class="col-md-12 js-telematics-edit-date-picker" data-repeater-list="monthlyTelematicsCostRepeater">
        @if($telematicsValueDisplay)
            @foreach ($telematicsValueDisplay as $fleetCost)
                <div class="row gutters-tiny d-flex align-items-center js-telematics-fields-wrapper margin-bottom-25" data-repeater-item>
                    <div class="col-md-3">
                        <label>Monthly cost*:</label>
                        <div class="error-class">
                            <div class="input-group">
                                <span class="input-group-addon">&pound;</span>
                                <input type="text" name="edit_annual_telematics_cost" class="form-control telematics_insurance edit_annual_telematics_cost" value="{{ number_format($fleetCost['cost_value'], 2) }}">
                            </div>
                            <span class="help-block help-block-error telematics_cost_error" style="display:none;">This field is required</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row gutters-tiny position-relative">
                            <div class="col-md-6">
                                <label>From*:</label>
                                <div class="error-class">
                                    <div class="input-group date form_date insuranceCostFromDate">
                                        <input type="text" class="form-control telematics_from_date edit_annual_telematics_from_date datepicker-pointer-events-none" readonly name="edit_annual_telematics_from_date" value="{{ $fleetCost['cost_from_date'] }}">
                                        <span class="input-group-btn">
                                            <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                        </span>
                                    </div>
                                    <span class="help-block help-block-error telematics_cost_from_date_error" style="display:none;">This field is required</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="telematics-to-date">
                                    <label>To*:</label>
                                    <div class="error-class">
                                        <div class="input-group date form_date insuranceCostToDate" >
                                            <input type="text" class="form-control telematics_to_date edit_annual_telematics_to_date datepicker-pointer-events-none" readonly name="edit_annual_telematics_to_date" value="{{ $fleetCost['cost_to_date'] }}">
                                            <span class="input-group-btn">
                                                <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                            </span>
                                        </div>
                                        <span class="help-block help-block-error telematics_cost_to_date_error" style="display:none;">This field is required</span>
                                    </div>
                                </div>  
                            </div>

                        </div>
                    </div>
                    
                    <div class="col-md-2 padding0">
                        <div id="cost_continuous_block">
                            <label class="invisible">Cost</label>
                            <div class="d-flex align-items-center">
                                <div>
                                    <input type="checkbox" name="edit_telematics_cost_continuous" class="form-check-input edit-telematics-checkbox edit_telematics_cost_continuous telematics_cost_continuous annual_telematics_cost_continuous" value="{{isset($fleetCost['cost_continuous']) && $fleetCost['cost_continuous'] == "true" ? 1 : 0 }}" {{isset($fleetCost['cost_continuous']) && $fleetCost['cost_continuous'] == "true" ? 'checked' : '' }}>Cost is continuous
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <label class="d-block">&nbsp;</label>
                        <a title="Details" data-repeater-delete class="btn btn-xs grey-gallery tras_btn js-telematics-delete"><i class="jv-icon jv-dustbin icon-big"></i></a>
                    </div>
                </div>
            @endforeach
        @else
            <div class="row gutters-tiny d-flex align-items-center js-telematics-fields-wrapper margin-bottom-25" data-repeater-item>
                <div class="col-md-3">
                    <label>Monthly cost*:</label>
                    <div class="error-class">
                        <div class="input-group">
                            <span class="input-group-addon">&pound;</span>
                            <input type="text" name="edit_annual_telematics_cost" class="form-control telematics_insurance edit_annual_telematics_cost" value="">
                        </div>
                        <span class="help-block help-block-error telematics_cost_error" style="display:none;">This field is required</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row gutters-tiny position-relative">
                        <div class="col-md-6">
                            <label>From*:</label>
                            <div class="error-class">
                                <div class="input-group date form_date insuranceCostFromDate">
                                    <input type="text" class="form-control telematics_from_date edit_annual_telematics_from_date datepicker-pointer-events-none" readonly name="edit_annual_telematics_from_date" value="{{ Carbon\Carbon::parse()->format('d M Y') }}">
                                    <span class="input-group-btn">
                                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                    </span>
                                </div>
                                <span class="help-block help-block-error telematics_cost_from_date_error" style="display:none;">This field is required</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="telematics-to-date">
                                <label>To*:</label>
                                <div class="error-class">
                                    <div class="input-group date form_date insuranceCostToDate">
                                        <input type="text" class="form-control telematics_to_date edit_annual_telematics_to_date datepicker-pointer-events-none" readonly name="edit_annual_telematics_to_date" value="">
                                        <span class="input-group-btn">
                                            <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                        </span>
                                    </div>
                                    <span class="help-block help-block-error telematics_cost_to_date_error" style="display:none;">This field is required</span>
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
                                <input type="checkbox" name="edit_telematics_cost_continuous" class="form-check-input edit-telematics-checkbox edit_telematics_cost_continuous telematics_cost_continuous annual_telematics_cost_continuous" value="1" @if($vehicle->is_telematics_cost_override == '0') @endif>Cost is continuous
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-1">
                    <label class="invisible">Delete</label>
                    <div class="d-flex align-items-center justify-content-center" style="height: 45px;">
                        <a title="Details" data-repeater-delete class="btn btn-xs grey-gallery tras_btn js-telematics-delete"><i class="jv-icon jv-dustbin icon-big"></i></a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
<div class="hide overlapping-date modal-date-validation" id="telematicsDateValidation">
    <span class="date">Dates on entries cannot be overlapping.</span>
</div>
<div class="row">
    <div class="col-md-offset-10 col-md-2 text-right">
        <button type="button" class="btn red-rubine btn-add telematics-add-button" data-repeater-create>+ Add</button>
    </div>
</div>
{{--
<div class="form-group">
    --}}
{{--<div class="col-md-3"></div>--}}{{--

    <div class="col-md-9 py-0">
        <div class="error-class">
            <label class="margin-0">
                <input type="checkbox" name="is_telematics_cost_override" class="form-check-input edit-annual-checkbox is-telematics-cost-override telematics-cost-override" @if($vehicle->is_telematics_cost_override == '1') checked="true" @endif value="">Override
            </label>
        </div>
    </div>
</div>--}}
