@extends('layouts.default')

@section('plugin-styles')
	<link href="{{ elixir('css/bootstrap-daterangepicker/daterangepicker-bs3.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal-bs3patch.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jquery-file-upload/jquery.fileupload.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jquery-file-upload/jquery.fileupload-ui.css') }}" rel="stylesheet" type="text/css"/>
    <noscript><link rel="stylesheet" href="{{ elixir('css/jquery-file-upload/jquery.fileupload-noscript.css') }}"></noscript>
    <noscript><link rel="stylesheet" href="{{ elixir('css/jquery-file-upload/jquery.fileupload-ui-noscript.css') }}"></noscript>
@endsection

@section('scripts')
	<script src="{{ elixir('js/bootstrap-daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jquery-file-upload/jquery.ui.widget.js') }}" type="text/javascript"></script>
    <!-- The Templates plugin is included to render the upload/download listings -->
    <script src="{{ elixir('js/jquery-file-upload/tmpl.min.js') }}" type="text/javascript"></script>
    <!-- The Load Image plugin is included for the preview images and image resizing functionality -->
    <script src="{{ elixir('js/jquery-file-upload/load-image.min.js') }}" type="text/javascript"></script>
    <!-- The Canvas to Blob plugin is included for image resizing functionality -->
    <script src="{{ elixir('js/jquery-file-upload/canvas-to-blob.min.js') }}" type="text/javascript"></script>
    <!-- blueimp Gallery script -->
    <script src="{{ elixir('js/blueimp-gallery/jquery.blueimp-gallery.min.js') }}" type="text/javascript"></script>
    <!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.iframe-transport.js') }}" type="text/javascript"></script>
    <!-- The basic File Upload plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload.js') }}" type="text/javascript"></script>
    <!-- The File Upload processing plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-process.js') }}" type="text/javascript"></script>
    <!-- The File Upload image preview & resize plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-image.js') }}" type="text/javascript"></script>
    <!-- The File Upload audio preview plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-audio.js') }}" type="text/javascript"></script>
    <!-- The File Upload video preview plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-video.js') }}" type="text/javascript"></script>
    <!-- The File Upload validation plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-validate.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jasny-bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-ui.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modal.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modalmanager.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/incidents.js') }}" type="text/javascript"></script>
