<div class="form-group d-flex align-items-center monthly-depreciation-cost" id="monthly_depreciation_cost_value">
    <div class="col-md-3">
        <div class="d-flex align-items-center h-100">
            <label for="monthly_depreciation_cost" class="control-label align-self-center pt-0 w-100">Monthly depreciation:</label>
        </div>
    </div>
    <div class="col-md-9">
        <div class="d-flex align-items-center">
            <div class="flex-grow-1">
                <div class="input-group">
                    <span class="input-group-addon">&pound;</span>
                    <input type="text" name="owned_monthly_depreciation_cost" readonly id="owned_monthly_depreciation_cost" class="form-control depreciation-cost-history" value="{{ isset($depreciationCurrentCost) ? (is_numeric($depreciationCurrentCost) ? number_format($depreciationCurrentCost, 2) : $depreciationCurrentCost) : '' }}">
                </div>
            </div>
            <div class="margin-left-30">
                @if($from == 'edit')
                <a title="Details" class="btn btn-xs grey-gallery tras_btn" data-target="#depreciation_cost_history" data-toggle="modal" href="#depreciation_cost_history"><i class="jv-icon jv-find-doc icon-big"></i></a>
                @endif

                <a title="Edit" class="btn btn-xs grey-gallery tras_btn edit-depreciation-cost edit-depreciation-cost" data-target="#edit_depreciation_cost" href="#edit_depreciation_cost" data-toggle="modal"><i class="jv-icon jv-edit icon-big"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="form-group d-flex align-items-center" id="monthly_lease_cost_value">
    <div class="col-md-3">
        <div class="d-flex align-items-center h-100">
            <label for="monthly_lease_cost" class="control-label align-self-center pt-0 w-100">Monthly hire:</label>
        </div>
    </div>
    <div class="col-md-9">
        <div class="d-flex align-items-center">
            <div class="flex-grow-1">
                <div class="input-group">
                    <span class="input-group-addon">&pound;</span>
                    <input type="text" name="monthly_lease_cost" id="monthly_lease_cost" class="form-control" readonly value="{{ isset($currentMonthLeaseCost) ? (is_numeric($currentMonthLeaseCost) ? number_format($currentMonthLeaseCost, 2) : $currentMonthLeaseCost) : ''}}">
                </div>
            </div>
            <div class="margin-left-30">
                @if($from == 'edit')
                <a title="Details" class="btn btn-xs grey-gallery tras_btn" data-target="#lease_cost_history" data-toggle="modal" href="#lease_cost_history"><i class="jv-icon jv-find-doc icon-big"></i></a>
                @endif
                <a title="Edit" class="btn btn-xs grey-gallery tras_btn" data-target="#monthly_lease_cost_modal" href="#monthly_lease_cost_modal" data-toggle="modal"><i class="jv-icon jv-edit icon-big"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="form-group d-flex align-items-center" id="permitted_annual_mileage">
    <div class="col-md-3">
        <div class="d-flex align-items-center h-100">
            <label for="permitted_annual_mileage" class="control-label align-self-center pt-0 w-100">Permitted annual mileage:</label>
        </div>
    </div>
    <div class="col-md-9">
            {{-- <span class="input-group-addon">&pound;</span> --}}
        <input type="text" name="permitted_annual_mileage" id="permitted_annual_mileage" class="form-control" value="{{ isset($vehicle['permitted_annual_mileage']) ? (is_numeric($vehicle['permitted_annual_mileage']) ? number_format($vehicle['permitted_annual_mileage']) : $vehicle['permitted_annual_mileage']) : ''}}">
    </div>
</div>

<div class="form-group d-flex align-items-center" id="excess_cost_per_mile">
    <div class="col-md-3">
        <div class="d-flex align-items-center h-100">
            <label for="excess_cost_per_mile" class="control-label align-self-center pt-0 w-100">Excess cost per mile:</label>
        </div>
    </div>
    <div class="col-md-9">
        <div class="input-group">
            <span class="input-group-addon">&pound;</span>
            <input type="text" name="excess_cost_per_mile" id="excess_cost_per_mile" class="form-control" value="{{ isset($vehicle['excess_cost_per_mile']) ? $vehicle['excess_cost_per_mile'] : ''}}">
        </div>
    </div>
