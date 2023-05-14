<div class="row">
    <div class="col-md-12">
    	<div class="row">
    		<div class="col-md-10">
        		<div class="form-group">UK Tax rates applied to vehicles based on their carbon emissions are stored here. Select the relevant tax year below.</div>
        		<div class="form-group"><b>Note:</b> previous tax years cannot be edited.</div>
        	</div>
        	<div class="col-md-2 text-right">
        		<a href="#hmrcco2_add" class="btn btn-plain" data-toggle="modal">Add new tax year</a>
        	</div>
    	</div>
        <div class="row">
        	<div class="col-md-12">
                <div class="custom-responsive-table custom-responsive-table-detail" id="hmrcco2Index">
                    @include('_partials.settings.hmrcCo2Index')
                </div>
            </div>
        </div>   
    </div>

    <!-- Modal to edit record starts here -->
    <div id="hmrcco2_edit" class="modal modal-fix  fade modal-overflow in" tabindex="-1"  aria-hidden="false" data-width="900">
    	{!! BootForm::openHorizontal(['md' => [4, 8]])->addClass('form-bordered form-validation hmrc-form')->action('settings/hmrcco2/update/')->id('saveHmrcco2') !!}
	    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
	        <h4 class="modal-title">Edit HMRC CO2</h4>
	        <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
	            <i class="jv-icon jv-close"></i>
	        </a>
	    </div>
	    <div class="modal-body">
	        <!------------------- -->
	    	<!------------------- -->
	    </div>
	    <div class="modal-footer">
	    	<div class="col-md-offset-2 col-md-8 ">
	            <div class="btn-group pull-left width100">
	                <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
	                <button type="submit" class="btn red-rubine btn-padding submit-button col-md-6">Save</button>
	            </div>
	        </div>
	    </div>
	    {!! BootForm::close() !!}
	</div>

    <!-- Modal to view record starts here -->
    <div id="hmrcco2_detail" class="modal modal-fix  fade modal-overflow in" tabindex="-1"  aria-hidden="false" data-width="900">
	    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
	    	<h4 class="modal-title">HMRC CO2</h4>
	        <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
	            <i class="jv-icon jv-close"></i>
	        </a>

	        {{-- <h4 class="col-md-11 modal-title">HMRC CO2</h4>
	        <div class="col-md-1 actions new_btn">
	        	<a href="{{ url('/settings/hmrc/exportexcel/2016-2020') }}" class="" style="margin-left: 0" id="exportHMRCExcel">
                    <span onclick="" class="m5 jv-icon jv-download"></span>
                </a> 
                
            </div>
	        <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
	            <i class="jv-icon jv-close"></i>
	        </a> --}}
	    </div>
	    <div class="modal-body">
	        <!------------------- -->
	    	<!------------------- -->
	    </div>
	</div>
    <!-- Modal to view record starts here -->
    <div id="hmrcco2_add" class="modal modal-fix  fade modal-overflow in" tabindex="-1"  aria-hidden="false">
	    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
	    	<h4 class="modal-title">Add New Tax Year</h4>
	        <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
	            <i class="jv-icon jv-close"></i>
	        </a>
	    </div>
	    <div class="modal-body">
	        {!! BootForm::openHorizontal(['md' => [4, 8]])->addClass('form-bordered form-validation hmrc-form')->action('settings/hmrcco2/add')->id('addHmrcco2TaxYear') !!}
	        <div class="form-group">
                <label class="control-label col-md-3">Tax year*:</label>
                <div class="col-md-9">
                    <select class="form-control select2me" id="tax_year_to_add" name="tax_year_to_add">
                        @foreach ($taxYearList as $key => $taxYear)
                            @if(in_array($taxYear, $taxYearsAdded) || in_array($taxYear, $taxYearsFinalised))
                               <option disabled="disabled" value="{{ $taxYear }}">{{ $taxYear }}</option>
                            @else
                               <option value="{{ $taxYear }}">{{ $taxYear}}</option>
                            @endif 
                        @endforeach 
                   </select>
                </div>   
            </div> 
            <div class="form-group tax-year-error-message help-block help-block-error has-error" style="display:none"><div class="col-md-3"></div><div class="col-md-9">This field is required</div></div>
	        {!! BootForm::close() !!}
	    </div>
	    <div class="modal-footer">
	    	<div class="col-md-offset-2 col-md-8 ">
	            <div class="btn-group pull-left width100">
	                <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
	                <button type="button" id="addHmrcco2TaxYearConfirm" class="btn red-rubine btn-padding submit-button col-md-6">Confirm</button>
	            </div>
	        </div>
	    </div>
	</div>
</div>