<form class="form-horizontal display-settings" id="fleet_cost_form" role="form" action="/settings/storeFleetCostDetail" method="POST" novalidate>
    <div class="row">
        <div class="col-md-12">
            {{ csrf_field() }}
            <div class="form-body">
                <h4 class="margin-0 margin-bottom-20 font-weight-700">Global Fleet Costs</h4>

                <div class="row">
                    <div class="col-md-10">
                        
                        <div class="form-group d-flex row align-items-center">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <label for="annual_telematics_cost" class="control-label align-self-center pt-0 w-100">Monthly telematics cost per vehicle:</label>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-addon">&pound;</span>
                                    <input type="text" name="annual_telematics_cost" id="annual_telematics_cost" class="form-control" value="{{ (isset($telematicsCurrentCost) && $telematicsCurrentCost != '') ?  number_format($telematicsCurrentCost, 2) : '0.00'}}" readonly="readonly">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <a title="Details" class="btn btn-xs grey-gallery tras_btn" data-target="#annual_telematics_cost_history" data-toggle="modal" href="#annual_telematics_cost_history"><i class="jv-icon jv-find-doc icon-big"></i></a>

                                <a title="Edit" class="btn btn-xs grey-gallery tras_btn" data-target="#edit_telematics_insurance_cost" href="#edit_telematics_insurance_cost" data-toggle="modal" ><i class="jv-icon jv-edit icon-big"></i></a>
                            </div>
                        </div>
                        <div class="form-group d-flex row align-items-center">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <label for="vor_opportunity_cost" class="control-label align-self-center pt-0 w-100">VOR opportunity cost per day:</label>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-addon">&pound;</span>
                                    <input type="text" name="vor_opportunity_cost" id="vor_opportunity_cost" class="form-control" value="{{ isset($fleetCostData['vor_opportunity_cost_per_day']) ? number_format($fleetCostData['vor_opportunity_cost_per_day'],2) : '0.00' }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group align-items-start">
                            <div class="col-md-3" id="manual_cost_label">
                                <label class="control-label align-self-center padding-top-8 w-100">Manual cost adjustments:</label>
                            </div>
                            <div class="col-md-7">
                                <div class="js-fleet-cost-adjustment">
                                @if(isset($fleetCostData['manual_cost_adjustment']) && !empty($fleetCostData['manual_cost_adjustment']))
                                    @foreach($fleetCostData['manual_cost_adjustment'] as $key => $manualCost)
                                    <div class="manual-cost-adjustment-wrapper">
                                        <div class="row">
                                            <div class="col-md-10">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="row margin-bottom-15">
                                                            <div class="col-md-6">
                                                                <label class="font-weight-700">Amount:</label>
                                                                <div id="cost_value">&#xa3;{{ isset($manualCost['cost_value']) ? number_format($manualCost['cost_value'],2) : ''}}</div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="font-weight-700">Period:</label>
                                                                <div>
                                                                    <span id="cost_from_date">{{ isset($manualCost['cost_from_date']) ? $manualCost['cost_from_date'] : ''}}</span>  - 
                                                                    <span id="cost_to_date">{{ isset($manualCost['cost_to_date']) ? $manualCost['cost_to_date'] : ''}}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2 d-flex justify-content-end">
                                                <a title="Edit" class="btn btn-xs grey-gallery tras_btn" href="javascript:void(0);" id="edit_manual_cost_adjustments" data-id="{{ $key+1 }}" data-modal-comments="{{ isset($manualCost['cost_comments']) ? $manualCost['cost_comments'] : ''}}" data-modal-cost-to="{{ isset($manualCost['cost_to_date']) ? $manualCost['cost_to_date'] : ''}}" data-modal-cost-from="{{ isset($manualCost['cost_from_date']) ? $manualCost['cost_from_date'] : ''}}" data-cost="{{ isset($manualCost['cost_value']) ? $manualCost['cost_value'] : ''}}"><i class="jv-icon jv-edit icon-big"></i></a>
                                                <a title="Details" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn manual_cost_adjustment_delete manual_cost_delete"><i class="jv-icon jv-dustbin icon-big"></i></a>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="font-weight-700 margin-bottom0">Comments:</div>
                                                <div class="margin-bottom0" id="comments">{{ isset($manualCost['cost_comments']) ? $manualCost['cost_comments'] : ''}}</div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @endif
                                </div>
                                <div>
                                    <button type="button" data-toggle="modal" data-target="#fleet_manual_cost_adjustment" id="fleet_manual_cost" class="btn red-rubine btn-add">+ Add</button>
                                </div>
                                <?php
                                    $fleetcostmanual = "";
                                    if(isset($fleetCostData['manual_cost_adjustment'])) {
                                        $fleetcostmanual = $fleetCostData ? json_encode($fleetCostData['manual_cost_adjustment'], true) : '';
                                    }
                                    
                                    
                                ?>
                                <input type="hidden" id="fleet_cost_adjustments" name="fleet_cost_adjustments"  value="{{$fleetcostmanual}}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-2 pt15">
                <div class="col-md-4 col-md-offset-4">
                    <button type="submit" id="fleetCostSubmit" class="btn red-rubine btn-padding btn-block" name="submit">Save</button>
                </div>
            </div>
            <h4 class="margin-top-20 font-weight-700">Forecast Fleet Costs</h4>
            <p class="margin-bottom-20 text-muted">Fleet forecast costs are used in <b>fleet</b>mastr dashboard and reports</p>
            <div class="row">
                <div class="col-md-11">
                    <div class="row gutters-tiny">
                        <div class="col-md-3">
                            <button type="button" class="btn red-rubine btn-block btn-h-45" data-toggle="modal" data-target="#forecast_variable_per_month">Variable costs <small>/ month</small></button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn red-rubine btn-block btn-h-45" data-toggle="modal" data-target="#forecast_fixed_per_month">Fixed costs <small>/ month</small></button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn red-rubine btn-block btn-h-45" data-toggle="modal" data-target="#forecast_fleet_per_month">Fleet miles <small>/ month</small></button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn red-rubine btn-block btn-h-45" data-toggle="modal" data-target="#forecast_defect_per_month">Defects/Damage costs <small>/ month</small></button>
                        </div>
                    </div>
                </div>
            </div>
      </div>    
    </div>