</div>

<div class="form-group d-flex align-items-center" id="maintenance_cost_field">
    <div class="col-md-3">
        <div class="d-flex align-items-center h-100">
            <label for="maintenance_cost" class="control-label align-self-center pt-0 w-100">Monthly management:</label>
        </div>
    </div>
    <div class="col-md-9">
        <div class="d-flex align-items-center">
            <div class="flex-grow-1">
                <div class="input-group">
                    <span class="input-group-addon">&pound;</span>
                    <input type="text" name="leased_annual_maintenance_cost" id="leased_annual_maintenance_cost" readonly class="form-control maintenance_cost" value="{{ isset($currentMonthMaintenanceCost) ? (is_numeric($currentMonthMaintenanceCost) ? number_format($currentMonthMaintenanceCost, 2) : $currentMonthMaintenanceCost) : ''}} ">
                </div>
            </div>
            <div class="margin-left-30">
                @if($from == 'edit')
                <a title="Details" class="btn btn-xs grey-gallery tras_btn" data-target="#maintenance_cost_history" data-toggle="modal" href="#maintenance_cost_history"><i class="jv-icon jv-find-doc icon-big"></i></a>
                @endif
                <a title="Edit" class="btn btn-xs grey-gallery tras_btn" data-target="#monthly_maintenance_cost" href="#monthly_maintenance_cost" data-toggle="modal"><i class="jv-icon jv-edit icon-big"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="form-group d-flex align-items-center" id="annual_vehicle_cost_tax">
    <div class="col-md-3">
        <div class="d-flex align-items-center h-100">
            <label for="annual_vehicle_cost" class="control-label align-self-center pt-0 w-100">Monthly vehicle tax:</label>
        </div>
    </div>
    <div class="col-md-9">
        <div class="input-group">
            <span class="input-group-addon">&pound;</span>
            <input type="text" name="annual_vehicle_cost" id="annual_vehicle_cost" readonly class="form-control" value="{{ isset($vehicleTaxValue) ? ( is_numeric($vehicleTaxValue) ? number_format($vehicleTaxValue, 2) : $vehicleTaxValue ) : ''}}">
        </div>
    </div>
</div>

<div class="form-group d-flex align-items-center annual-insurrance-cost">
    <div class="col-md-3">
        <div class="d-flex align-items-center h-100">
            <label for="annual_insurance" class="control-label align-self-center pt-0 w-100">Monthly insurance:</label>
        </div>
    </div>
    <div class="col-md-9">
        <div class="d-flex align-items-center">
            <div class="flex-grow-1 monthly_insurance_div">
                <div class="error-class">
                    <div class="input-group">
                        <span class="input-group-addon">&pound;</span>
                        <input type="text" name="annual_insurance" readonly id="leased_annual_insurance" class="form-control" value="{{ isset($insuranceFieldCurrentCost) ? (is_numeric($insuranceFieldCurrentCost) ? number_format($insuranceFieldCurrentCost, 2) : $insuranceFieldCurrentCost) : '' }}">
                    </div>
                    <span class="help-block help-block-error montly_insurance_error" style="display:none;">You have to select a type.</span>
                </div>
            </div>
            <div class="margin-left-30">
                @if($from == 'edit')
                    <a title="Details" class="btn btn-xs grey-gallery tras_btn insurance-cost-history" data-target="#monthly_insurance_cost_history" data-toggle="modal" href="#monthly_insurance_cost_history"><i class="jv-icon jv-find-doc icon-big"></i></a>
                    <a title="Edit" class="btn btn-xs grey-gallery tras_btn edit-insurance-cost js-edit-insurance-icon" data-target="#edit_monthly_insurance_cost" href="#edit_monthly_insurance_cost" data-toggle="modal"><i class="jv-icon jv-edit icon-big"></i></a>
                @else
                    <a title="Edit" class="btn btn-xs grey-gallery tras_btn edit-insurance-cost js-edit-insurance-icon js-insurance-edit-modal" href="javascript:void(0)"><i class="jv-icon jv-edit icon-big"></i></a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="form-group d-flex align-items-center">
    <div class="col-md-3"></div>
    <div class="col-md-9 py-0">
        <div class="error-class">
            <label class="margin-0">
                <input type="checkbox" name="is_insurance_cost_override" id="is_insurance_cost_override" class="form-check-input edit-annual-checkbox is-insurance-cost-override insurance-cost-override" @if($vehicle->is_insurance_cost_override == '1') checked="true" @endif value="">Override
            </label>
        </div>
    </div>
