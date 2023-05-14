<div class="row" id="privateUse">
	<div class="col-md-12">
    	<div class="portlet box marginbottom0">
            <div class="portlet-body">
	        	<div class="row portlet-search">
			        <div class="col-md-12">
			            <div class="clearfix">
		                    <div class="row d-flex align-items-center">
		                        <div class="col-md-6">
		                			<form class="form" id="privateUse-filter-form">
		                            	<div class="col-md-8 padding0">
					                        <div class="form-group">
					                            <div class="input-group">
					                                {!! Form::text('range', null, ['class' => 'form-control','placeholder' => 'Fliter by date', 'readonly' => 'readonly']) !!}
					                                <span class="input-group-addon js-daterangepicker-button" id="basic-addon1"><i class="jv-icon jv-calendar font-weight-700"></i></span>
					                            </div>
					                            <small class="text-danger">{{ $errors->first('range') }}</small>
					                        </div>
					                    </div>
					                    <div class="col-md-4">
					                        <div class="search_option">
					                            <!-- {!! Form::submit('Search', ['class' => 'btn btn-success grey-gallery']) !!}
					                            <span class="btn btn-success grey-gallery grid-clear-btn">Clear</span> -->

					                                <button class="btn btn-h-45 red-rubine" type="submit">
					                                    <i class="jv-icon jv-search"></i>
					                                </button>
					                                <button class="btn grid-clear-btn btn-h-45 grey-gallery" style="margin-right: 0">
					                                    <i class="jv-icon jv-close"></i>
					                                </button>
					                        </div>
					                    </div>
		                			</form>
		                        </div>
		                        <div class="col-md-4 text-right">
		                        	<span>
			                        	<strong>Total: </strong>
			                        	<span id="showPrivateUseDays">{{$totalPrivateUseDays}} </span> days
		                        	</span>
		                        </div>
		                        <div class="col-md-2">
		                        	<a href="#privateUseAdd" data-toggle="modal" class="btn btn-plain btn-block"><i class="jv-icon jv-plus"></i> Add new entry</a>
		                        </div>
		                    </div>
			            </div>
			        </div>
			    </div>
		        <div class="row">
			        <div class="col-md-12">
			            <div class="portlet box marginbottom0">
			                <div class="portlet-body">
			                    <div class="jqgrid-wrapper vehicle_history_table">
									<table id="jqGrid" class="table-striped table-bordered table-hover user-vehicle-history-table"></table>
									<div id="jqGridPager_privateuse"></div>
								</div>
			                </div>
			            </div>
			        </div>
		        </div>
		    </div>
		</div>
	</div>
</div>
<div class="modal modal-fix  fade modal-overflow in" tabindex="-1"  aria-hidden="false" data-background="static" id="privateUseDeleteConfirmModal">
    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
        <h4 class="modal-title">Confirmation</h4>
        <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
            <i class="jv-icon jv-close"></i>
        </a>
    </div>
    <div class="modal-body">
        <div>Are you sure you would like to delete this entry?</div>
        <input type="hidden" id="logDelId" value="" name=""/>
    </div>
    <div class="modal-footer">
    	<button data-bb-handler="cancel" type="button" data-dismiss="modal" class="btn btn white-btn btn-padding col-md-6 white-btn-border">Cancel</button>
        <button data-bb-handler="confirm" type="button" class="btn btn red-rubine btn-padding submit-button col-md-6 margin-left-5 red-rubine-border pull-right" id="privateUseDeleteConfirm">Yes</button>
    </div>
</div>
