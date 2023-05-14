{{ csrf_field() }}
<div class="row">
    <div class="col-md-12 js-lease-cost-edit-date-picker" data-repeater-list="leaseCostRepeater">
        @if(isset($vehicle->lease_cost))
        <?php $lease_costs = json_decode($vehicle->lease_cost, true); ?>
            @foreach ($lease_costs as $leaseCost)
                <div class="row gutters-tiny js-lease-cost-fields-wrapper d-flex align-items-center margin-bottom-25" data-repeater-item>
                    <div class="col-md-3">
                        <label>Monthly cost*:</label>
                        <div class="error-class">
                            <div class="input-group">
                                <span class="input-group-addon">&pound;</span>
                                <input type="text" name="edit_lease_cost" class="form-control lease_cost" value="{{ number_format($leaseCost['cost_value'], 2,'.',',') }}">
                            </div>
                            <span class="help-block help-block-error edit_lease_cost_error" style="display:none;">This field is required</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row gutters-tiny position-relative">
                            <div class="col-md-6">
                                <label>From*:</label>
                                <div class="error-class">
                                    <div class="input-group date form_date insuranceCostFromDate">
                                        <input type="text" class="form-control lease_cost_from_date datepicker-pointer-events-none" readonly name="edit_lease_cost_from_date" value="{{ $leaseCost['cost_from_date'] }}">
                                        <span class="input-group-btn">
                                            <button class="btn default date-set grey-gallery btn-h-45" type="button">   <i class="jv-icon jv-calendar"></i></button>
                                        </span>
                                    </div>
                                    <span class="help-block help-block-error edit_lease_cost_from_date_error" style="display:none;">This field is required</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="lease_cost_end_date">
                                    <label>To*:</label>
                                    <div class="error-class">
                                        <div class="input-group date form_date insuranceCostToDate">
                                            <input type="text" class="form-control lease_cost_to_date datepicker-pointer-events-none" readonly name="edit_lease_cost_to_date" value="{{ $leaseCost['cost_to_date'] }}">
                                            <span class="input-group-btn">
                                                <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                            </span>
                                        </div>
                                        <span class="help-block help-block-error edit_lease_cost_to_date_error" style="display:none;">This field is required</span>
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
                                    <input type="checkbox" name="edit_lease_cost_continuous" class="form-check-input edit-lease-checkbox edit_lease_cost_continuous"  value="{{isset($leaseCost['cost_continuous']) && $leaseCost['cost_continuous'] == "true" ? 1 : 0 }}" {{isset($leaseCost['cost_continuous']) && $leaseCost['cost_continuous'] == "true" ? 'checked' : '' }} >Cost is continuous
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <label class="invisible">Delete</label>
                        <div class="d-flex align-items-center justify-content-center" style="height: 45px;">
                            <a title="Details" data-repeater-delete class="btn btn-sm grey-gallery tras_btn js-lease-cost-delete"><i class="jv-icon jv-dustbin icon-big"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="row gutters-tiny d-flex align-items-center js-lease-cost-fields-wrapper margin-bottom-25" data-repeater-item>
                <div class="col-md-3">
                    <label>Monthly cost*:</label>
                    <div class="error-class">
                        <div class="input-group">
                            <span class="input-group-addon">&pound;</span>
                            <input type="text" name="edit_lease_cost" class="form-control lease_cost" value="">
                        </div>
                        <span class="help-block help-block-error edit_lease_cost_error" style="display:none;">This field is required</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row gutters-tiny position-relative">
                        <div class="col-md-6">
                            <label>From*:</label>
                            <div class="error-class">
                                <div class="input-group date form_date insuranceCostFromDate">
                                    <input type="text" class="form-control lease_cost_from_date datepicker-pointer-events-none" readonly name="edit_lease_cost_from_date" value="{{ Carbon\Carbon::parse()->format('d M Y') }}">
                                    <span class="input-group-btn">
                                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                    </span>
                                </div>
                                <span class="help-block help-block-error edit_lease_cost_from_date_error" style="display:none;">This field is required</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="lease_cost_end_date">
                                <label>To*:</label>
                                <div class="error-class">
                                    <div class="input-group date form_date insuranceCostToDate">
                                        <input type="text" class="form-control lease_cost_to_date datepicker-pointer-events-none" readonly name="edit_lease_cost_to_date" value="">
                                        <span class="input-group-btn">
                                            <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                        </span>
                                    </div>
                                    <span class="help-block help-block-error edit_lease_cost_to_date_error" style="display:none;">This field is required</span>
                                </div>
                            </div>  
                        </div>
                    </div>
                </div>
                
                <div class="col-md-2 padding0">
                    <div id="cost_continuous_block" style="display: none;">
                        <label class="invisible">Cost</label>
                        <div class="d-flex align-items-center">
                            <div>
                                <input type="checkbox" name="edit_maintanance_cost_continuous" class="form-check-input edit-annual-checkbox edit_lease_cost_continuous"  value="1">Cost is continuous
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-1">
                    <label class="invisible">Delete</label>
                    <div class="d-flex align-items-center justify-content-center" style="height: 45px;">
                        <a title="Details" data-repeater-delete class="btn btn-sm grey-gallery tras_btn js-lease-cost-delete"><i class="jv-icon jv-dustbin icon-big"></i></a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
<div class="hide overlapping-date modal-date-validation margin-left" id="leaseDateValidation">
    <span class="date">Dates on entries cannot be overlapping.</span>
</div>
<div class="row">
    <div class="col-md-offset-10 col-md-2 text-right">
        <button type="button" class="btn red-rubine btn-add lease-cost-add-button" data-repeater-create>+ Add</button>
    </div>
</div>