</div>

<div class="annual-telematics-cost">
    <div class="form-group d-flex align-items-center">
        <div class="col-md-3">
            <div class="d-flex align-items-center h-100">
                <label for="annual_telematics" class="control-label align-self-center pt-0 w-100">Monthly telematics:</label>
            </div>
        </div>

        <div class="col-md-9">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="input-group">
                        <span class="input-group-addon">&pound;</span>
                        <input type="text" name="annual_telematics" readonly id="leased_annual_telematics" class="form-control" value="{{ is_numeric($telematicsFieldCurrentCost) ? number_format($telematicsFieldCurrentCost, 2) : $telematicsFieldCurrentCost }}">
                    </div>
                </div>

                <div class="margin-left-30">
                    @if($from == 'edit')
                    <a title="Details" class="btn btn-xs grey-gallery tras_btn telematics-cost-history" data-target="#monthly_telematics_cost_history" data-toggle="modal" href="#monthly_telematics_cost_history"><i class="jv-icon jv-find-doc icon-big"></i></a>
                    @endif
                    <a title="Edit" class="btn btn-xs grey-gallery tras_btn edit-telematics-cost js-edit-telematics-icon" data-target="#edit_monthly_telematics_cost" href="#edit_monthly_telematics_cost" data-toggle="modal"><i class="jv-icon jv-edit icon-big"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-3"></div>
        <div class="col-md-9 py-0">
            <div class="error-class">
                <label class="margin-0">
                    <input type="checkbox" name="is_telematics_cost_override" class="form-check-input edit-telematics-checkbox is-telematics-cost-override telematics-cost-override" @if($vehicle->is_telematics_cost_override == '1') checked="true" @endif value="">Override
                </label>
            </div>
        </div>
    </div>
</div>

@if($from == 'edit')
    <div class="form-group d-flex align-items-center" id="miles_per_month">
        <div class="col-md-3">
            <div class="d-flex align-items-center h-100">
                <label for="miles_per_month" class="control-label align-self-center pt-0 w-100">{{ $vehicle->type->odometer_setting == "km" ? 'Km this month:' : 'Miles this month:'}}</label>
            </div>
        </div>
        <div class="col-md-9">
                {{-- <span class="input-group-addon">&pound;</span> --}}
            <input type="text" name="miles_per_month" id="miles_per_month" class="form-control" readonly="readonly" value="{{ isset($odometerMilesPerMonthValue) ? (is_numeric($odometerMilesPerMonthValue) ? number_format($odometerMilesPerMonthValue) : $odometerMilesPerMonthValue) : 0}}">
        </div>
    </div>
@endif

<!--
<div class="form-group">
    <label class="control-label col-md-3" for="ad_hoc_costs">Ad hoc costs:</label>
    <div class="col-md-9">
        <div class="d-flex justify-content-between">
            <div class="flex-grow-1 margin-right-10">
                <select class="form-control select2me" name="ad_hoc_costs" id="ad_hoc_costs" placeholder="Select">
                    @foreach ($vehicleAdHocCosts as $key => $adHosCosts)
                        <option value="{{ $key }}">{{ $adHosCosts }}</option>
                    @endforeach
                </select>
            </div>
            <div>
        </div>
            <div class="desboard_thumbnail">
                <button type="button" id="add_hoc_costs_button" data-toggle="modal" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center" style="margin-right: 0">
                    + Add Cost
                </button>
            </div>
        </div>
    </div>
