	@extends('layouts.default')

	@section('plugin-styles')
	    <link href="{{ elixir('css/bootstrap-daterangepicker/daterangepicker-bs3.css') }}" rel="stylesheet" type="text/css"/>
	    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal-bs3patch.css') }}" rel="stylesheet" type="text/css"/>
    	<link href="{{ elixir('css/bootstrap-modal/bootstrap-modal.css') }}" rel="stylesheet" type="text/css"/>
	@endsection

	@section('plugin-scripts')
		<script src="{{ elixir('js/bootstrap-modal/bootstrap-modal.js') }}" type="text/javascript"></script>
	    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modalmanager.js') }}" type="text/javascript"></script>
	@endsection

	@section('content')
	    <div class="page-bar">
        	{!! Breadcrumbs::render('user_vehicle_history', $id) !!}
        	<div class="page-toolbar">
		        <div>
		            <a class="btn hidden-print btn-plain" href="{{ url('users/vehicle/history/exportPdf/' .$id) }}">
		                <i class="jv-icon jv-download"></i> Export user history
		            </a>
	            </div>
        	</div>
    	</div>
    	<div class="row">
	        <div class="col-md-12">
	            <div class="portlet box marginbottom0">
	                <div class="portlet-title">
	                    <div class="caption blue_bracket mb6" style="min-width: 450px;">
	                        {{ $user->first_name}} {{ $user->last_name}}
	                        <input type="hidden" id="user_id" value="{{ $user->id }}">
	                    </div>
	                </div>
	                <div class="portlet-body">
	                	<div class="row">
	                		<div class="col-md-12">
	                			<div class="tabbable-custom tabbable-rubine nav-justified margin-bottom0">
		                			<ul class="nav nav-tabs nav-justified border-bottom-0">
							            <li class="active">
							                <a href="#quick_search" data-toggle="tab">  Vehicle use history </a>
							            </li>
							            <li>
							                <a href="#advanced_search" data-toggle="tab">Private use log</a>
							            </li>            
							        </ul>

							        <div class="tab-content">
							            <div class="tab-pane active" id="quick_search">
							                @include('_partials.users.vehicle_history')
							            </div>
							            <div class="tab-pane" id="advanced_search">
							            	@include('_partials.users.private_use_log')
							        	</div>
							        </div>
							    </div>
	                		</div>
	                	</div>
	                	{{-- <div class="row">
	                		<div class="col-md-12">
				                <button type="button" class="btn red-rubine btn-padding col-md-2" id="vehicleHistoryBtn">
				                	Vehicle use history
				                </button>
				                <button type="button" class="btn btn-default btn-padding col-md-2" id="privateUseLogBtn">
				                	Private use log
				                </button>
				            </div>
	                	</div>
				    	<div class="" id="swapSectionDiv">
					        @include('_partials.users.vehicle_history')
					        @include('_partials.users.private_use_log')
				        </div> --}}
	                </div>
	            </div>
	        </div>
        </div>
    <div id="privateUseEditDiv">
	<!-- Modal to edit record comes here -->
    </div>
    <!-- Modal to add record starts here -->
    <div id="privateUseAdd" class="modal modal-fix  fade modal-overflow in privateUseAdd" tabindex="-1"  aria-hidden="false" data-background="static">
    	<?php //print_r($user);exit;?>
    	{!! BootForm::openHorizontal(['md' => [4, 8]])->addClass('form-bordered form-validation hmrc-form')->id('savePrivateUseEntry') !!}
	    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
	        <h4 class="modal-title">Add New Entry</h4>
	        <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
	            <i class="jv-icon jv-close"></i>
	        </a>
	    </div>
	    <div class="modal-body">
    		<div class="error-message help-block help-block-error has-error" style="display: none">Invalid Dates: Vehicle was not allocated to the driver during the selected date range.</div>
	    	<div class="form-group">
	    		<label class="col-md-4 control-label" for="first_name">User:</label>
	    		<div class="col-md-8">
	    			<span class="form-control" style="border:none">{{$user->first_name.' '.$user->last_name}}</span>
	    		</div>
	    	</div>
	    	{!! BootForm::select('Registration*:', 'vehicle_id')->options($vehicleRegistrations)->addClass('select2me') !!}
	    	<div class="form-group reg-error-message help-block help-block-error has-error" style="display:none"><div class="col-md-4"></div><div class="col-md-8">This field is required</div></div>
	    	<div class="form-group{{ $errors->has('start_date') ? ' has-error' : '' }}">
                <label class="control-label col-md-4">From:</label>
                <input type="hidden" name="user_id" value="{{$user->id}}">
                <div class="col-md-8">
                    <div class="input-group date start_form_date">
                        <?php
                            /*if ($vehicle->start_date == "") {
                                $value = date('d M Y');
                            } else {
                                $value = $vehicle->start_date;
                            }*/
                        ?>
                        <input type="text" size="16" readonly class="form-control" name="start_date" id="start_date">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div> 
            <div class="form-group{{ $errors->has('end_date') ? ' has-error' : '' }}">
                <label class="control-label col-md-4">To:</label>
                <div class="col-md-8">
                    <div class="input-group date end_form_date">
                        <?php
                            /*if ($vehicle->end_date == "") {
                                $value = date('d M Y');
                            } else {
                                $value = $vehicle->end_date;
                            }*/
                        ?>
                        <input type="text" size="16" readonly class="form-control" name="end_date" id="end_date">
                        <span class="input-group-btn">
                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                        </span>
                    </div>
                </div>
            </div> 
            <div class="form-group">
                <label class="col-md-4 control-label ">&nbsp;</label>
                <div class="col-md-8">
                    <label class="checkbox-inline pt-0">
                      <input type="checkbox" id="private_use" 
                      name="private_use">Vehicle is in private use continuously
                    </label>
                </div>
            </div>
	    </div>
	    <div class="modal-footer">
	    	<div class="col-md-12">
	            <div class="btn-group pull-left width100">
	                <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
	                <button type="button" id="saveLogBtn" class="btn red-rubine btn-padding submit-button col-md-6">Save</button>
	            </div>
	        </div>
	    </div>
	    {!! BootForm::close() !!}
	</div>
	@endsection

	@push('scripts')
		<script src="{{ elixir('js/bootstrap-daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
		<script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
		<script src="{{ elixir('js/user_vehicle_history.js') }}" type="text/javascript"></script>
		<script src="{{ elixir('js/user_vehicle_private_use.js') }}" type="text/javascript"></script>
	@endpush