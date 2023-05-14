{{ csrf_field() }}
<div class="row">
    <div class="col-md-12 js-depreciation-edit-date-picker" data-repeater-list="monthlyDepreciationCostRepeater">
        @if(isset($depreciationDataDisplay))
            @foreach ($depreciationDataDisplay as $depreciation)
                <div class="row gutters-tiny d-flex align-items-center js-depreciation-fields-wrapper margin-bottom-25" data-repeater-item>
                    <div class="col-md-3">
                        <label>Monthly cost*:</label>
                        <div class="error-class">
                            <div class="input-group">
                                <span class="input-group-addon">&pound;</span>
                                <input type="text" name="edit_depreciation_cost" class="form-control depreciation_cost" value="{{ number_format($depreciation['cost_value'], 2) }}">
                            </div>
                            <span class="help-block help-block-error depreciation_cost_error" style="display:none;">This field is required</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row gutters-tiny position-relative">
                            <div class="col-md-6">
                                <label>From*:</label>
                                <div class="error-class">
                                    <div class="input-group date form_date insuranceCostFromDate">
                                        <input type="text" class="form-control depreciation_from_date datepicker-pointer-events-none" readonly name="edit_depreciation_from_date" value="{{ $depreciation['cost_from_date'] }}">
                                        <span class="input-group-btn">
                                            <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                        </span>
                                    </div>
                                    <span class="help-block help-block-error depreciation_from_date_error" style="display:none;">This field is required</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="depreciation-to-date">
                                    <label>To*:</label>
                                    <div class="error-class">
                                        <div class="input-group date form_date insuranceCostToDate">
                                            <input type="text" class="form-control depreciation_to_date datepicker-pointer-events-none" readonly name="edit_depreciation_to_date" value="{{ $depreciation['cost_to_date'] }}">
                                            <span class="input-group-btn">
                                                <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                            </span>
                                        </div>
                                        <span class="help-block help-block-error depreciation_to_date_error" style="display:none;">This field is required</span>
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
                                    <input type="checkbox" name="edit_depreciation_cost_continuous" class="form-check-input edit-depreciation-checkbox edit_depreciation_cost_continuous depreciation_cost_continuous" value="{{isset($depreciation['cost_continuous']) && $depreciation['cost_continuous'] == "true" ? 1 : 0 }}" {{isset($depreciation['cost_continuous']) && $depreciation['cost_continuous'] == "true" ? 'checked' : '' }}>Cost is continuous
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <label class="invisible">Delete</label>
                        <div class="d-flex align-items-center justify-content-center" style="height: 45px;">
                            <a title="Details" data-repeater-delete class="btn btn-xs grey-gallery tras_btn js-depreciation-delete"><i class="jv-icon jv-dustbin icon-big"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="row gutters-tiny d-flex align-items-center js-depreciation-fields-wrapper margin-bottom-25" data-repeater-item>
                <div class="col-md-3">
                    <label>Monthly cost*:</label>
                    <div class="error-class">
                        <div class="input-group">
                            <span class="input-group-addon">&pound;</span>
                            <input type="text" name="edit_depreciation_cost" class="form-control depreciation_cost" value="">
                        </div>
                        <span class="help-block help-block-error depreciation_cost_error" style="display:none;">This field is required</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row gutters-tiny position-relative">
                        <div class="col-md-6">
                            <label>From*:</label>
                            <div class="error-class">
                                <div class="input-group date form_date insuranceCostFromDate">
                                    <input type="text" class="form-control depreciation_from_date datepicker-pointer-events-none" readonly name="edit_depreciation_from_date" value="{{ Carbon\Carbon::parse()->format('d M Y') }}">
                                    <span class="input-group-btn">
                                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                    </span>
                                </div>
                                <span class="help-block help-block-error depreciation_from_date_error" style="display:none;">This field is required</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="depreciation-to-date">
                                <label>To*:</label>
                                <div class="error-class">
                                    <div class="input-group date form_date insuranceCostToDate">
                                        <input type="text" class="form-control depreciation_to_date datepicker-pointer-events-none" readonly name="edit_depreciation_to_date" value="">
                                        <span class="input-group-btn">
                                            <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                        </span>
                                    </div>
                                    <span class="help-block help-block-error depreciation_to_date_error" style="display:none;">This field is required</span>
                                </div>
                            </div>  
                        </div>
                    </div>
                </div>

                <div class="col-md-2s">
                    <div id="cost_continuous_block" style="display: none;">
                        <label class="invisible">Cost</label>
                        <div class="d-flex align-items-center">
                            <div>
                                <input type="checkbox" name="edit_depreciation_cost_continuous" class="form-check-input edit-depreciation-checkbox edit_depreciation_cost_continuous depreciation_cost_continuous" value="1">Cost is continuous
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-1">
                    <label class="invisible">Delete</label>
                    <div class="d-flex align-items-center justify-content-center" style="height: 45px;">
                        <a title="Details" data-repeater-delete class="btn btn-xs grey-gallery tras_btn js-depreciation-delete"><i class="jv-icon jv-dustbin icon-big"></i></a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
<div class="hide overlapping-date modal-date-validation" id="depreciationDateValidation">
    <span class="date">Dates on entries cannot be overlapping.</span>
</div>
<div class="row">
    <div class="col-md-offset-10 col-md-2 text-right">
        <button type="button" class="btn red-rubine btn-add depreciation-add-button" data-repeater-create>+ Add</button>
    </div>
</div>