</div>
 -->

 <!-- <div class="form-group align-items-start ad_hoc_manual_cost_adjustment {{ isset($vehicle['manual_cost_adjustment']) ? '' : 'hide' }}"> -->
<div class="form-group align-items-start">
    <div class="col-md-3" id="manual_cost_label">
        <div class="d-flex align-items-center h-100">
            <label class="control-label align-self-center padding-top-8 w-100">Manual cost adjustment:</label>
        </div>
    </div>
    <div class="col-md-9">
        <div class="js-vehicle-manual-cost-adjustment">
            @if(isset($vehicle['manual_cost_adjustment']))
                @foreach(json_decode($vehicle['manual_cost_adjustment']) as $key => $vehicleManualCost)
                    <div class="manual-cost-adjustment-wrapper">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row margin-bottom-15">
                                            <div class="col-md-6">
                                                <div class="font-weight-700">Amount:</div>
                                                <div id="cost">&#xa3;{{ isset($vehicleManualCost->cost_value) ? (is_numeric($vehicleManualCost->cost_value) ? number_format($vehicleManualCost->cost_value,2,".",",") : $vehicleManualCost->cost_value) : ''}}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="font-weight-700">Period:</div>
                                                <div>
                                                    <span id="vehicle_manual_cost_from_date">{{ isset($vehicleManualCost->cost_from_date) ? $vehicleManualCost->cost_from_date : ''}}</span>  -
                                                    <span id="vehicle_manual_cost_to_date">{{ isset($vehicleManualCost->cost_to_date) ? $vehicleManualCost->cost_to_date : ''}}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex justify-content-end">
                                <a title="Edit" class="btn btn-xs grey-gallery tras_btn" href="javascript:void(0);" id="edit_vehicle_manaual_cost_adjustments"
                                data-id="{{ $key+1 }}" data-modal-comments="{{ isset($vehicleManualCost->cost_comment) ? $vehicleManualCost->cost_comment : ''}}" data-modal-cost-to="{{ isset($vehicleManualCost->cost_to_date) ? $vehicleManualCost->cost_to_date : ''}}"
                                data-modal-cost-from="{{ isset($vehicleManualCost->cost_from_date) ? $vehicleManualCost->cost_from_date : ''}}" data-cost="{{ isset($vehicleManualCost->cost_value) ? $vehicleManualCost->cost_value : ''}}"><i class="jv-icon jv-edit icon-big"></i></a>
                                <a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn margin_left vehicle_manual_cost_adjustment_delete manual_cost_delete"><i class="jv-icon jv-dustbin icon-big"></i></a>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="font-weight-700">Comments:</div>
                                <div id="comments">{{ isset($vehicleManualCost->cost_comment) ? $vehicleManualCost->cost_comment : ''}}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <div>
            <button type="button" data-toggle="modal" data-target="#vehicle_manual_cost_adjustment" class="btn red-rubine btn-add vehicle-manual-cost-form">+ Add</button>
        </div>
    </div>
</div>

