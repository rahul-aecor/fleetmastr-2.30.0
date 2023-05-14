<div id="privateUseEdit" class="modal modal-fix  fade modal-overflow in privateUseEdit" tabindex="-1"  aria-hidden="false" data-background="static">
    	<?php //print_r($user);exit;?>
    	{!! BootForm::openHorizontal(['md' => [4, 8]])->addClass('form-bordered form-validation ')->id('updatePrivateUseEntry') !!}
	    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
	        <h4 class="modal-title">Edit Entry</h4>
	        <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
	            <i class="jv-icon jv-close"></i>
	        </a>
	    </div>
	    <div class="modal-body">
            <div class="error-message help-block help-block-error has-error" style="display: none">Invalid Dates: Vehicle was not allocated to the driver during the selected date range.</div>
	    	<div class="form-group">
	    		<label class="col-md-4 control-label" for="first_name">User:</label>
	    		<div class="col-md-8">
	    			{{$private_use_log->user->first_name.' '.$private_use_log->user->last_name}}
	    		</div>
	    	</div>
            <input type="hidden" name="user_id" id="user_id_edit" value="{{ $private_use_log->user_id}}">
	    	{!! BootForm::select('Registration*:', 'vehicle_id_edit')->options($vehicleRegistrations)->addClass('select2me')->select($private_use_log->vehicle_id) !!}
            <div class="form-group reg-error-message help-block help-block-error has-error" style="display:none"><div class="col-md-4"></div><div class="col-md-8">This field is required</div></div>
            <input type="hidden" name="private_use_log_id" id="private_use_log_id" value="{{ $private_use_log->id }}">
	    	<div class="form-group{{ $errors->has('start_date') ? ' has-error' : '' }}">
                <label class="control-label col-md-4">From:</label>
                <div class="col-md-8">
                    <div class="input-group date start_form_date">
                        <?php
                            /*if ($vehicle->start_date == "") {
                                $value = date('d M Y');
                            } else {
                                $value = $vehicle->start_date;
                            }*/
                        ?>
                        <input type="text" size="16" readonly class="form-control" name="start_date_edit" id="start_date_edit" value="{{ $private_use_log->start_date }}">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div> 
            <div class="form-group{{ $errors->has('end_date_edit') ? ' has-error' : '' }}">
                <label class="control-label col-md-4">To:</label>
                <div class="col-md-8">
                    <div class="input-group date end_form_date">
                        <input type="text" size="16" readonly class="form-control" name="end_date_edit" id="end_date_edit" value="{{ $private_use_log->end_date }}">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div> 
            <div class="form-group">
                <label class="col-md-4 control-label ">&nbsp;</label>
                <div class="col-md-8">
                    <!-- <label class="checkbox-inline pt-0">
                      <div class="checker" id="uniform-private_use">
                        <span class=""><input type="checkbox" id="private_use_edit" name="private_use_edit" @if($private_use_log->end_date == null) checked @endif></span></div>Vehicle is in private use continuously
                    </label> -->
                    <label class="checkbox-inline pt-0">
                      <input type="checkbox" id="private_use_edit" 
                      name="private_use_edit" @if($private_use_log->end_date == null) checked @endif>Vehicle is in private use continuously
                    </label>
                </div>
            </div>
	    </div>
	    <div class="modal-footer">
	    	<div class="col-md-12">
	            <div class="btn-group pull-left width100">
	                <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
	                <button type="button" id="editLogBtn" class="btn red-rubine btn-padding submit-button col-md-6">Save</button>
	            </div>
	        </div>
	    </div>
	    {!! BootForm::close() !!}
	</div>
