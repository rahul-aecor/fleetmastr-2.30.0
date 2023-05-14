{{ csrf_field() }}
<div id="monthly_vehicle_tax_cost" class="modal fade default-modal vehicle_tax_costs_modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Vehicle Tax Costs</h4>
                <a class="font-red-rubine" id="monthly_vehicle_tax_cost_cancel_button" data-dismiss="modal" aria-label="Close">
                        <i class="jv-icon jv-close"></i>
                </a>
            </div>           
           
            
            {{-- <input type="hidden" name="current_vehicle_tax_cost" value="{{$vehicleType->vehicle_tax}}"/> --}}
            <form id="vehicleTaxReset">
            <div class="modal-body repeater">
                <div class="row">
                    <div class="col-md-12 js-vehicle-tax-cost-edit-date-picker" data-repeater-list="vehicleTaxCostRepeater">
                        @if ($fromPage == "edit")
                        <input type="hidden" class="vehicle_type_id" name="vehicle_type_id" value="{{$vehicleType->id}}"/>
                        @endif
                        @if(isset($vehicleType->vehicle_tax))
                        <?php $vehicle_tax_costs = json_decode($vehicleType->vehicle_tax, true); ?>
                            @foreach ($vehicle_tax_costs as $vehicleTaxCost)
                                <div class="row gutters-tiny js-vehicle-tax-cost-fields-wrapper d-flex align-items-center margin-bottom-25" data-repeater-item>
                                    <div class="col-md-3">
                                        <label>Monthly cost*:</label>
                                        <div class="error-class">
                                            <div class="input-group">
                                                <span class="input-group-addon">&pound;</span>
                                                <input type="text" name="edit_vehicle_tax_cost" class="form-control vehicle_tax_cost vehicleTaxCurrentCost" value="{{ number_format($vehicleTaxCost['cost_value'], 2) }}">
                                            </div>
                                            <span class="help-block help-block-error edit_vehicle_tax_cost_error" style="display:none;">This field is required</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row gutters-tiny position-relative">
                                            <div class="col-md-6">
                                                <label>From*:</label>
                                                <div class="error-class">
                                                    <div class="input-group date form_date costFromDate">
                                                        <input type="text" class="form-control vehicle_tax_cost_from_date datepicker-pointer-events-none" readonly name="edit_vehicle_tax_cost_from_date" value="{{ $vehicleTaxCost['cost_from_date'] }}">
                                                        <span class="input-group-btn">
                                                            <button class="btn default date-set grey-gallery btn-h-45" type="button">   <i class="jv-icon jv-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                    <span class="help-block help-block-error edit_vehicle_tax_cost_from_date_error" style="display:none;">This field is required</span>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="vehicle_tax_cost_end_date">
                                                    <label>To*:</label>
                                                    <div class="error-class">
                                                        <div class="input-group date form_date costToDate">
                                                            <input type="text" class="form-control vehicle_tax_cost_to_date datepicker-pointer-events-none" readonly name="edit_vehicle_tax_cost_to_date" value="{{ $vehicleTaxCost['cost_to_date'] }}">
                                                            <span class="input-group-btn">
                                                                <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                                            </span>
                                                        </div>
                                                        <span class="help-block help-block-error edit_vehicle_tax_cost_to_date_error" style="display:none;">This field is required</span>
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
                                                    <input type="checkbox" name="edit_vehicle_tax_cost_continuous" class="form-check-input edit-annual-checkbox edit_vehicle_tax_cost_continuous"  value="{{isset($vehicleTaxCost['cost_continuous']) && $vehicleTaxCost['cost_continuous'] == "true" ? 1 : 0 }}" {{isset($vehicleTaxCost['cost_continuous']) && $vehicleTaxCost['cost_continuous'] == "true" ? 'checked' : '' }} >Cost is continuous
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="invisible">Delete</label>
                                        <div class="d-flex align-items-center justify-content-center" style="height: 45px;">
                                            <a title="Details" data-repeater-delete class="btn btn-xs grey-gallery tras_btn js-vehicle-tax-cost-delete"><i class="jv-icon jv-dustbin icon-big"></i></a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="row gutters-tiny d-flex align-items-center js-vehicle-tax-cost-fields-wrapper margin-bottom-25" data-repeater-item>
                                <div class="col-md-3">
                                    <label>Monthly cost*:</label>
                                    <div class="error-class">
                                        <div class="input-group">
                                            <span class="input-group-addon">&pound;</span>
                                            <input type="text" name="edit_vehicle_tax_cost" class="form-control vehicle_tax_cost" value="">
                                        </div>
                                        <span class="help-block help-block-error edit_vehicle_tax_cost_error" style="display:none;">This field is required</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row gutters-tiny position-relative">
                                        <div class="col-md-6">
                                            <label>From*:</label>
                                            <div class="error-class">
                                                <div class="input-group date form_date costFromDate">
                                                    <input type="text" class="form-control vehicle_tax_cost_from_date datepicker-pointer-events-none" readonly name="edit_vehicle_tax_cost_from_date" value="{{ Carbon\Carbon::parse()->format('d M Y') }}">
                                                    <span class="input-group-btn">
                                                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                                    </span>
                                                </div>
                                                <span class="help-block help-block-error edit_vehicle_tax_cost_from_date_error" style="display:none;">This field is required</span>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="vehicle_tax_cost_end_date">
                                                <label>To*:</label>
                                                <div class="error-class">
                                                    <div class="input-group date form_date costToDate">
                                                        <input type="text" class="form-control vehicle_tax_cost_to_date datepicker-pointer-events-none" readonly name="edit_vehicle_tax_cost_to_date" value="">
                                                        <span class="input-group-btn">
                                                            <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                    <span class="help-block help-block-error edit_vehicle_tax_cost_to_date_error" style="display:none;">This field is required</span>
                                                </div>
                                            </div>  
                                        </div>
                                        <div class="hide overlapping-date modal-date-validation" id="vehicleTaxCostDateValidation">
                                            <span class="date">Overlapping date not allow</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div id="cost_continuous_block" style="display: none;">
                                        <label class="invisible">Cost</label>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <input type="checkbox" name="edit_vehicle_tax_cost_continuous" class="form-check-input edit-annual-checkbox edit_vehicle_tax_cost_continuous"  value="1">Cost is continuous
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <label class="invisible">Delete</label>
                                    <div class="d-flex align-items-center justify-content-center" style="height: 45px;">
                                        <a title="Details" data-repeater-delete class="btn btn-xs grey-gallery tras_btn js-vehicle-tax-cost-delete"><i class="jv-icon jv-dustbin icon-big"></i></a>
                                    </div>
                                </div>
                            </div>
                        @endif


                    </div>
                </div>
                <div class="hide overlapping-date modal-date-validation margin-left" id="vehicleTaxDateValidation">
                    <span class="date">Dates on entries cannot be overlapping.</span>
                </div>
                <div class="row d-flex justify-content-end">
                    <div class="col-md-2 text-right">
                        <button type="button" class="btn red-rubine btn-add vehicle-tax-cost-add-button" data-repeater-create>+ Add</button>
                    </div>
                </div>
            </div>
            </form>
            <div class="modal-footer">
                <div class="btn-group pull-left width100">
                    @if ($fromPage == "edit")
                    <button type="button" id="edit_monthly_vehicle_tax_cost_cancel_button" class="btn white-btn btn-padding col-md-6 vehicle-cancle-button" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn red-rubine btn-padding col-md-6 submit-button monthly_vehicle_tax_cost_edit">Save</button>
                    @else
                    <button type="button" id="create_monthly_vehicle_tax_cost_cancel_button" class="btn white-btn btn-padding col-md-6 vehicle-cancle-button" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn red-rubine btn-padding col-md-6 submit-button monthly_vehicle_tax_cost_create">Save</button>
                    @endif
                </div>
            </div>
                
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

{{-- vehicle tax cost delete modal --}}
<div class="modal fade default-modal vehicle_tax_cost_delete_pop_up" tabindex="-1" role="dialog" data-backdrop="static">
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
                    <button id="" type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                    <button id="vehicle_tax_cost_delete_save" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Yes</button>           
                </div>
            </div>
        </div>
    </div>
</div>