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
    <div class="page-bar">
        {!! Breadcrumbs::render('vehicle_add') !!}
    </div>
    <!-- Modal to Confirm usage override -->
    <div id="usage_override" class="modal modal-fix  fade modal-overflow in" tabindex="-1" data-backdrop="static" data-keyboard="false" aria-hidden="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><a class="bootbox-close-button font-red-rubine close-icon-color" data-dismiss="modal" aria-hidden="true"><i class="jv-icon jv-close"></i></a><h4 class="modal-title">Confirmation</h4></div>
                <div class="modal-body"><div class="bootbox-body">Do you want to override the default vehicle profile 'usage' value?</div></div>
                <div class="modal-footer">
                    <button type="button" id="usage_override_cancel" class="btn btn white-btn btn-padding white-btn-border col-md-6 pull-left" data-dismiss="modal">Cancel</button>
                    <button type="button" id="usage_override_btn" class="btn btn red-rubine btn-padding white-btn-border col-md-6">Yes</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="portlet box">
                <!-- <div class="portlet-title bg-red-rubine">
                    <div class="caption">
                        Create New Vehicle
                    </div>
                </div> -->
                <div class="portlet-body form ipad_edit_form">
                    <!-- BEGIN FORM-->
                    {!! BootForm::openHorizontal(['md' => [3, 9]])->addClass('form-bordered form-validation form-label-center-fix')->action('/vehicles/store')->id('saveVehicle')->post() !!}
                        {{ BootForm::bind($vehicle) }}
                    <input type="hidden" name="vehicle_type_usage" class="vehicle_type_usage" value="{{ $vehicleType->usage_type }}">
                    <input type="hidden" name="usage_override_flag" class="usage_override_flag" value="{{ $vehicle->usage_type == null ? 0 : 1 }}">
                    <input type="hidden" name="monthly_lease_cost_json" id="create_monthly_lease_cost_json"  class="" value="{{ $vehicle->lease_cost == null ? $vehicle->lease_cost : '' }}">
                    <input type="hidden" name="monthly_maintenance_cost_json" id="create_monthly_maintenance_cost_json"  class="" value="{{ $vehicle->maintenance_cost == null ? $vehicle->maintenance_cost : '' }}">
                    <input type="hidden" name="monthly_depreciation_cost_json" id="create_monthly_depreciation_cost_json"  class="" value="{{ $vehicle->monthly_depreciation_cost == null ? $vehicle->monthly_depreciation_cost : '' }}">
                    <input type="hidden" name="insurance_cost_json" id="create_insurance_cost_json"  class="" value="{{ $vehicle->insurance_cost == null ? $vehicle->monthly_depreciation_cost : '' }}">
                    <input type="hidden" name="telematics_cost_json" id="create_telematics_cost_json"  class="" value="{{ $vehicle->telematics_cost == null ? $vehicle->monthly_depreciation_cost : '' }}">
                        @include('_partials.vehicles.form', ['from' => 'add'])


                        <!--  monthly maintenance cost modal -->
                        <div id="monthly_maintenance_cost" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                                        <h4 class="modal-title">Monthly Management</h4>
                                        <a class="font-red-rubine monthly_maintenance_cost_cancel_button" data-dismiss="modal" aria-label="Close">
                                                <i class="jv-icon jv-close"></i>
                                        </a>
                                    </div>
                                    <div class="modal-body repeater">
                                        @include('_partials.vehicles.maintenance_cost')
                                    </div>

                                    <div class="modal-footer">
                                        <div class="btn-group pull-left width100">
                                            <input type="hidden" name="saveMonthlyCostFlag" class="saveMonthlyCostFlag"/>
                                            <button type="button" class="btn white-btn btn-padding col-md-6 monthly_maintenance_cost_cancel_button" data-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn red-rubine btn-padding col-md-6 submit-button monthly_maintenance_cost_create">Save</button>
                                        </div>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div>

                        <div id="monthly_lease_cost_modal" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                                        <h4 class="modal-title">Monthly Hire</h4>
                                        <a class="font-red-rubine monthly_lease_cost_cancel_button" data-dismiss="modal" aria-label="Close">
                                                <i class="jv-icon jv-close"></i>
                                        </a>
                                    </div>
                                    <div class="modal-body repeater">
                                        @include('_partials.vehicles.lease_cost')
                                    </div>

                                    <div class="modal-footer">
                                        <div class="btn-group pull-left width100">
                                            <input type="hidden" name="saveLeaseCostFlag" class="saveLeaseCostFlag"/>
                                            <button type="button" class="btn white-btn btn-padding col-md-6 monthly_lease_cost_cancel_button" data-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn red-rubine btn-padding col-md-6 submit-button monthly_lease_cost_create">Save</button>
                                        </div>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div>

                        <div id="edit_monthly_insurance_cost" class="modal fade default-modal edit-annual-insurance-cost-modal" tabindex="-1" role="dialog" data-backdrop="static">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                                        <h4 class="modal-title">Monthly Insurance</h4>
                                        <a class="font-red-rubine edit_insurance_cancle_button" data-dismiss="modal" aria-label="Close">
                                                <i class="jv-icon jv-close"></i>
                                        </a>
                                    </div>
                                    <div class="modal-body repeater create-monthly-insurance">
                                        @include('_partials.vehicles.edit_monthly_insurance_cost')
                                    </div>
                                    <div class="modal-footer">
                                        <div class="btn-group pull-left width100">
                                            <button type="button" class="btn white-btn btn-padding col-md-6 edit_insurance_cancle_button" data-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn red-rubine btn-padding col-md-6 submit-button monthly_insurance_cost_create">Save</button>
                                        </div>
                                    </div>
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
                                    <div class="modal-body repeater create-monthly-telematics">
                                        @include('_partials.vehicles.edit_monthly_telematics')
                                    </div>
                                    <div class="modal-footer">
                                        <div class="btn-group pull-left width100">
                                            <button type="button" class="btn white-btn btn-padding col-md-6 edit_telematics_cancle_button" data-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn red-rubine btn-padding col-md-6 submit-button monthly_telematics_cost_create">Save</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                    <div class="modal-body repeater create-depreciation-cost">
                                        @include('_partials.vehicles.edit_depreciation_cost')
                                    </div>
                                    <div class="modal-footer">
                                        <div class="btn-group pull-left width100">
                                            <input type="hidden" name="saveDeprectionCostFlag" class="saveDeprectionCostFlag"/>
                                            <button type="button" class="btn white-btn btn-padding col-md-6 edit_depreciation_cost_cancle_button" data-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn red-rubine btn-padding col-md-6 submit-button monthly_depreciation_cost_create">Save</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    {!! BootForm::close() !!}
                    <!-- END FORM-->
                    @include('_partials.vehicles.repeater_delete_modals')
                    <!-- Manual Cost Adjustment -->
                    <div id="vehicle_manual_cost_adjustment" class="modal fade default-modal vehicle_manual_cost_adjustment" tabindex="-1" data-keyboard="false" data-backdrop="static" role="dialog">
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
                    <div class="modal fade" id="vehicle_oil_use" tabindex="-1" data-keyboard="false" role="dialog" data-backdrop="static" aria-labelledby="defect-info-modal-title">
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
                    <div class="modal fade" id="vehicle_adblue_use" tabindex="-1" data-keyboard="false" role="dialog" data-backdrop="static" aria-labelledby="defect-info-modal-title">
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

                    <!--  Vehicle Screen Wash -->
                    <div class="modal fade" id="vehicle_screen_wash_use" tabindex="-1" data-keyboard="false" role="dialog" data-backdrop="static" aria-labelledby="defect-info-modal-title">
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
                    <div class="modal fade" id="vehicle_fleet_livery_wash" tabindex="-1" data-keyboard="false" role="dialog" data-backdrop="static" aria-labelledby="defect-info-modal-title">
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
                </div>
            </div>
            <div class="portlet box" style="cursor: not-allowed;">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">
                        Vehicle Documents
                    </div>
                </div>
                <div class="portlet-body">
                    <span class="btn fileinput-button red-rubine btn-padding disabled">
                        <i class="jv-icon jv-plus"></i>
                        Add files
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="portlet box">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">
                        Vehicle Type Information
                    </div>
                </div>
                <div class="portlet-body vehicle-information">
                </div>
            </div>
            <div class="well well-sm text-muted">
                <button class="btn btn-icon-only btn-circle small"><i class="fa fa-info"></i></button> &nbsp;&nbsp;Click 'Save' below before adding documents.
            </div>
        </div>
    </div>

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
