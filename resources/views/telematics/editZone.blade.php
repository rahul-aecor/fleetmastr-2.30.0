@extends('layouts.default')
@section('plugin-styles')
<link href="{{ elixir('css/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
<link href="{{ elixir('css/bootstrap-modal/bootstrap-modal-bs3patch.css') }}" rel="stylesheet" type="text/css"/>
<link href="{{ elixir('css/bootstrap-modal/bootstrap-modal.css') }}" rel="stylesheet" type="text/css"/>
@endsection
@section('scripts')
<script src="{{ elixir('js/telematics_zones.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function(){
        initZonemap();
    });
</script>
@endsection
@section('plugin-scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_KEY') }}&libraries=geometry,drawing&v=weekly"></script>
<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
<script src="{{ elixir('js/bootstrap-modal/bootstrap-modal.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/bootstrap-modal/bootstrap-modalmanager.js') }}" type="text/javascript"></script>
@endsection
@section('content')
<div class="page-title-inner">
    <h3 class="page-title">{{ $title }}</h3>
    <br>
</div>
<div class="page-bar">
    {!! Breadcrumbs::render('telematics_editzone') !!}
</div>
<?php
    $columnSizes = [
      'md' => [4, 4]
    ];
    $url= '/telematics/updateZone';
    $cancelUrl= '/telematics';
    ?>
<div class="row">
    {!! BootForm::openHorizontal($columnSizes)->addClass('form-bordered form-validation form')->id('editZoneForm')->action($url)->multipart()->post() !!}
    {!! BootForm::bind($zone) !!}
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-7 col-lg-8">
                <div class="portlet box">
                    <div class="portlet-title bg-red-rubine">
                        <div class="caption">
                            Edit Zone
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="form-bordered form-label-center-fix form-add-vehicle-profile">
                            <div class="alert alert-danger display-hide  bg-red-rubine">
                                <button class="close" data-close="alert"></button>
                                <!-- You have some form errors. Please check below. -->
                                Please complete the errors highlighted below.
                            </div>
                            <div class="alert alert-success display-hide">
                                <button class="close" data-close="alert"></button>
                                Your form validation is successful!
                            </div>
                            <input type="hidden" id="zone_bounds" name="zone_bounds" value="{{ $zoneBoundsJson}}">
                            {!! BootForm::hidden('id', 'id') !!}
                            {!! BootForm::text('Name of zone*:', 'name') !!}
                            {!! BootForm::select('Zone tracking*:', 'alert_setting')->options($alertSetting)->addClass('select2me js-alert-setting') !!}
                            <br/>
                         </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5 col-lg-4">
                <div class="portlet box vehicle--profile">
                    <div class="portlet-title bg-red-rubine">
                        <div class="caption">
                            Add Zone Settings
                        </div>
                    </div>
                    <!-- <div class="portlet-body"> -->
                        <div class="portlet-body form-bordered form-label-center-fix">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Zone status:</label>
                                <div class="col-md-4">
                                    <label class="toggle_switch margin-top-10">
                                    <input type="checkbox" id="status" data-toggle="toggle" data-on="Active" data-off="In-active" name="status" {{ $zone->zone_status == '1' ? 'checked' : '' }}>
                                    </label>
                                </div>
                            </div>
                           
                          
                        </div>
                    <!-- </div> -->
                </div>
            </div>
            <div class="col-md-12 col-lg-12">
                <div class="portlet box mb-0">
                    <div class="portlet-title bg-red-rubine">
                        <div class="caption">
                            Add location:
                        </div>
                    </div>
                    <!-- <div class="portlet-body form">
                        <div class="row">
                            <div class="col-md-7 col-lg-8">
                                <div class="form-body form-bordered form-label-center-fix form-add-vehicle-profile">
                                    <div class="form-group">
                                        <label class="col-md-4 control-label" for="location">Add location:</label>
                                        <div class="col-md-4">
                                            <div class="d-flex justify-content-between">
                                                <div class="flex-grow-1 margin-right-10">
                                                    <input type="text" name="location" id="location" class="form-control" placeholder="Enter postcode">
                                                </div>
                                                <div class="desboard_thumbnail">
                                                    <a id="change_map_view" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center" style="margin-right: 0">
                                                    Go
                                                    </a>
                                                </div>
                                                
                                                <div class="desboard_thumbnail d-none"
                                                    id="remove_polygon_shape_hide">
                                                    <a id="remove_polygon_shape" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center margin-left-15">
                                                    Remove zone
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="has-error">
                                                <span class="help-block addLocationErr" style="display:none">Please enter a valid postcode</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> -->
                </div>
                <div class="form-body form-bordered form-label-center-fix">
                    <div id="zone_map_canvas" style="width: 100%;height: 600px;"></div>
                </div>
            </div>
            <div class="col-md-offset-3 col-md-6">
                <div class="btn-group pull-left width100">
                    <a href="{{ $cancelUrl }}" type="button" class="btn white-btn btn-padding col-md-6">Cancel</a>
                    <button type="submit" class="btn red-rubine btn-padding submit-button col-md-6" id="edit-zone-submit">Save</button>
                </div>
            </div>
        </div>
    </div>
    {!! BootForm::close() !!}
</div>
@endsection

@push('scripts')
<script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/repeater.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/types.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/datatable/select2.min.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/jasny-bootstrap.min.js') }}" type="text/javascript"></script>
@endpush