<form class="form-horizontal" role="form" id="editAlertCenters">
    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
        <h4 class="modal-title">Edit Alert</h4>
        <a class="font-red-rubine" data-dismiss="modal" aria-label="Close" id="editAlertClose">
            <i class="jv-icon jv-close"></i>
        </a>
    </div>

    <div class="modal-body">
       
        <div class="tabbable-custom tabbable-rubine nav-justified margin-bottom0">
            <ul class="nav nav-tabs nav-justified">
                <li class="active">
                    <a href="#alert_details_data" data-toggle="tab" id="alert_details_tab">
                    Alert Details </a>
                </li>
            </ul>
            <div class="tab-content rl-padding">
                <div class="tab-pane active" id="alert_details_data">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="cost" class="col-md-3 control-label">Alert name:</label>
                                <div class="col-md-6 error-class">
                                    <input type="text" name="edit_alert_name" id="edit_alert_name" class="form-control" value="{{$editAlertCentersData->name}}" disabled="disabled">
                                </div>
                            </div>

                            <div class="form-group row d-flex">
                                <label class="col-md-3 control-label">Description:</label>
                                <div class="col-md-6 error-class">
                                    <textarea rows="4" class="form-control maintenance-history-comments-textarea" id="edit_alert_description" name="edit_alert_description" placeholder="This alert notification triggers when a vehicle is moving (notified via telematics data) and an accompanying vehicle check has not been completed for that vehicle." disabled="disabled">{{$editAlertCentersData->description}}</textarea>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-md-3 control-label">Source:</label>
                                <div class="col-md-6 error-class">
                                    <select class="form-control select2me select2-edit-alert-source" id="edit_alert_source" name="edit_alert_source" disabled="disabled">
                                        @foreach ($alertSource as $key => $source)
                                            <option {{ $editAlertCentersData->source === $key ? 'selected': '' }} value="{{ $key }}">{{ $source}}</option>
                                        @endforeach 
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Type:</label>
                                <div class="col-md-6 error-class">
                                    <select class="form-control select2me select2-edit-alert-centers-type" id="edit_alert_type" name="edit_alert_type" disabled="disabled">
                                        @foreach ($alertType as $key => $type)
                                            <option {{ $editAlertCentersData->type === $key ? 'selected': '' }} value="{{ $key }}">{{ $type}}</option>
                                        @endforeach 
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Severity<span class="js-required">*</span>:</label>
                                <div class="col-md-6 error-class">
                                    <select class="form-control select2me select2-edit-alert-centers-severity" id="edit_alert_severity" name="edit_alert_severity">
                                        @foreach ($alertSeverity as $key => $severity)
                                            <option {{ $editAlertCentersData->severity === $key ? 'selected': '' }} value="{{ $key }}">{{ $severity}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group d-flex align-items-center margin-top-20">
                                <label class="col-md-3 control-label align-self-center">Alert status:</label>
                                <div class="col-md-4">
                                    <label class="checkbox-inline toggle_switch">
                                        <div class="checker">
                                            <span>
                                                <input type="checkbox" data-toggle="toggle" data-on="Enabled" data-off="Disabled" class="js_alert_slot_toggle" name="edit_alert_status_value" {{ $editAlertCentersData->is_active == 1 ? 'checked' : '' }}>    
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>  
                    </div>
                </div>
                <div class="tab-pane notifications-error-block mt-22" id="notifications">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="col-md-3 text-right">Notifications:</div>
                                <div class="col-md-4">
                                     <input type="checkbox" data-toggle="toggle" data-on="Enabled" data-off="Disabled" class="js_alert_slot_toggle" id="alert_notifications" name="alert_notifications" {{ $editAlertCentersData->is_notification_enabled == 1 ? 'checked' : '' }}>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 text-right">Schedule:</div>
                                <div class="col-md-9">
                                    @for($i = 1; $i <= 7; $i++)
                                        <div class="row margin-bottom-20">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <input type="hidden" name="alert_status_toggle_value" id="alert_status_toggle_value" value="">
                                                    <div class="col-md-2 text-right">
                                                        <label class="control-label pt-0 font-weight-700">{{date('l', strtotime("Sunday +{$i} days"))}}</label>
                                                    </div>
                                                    <div class="col-md-10"> 
                                                        <div class="row gutter-tiny">
                                                            <div class="col-md-3">
                                                                <label>
                                                                    <input type="checkbox" id="edit_alert_monday" name="days[edit_alert_{{$i}}]" {{ isset($editAlertNotification[strtolower(date('l', strtotime("Sunday +{$i} days")))]) && $editAlertNotification[strtolower(date('l', strtotime("Sunday +{$i} days")))]->is_all_day == 1 ? 'checked' : '' }}> All day
                                                                </label>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <input type="checkbox" data-toggle="toggle" data-on="On" data-off="Off" class="js_alert_slot_toggle" data-size="small">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row" id="alertNotificationTemplate" style="display: none;">
                                                    <div class="col-md-2">
                                                    </div>
                                                    <div class="col-md-10">
                                                        <div class="row alertItem gutters-tiny margin-top-20 d-flex align-items-center" id="aletNotification_{l}_{i}">
                                                            <div class="col-md-2">
                                                                Slot {i}:
                                                            </div>
                                                            <div class="col-md-6 align-items-center">
                                                                {time}
                                                                <input type="hidden" name="timeData[{l}][{i}][time]" value="{time}">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="d-flex">
                                                                    <div>
                                                                        <div class="flex-grow-1">
                                                                            <input type="checkbox" {is_checked} name="timeData[{l}][{i}][checkboxToggle]" data-toggle="toggle" data-on="On" data-off="Off" class="js_alert_slot_toggle" data-height="44">
                                                                        </div>
                                                                    </div>
                                                                    <div class="ml-3">
                                                                        <button type="button" id="{l}_{i}" class="btn btn-link btn-h-45 delete-template" data-counter="{{$i}}"><i class="jv-icon jv-dustbin"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row margin-bottom-20 margin-top-20">
                                                    <div class="col-md-2">
                                                    </div>
                                                    <div class="js-alert-notification" id="templateContainer_{{$i}}"></div>
                                                    <div class="col-md-10">
                                                        <div class="card" style="box-shadow: rgba(0, 0, 0, 0.1) 0px 1px 3px 0px, rgba(0, 0, 0, 0.06) 0px 1px 2px 0px;">
                                                            <div class="card-body">
                                                                <div class="row gutters-tiny d-flex align-items-end">
                                                                    <div class="col-md-3">
                                                                        <label>From:</label>
                                                                        <div class="input-group date">
                                                                            <input type="text" size="16" readonly class="form-control notifications-date-value alert-date" name="alert_from_date" id="alert_from_date_{{$i}}" value="">
                                                                            <span class="input-group-btn">
                                                                            <button class="btn default date-set grey-gallery btn-h-45 registration-date js-time-picker" type="button"><i class="jv-icon jv-calendar"></i></button>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label>To:</label>
                                                                        <div class="input-group date">
                                                                            <input type="text" size="16" readonly class="form-control notifications-date-value alert-date" name="alert_to_date" id="alert_to_date_{{$i}}" value="">
                                                                            <span class="input-group-btn">
                                                                            <button class="btn default date-set grey-gallery btn-h-45 registration-date js-time-picker" type="button"><i class="jv-icon jv-calendar"></i></button>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="d-flex flex-column">
                                                                            <label for="is_checked" class="invisible">Status</label>
                                                                            <div class="flex-grow-1">
                                                                                <input type="checkbox" data-toggle="toggle" name="is_checked" data-on="On" data-off="Off" class="js_alert_slot_toggle" id="is_checked_{{$i}}" data-height="44" rel="{{$i}}">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <button type="button" class="btn red-rubine btn-h-45 btn-block add-slot-button" data-counter="1">Add slot</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if($i != 7) 
                                            <hr>
                                        @endif
                                    @endfor
                                </div>
                            </div>
                        </div>  
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="edit_alert_centers_id" id="edit_alert_centers_id" value="{{$editAlertCentersData->id}}">
    </div>
    <div class="modal-footer">
        <div class="row">
            <div class="col-md-offset-2 col-md-8">
                <div class="btn-group pull-left width100">
                    <button type="button" class="btn white-btn btn-padding col-md-6" id="editAlertClose" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn red-rubine btn-padding submit-button col-md-6" id="editAlertInfoUpdate">Update</button>
                </div>
            </div>
        </div>
    </div>
</form>