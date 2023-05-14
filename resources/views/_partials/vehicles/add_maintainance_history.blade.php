<form class="form-horizontal" role="form" action="{{ '/vehicles/upload_vehicle_maintenance_docs' }}" id="addMaintenanceHistory" data-upload-template-id="template-upload" data-download-template-id="template-download" enctype="multipart/form-data">
    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
        <h4 class="modal-title">Add New Maintenance History</h4>
        <a class="font-red-rubine" data-dismiss="modal" aria-label="Close" id="maintenanceHistoryClose">
            <i class="jv-icon jv-close"></i>
        </a>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group row gutters-tiny">
                    <label class="col-md-3 control-label">Event*:</label>
                    <div class="col-md-9 error-class">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="flex-grow-1 maintenance--modal-col-73">
                                <input type="hidden" name="is_update_pmi_schedule" id="is_update_pmi_schedule" value = "N/A">
                                <select class="form-control select2me" id="maintenance_event_type" name="maintenance_event_type" placeholder="Select">
                                    <option></option>
                                    @foreach($maintenanceEventTypes as $key => $event)
                                        <option value="{{ $event->id }}" data-slug="{{ $event->slug }}">{{ $event->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="d-flex margin-left-15 maintenance--modal-col-97">
                                <div class="company--modal-col desboard_thumbnail">
                                    <a href="#add-event" data-toggle="modal" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center" style="margin-right: 0">
                                        <i class="jv-icon jv-plus"></i>
                                    </a>
                                </div>
                                <div class="company--modal-col desboard_thumbnail">
                                    <a href="#view-events" data-path="user" data-toggle="modal" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center" style="margin-right: 0">
                                        <i class="jv-icon jv-edit"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="js-maintenance-planned-date" style="display: none;">
                    <div class="form-group row gutters-tiny">
                        <label class="col-md-3 control-label">Planned event*:</label>
                        <div class="col-md-9 error-class">
                            <div class="input-group date maintenance_history_form_date">
                                <input type="text" size="16" readonly class="form-control bg-white cursor-pointer" name="maintenance_planned_date" id="maintenance_planned_date" value="" placeholder="Select">
                                <span class="input-group-btn">
                                <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group row gutters-tiny">
                    <label class="col-md-3 control-label">Event date<span class="js-required">*</span>:</label>
                    <div class="col-md-9 error-class">
                        <div class="input-group date maintenance_history_form_date" id="maintenanceHistoryDate">
                            <input type="text" size="16" readonly class="form-control bg-white cursor-pointer" name="maintenance_event_date" id="maintenance_event_date" value="" placeholder="Select">
                            <span class="input-group-btn">
                            <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-group row gutters-tiny js-maintenance-odometer-reading">
                    <label class="col-md-3 control-label">Odometer reading:</label>
                    <div class="col-md-9 error-class">
                        <div class="input-group">
                            <input type="text" size="16" class="form-control bg-white cursor-pointer" name="maintenance_odometer_reading" id="maintenance_odometer_reading" value="">
                            <span class="input-group-addon" id="odometer_reading_unit_display">{{ $vehicle->type->odometer_setting == 'km' ? 'KM' : 'Miles' }}</span>
                        </div>
                    </div>
                </div>

                <div class="mot_show_hide" style="display:none">
                    <div class="form-group row gutters-tiny">
                        <label class="col-md-3 control-label">Type<span class="js-required">*</span>:</label>
                        <div class="col-md-9 error-class">
                            <select class="form-control select2me" id="maintenance_mot_type" name="maintenance_mot_type" placeholder="Select">
                                    <option></option>
                                    <option value="Initial">Initial</option>
                                    <option value="Re-test">Re-test</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row gutters-tiny">
                        <label class="col-md-3 control-label">Outcome<span class="js-required">*</span>:</label>

                        <div class="col-md-9 error-class">
                            <select class="form-control select2me" id="maintenance_mot_outcome" name="maintenance_mot_outcome" placeholder="Select">
                                    <option></option>
                                    <option value="Fail">Fail</option>
                                    <option value="Pass">Pass</option>
                                    <option value="PRS">PRS</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group row gutters-tiny">
                    <label class="col-md-3 control-label">Status*:</label>
                    <div class="col-md-9 error-class">
                        <select class="form-control select2me" id="maintenance_status" name="maintenance_status">
                            @foreach ($maintenanceHistoryStatus as $key => $status)
                                <option value="{{$key}}">{{$status}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row gutters-tiny">
                    <label class="col-md-3 control-label">Comments<span class="js-required">*</span>:</label>
                    <div class="col-md-9 error-class">
                        <textarea rows="4" class="form-control maintenance-history-comments-textarea" id="maintenance_comments" name="maintenance_comments" value="" placeholder="Enter details"></textarea>
                    </div>
                </div>

                @if ($isDVSAConfigurationTabEnabled)
                <div class="form-group row gutters-tiny js-maintenance-acknowledgment display-none">
                    <label class="col-md-3 control-label">Acknowledgment<span class="js-acknowledgment-required">*</span>:</label>
                    <div class="col-md-9 error-class">
                        <p>Has the safety inspection sheet been completed in line with DVSA requirements and where appropriate the vehicle/asset been signed off as roadworthy or the relevant procedures taken where remedial actions are required?</p>
                        <div class="row">
                            <div class="col-md-3">
                                <label class="radio-default-overright">
                                    <input type="radio" name="acknowledgment" class="js-acknowledgment-radio" value="yes">Yes
                                </label>
                            </div>
                            <div class="col-md-3">
                                <label class="radio-default-overright">
                                    <input type="radio" name="acknowledgment" class="js-acknowledgment-radio" value="no">No
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="form-group row">
                    <div class="col-md-12">
                        <input type="hidden" name="vehicle_maintenance_img_url" id="vehicle_maintenance_img_url" value="{{ '/vehicles/upload_vehicle_maintenance_docs' }}">
                        {{-- <input type="hidden" name="temp_images" id="temp_images"> --}}
                        <div class="fileupload-buttonbar">
                            <div class="dropZoneElement">
                                <div class="fileinput-button">
                                    <div>
                                        <p class="fileinput-button-title"><span>+</span>Add file</p>
                                        <p class="dropImageHereText">Click or drop your file here to upload</p>
                                        <input type="file" id="disabled-dropzone"multiple="" name="maintenance_files[]" accept=".gif, .jpg, .jpeg, .png, .doc, .docx, .xls, .xlsx, .csv, .pdf">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="help-block text-center">(*.gif, *.jpg, *.jpeg, *.png, *.doc, *.docx, *.xls, *.xlsx, *.csv, *.pdf)</div>
                        
                        <table role="presentation" class="table table-striped table-hover custom-table-striped maintenanceEventDetail clearfix" id="upload-media-table-1">
                            <thead>
                                <th>Preview</th>
                                <th>Document Name</th>
                                <th style="text-align: right; white-space: nowrap;">Actions</th>
                            </thead>
                            <tbody class="files">
                            </tbody>
                        </table>
                        <script id="template-upload" type="text/x-tmpl">
                            {% for (var i=0, file; file=o.files[i]; i++) { %}
                                <tr class="template-upload fade">
                                    <td>
                                        <span class="preview">
                                        </span>
                                    </td>
                                    <td class="js-file-name-td">
                                        <input type="text" placeholder="Enter document name" id="caption" name="name" class="form-control"/>
                                        <span class="help-block help-block-error" style="display: none;">This field is required</span>
                                        <!-- <div class="progress progress-striped progress-bar-red-rubine active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                            <div class="progress-bar progress-bar-red-rubine" style="width:0%;">
                                            </div>
                                        </div> -->
                                        <div class="progress mb-0 bg-grey">
                                            <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                                <span class="sr-only">20% Complete</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">

                                        {% if (!i) { %}
                                            <button class="btn grey-gallery cancel">
                                                <span>Cancel</span>
                                            </button>
                                        {% } %}
                                        {% if (!i && !o.options.autoUpload) { %}
                                            <button class="btn red-rubine start" disabled>
                                                <span>Upload</span>
                                            </button>
                                        {% } %}
                                    </td>
                                </tr>
                            {% } %}
                        </script>

                        <script id="template-download" type="text/x-tmpl">
                            {% for (var i=0, file; file=o.files[i]; i++) { %}
                                <input type="hidden" name="temp_images[]" class="js_temp_images" value={%=file.tempId%}>
                                <tr class="template-download fade">
                                    <td>
                                        {% if (file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/jpeg') { %}
                                            <span class="jv-icon jv-file-image table-docpreview-icon"></span>
                                        {% } else if (file.type === 'image/gif') { %}
                                            <span class="jv-icon jv-file-gif table-docpreview-icon"></span>
                                        {% } else if (file.type === 'application/pdf') { %}
                                            <span class="jv-icon jv-file-pdf table-docpreview-icon"></span>
                                        {% } else if (file.type === 'application/msword' || file.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') { %}
                                            <span class="jv-icon jv-file-word table-docpreview-icon"></span>
                                        {% } else if (file.type === 'application/mspowerpoint' || file.type === 'application/powerpoint' || file.type === 'application/vnd.ms-powerpoint' || file.type === 'application/x-mspowerpoint' || file.type === 'application/vnd.openxmlformats-officedocument.presentationml.presentation') { %}
                                            <img src="/img/document_icons/ppt.png" style="height: 45px;">
                                        {% } else if (file.type === 'application/vnd.ms-excel' || file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') { %}
                                            <span class="jv-icon jv-file-excel table-docpreview-icon"></span>
                                        {% } else if (file.extension === 'csv') { %}
                                            <span class="jv-icon jv-file-csv table-docpreview-icon"></span>
                                        {% } else { %}
                                            <span class="jv-icon jv-doc table-docpreview-icon"></span>
                                        {% } %}
                                    </td>
                                    <td>
                                        <p class="name">
                                            {% if (file.url) { %}
                                                <a rel="noopener" target="_blank" href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                                            {% } else { %}
                                                <span>{%=file.name%}</span>
                                            {% } %}
                                        </p>
                                        {% if (file.error) { %}
                                            <div><span class="label label-danger">Error</span> {%=file.error%}</div>
                                        {% } %}
                                    </td>
                                    <td style="text-align: right">
                                        {% if (file.deleteUrl) { %}

                                            <div class="delete-wrapper">
                                                <button class="delete" style="display:none;" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                                                    <i class="jv-icon jv-dustbin normal-font"></i>
                                                </button>
                                                <button class="btn btn-xs doc-delete-btn1 date-set grey-gallery tras_btn maintenance-doc-delete-btn" type="button">
                                                    <i class="jv-icon jv-dustbin normal-font"></i>
                                                </button>
                                            </div>
                                        {% } else { %}
                                            <button class="btn btn-warning cancel">
                                                <i class="glyphicon glyphicon-ban-circle"></i>
                                                <span>Cancel</span>
                                            </button>
                                        {% } %}
                                    </td>
                                </tr>
                            {% } %}
                        </script>

                    </div>
                </div>
            </div>  
        </div>
        <input type="hidden" name="maintenance_vehicle_id" id="maintenance_vehicle_id" value="{{ $vehicle->id }}">
    </div>
    <div class="modal-footer">
        <div class="col-md-offset-2 col-md-8 ">
            <div class="btn-group pull-left width100">
                <button type="button" class="btn white-btn btn-padding col-md-6" id="maintenanceHistoryCancle" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn red-rubine btn-padding submit-button col-md-6" id="maintenanceHistorySave">Save</button>
            </div>
        </div>
    </div>
</form>