<!-- <div class="form-group align-items-start ad_hoc_fuel {{ isset($vehicle['fuel_use']) ? '' : 'hide' }}"> -->
<div class="form-group align-items-start">
    <div class="col-md-3" id="manual_cost_label">
        <div class="d-flex align-items-center h-100">
            <label class="control-label align-self-center padding-top-8 w-100">Fuel:</label>
        </div>
    </div>
    <div class="col-md-9">
        <div class="js-vehicle-fuel-use">
            @if(isset($vehicle['fuel_use']))
                @foreach(json_decode($vehicle['fuel_use']) as $key => $vehicleFuelCost)
                <div class="manual-fuel-use-wrapper">
                    <div class="row">
                        <div class="col-md-10">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row margin-bottom-15">
                                        <div class="col-md-6">
                                            <div class="font-weight-700">Amount:</div>
                                            <div id="cost">&#xa3;{{ isset($vehicleFuelCost->cost_value) ? (is_numeric($vehicleFuelCost->cost_value) ? number_format($vehicleFuelCost->cost_value,2) : $vehicleFuelCost->cost_value) : ''}}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="font-weight-700">Period:</div>
                                            <div>
                                                <span id="vehicle_fuel_cost_from_date">{{ isset($vehicleFuelCost->cost_from_date) ? $vehicleFuelCost->cost_from_date : ''}}</span>  -
                                                <span id="vehicle_fuel_cost_to_date">{{ isset($vehicleFuelCost->cost_to_date) ? $vehicleFuelCost->cost_to_date : ''}}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex justify-content-end">
                            <a title="Edit" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn" id="edit_fuel_value_modal" data-id="{{ $key+1 }}" data-modal-cost-to="{{ isset($vehicleFuelCost->cost_to_date) ? $vehicleFuelCost->cost_to_date : ''}}" data-modal-cost-from="{{ isset($vehicleFuelCost->cost_from_date) ? $vehicleFuelCost->cost_from_date : ''}}" data-cost="{{ isset($vehicleFuelCost->cost_value) ? $vehicleFuelCost->cost_value : ''}}"><i class="jv-icon jv-edit icon-big"></i></a>
                            <a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn margin_left vehicle_fuel_use_delete fuel_use_delete_modal"><i class="jv-icon jv-dustbin icon-big"></i></a>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
        <div>
            <button type="button" data-toggle="modal" data-target="#vehicle_fuel_use" class="btn red-rubine btn-add vehicle-fuel-use-form">+ Add</button>
        </div>
    </div>
</div>

<!-- <div class="form-group align-items-start ad_hoc_oil {{ isset($vehicle['oil_use']) ? '' : 'hide' }}"> -->
<div class="form-group align-items-start">
    <div class="col-md-3" id="manual_cost_label">
        <div class="d-flex align-items-center h-100">
            <label class="control-label align-self-center padding-top-8 w-100">Oil:</label>
        </div>
    </div>
    <div class="col-md-9">
        <div class="js-oil-use-adjustment">
            @if(isset($vehicle['oil_use']))
                @foreach(json_decode($vehicle['oil_use']) as $key => $vehicleOilCost)
                    <div class="manual-oil-use-wrapper">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row margin-bottom-15">
                                            <div class="col-md-6">
                                                <div class="font-weight-700">Amount:</div>
                                                <div id="cost">&#xa3;{{ isset($vehicleOilCost->cost_value) ? (is_numeric($vehicleOilCost->cost_value) ? number_format($vehicleOilCost->cost_value,2) : $vehicleOilCost->cost_value) : ''}}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="font-weight-700">Period:</div>
                                                <div>
                                                    <span id="vehicle_oil_use_from_date">{{ isset($vehicleOilCost->cost_from_date) ? $vehicleOilCost->cost_from_date : ''}}</span>  -
                                                    <span id="vehicle_oil_use_to_date">{{ isset($vehicleOilCost->cost_to_date) ? $vehicleOilCost->cost_to_date : ''}}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex justify-content-end">
                                <a title="Edit" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn" id="edit_vehicle_oil_use_adjustments" data-id="{{ $key+1 }}" data-modal-cost-to="{{ isset($vehicleOilCost->cost_to_date) ? $vehicleOilCost->cost_to_date : ''}}" data-modal-cost-from="{{ isset($vehicleOilCost->cost_from_date) ? $vehicleOilCost->cost_from_date : ''}}" data-cost="{{ isset($vehicleOilCost->cost_value) ? $vehicleOilCost->cost_value : ''}}"><i class="jv-icon jv-edit icon-big"></i></a>
                                <a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn margin_left manual_cost_adjustment_delete vehicle_oil_use_delete"><i class="jv-icon jv-dustbin icon-big"></i></a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <div>
            <button type="button" data-toggle="modal" data-target="#vehicle_oil_use" class="btn red-rubine btn-add vehicle-oil-use-form">+ Add</button>
        </div>
    </div>
