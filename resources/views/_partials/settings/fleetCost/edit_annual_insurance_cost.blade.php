{{ csrf_field() }}
<div class="row">
    <div class="col-md-12 js-annual-insurance-edit-date-picker" data-repeater-list="annualInsurancerepeater">
        @if(isset($fleetCostData['annual_insurance_cost']))
            @foreach ($fleetCostData['annual_insurance_cost'] as $fleetCost)
                <div class="row gutters-tiny js-annual-insurance-fields-wrapper d-flex align-items-center margin-bottom-25" data-repeater-item>
                    <div class="col-md-3">
                        <label>Monthly cost*:</label>
                        <div class="error-class">
                            <div class="input-group">
                                <span class="input-group-addon">&pound;</span>
                                <input type="text" name="edit_annual_insurance_cost" class="form-control
                                insurance_cost" value="{{ number_format($fleetCost['cost_value'], 2) }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row gutters-tiny position-relative">
                            <div class="col-md-6">
                                <label>From*:</label>
                                <div class="error-class">
                                    <div class="input-group date form_date costFromDate">
                                        <input type="text" class="form-control datepicker-pointer-events-none" readonly name="edit_annual_insurance_from_date" value="{{ $fleetCost['cost_from_date'] }}">
                                        <span class="input-group-btn">
                                            <button class="btn default date-set grey-gallery btn-h-45" type="button">   <i class="jv-icon jv-calendar"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="annual_insurance_end_date">
                                    <label>To*:</label>
                                    <div class="error-class">
                                        <div class="input-group date form_date costToDate">
                                            <input type="text" class="form-control datepicker-pointer-events-none" readonly name="edit_annual_insurance_to_date" value="{{ $fleetCost['cost_to_date'] }}">
                                            <span class="input-group-btn">
                                                <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>

                    <div class="col-md-2">
                        <div id="cost_continuous_block" style="display: none;">
                            <label class="invisible">Cost</label>
                            <div class="d-flex align-items-center">
                                <div>
                                    <input type="checkbox" name="edit_insurance_cost_continuous" class="form-check-input edit-annual-checkbox edit_insurance_cost_continuous" id="edit_insurance_cost_continuous" value="{{isset($fleetCost['cost_continuous']) && $fleetCost['cost_continuous'] == "true" ? 1 : 0 }}" {{isset($fleetCost['cost_continuous']) && $fleetCost['cost_continuous'] == "true" ? 'checked' : '' }} >Cost is continuous
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <label class="invisible">Delete</label>
                        <div class="d-flex align-items-center justify-content-center" style="height: 45px;">
                            <a title="Details" data-repeater-delete class="btn btn-xs grey-gallery tras_btn js-annual-insurance-delete"><i class="jv-icon jv-dustbin icon-big"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="row gutters-tiny d-flex align-items-center js-annual-insurance-fields-wrapper margin-bottom-25" data-repeater-item>
                <div class="col-md-3">
                    <label>Monthly cost*:</label>
                    <div class="error-class">
                        <div class="input-group">
                            <span class="input-group-addon">&pound;</span>
                            <input type="text" name="edit_annual_insurance_cost" class="form-control insurance_cost" value="">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row gutters-tiny position-relative">
                        <div class="col-md-6">
                            <label>From*:</label>
                            <div class="error-class">
                                <div class="input-group date form_date costFromDate">
                                    <input type="text" class="form-control datepicker-pointer-events-none" readonly name="edit_annual_insurance_from_date" value="{{ Carbon\Carbon::parse()->format('d M Y') }}">
                                    <span class="input-group-btn">
                                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="annual_insurance_end_date">
                                <label>To*:</label>
                                <div class="error-class">
                                    <div class="input-group date form_date costToDate">
                                        <input type="text" class="form-control datepicker-pointer-events-none" readonly name="edit_annual_insurance_to_date" value="">
                                        <span class="input-group-btn">
                                            <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </div>  
                        </div>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div id="cost_continuous_block" style="display: none;">
                        <label class="invisible">Cost</label>
                        <div class="d-flex align-items-center">
                            <input type="checkbox" name="edit_insurance_cost_continuous" class="form-check-input edit-annual-checkbox edit_insurance_cost_continuous" id="edit_insurance_cost_continuous" value="1">Cost is continuous
                        </div>
                    </div>
                </div>
                <div class="col-md-1">
                    <label class="invisible">Delete</label>
                    <div class="d-flex align-items-center justify-content-center" style="height: 45px;">
                        <a title="Details" data-repeater-delete class="btn btn-xs grey-gallery tras_btn js-annual-insurance-delete"><i class="jv-icon jv-dustbin icon-big"></i></a>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
<div class="hide overlapping-date modal-date-validation margin-left" id="insuranceDateValidation">
    <span class="date">Dates on entries cannot be overlapping.</span>
</div>
<div class="row">
    <div class="col-md-offset-10 col-md-2 text-right">
        <button type="button" class="btn red-rubine btn-add annual-insurance-add-button" data-repeater-create>+ Add</button>
    </div>
</div>