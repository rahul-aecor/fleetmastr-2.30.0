<div class="tab-pane active" id="alert_settings">
	<div class="row">
        <div class="col-md-12">
            <form id="alert_notofication_id">
                <div class="row gutters-tiny">
                    <div class="col-md-9">
                        <div class="row gutters-tiny">
                            <div class="col-md-10">
                                <div class="row gutters-tiny">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {!! Form::select('severity', $alertSeverity, null, ['id' => 'severity', 'class' => 'form-control select2-notification-severity', 'data-placeholder' => 'Severity']) !!}
                                        </div> 
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {!! Form::select('type', $alertType, null, ['id' => 'type', 'class' => 'form-control select2-notification-type', 'data-placeholder' => 'Type']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {!! Form::select('source', $alertSource, null, ['id' => 'source', 'class' => 'form-control select2-notification-source', 'data-placeholder' => 'Source']) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="d-flex">
                                    <button class="btn red-rubine btn-h-45" type="submit" id="searchAlert">
                                        <i class="jv-icon jv-search"></i>
                                    </button>
                                    <button class="btn btn-success grey-gallery grid-clear-btn-user btn-h-45 js-notification-clear-btn" style="margin-right: 0" onclick="clearAlertNotificationGrid();">
                                        <i class="jv-icon jv-close"></i>
                                    </button>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div class="input-group">
                                {!! Form::text('notification_range',  $defaultDateRange, ['class' => 'form-control bg-white cursor-pointer','id'=>'alertSettingDateRange', 'placeholder' => 'Date' , 'readonly']) !!}
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
                            <div>Alerts</div>
                        </div>
                    </div>
                    <div class="actions">
                        <a href="javascript:void(0)" id="bulkAlertStatusRecord" class="btn red-rubine btn-padding" disabled="disabled">Bulk edit</a>
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="jqgrid-wrapper">
                        <table id="jqGridAlert" class="jqGridAlert table-striped table-bordered table-hover table" data-type="vehicles"></table>
                        <div id="jqGridAlertPager" class="jqGridAlertPager"></div>    
                    </div> 
                </div>
            </div>
        </div>
    </div>

    <div id="add_alert_centers" class="modal modal-fix  fade" tabindex="-1" data-backdrop="static" data-width="620" data-background="static" aria-labelledby="myModalLabel" aria-hidden="true" style="padding: 0">
        <div class="modal-dialog" style="margin-top: 0; margin-bottom: 0;">
            <div class="modal-content">
                @include("_partials.alertCenters.add_alert_centers")
            </div>
        </div>
    </div>

    <div id="edit_alert_centers_detail" class="modal modal-fix  fade" tabindex="-1" data-backdrop="static" data-width="620" data-background="static" aria-labelledby="myModalLabel" aria-hidden="true" style="padding: 0">
        <div class="modal-dialog modal-lg" style="margin-top: 0; margin-bottom: 0;">
            <div class="modal-content">
            </div>
        </div>
    </div>

    <div id="bulkAlertStatusAssigned" class="modal fade default-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine">
                    <button type="button" class="close" id="alertSettingBulkClose" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title btn-dark"><font color="black">Bulk Edit</font></h4>
                </div>
              <div class="modal-body">
                <form id="frmSettingBulkUploads" class="form-horizontal" role="form">
                    <div class="form-body">
                        <div class="form-group">
                            <label class="col-md-12 control-label" style="text-align:left;">Your update will be applied to the selected items.</label>
                        </div>
                        <div class="form-group row d-flex align-items-center">
                            <label for="cost" class="col-md-3 control-label padding-top-0">Alert status:<span class="js-required">*</span>:</label>
                            <div class="col-md-9 error-class">
                                <select class="form-control select2me select2-alert-setting-status" id="alert_setting_status" name="alert_status">
                                    @foreach ($alertStatus as $key => $source)
                                        <option value="{{$key}}">{{$source}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row d-flex align-items-center">
                            <label for="severity" class="col-md-3 control-label padding-top-0">Severity:<span class="js-required">*</span>:</label>
                            <div class="col-md-9 error-class">
                                <select class="form-control select2me select2-alert-setting-severity" id="alert_setting_severity" name="alert_severity">
                                    @foreach ($alertSeverity as $key => $severity)
                                        <option value="{{$key}}">{{$severity}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-3"></div>
                            <div class="col-md-9">
                                <p class="alert_setting_error text-danger help-block d-none">Please select any one dropdown value</p>
                            </div>
                        </div>
                    </div>
                </form>
              </div>
              <div class="modal-footer">
                <div class="btn-group pull-left width100">
                    <button id="closeSettingBulkUpload" type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                    <button id="updateAlertSettingBulkUpload" type="button" class="btn red-rubine btn-padding col-md-6">Update</button>
                </div>
              </div>
            </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div>
</div>