</div>

<!-- <div class="form-group align-items-start ad_hoc_adblue {{ isset($vehicle['adblue_use']) ? '' : 'hide' }}"> -->
<div class="form-group align-items-start">
    <div class="col-md-3" id="manual_cost_label">
        <div class="d-flex align-items-center h-100">
            <label class="control-label align-self-center padding-top-8 w-100">AdBlue:</label>
        </div>
    </div>
    <div class="col-md-9">
        <div class="js-vehicle-adblue-use-adjustment">
            @if(isset($vehicle['adblue_use']))
                @foreach(json_decode($vehicle['adblue_use']) as $key => $vehicleAdblueCost)
                    <div class="manual-adblue-adjustment-wrapper">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row margin-bottom-15">
                                            <div class="col-md-6">
                                                <div class="font-weight-700">Amount:</div>
                                                <div id="cost">&#xa3;{{ isset($vehicleAdblueCost->cost_value) ? (is_numeric($vehicleOilCost->cost_value) ? number_format($vehicleAdblueCost->cost_value,2) : $vehicleOilCost->cost_value) : ''}}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="font-weight-700">Period:</div>
                                                <div>
                                                    <span id="vehicle_adblue_cost_from_date">{{ isset($vehicleAdblueCost->cost_from_date) ? $vehicleAdblueCost->cost_from_date : ''}}</span>  -
                                                    <span id="vehicle_adblue_cost_to_date">{{ isset($vehicleAdblueCost->cost_to_date) ? $vehicleAdblueCost->cost_to_date : ''}}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex justify-content-end">
                                <a title="Edit" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn" id="edit_vehicle_adblue_adjustments" data-id="{{ $key+1 }}" data-modal-cost-to="{{ isset($vehicleAdblueCost->cost_to_date) ? $vehicleAdblueCost->cost_to_date : ''}}" data-modal-cost-from="{{ isset($vehicleAdblueCost->cost_from_date) ? $vehicleAdblueCost->cost_from_date : ''}}" data-cost="{{ isset($vehicleAdblueCost->cost_value) ? $vehicleAdblueCost->cost_value : ''}}"><i class="jv-icon jv-edit icon-big"></i></a>
                                <a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn margin_left vehicle_adblue_delete manual_cost_delete"><i class="jv-icon jv-dustbin icon-big"></i></a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <div>
            <button type="button" data-toggle="modal" data-target="#vehicle_adblue_use" class="btn red-rubine btn-add vehicle_adblue_use_form">+ Add</button>
        </div>
    </div>
</div>

