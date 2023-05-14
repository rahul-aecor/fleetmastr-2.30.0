@extends('layouts.default')

@section('styles')
    <link href="{{ elixir('css/jquery-file-upload/jquery.fileupload.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jquery-file-upload/jquery.fileupload-ui.css') }}" rel="stylesheet" type="text/css"/>
    <noscript><link rel="stylesheet" href="{{ elixir('css/jquery-file-upload/jquery.fileupload-noscript.css') }}"></noscript>
    <noscript><link rel="stylesheet" href="{{ elixir('css/jquery-file-upload/jquery.fileupload-ui-noscript.css') }}"></noscript>

    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('scripts')
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
    <!-- The File Upload user interface plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-ui.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/repeater.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/vehicles-repeater-elements.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/vehicles-add.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/vehicle-doc-upload.js') }}" type="text/javascript"></script>

    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modal.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modalmanager.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-editable/bootstrap-editable/js/bootstrap-editable.js') }}" type="text/javascript"></script>
@endsection

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <span id="summary"></span>
    <div class="page-bar">
        {!! Breadcrumbs::render('search_details_edit', $vehicle->id) !!}
    </div>
    <!-- Modal to Confirm usage override -->
    <div id="usage_override" class="modal modal-fix  fade modal-overflow in" tabindex="-1" data-backdrop="static" data-keyboard="false" aria-hidden="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                    <h4 class="modal-title">Edit Vehicle Profile</h4>
                    <a class="font-red-rubine bootbox-close-button" data-dismiss="modal" aria-label="Close">
                        <i class="jv-icon jv-close"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div>Do you want to override the default vehicle profile 'usage' value?</div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group pull-left width100">
                        <button type="button" id="usage_override_cancel" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                        <button type="button" id="usage_override_btn" class="btn red-rubine btn-padding submit-button col-md-6">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- monthly maintenance cost modal -->
    <div id="monthly_maintenance_cost" class="modal fade default-modal edit-annual-insurance-cost-modal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                    <h4 class="modal-title">Monthly Management</h4>
                    <a class="font-red-rubine monthly_maintenance_cost_cancel_button" data-dismiss="modal" aria-label="Close">
                            <i class="jv-icon jv-close"></i>
                    </a>
                </div>
                <form class="form-horizontal editMaintenanceCostValue" role="form" id="editMaintenanceCostValue" action="/vehicles/maintenanceCost" method="POST" novalidate>
                    <input type="hidden" class="vehicle_id" name="vehicle_id" value="{{$vehicle->id}}"/>
                    <input type="hidden" name="current_maintenance_cost" value="{{$vehicle->maintenance_cost}}"/>
                    <div class="modal-body repeater">
                        @include('_partials.vehicles.maintenance_cost')
                    </div>

                    <div class="modal-footer">
                        <div class="btn-group pull-left width100">
                            <button type="button" class="btn white-btn btn-padding col-md-6 monthly_maintenance_cost_cancel_button" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn red-rubine btn-padding col-md-6 submit-button monthly_maintenance_cost_edit">Save</button>
                        </div>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>

    <!-- monthly insurance cost modal -->
    <div id="edit_monthly_insurance_cost" class="modal fade default-modal edit-annual-insurance-cost-modal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                    <h4 class="modal-title">Monthly Insurance</h4>
                    <a class="font-red-rubine edit_insurance_cancle_button" data-dismiss="modal" aria-label="Close">
                            <i class="jv-icon jv-close"></i>
                    </a>
                </div>
                <form class="form-horizontal editInsuranceCostValue create-monthly-insurance" role="form" id="editInsuranceCostValue">
                    <input type="hidden" name="vehicle_insurance_cost" id="vehicle_insurance_cost" value="{{$vehicle->insurance_cost}}"/>
                    <div class="modal-body repeater">
                        @include('_partials.vehicles.edit_monthly_insurance_cost')
                    </div>

                    <div class="modal-footer">
                        <div class="btn-group pull-left width100">
                            <button type="button" class="btn white-btn btn-padding col-md-6 edit_insurance_cancle_button edit-monthly-cost-cancle-button" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn red-rubine btn-padding col-md-6 submit-button edit-insurance-cost-update">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="edit_monthly_telematics_cost" class="modal fade default-modal edit-annual-telematics-cost-modal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                    <h4 class="modal-title">Monthly Telematics</h4>
                    <a class="font-red-rubine edit_telematics_cancle_button" data-dismiss="modal" aria-label="Close">
                            <i class="jv-icon jv-close"></i>
                    </a>
                </div>
                <form class="form-horizontal editTelematicsCostValue create-monthly-telematics" id="editTelematicsCostValue">
                    <input type="hidden" name="vehicle_telematics_cost" id="vehicle_telematics_cost" value="{{$vehicle->telematics_cost}}"/>
                    <div class="modal-body repeater create-monthly-telematics">
                        @include('_partials.vehicles.edit_monthly_telematics')
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group pull-left width100">
                            <button type="button" class="btn white-btn btn-padding col-md-6 edit_telematics_cancle_button" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn red-rubine btn-padding col-md-6 submit-button edit_telematics_cost_update">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- monthly lease cost modal -->
    <div id="monthly_lease_cost_modal" class="modal fade default-modal edit-annual-insurance-cost-modal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                    <h4 class="modal-title">Monthly Hire</h4>
                    <a class="font-red-rubine monthly_lease_cost_cancel_button" data-dismiss="modal" aria-label="Close">
                            <i class="jv-icon jv-close"></i>
                    </a>
                </div>
                <form class="form-horizontal editLeaseCostValue" role="form" id="editLeaseCostValue" action="/vehicles/editLeaseCost" method="POST" novalidate>
                    <input type="hidden" class="lease_vehicle_id" name="lease_vehicle_id" value="{{$vehicle->id}}"/>
                    <input type="hidden" name="current_lease_cost" value="{{$vehicle->lease_cost}}"/>
                    <div class="modal-body repeater">
                        @include('_partials.vehicles.lease_cost')
                    </div>

                    <div class="modal-footer">
                        <div class="btn-group pull-left width100">
                            <button type="button" class="btn white-btn btn-padding col-md-6 monthly_lease_cost_cancel_button" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn red-rubine btn-padding col-md-6 submit-button monthly_lease_cost_edit">Save</button>
                        </div>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
    <div id="edit_depreciation_cost" class="modal fade default-modal edit-depreciation-cost-modal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                    <h4 class="modal-title">Monthly Depreciation</h4>
                    <a class="font-red-rubine edit_depreciation_cost_cancle_button" data-dismiss="modal" aria-label="Close">
                            <i class="jv-icon jv-close"></i>
                    </a>
                </div>
                <form class="form-horizontal editDepreciationCostValue create-monthly-depreciation" role="form" id="editDepreciationCostValue">
                    <input type="hidden" name="vehicle_depreciation_cost" id="vehicle_depreciation_cost" value="{{$vehicle->monthly_depreciation_cost}}"/>
                    <div class="modal-body repeater create-depreciation-cost">
                        @include('_partials.vehicles.edit_depreciation_cost')
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group pull-left width100">
                            <button type="button" class="btn white-btn btn-padding col-md-6 edit_depreciation_cost_cancle_button" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn red-rubine btn-padding col-md-6 submit-button edit_depreciation_cost_update">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-8">

            <div class="page-toolbar">
                <a class="btn red-rubine btn-plain hidden-print" href="#summary">Summary
                </a>
                <a class="btn red-rubine btn-plain hidden-print" href="#administration">Administration
                </a>
                <a class="btn red-rubine btn-plain hidden-print" href="#assignment">Assignment
                </a>
                <a class="btn red-rubine btn-plain hidden-print" href="#telematics">Telematics
                </a>
                <a class="btn red-rubine btn-plain hidden-print" href="#planning">Planning
                </a>
                @if(setting('is_fleetcost_enabled'))
                    <a class="btn red-rubine btn-plain hidden-print" href="#costs">Costs
                    </a>
                @endif
                <a class="btn red-rubine btn-plain hidden-print" href="#documents">Documents
                </a>
            </div>

            <div class="portlet-body form ipad_edit_form">
                <!-- BEGIN FORM-->
                {!! BootForm::openHorizontal(['md' => [3, 9]])->addClass('form-bordered form-validation form-label-center-fix')->action('/vehicles/update/'.$vehicle->id)->id('saveVehicle')->post() !!}
                    {!! BootForm::bind($vehicle) !!}
                    @include('_partials.vehicles.form', ['from' => 'edit'])
                    <input type="hidden" name="vehicle_type_usage" class="vehicle_type_usage" value="{{ $vehicleType->usage_type }}">
                    <input type="hidden" name="usage_override_flag" class="usage_override_flag" value="{{ $vehicle->usage_type == null ? 0 : 1 }}">
                {!! BootForm::close() !!}
                <!-- END FORM-->

                <!-- Manual Cost Adjustment -->
                <div id="vehicle_manual_cost_adjustment" class="modal fade default-modal" tabindex="-1" data-keyboard="false" data-backdrop="static" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                                <h4 class="modal-title">Manual Cost Adjustment</h4>
                                <a class="font-red-rubine manualCostCancle" data-dismiss="modal" aria-label="Close">
                                    <i class="jv-icon jv-close"></i>
                                </a>
                            </div>
                            <div class="modal-body">
                                 @include('_partials.vehicles.vehicle_manual_cost_adjustment')
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Fuel Use -->
                <div class="modal fade" id="vehicle_fuel_use" tabindex="-1" data-keyboard="false" role="dialog" aria-labelledby="defect-info-modal-title" data-backdrop="static">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                                <h4 class="modal-title">Fuel Used</h4>
                                <a class="font-red-rubine vehicleFuelCancle" data-dismiss="modal" aria-label="Close">
                                        <i class="jv-icon jv-close"></i>
                                </a>
                            </div>
                            <div class="modal-body">
                                 @include('_partials.vehicles.vehicle_fuel_use')
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Oil Use -->
                <div class="modal fade" id="vehicle_oil_use" tabindex="-1" data-keyboard="false" role="dialog" aria-labelledby="defect-info-modal-title">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                                <h4 class="modal-title">Oil Used</h4>
                                <a class="font-red-rubine vehicleOilCancle" data-dismiss="modal" aria-label="Close">
                                        <i class="jv-icon jv-close"></i>
                                </a>
                            </div>
                            <div class="modal-body">
                                 @include('_partials.vehicles.vehicle_oil_use')
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle AdBlue Use -->
                <div class="modal fade" id="vehicle_adblue_use" tabindex="-1" data-keyboard="false" role="dialog" aria-labelledby="defect-info-modal-title">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                                <h4 class="modal-title">AdBlue Used</h4>
                                <a class="font-red-rubine vehicleAdbluecancle" data-dismiss="modal" aria-label="Close">
                                        <i class="jv-icon jv-close"></i>
                                </a>
                            </div>
                            <div class="modal-body">
                                 @include('_partials.vehicles.vehicle_adblue_use')
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Screen Wash -->
                <div class="modal fade" id="vehicle_screen_wash_use" tabindex="-1" data-keyboard="false" role="dialog" aria-labelledby="defect-info-modal-title">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                                <h4 class="modal-title">Screen Wash Used</h4>
                                <a class="font-red-rubine vehicleScreenWashCancle" data-dismiss="modal" aria-label="Close">
                                        <i class="jv-icon jv-close"></i>
                                </a>
                            </div>
                            <div class="modal-body">
                                 @include('_partials.vehicles.vehicle_screen_wash_use')
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Fleet Livery Wash -->
                <div class="modal fade" id="vehicle_fleet_livery_wash" tabindex="-1" data-keyboard="false" role="dialog" aria-labelledby="defect-info-modal-title">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                                <h4 class="modal-title">Fleet Livery Wash</h4>
                                <a class="font-red-rubine vehicleFleetLiveryCancle" data-dismiss="modal" aria-label="Close">
                                        <i class="jv-icon jv-close"></i>
                                </a>
                            </div>
                            <div class="modal-body">
                                 @include('_partials.vehicles.vehicle_fleet_livery_wash')
                            </div>
                        </div>
                    </div>
                </div>

                <div id="monthly_insurance_cost_history" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                                <h4 class="modal-title">Insurance Cost History</h4>
                                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                                        <i class="jv-icon jv-close"></i>
                                </a>
                            </div>
                            <div class="modal-body">
                                @include('_partials.vehicles.monthly_insurance_cost_history')
                            </div>
                        </div>
                    </div>
                </div>

                <div id="monthly_telematics_cost_history" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                                <h4 class="modal-title">Telematics Cost History</h4>
                                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                                        <i class="jv-icon jv-close"></i>
                                </a>
                            </div>
                            <div class="modal-body">
                                @include('_partials.vehicles.monthly_telematics_cost_history')
                            </div>
                        </div>
                    </div>
                </div>

                <div id="depreciation_cost_history" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                                <h4 class="modal-title">Depreciation Cost History</h4>
                                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                                        <i class="jv-icon jv-close"></i>
                                </a>
                            </div>
                            <div class="modal-body">
                                @include('_partials.vehicles.depreciation_cost_history')
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="portlet box">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">
                        Documents
                    </div>
                </div>
                <div class="portlet-body">
                    {!! BootForm::open()->action('/vehicles/get_store_docs/'.$vehicle->id)->id('updateVehicleDocument')->multipart() !!}
                    <div class="fileupload-buttonbar">
                        <!-- The fileinput-button span is used to style the file input field as button -->
                        <span class="btn fileinput-button red-rubine btn-padding">
                            <i class="jv-icon jv-plus"></i>
                            Add files
                            <input type="file" name="files[]" multiple="" accept=".gif, .jpg, .jpeg, .png, .doc, .pdf">
                        </span>
                        <!-- <button type="submit" class="btn blue start"><i class="fa fa-upload"></i>Start upload</button>
                        <button type="reset" class="btn warning cancel grey-gallery">Cancel upload</button> -->
                        <!-- <button type="button" class="btn btn-danger delete"><i class="fa fa-trash"></i>Delete</button>
                        <input type="checkbox" class="toggle"> -->
                    </div>
                    <span class="help-block">(*.gif, *.jpg, *.jpeg, *.png, *.doc, *.pdf)</span>

                    <!-- The table listing the files available for upload/download -->
                    <table role="presentation" class="table table-striped clearfix" id="upload-media-table">
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
                                <td>
                                    <input type="text" placeholder="Enter document name" id="caption" name="name" class="form-control"/>
                                </td>
                                <td style="white-space: nowrap">
                                    <p class="size">Processing...</p>
                                    <div class="progress progress-striped progress-bar-red-rubine active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="position: relative; top:5px;">
                                        <div class="progress-bar progress-bar-red-rubine" style="width:0%;"></div>
                                    </div>
                                </td>
                                <td style="word-break: break-all;">
                                    <p class="name">{%=file.name%}</p>
                                    <strong class="error text-danger"></strong>
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
                            <tr class="template-download fade">
                                <td>
                                    <span class="preview">
                                        {% if (file.type.substr(0,5) === 'image') { %}
                                            <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.url%}" style="max-width: 80px; height: 45px;"></a>
                                        {% } else if (file.type === 'application/pdf') { %}
                                            <img src="/img/document_icons/pdf.png" style="height: 40px;">
                                        {% } else if (file.type === 'application/msword' || file.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') { %}
                                            <img src="/img/document_icons/doc.png" style="height: 45px;">
                                        {% } else if (file.type === 'text/plain') { %}
                                            <img src="/img/document_icons/text.png" style="height: 45px;">
                                        {% } else if (file.type === 'application/mspowerpoint' || file.type === 'application/powerpoint' || file.type === 'application/vnd.ms-powerpoint' || file.type === 'application/x-mspowerpoint' || file.type === 'application/vnd.openxmlformats-officedocument.presentationml.presentation') { %}
                                            <img src="/img/document_icons/ppt.png" style="height: 45px;">
                                        {% } else if (file.type === 'application/excel' || file.type === 'application/vnd.ms-excel' || file.type === 'application/x-excel' || file.type === 'application/x-msexcel') { %}
                                            <img src="/img/document_icons/xls.png" style="height: 45px;">
                                        {% } else { %}
                                            <img src="/img/document_icons/generic.png" style="max-width: 80px; max-height: 45px;">
                                        {% } %}
                                    </span>
                                </td>
                                <td style="word-break: break-all;">
                                    <p class="name">
                                        {% if (file.url) { %}
                                            <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                                        {% } else { %}
                                            <span>{%=file.name%}</span>
                                        {% } %}
                                    </p>
                                    {% if (file.error) { %}
                                        <div><span class="label label-danger">Error</span> {%=file.error%}</div>
                                    {% } %}
                                </td>
                                <td style="white-space: nowrap">
                                    <p class="size">{%=file.size%}</p>
                                </td>
                                <td><p>{%=file.created%}</p></td>
                                <td style="text-align: right">
                                    {% if (file.deleteUrl) { %}
                                    <div class="delete-wrapper">
                                        {% if (file.deleteUrl == 'Maintenance') { %}
                                            <span class="btn doc-delete-btn1 disabled btn-height" >
                                                <i class="jv-icon jv-dustbin normal-font"></i>
                                            </span>
                                            <button class="disabled" style="display:none;" data-type="{%=file.deleteType%}">
                                            <i class="jv-icon jv-dustbin normal-font"></i>
                                            <!-- <span>Delete</span> -->
                                            </button>
                                        {% } else { %}
                                            <span class="btn doc-delete-btn1 btn-height" >
                                                <i class="jv-icon jv-dustbin normal-font"></i>
                                                <!-- <span>Delete</span> -->
                                            </span>
                                            <button class="delete" style="display:none;" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                                            <i class="jv-icon jv-dustbin normal-font"></i>
                                            <!-- <span>Delete</span> -->
                                            </button>
                                        {% } %}
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
                    {!! BootForm::close() !!}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="portlet box vehicle_type_align">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">
                        Vehicle Type Information
                    </div>
                </div>
                <div class="portlet-body vehicle-information form">
                </div>
            </div>
        </div>
    </div>
@include('_partials.vehicles.repeater_delete_modals')
<div id="add_vehicle_repair_location" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <form class="form-horizontal" role="form" id="vehicleRepairLocation">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                    <h4 class="modal-title">Add Repair/Maintenance Location</h4>
                    <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                        <i class="jv-icon jv-close"></i>
                    </a>
                </div>
            <div class="modal-body">
                <div class="form-group row">
                    <label for="location_name" class="col-md-2 control-label">Location*:</label>
                    <div class="col-md-10 error-class" id="vehicle_location_name">
                        <input type="text" name="location_name" id="location_name" class="form-control" required="true">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="col-md-offset-2 col-md-8 ">
                    <div class="btn-group pull-left width100">
                        <button id="addVehicleCancle" type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                        {{-- <button id="fleetCostAreaFormSave" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Save</button>   --}}
                        <button id="addVehicleRepairLocationSave" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Save</button>
                    </div>
                </div>
            </div>
        </form>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
@endsection