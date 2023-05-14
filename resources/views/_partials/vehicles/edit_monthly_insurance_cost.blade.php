{{ csrf_field() }}
<div class="row">
    <div class="col-md-12 js-insurance-edit-date-picker" data-repeater-list="monthlyInsuranceCostRepeater">
        @include('_partials.vehicles.vehicle_insurance_details')
    </div>
</div>
<div class="hide overlapping-date modal-date-validation" id="insuranceDateValidation">
    <span class="date">Dates on entries cannot be overlapping.</span>
</div>
<div class="row">
    <div class="col-md-offset-10 col-md-2 text-right">
        <button type="button" class="btn red-rubine btn-add insurance-add-button" data-repeater-create >+ Add</button>
    </div>
</div>
{{--
<div class="form-group">
    --}}
{{--<div class="col-md-3"></div>--}}{{--


    <div class="col-md-9 py-0">
        <div class="error-class">
            <label class="margin-0">
               <input type="checkbox" name="is_insurance_cost_override" class="form-check-input edit-annual-checkbox is-insurance-cost-override insurance-cost-override" @if($vehicle->is_insurance_cost_override == '1') checked="true" @endif value="1">Override
            </label>
        </div>
    </div>
</div>--}}
