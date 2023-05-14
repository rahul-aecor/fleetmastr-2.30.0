{{ csrf_field() }}
<div id="monthly_vehicle_insurance_cost" class="modal fade default-modal vehicle_insurance_costs_modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Insurance Costs</h4>
                <a class="font-red-rubine" id="monthly_vehicle_insurance_cost_cancel_button" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>           
            
            {{-- <input type="hidden" name="current_vehicle_insurance_cost" value="{{$vehicleType->annual_insurance_cost}}"/> --}}
            <form id="vehicleInsuranceReset">
                <div class="modal-body repeater">
                    <div class="row">
                        <div class="col-md-12 js-vehicle-insurance-cost-edit-date-picker" data-repeater-list="vehicleInsuranceCostRepeater">
                            @include('_partials.vehicle_types.vehicle_insurance_details')
                        </div>
                    </div>
                    <div class="hide overlapping-date modal-date-validation margin-left" id="vehicleInsuranceDateValidation">
                        <span class="date">Dates on entries cannot be overlapping.</span>
                    </div>
                    <div class="row d-flex justify-content-end">
                        <div class="col-md-2 text-right">
                            <button type="button" class="btn red-rubine btn-add vehicle-insurance-cost-add-button" data-repeater-create>+ Add</button>
                        </div>
                    </div>
                </div>
            </form>
            <div class="modal-footer">
                <div class="btn-group pull-left width100">
                    @if ($fromPage == "edit")
                        <button type="button" id="edit_monthly_vehicle_insurance_cost_cancel_button" class="btn white-btn btn-padding col-md-6 vehicle-cancle-button" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn red-rubine btn-padding col-md-6 submit-button monthly_vehicle_insurance_cost_edit">Save</button>
                    @else
                        <button type="button" id="create_monthly_vehicle_insurance_cost_cancel_button" class="btn white-btn btn-padding col-md-6 vehicle-cancle-button" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn red-rubine btn-padding col-md-6 submit-button monthly_vehicle_insurance_cost_create">Save</button>
                    @endif
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

{{-- vehicle insurance cost delete modal --}}
<div class="modal fade default-modal vehicle_insurance_cost_delete_pop_up" tabindex="-1" role="dialog" data-backdrop="static">
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
                    <button id="vehicle_insurance_cost_delete_save" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Yes</button>           
                </div>
            </div>
        </div>
    </div>
</div>