@endsection

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>

    <div class="tabbable-custom tabbable-rubine nav-justified margin-bottom0">
        <ul class="nav nav-tabs nav-justified">
            <li class="active">
                <a href="#quick_search" data-toggle="tab">
                Quick Search </a>
            </li>
            <li>
                <a href="#advanced_search" data-toggle="tab">
                Advanced Search </a>
            </li>
        </ul>
        <div class="tab-content rl-padding">
            <div class="tab-pane active" id="quick_search">
                <form class="form row gutters-tiny" id="incidents-quick-filter-form">
                    <div class="col-md-10">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="vehicle-select-box-wrapper incident_search">
                                    @if(Auth::user()->isUserInformationOnly())
                                        <div class="form-group margin-bottom0">
                                            {!! Form::text('registration1', null, ['class' => 'form-control data-filter', 'placeholder' => 'Vehicle registration', 'id' => 'registration1']) !!}
                                        </div>   
                                    @else 
                                        <div class="form-group margin-bottom0">
                                            {!! Form::text('registration', null, ['class' => 'form-control data-filter', 'placeholder' => 'Vehicle registration', 'id' => 'registration']) !!}
                                        </div> 
                                    @endif                      
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="vehicle-select-box-wrapper incident_search">
                                    <div class="form-group margin-bottom0">                                
                                        {!! Form::text('incident_id', null, ['class' => 'form-control', 'placeholder' => 'Enter incident ID', 'id' => 'incident_id']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="incident_search">
                                    <div class="form-group margin-bottom0">                                
                                        {!! Form::text('created_by', null, ['class' => 'form-control data-filter', 'placeholder' => 'Created by', 'id' => 'created_by']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="incident_search">
                            <div class="form-group margin-bottom0">
                                <!-- {!! Form::text('incident_id', null, ['class' => 'form-control', 'placeholder' => 'Enter incident ID', 'id' => 'incident_id']) !!} -->
                                 <button class="btn btn-h-45 red-rubine pull-left js-quick-search-btn" type="submit">
                                    <i class="jv-icon jv-search"></i>
                                </button>
                                  <button class="btn grid-clear-btn btn-h-45 grey-gallery " id="vehicle-registration" style="margin-right: 0">
                                    <i class="jv-icon jv-close"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="row js-quick-search-error-msg" style="display: none">
                    <div class="col-md-12">
                        <div class="form-group has-error">
                          <span class="help-block help-block-error"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="advanced_search">
            <div class="row">
                <div class="col-md-12">
                    <form class="form" id="incidents-advanced-filter-form">
                        <div class="row gutters-tiny">
                            <div class="col-lg-3 col-md-12 col-sm-12">
                                @if(Auth::user()->isUserInformationOnly())
                                    <div class="form-group">
                                        {!! Form::text('region',  null, ['class' => 'form-control', 'placeholder' => 'All regions','id' => 'region','disabled'=>'disabled']) !!}
                                    <small class="text-danger">{{ $errors->first('region') }}</small>
                                    </div>
                                @else 
                                    <div class="form-group">
                                    {!! Form::select('region', $vehicleListing, null, ['id' => 'region', 'class' => 'form-control select2-vehicle-region']) !!}
                                    <small class="text-danger">{{ $errors->first('region') }}</small>
                                    </div>
                                @endif
                            </div>
                            <div class="col-lg-4 col-md-12 col-sm-12">
                                <div class="row gutters-tiny">
                                    <div class="col-lg-6 col-md-12 col-sm-12">
                                        <div class="form-group">
                                            {!! Form::select('allocated_to', ['' => '','Company' => 'Company', 'Insurance company' => 'Insurance company', 'Insurance broker' => 'Insurance broker'], null, ['id' => 'allocated_to', 'class' => 'form-control']) !!}
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12 col-sm-12">
                                        <div class="form-group">
                                            {!! Form::select('status', ['' => '', 'All' => 'All', 'Reported' => 'Reported', 'Under investigation' => 'Under investigation', 'Allocated' => 'Allocated', 'Closed' => 'Closed'], null, ['id' => 'status', 'class' => 'form-control']) !!}
                                        </div>
                                    </div>                                    
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-12 col-sm-12">
                                <div class="form-group">
                                    <div class="input-group">
                                        {!! Form::text('range', null, ['class' => 'form-control', 'placeholder' => 'Date', 'readonly' => 'readonly']) !!}
                                        <span class="input-group-addon js-daterangepicker-button" id="basic-addon1"><i class="jv-icon jv-calendar font-weight-700"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="search_option">
                                    <!-- {!! Form::submit('Search', ['class' => 'btn btn-success grey-gallery']) !!}
                                    <span class="btn btn-success grey-gallery grid-clear-btn">Clear</span> -->
                                        <button class="btn btn-h-45 red-rubine pull-left" type="submit">
                                            <i class="jv-icon jv-search"></i>
                                        </button>
                                        <button class="btn grid-clear-btn btn-h-45 grey-gallery" style="margin-right: 0">
                                            <i class="jv-icon jv-close"></i>
                                        </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
                   
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="portlet box marginbottom0">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption blue_bracket mb6" style="min-width: 350px;">
                        Incident List&nbsp;<span id="selected-region-name">All Regions</span>
                    </div>
                    <?php if (!Auth::user()->isWorkshopManager()) {
                        ?>
                        <div class="actions new_btn">
                            <a href="javascript:void(0)" onclick="clickResetGrid()" class="btn red-rubine btn-padding">Reset columns</a>
                            <span onclick="clickShowHideColumn()" class="m5 jv-icon jv-chevron-square-down js-show-hide-col-bt"></span>
                            {{-- <span onclick="clickSearch();" class="m5 fa fa-search"></span> --}}
                            <span onclick="clickRefresh();" class="m5 jv-icon jv-reload"></span> 
                            <span onclick="clickExport();" class="m5 jv-icon jv-download"></span>
                            <a href="javascript:void(0);" class="config btn btn-sm gray_btn_border table-group-action-submit title_right_btn" id="showIncidentReportModal"><i class="jv-icon jv-plus"></i> Add new incident</a>
                        </div>
                        <?php
                    } ?>
                    

                </div>
                <div class="portlet-body">
                    <div class="jqgrid-wrapper">
                        <table id="jqGrid" class="table-striped table-bordered table-hover check-table" data-type="incidents"></table>
                        <div id="jqGridPager"></div>    
                    </div> 
                </div>
            </div>
        </div>
    </div>

    <div id="add_incident_report" class="modal modal-fix  fade" tabindex="-1" data-backdrop="static" data-width="620" data-background="static" aria-labelledby="myModalLabel" aria-hidden="true" style="padding: 0">
        <div class="modal-dialog" style="margin-top: 0; margin-bottom: 0;">
            <div class="modal-content">
                <form class="form-horizontal" role="form" action="{{ '/incidents/upload_incident_images' }}" id="addIncidentReport" data-upload-template-id="template-upload" data-download-template-id="template-download" enctype="multipart/form-data">
                    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                        <h4 class="modal-title">Create New Incident</h4>
                        <a class="font-red-rubine" data-dismiss="modal" aria-label="Close" id="maintenanceHistoryClose">
                            <i class="jv-icon jv-close"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row gutters-tiny">
                                    <label class="col-md-4 control-label">Registration*:</label>
                                    <div class="col-md-8 error-class">
                                        {!! Form::text('incident_registration', null, ['class' => 'form-control data-filter', 'placeholder' => 'Vehicle registration', 'id' => 'incident_registration']) !!}
                                    </div>
                                </div>
                                <div class="form-group row gutters-tiny">
                                    <label class="col-md-4 control-label">Date & time*:</label>
                                    <div class="col-md-8 error-class">
                                        <div class="input-group date js-calendar-btn">
                                            <input type="text" size="16" readonly class="form-control bg-white cursor-pointer js-calendar1" name="incident_datetime" id="incident_datetime" value="" placeholder="Select">
                                            <span class="input-group-btn">
                                            <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row gutters-tiny">
                                    <label class="col-md-4 control-label">Type of incident*:</label>
                                    <div class="col-md-8 error-class">
                                        {!! Form::select('incident_type', $incidentType, null, ['id' => 'incident_type', 'class' => 'form-control select2me', 'data-placeholder' => 'Select one option from this list']) !!}
                                    </div>
                                </div>
                                <div class="form-group row gutters-tiny">
                                    <label class="col-md-4 control-label">Classification*:</label>
                                    <div class="col-md-8 error-class">
                                        <select class="form-control select2me" id="classification" name="classification" placeholder="Select one option from this list">
                                            <option></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row gutters-tiny">
                                    <label class="col-md-4 text-right">Reported to insurance<span class="js-required">*</span>:</label>
                                    <div class="col-md-8 error-class">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="radio-default-overright">
                                                    <input type="radio" name="is_reported_to_insurance" class="js-insurance-reported-radio" value="Yes">Yes
                                                </label>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="radio-default-overright">
                                                    <input type="radio" name="is_reported_to_insurance" class="js-insurance-reported-radio" value="No">No
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-12">
                                        <input type="hidden" name="vehicle_maintenance_img_url" id="vehicle_maintenance_img_url" value="{{ '/incidents/upload_incident_images' }}">
                                        {{-- <input type="hidden" name="temp_images" id="temp_images"> --}}
                                        <div class="fileupload-buttonbar">
                                            <div class="dropZoneElement">
                                                <div class="fileinput-button">
                                                    <div>
                                                        <p class="fileinput-button-title"><span>+</span>Add file</p>
                                                        <p class="dropImageHereText">Click or drop your file here to upload</p>
                                                        <input type="file" id="disabled-dropzone"multiple="" name="incident_images[]" accept=".gif, .jpg, .jpeg, .png">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="help-block text-center">(*.gif, *.jpg, *.jpeg, *.png)</div>

                                        <table role="presentation" class="table table-striped table-hover custom-table-striped clearfix" id="upload-media-modal-table">
                                            <thead>
                                                <th>Preview</th>
                                                <th>Image Name</th>
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
                                                        <input type="text" placeholder="Enter image name" id="caption" name="name" class="form-control"/>
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
                                                                <button class="btn btn-xs doc-delete-btn1 date-set grey-gallery tras_btn incident-img-delete-btn" type="button">
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
                    </div>
                    <div class="modal-footer">
                        <div class="col-md-offset-2 col-md-8 ">
                            <div class="btn-group pull-left width100">
                                <button type="button" class="btn white-btn btn-padding col-md-6" id="addIncidentReportCancel" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn red-rubine btn-padding submit-button col-md-6" id="addIncidentReportSave">Save</button>
                            </div>
                        </div>
                    </div>
                </form>



            </div>
        </div>
    </div><!-- /.modal -->

@endsection