</form>

<div id="fleet_manual_cost_adjustment" class="modal fade default-modal fleet-manual-cost" tabindex="-1" role="dialog" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
        <h4 class="modal-title manual-cost-modal-text">Manual Cost Adjustment</h4>
        <a class="font-red-rubine" id="manual_cost_adjustment_close" data-dismiss="modal" aria-label="Close">
                <i class="jv-icon jv-close"></i>
        </a>
      </div>
      <div class="modal-body">
        <form class="form-horizontal manual-cost-adjustment" role="form" id="fleetCostAreaForm">
            <div class="form-group row d-flex align-items-center margin-bottom-30">
                <label for="cost" class="col-md-2 control-label padding-top-0">Cost*:</label>
                <div class="col-md-10 error-class">
                    <div class="input-group">
                        <span class="input-group-addon">&pound;</span>
                        <input type="text" name="cost_value" id="cost_value" class="form-control" value="" required="true">
                    </div>
                </div>
            </div>
            <div class="form-group row d-flex align-items-center js-manual-cost-date-picker margin-bottom-30">
                <label for="from" class="col-md-2 control-label padding-top-0">From*:</label>
                <div class="col-md-4 error-class">
                    <div class='input-group date manualCostFromDate'>
                        <input type='text' name="cost_from_date" id="cost_from_date" class="form-control datepicker-pointer-events-none" required="true">
                        <span class="input-group-btn">
                            <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
                <label for="to" class="col-md-2 control-label padding-top-0" style="text-align: center;">To*:</label>
                <div class="col-md-4 error-class">
                    <div class='input-group date manualCostToDate'>
                        <input type='text' name="cost_to_date" id="cost_to_date" class="form-control datepicker-pointer-events-none" required="true">
                        <span class="input-group-btn">
                            <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group row d-flex align-items-center margin-bottom-30">
                <label for="comments" class="col-md-2 control-label padding-top-0">Comments*:</label>
                <div class="col-md-10 error-class">
                    <textarea type="text" name="cost_comments" id="cost_comments" class="
                    manual-cost-adjustment-textarea form-control" rows="2" maxlength="100"></textarea>
                    <span class="position-absolute comment-manual-cost"><span class="js-fleetcost-manual-cost-comment">100</span>/100 remaining characters</span>
                    <div class="form-control-focus"></div>
                </div>
            </div>
            <input type="hidden" name="modal_manual_data_id" id="modal_manual_data_id" value="">
        </form>
      </div>
      <div class="modal-footer">
        <div class="btn-group pull-left width100">
            <button id="manualCostCancleButton" type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
            {{-- <button id="fleetCostAreaFormSave" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Save</button>   --}}
            <button id="fleetManualCostSave" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Save</button>            
        </div>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="annual_insurance_cost_history" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
            <h4 class="modal-title">Insurance Cost History</h4>
            <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
            </a>
           </div>
            <div class="modal-body">
                @include('_partials.settings.fleetCost.annual_insurance_cost_history')
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div id="annual_telematics_cost_history" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Telematics Cost History</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                        <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                @include('_partials.settings.fleetCost.annual_telematics_cost_history')
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div id="edit_annual_insurance_cost" class="modal fade default-modal edit-annual-insurance-cost-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Insurance Costs</h4>
                <a class="font-red-rubine" id="edit_annual_insurance_cancle_button" data-dismiss="modal" aria-label="Close">
                        <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <form class="form-horizontal repeater create-annual-insurance" role="form" id="editAnnualInsuranceCostValue" action="/settings/annualInsurance" method="POST">
                <div class="modal-body">
                    @include('_partials.settings.fleetCost.edit_annual_insurance_cost')
                </div>

                <div class="modal-footer">
                    <div class="btn-group pull-left width100">
                        <button type="button" id="edit_annual_insurance_cancle_button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn red-rubine btn-padding col-md-6 submit-button fleet_annual_insurance_cost">Save</button>
                    </div>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="edit_telematics_insurance_cost" class="modal fade default-modal edit-telematics-insurance-cost-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Telematics Costs</h4>
                <a class="font-red-rubine" id="edit_telematics_cost_cancle_button" data-dismiss="modal" aria-label="Close">
                        <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <form class="form-horizontal repeater create-telematics-insurance" role="form" id="editTelematicsInsuranceCostValue" action="/settings/telematicsInsurance" method="POST">
                <div class="modal-body">
                    @include('_partials.settings.fleetCost.edit_telematics_insurance_cost')
                </div>
                <div class="modal-footer">
                    <div class="btn-group pull-left width100">
                        <button type="button" id="edit_telematics_cost_cancle_button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn red-rubine btn-padding col-md-6 submit-button fleet_annual_telematics_cost">Save</button>
                    </div>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php 