<!-- <div class="form-group align-items-start ad_hoc_screen_wash {{ isset($vehicle['screen_wash_use']) ? '' : 'hide' }}"> -->
<div class="form-group align-items-start">
    <div class="col-md-3" id="manual_cost_label">
        <div class="d-flex align-items-center h-100">
            <label class="control-label align-self-center padding-top-8 w-100">Screen wash:</label>
        </div>
    </div>
    <div class="col-md-9">
        <div class="js-screen-wash-adjustment">
            @if(isset($vehicle['screen_wash_use']))
                @foreach(json_decode($vehicle['screen_wash_use']) as $key => $vehicleScreenWash)
                    <div class="manual-screen-wash-wrapper">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row margin-bottom-15">
                                            <div class="col-md-6">
                                                <div class="font-weight-700">Amount:</div>
                                                <div id="cost">&#xa3;{{ isset($vehicleScreenWash->cost_value) ? (is_numeric($vehicleScreenWash->cost_value) ? number_format($vehicleScreenWash->cost_value,2) : $vehicleScreenWash->cost_value) : ''}}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="font-weight-700">Period:</div>
                                                <div>
                                                    <span id="vehicle_screen_wash_from_date">{{ isset($vehicleScreenWash->cost_from_date) ? $vehicleScreenWash->cost_from_date : ''}}</span>  -
                                                    <span id="vehicle_screen_wash_to_date">{{ isset($vehicleScreenWash->cost_to_date) ? $vehicleScreenWash->cost_to_date : ''}}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex justify-content-end">
                                <a title="Edit" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn" id="edit_screen_wash_adjustments" data-id="{{ $key+1 }}" data-modal-cost-to="{{ isset($vehicleScreenWash->cost_to_date) ? $vehicleScreenWash->cost_to_date : ''}}" data-modal-cost-from="{{ isset($vehicleScreenWash->cost_from_date) ? $vehicleScreenWash->cost_from_date : ''}}" data-cost="{{ isset($vehicleScreenWash->cost_value) ? $vehicleScreenWash->cost_value : ''}}"><i class="jv-icon jv-edit icon-big"></i></a>
                                <a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn margin_left vehicle_screen_wash_delete manual_screen_wash_delete manual_cost_delete"><i class="jv-icon jv-dustbin icon-big"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <div>
            <button type="button" data-toggle="modal" data-target="#vehicle_screen_wash_use" class="btn red-rubine btn-add vehicle-screen-wash-use-form">+ Add</button>
        </div>
    </div>
</div>

<!-- <div class="form-group align-items-start ad_hoc_fleet_livery_wash {{ isset($vehicle['fleet_livery_wash']) ? '' : 'hide' }}"> -->
<div class="form-group align-items-start">
    <div class="col-md-3" id="manual_cost_label">
        <div class="d-flex align-items-center h-100">
            <label class="control-label align-self-center padding-top-8 w-100">Fleet livery wash:</label>
        </div>
    </div>
    <div class="col-md-9">
        <div class="js-fleet-livery-adjustment">
            @if(isset($vehicle['fleet_livery_wash']))
                @foreach(json_decode($vehicle['fleet_livery_wash']) as $key => $vehicleFleetLiveryWash)
                    <div class="manual-fleet-livery-wrapper">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row margin-bottom-15">
                                            <div class="col-md-6">
                                                <div class="font-weight-700">Amount:</div>
                                                <div id="cost">&#xa3;{{ isset($vehicleFleetLiveryWash->cost_value) ? (is_numeric($vehicleFleetLiveryWash->cost_value) ? number_format($vehicleFleetLiveryWash->cost_value,2) : $vehicleFleetLiveryWash->cost_value) : ''}}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="font-weight-700">Period:</div>
                                                <div>
                                                    <span id="vehicle_fleet_livery_from_date">{{ isset($vehicleFleetLiveryWash->cost_from_date) ? $vehicleFleetLiveryWash->cost_from_date : ''}}</span>  -
                                                    <span id="vehicle_fleet_livery_to_date">{{ isset($vehicleFleetLiveryWash->cost_to_date) ? $vehicleFleetLiveryWash->cost_to_date : ''}}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex justify-content-end">
                                <a title="Edit" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn" id="edit_fleet_livery_adjustments" data-id="{{ $key+1 }}" data-modal-cost-to="{{ isset($vehicleFleetLiveryWash->cost_to_date) ? $vehicleFleetLiveryWash->cost_to_date : ''}}" data-modal-cost-from="{{ isset($vehicleFleetLiveryWash->cost_from_date) ? $vehicleFleetLiveryWash->cost_from_date : ''}}" data-cost="{{ isset($vehicleFleetLiveryWash->cost_value) ? $vehicleFleetLiveryWash->cost_value : ''}}"><i class="jv-icon jv-edit icon-big"></i></a>

                                <a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn margin_left vehicle_fleet_livery_delete manual_cost_delete"><i class="jv-icon jv-dustbin icon-big"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <div>
            <button type="button" data-toggle="modal" data-target="#vehicle_fleet_livery_wash" class="btn red-rubine btn-add vehicle-fleet-livery-wash-form">+ Add</button>
        </div>
    </div>
</div>