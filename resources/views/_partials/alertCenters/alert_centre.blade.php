<div class="tab-pane active" id="alert">
    <div class="row">
        <div class="col-md-12">
            <form id="alerts_id">
                <div class="row gutters-tiny">
                    <div class="col-md-9">
                        <div class="row gutters-tiny">
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    {!! Form::text('registration', null, ['class' => 'form-control data-filter alert-reset-filter', 'placeholder' => 'Vehicle registration', 'id' => 'registration']) !!}
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group"> 
                                     <div class="d-flex">
                                        <div class="flex-grow-1 margin-right-15">
                                            {!! Form::select('alert_status', $alertNotification, null, ['id' => 'status', 'class' => 'form-control select2-alert-status alert-reset-filter', 'data-placeholder' => 'Status']) !!}
                                        </div>
                                        <button class="btn red-rubine btn-h-45" type="submit" id="search">
                                            <i class="jv-icon jv-search"></i>
                                        </button>
                                        <button class="btn btn-success grey-gallery grid-clear-btn-user btn-h-45 js-alert-clear-btn" style="margin-right: 0" onclick="clearAlertCentreGrid();">
                                            <i class="jv-icon jv-close"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row gutters-tiny collapse" id="collapseExample">
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    {!! Form::text('user', null, ['class' => 'form-control data-filter alert-reset-filter', 'placeholder' => 'User', 'id' => 'user']) !!}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    {!! Form::select('alert_type', $alertType, null, ['id' => 'type', 'class' => 'form-control select2-alert-type alert-reset-filter', 'data-placeholder' => 'Type']) !!}
                                 </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    {!! Form::select('alert_source', $alertSource, null, ['id' => 'source', 'class' => 'form-control select2-alert-source alert-reset-filter', 'data-placeholder' => 'Source']) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="text-right">
                                    <div class="c-badge alert-filter-hide font-weight-700 d-none margin-right-10">
                                        <span>Reset filter</span>
                                        <button type="button" class="js-reset-filter" aria-label="Close">
                                            <svg stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                                <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <a class="btn-link" style="color: var(--primary-color);min-width: 130px;display:inline-block;" role="button" data-toggle="collapse" href="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                                        <span class="open-cont">Show advanced search</span>
                                        <span class="close-cont">Hide advanced search</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div class="input-group">
                                {!! Form::text('range',  $defaultDateRange, ['class' => 'form-control bg-white cursor-pointer','id'=>'alertCenterDateRange', 'placeholder' => 'Date' , 'readonly']) !!}
                                <span class="input-group-addon js-daterangepicker-button" id="basic-addon1"><i class="jv-icon jv-calendar font-weight-700"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-12">
            <div class="portlet box marginbottom0">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption blue_bracket has-btn">
                        <div class="d-flex align-items-center">
                            <div>Alert Notifications</div>
                        </div>
                        {{-- <div>
                            <a href="javascript:void(0)" onclick="alertResetFilter();" class="alert-filter-hide d-none">Reset filter x</a>
                        </div> --}}
                    </div>
                    <div class="actions">
                        <span onclick="clickAlertNotificationExport();" class="m5 jv-icon jv-download"></span>
                        <a href="javascript:void(0)" id="bulkAlertStatus" class="btn red-rubine btn-padding" disabled="disabled">Bulk edit</a>
                        {{-- <button type="button" class="btn red-rubine btn-padding submit-button test-alert" id="testAlert">Test Alert</button> --}}
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="jqgrid-wrapper">
                        <table id="jqGrid" class="jqGrid table-striped table-bordered table-hover table" data-type="vehicles"></table>
                        <div id="jqGridPager" class="multiple-action jqGridPagination"></div>
                    </div> 
                </div>
            </div>
        </div>
    </div>

    <div id="add_alert_show" class="modal modal-fix  fade" tabindex="-1" data-backdrop="static" data-width="620" data-background="static" aria-labelledby="myModalLabel" aria-hidden="true" style="padding: 0">
        <div class="modal-dialog" style="margin-top: 0; margin-bottom: 0;">
            <div class="modal-content">
                {{-- @include("_partials.alertCenters.alert_notification_show") --}}
            </div>
        </div>
    </div>

    <div id="bulkStatusAssigned" class="modal fade default-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine">
                    <button type="button" class="close" data-dismiss="modal" id="alertCentreBulkClose" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title btn-dark"><font color="black">Bulk Edit</font></h4>
                </div>
              <div class="modal-body">
                <form id="frmCentreBulkUploads" class="form-horizontal" role="form">
                    <div class="form-body">
                        <div class="form-group">
                            <label class="col-md-12 control-label" style="text-align:left;">Your update will be applied to the selected items.</label>
                        </div>
                        <div class="form-group row d-flex align-items-center">
                            <label for="cost" class="col-md-3 control-label padding-top-0">Status:<span class="js-required">*</span>:</label>
                            <div class="col-md-9 error-class">
                                <select class="form-control select2me select2-alert-centre-status" id="alert_status" name="alert_status">
                                    @foreach ($alertNotification as $key => $source)
                                        <option value="{{$key}}">{{$source}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
              </div>
              <div class="modal-footer">
                <div class="btn-group pull-left width100">
                    <button id="closeBulkUpload" type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                    <button id="updateBulkUpload" type="button" class="btn red-rubine btn-padding col-md-6">Update</button>
                </div>
              </div>
            </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div>
</div>