$months = array(1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec');
?>

<div id="forecast_variable_per_month" class="modal fade default-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Forecast Variable Costs Per Month</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                @include('_partials.settings.fleetCost.forecast_variable_costs_per_month')
            </div>
        </div>
    </div>
</div>

<div id="forecast_fixed_per_month" class="modal fade default-modal" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Forecast Fixed Costs Per Month</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                @include('_partials.settings.fleetCost.forecast_fixed_costs_per_month')
            </div>
        </div>
    </div>
</div>

<div id="forecast_fleet_per_month" class="modal fade default-modal" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Forecast Fleet Miles Per Month</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                @include('_partials.settings.fleetCost.forecast_fleet_miles_per_month')
            </div>
        </div>
    </div>
</div>

<div id="forecast_defect_per_month" class="modal fade default-modal" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Forecast Defects/Damage Costs Per Month</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                @include('_partials.settings.fleetCost.forecast_damage_costs_per_month')
            </div>
        </div>
    </div>
</div>


<div class="modal fade default-modal manual_cost_delete_pop_up" tabindex="-1" role="dialog" data-backdrop="static">
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
                    <button id="manual_cost_adjustment_delete_save_button" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Yes</button>            
                </div>
            </div>
        </div>
    </div>
</div>



<div class="modal fade default-modal annual_insurance_delete_pop_up" tabindex="-1" role="dialog" data-backdrop="static">
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
                    <button id="annual_insurance_delete_save" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Yes</button>           
                </div>
            </div>
        </div>
    </div>
</div>