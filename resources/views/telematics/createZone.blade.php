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
@section('styles')
<style>
    #zone_map_canvas {
        overflow-anchor:none;
    }

    #zone_apply_to:focus {
        color:#fff;
    }

</style>
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
    {!! Breadcrumbs::render('telematics_addzone') !!}
</div>
<?php
    $columnSizes = [
      'md' => [4, 4]
    ];
    $url= '/telematics/storeZone';
    $cancelUrl= '/telematics';
    ?>
<div class="row">
    {!! BootForm::openHorizontal($columnSizes)->addClass('form-bordered form-validation form')->id('createZoneForm')->action($url)->multipart()->post() !!}
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-7 col-lg-8">
                <div class="portlet box">
                    <div class="portlet-title bg-red-rubine">
                        <div class="caption">
                            Add New Zone
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
                            <input type="hidden" id="zone_bounds" name="zone_bounds" value="">
                            {!! BootForm::text('Name of zone*:', 'name') !!}
                            {{-- {!! BootForm::select('Region*:', 'region')->options($vehicleRegions)->addClass('select2me region') !!} --}}
                            {{-- {!! BootForm::select('Tracking*:', 'is_tracking_inside')->options($zoneTracking)->addClass('select2me is_tracking_inside') !!}
                            <div class="form-group">
                                <label class="col-md-4 control-label">Apply to:</label>
                                <div class="col-md-2">
                                    <a id="zone_apply_to" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center zone_apply_to" style="margin-right: 0" data-target="#zone_apply_to_modal" data-toggle="modal" href="#zone_apply_to_modal">
                                    Select
                                    </a>
                                </div>
                            </div> --}}

                            {!! BootForm::select('Zone tracking*:', 'alert_setting')->options($alertSetting)->addClass('select2me js-alert-setting') !!}

                            <div class="form-group">
                                <label class="col-md-4 control-label" for="location">Add location:</label>
                                <div class="col-md-4">
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <input type="text" name="location" id="location" class="form-control" placeholder="Enter postcode">
                                        </div>
                                        <div class="desboard_thumbnail">
                                            <a id="change_map_view" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center" style="margin-right: 0"><i class="jv-icon jv-search icon-big"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="has-error">
                                        <span class="addLocationErr" style="display:none">Please enter a valid postcode</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="desboard_thumbnail d-none" id="remove_polygon_shape_hide">
                                        <a id="remove_polygon_shape" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center margin-left-15">
                                            Remove zone
                                        </a>
                                    </div>
                                </div>
                            </div>

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
                    <div class="portlet-body form-bordered form-label-center-fix">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Zone status:</label>
                            <div class="col-md-4">
                                <label class="toggle_switch margin-top-10">
                                    <input type="checkbox" id="status" data-toggle="toggle" data-on="Active" data-off="In-active" name="status">
                                </label>
                            </div>
                        </div>
                        {{-- <div class="form-group">
                            <label class="col-md-4 control-label">Alert status:</label>
                            <div class="col-md-4">
                                <label class="toggle_switch">
                                    <input type="checkbox" id="alert_status" data-toggle="toggle" data-on="Active" data-off="In-active" name="alert_status">
                                </label>
                            </div>
                        </div> --}}
                        {{-- <div class="form-group">
                            <label class="col-md-4 control-label toggle_switch" for="alert_type">Alert type:</label>
                            <div class="col-md-8">
                                <select class="form-control select2me alert_type" id="alert_type" name="alert_type">
                                    @foreach ($alertType as $key => $type)
                                        <option value="{{ $key }}">{{ $type}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}
                       
                        {{-- <div class="form-group hidden" id="alert_interval_div">
                            <label class="col-md-4 control-label toggle_switch" for="alert_interval">Alert interval:</label>
                            <div class="col-md-8">
                                <select class="form-control select2me alert_interval" id="alert_interval" name="alert_interval">
                                    @foreach ($alertInterval as $key => $interval)
                                        <option value="{{ $key }}">{{ $interval}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-12">
                <!--<div class="portlet box mb-0">
                    <div class="portlet-title bg-red-rubine">
                        <div class="caption">
                            Add Geo-Fencing
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="row">
                            <div class="col-md-7 col-lg-8">
                                <div class="form-body form-bordered form-label-center-fix form-add-vehicle-profile">
                                    {{-- 
                                    <div class="col-md-8">
                                        <input type="text" name="location" id="location" class="form-control">
                                    </div>
                                    <div class="desboard_thumbnail">
                                        <a id="change_map_view" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center" style="margin-right: 0">
                                        Go
                                        </a>
                                    </div>
                                    <div class="desboard_thumbnail">
                                        <a id="remove_polygon_shape" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center" style="margin-right: 0">
                                        Remove shape
                                        </a>
                                    </div>
                                    --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>-->
                <div class="form-body form-bordered form-label-center-fix position-relative">
                    <div id="zone_map_canvas" style="width: 100%;height: 600px;">
                        {{-- <input id="polygonbtn" type="button" value="Draw Polygon"/> --}}
                    </div>
                    {{-- <input id="polygonbtn" type="button" value="Draw Polygon"/> --}}
                    <button class="btn btn-blue-color draw_button" id="polygonbtn" type="button" style="display:none">Draw zone</button>
                    <input id="removepolygonbtn" type="button" value="Remove Polygon" class="d-none" />
                </div>
            </div>
            <div class="col-md-offset-3 col-md-6">
                <div class="btn-group pull-left width100">
                    <a href="{{ $cancelUrl }}" type="button" class="btn white-btn btn-padding col-md-6">Cancel</a>
                    <button type="submit" class="btn red-rubine btn-padding submit-button col-md-6" id="submit-button">Save</button>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="zoneApplyToType" id='zoneApplyToType' />
    <input type="hidden" name="zoneApplyToDetails" id='zoneApplyToDetails' />
    {!! BootForm::close() !!}
    <div id="zone_apply_to_modal" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                @include('_partials.telematics.zone_apply_to_form')              
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
</div>
@endsection
@push('scripts')
<script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/repeater.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/types.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/datatable/select2.min.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/jasny-bootstrap.min.js') }}" type="text/javascript"></script>
@endpush
