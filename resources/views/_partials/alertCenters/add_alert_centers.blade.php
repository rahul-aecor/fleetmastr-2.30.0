<form class="form-horizontal" role="form" id="addAlertCenter">
    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
        <h4 class="modal-title">Add Alert </h4>
        <a class="font-red-rubine" data-dismiss="modal" aria-label="Close" id="maintenanceHistoryClose">
            <i class="jv-icon jv-close"></i>
        </a>
    </div>
    <div class="modal-body">
        <div class="tabbable-custom tabbable-rubine nav-justified margin-bottom0">
            <ul class="nav nav-tabs nav-justified">
                <li class="active">
                    <a href="#alert_details" data-toggle="tab">
                    Alert Details </a>
                </li>
            </ul>
            <div class="tab-content rl-padding">
                <div class="tab-pane active" id="alert_details">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row d-flex align-items-center">
                                <label for="cost" class="col-md-3 col-form-label">Alert name:</label>
                                <div class="col-md-9 error-class">
                                    <div class="input-group">
                                        <input type="text" name="alert_name" id="alert_name" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row d-flex align-items-center">
                                <label for="cost" class="col-md-3 control-label padding-top-0">Source:</label>
                                <div class="col-md-9 error-class">
                                    <select class="form-control select2me" id="alert_source" name="alert_source">
                                        @foreach ($alertSource as $key => $source)
                                            <option value="{{$key}}">{{$source}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row d-flex align-items-center">
                                <label class="col-md-3 control-label padding-top-0">Type:</label>
                                <div class="col-md-9 error-class">
                                    <select class="form-control select2me" id="alert_type" name="alert_type">
                                        @foreach ($alertType as $key => $type)
                                            <option value="{{$key}}">{{$type}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row d-flex align-items-center">
                                <label class="col-md-3 control-label padding-top-0">Severity:</label>
                                <div class="col-md-9 error-class">
                                    <select class="form-control select2me" id="alert_severity" name="alert_severity">
                                        @foreach ($alertSeverity as $key => $severity)
                                            <option value="{{$key}}">{{$severity}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row d-flex align-items-center">
                                <label class="col-md-3 control-label padding-top-0">Comments<span class="js-required">*</span>:</label>
                                <div class="col-md-9 error-class">
                                    <textarea rows="4" class="form-control maintenance-history-comments-textarea" id="alert_description" name="alert_description" placeholder="Enter details"></textarea>
                                </div>
                            </div>
                            <div class="form-group d-flex align-items-center margin-top-20">
                                <label class="col-md-3 control-label align-self-center pt-0">Alert status:</label>
                                <div class="col-md-4">
                                    <label class="checkbox-inline pt-0 toggle_switch toggle_switch--height-auto">
                                      <input type="checkbox" id="alertStatus" data-toggle="toggle" data-on="Enabled" data-off="Disabled"
                                      name="alert_status_value">
                                    </label>
                                </div>
                            </div>
                        </div>  
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <div class="col-md-offset-2 col-md-8 ">
            <div class="btn-group pull-left width100">
                <button type="button" class="btn white-btn btn-padding col-md-6" id="maintenanceHistoryCancle" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn red-rubine btn-padding submit-button col-md-6" id="alertCentreSave">Save</button>
            </div>
        </div>
    </div>
</form>


