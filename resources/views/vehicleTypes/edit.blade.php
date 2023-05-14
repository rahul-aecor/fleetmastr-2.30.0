@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection
@section('content')
<div class="page-title-inner">
    <h3 class="page-title">{{ $title }}</h3><br>
</div>
<div class="page-bar">
        {!! Breadcrumbs::render('profile_details_edit', $vehicleType->id) !!}
    </div>
{{-- <div class="modal-header bg-red-rubine">
    <h4 class="modal-title">Vehicle Type Editor</h4>
</div> --}}
<?php
    $columnSizes = [
      'md' => [4, 8]
    ];
    $url= '/profiles/'.$vehicleType->id;
?>
<div class="row">
    
    {!! BootForm::openHorizontal($columnSizes)->addClass('form-bordered form-validation')->id('editType')->action($url)->multipart()->put() !!}
    <input type="hidden" name="fromPage" class="fromPage" value={{$fromPage}}>
    {!! BootForm::bind($vehicleType) !!}
    <div class="col-md-7 col-lg-8">
        <div class="portlet box">
            <div class="portlet-title bg-red-rubine">
                <div class="caption">
                    Edit Vehicle Profile
                </div>
            </div>
            <div class="portlet-body">
                <div class="portlet-body form">
                        <div class="form-body form-bordered form-label-center-fix">
                            <div class="alert alert-danger display-hide  bg-red-rubine">
                                <button class="close" data-close="alert"></button>
                                <!-- You have some form errors. Please check below. -->
                                Please complete the errors highlighted below.
                            </div>
                            <div class="alert alert-success display-hide">
                                <button class="close" data-close="alert"></button>
                                Your form validation is successful!
                            </div>
                            {!! BootForm::select('Profile status*:', 'profile_status')->options($profileStatus)->addClass('select2') !!}    
                            {!! BootForm::text('Type*:', 'vehicle_type') !!}
                            {!! BootForm::select('Category*:', 'vehicle_category')->options($vehicleCategoryList)->addClass('select2 checkPmi') !!}
                            {!! BootForm::select('Sub category*:', 'vehicle_subcategory')->options($vehicleSubCategoriesNonHGV)->addClass('select2') !!}
                            {!! BootForm::select('Odometer setting*:', 'odometer_setting')->options(config('config-variables.vehicle_type_odometer_setting'))->addClass('select2me') !!}
                            {!! BootForm::select('Usage*:', 'usage_type')->options($usageTypeList)->addClass('select2') !!}
                            {!! BootForm::text('Manufacturer*:', 'manufacturer') !!}                       
                            {!! BootForm::text('Model*:', 'model') !!}
                            {!! BootForm::text('Bodybuilder:', 'body_builder') !!}
                            {!! BootForm::text('Gross vehicle weight:', 'gross_vehicle_weight') !!}
                            {!! BootForm::text('Tyre size drive:', 'tyre_size_drive') !!}
                            {!! BootForm::text('Tyre size steer:', 'tyre_size_steer') !!}
                            {!! BootForm::text('Type pressure drive:', 'tyre_pressure_drive') !!}
                            {!! BootForm::text('Type pressure steer:', 'tyre_pressure_steer') !!}
                            {!! BootForm::text('Nut size:', 'nut_size') !!}
                            {!! BootForm::text('Re-torque:', 're_torque') !!}
                            {!! BootForm::text('Length (mm):', 'length') !!}
                            {!! BootForm::text('Width (mm):', 'width') !!}
                            {!! BootForm::text('Height (mm):', 'height') !!}
                            {!! BootForm::select('Fuel type*:', 'fuel_type')->options($fuelTypeList)->addClass('select2') !!}
                            {!! BootForm::select('Type of engine*:', 'engine_type')->options($engineTypeList)->addClass('select2')->placeholder('Select') !!}
                            <div class="form-group">
                                <input type="hidden" id="engineSizeMandatoryFlag" value="{{ $engineSizeMandatoryFlag }}">
                                <label class="col-md-4 control-label" for="engine_size">Engine size (cc):</label>
                                <div class="col-md-8">
                                    <input type="text" name="engine_size" id="engine_size" class="form-control" value="{{$vehicleType->engine_size}}">
                                </div>
                            </div>
                            {!! BootForm::select('Oil grade:', 'oil_grade')->options($oilGrade)->addClass('select2')  !!}
                            {!! BootForm::text('CO2 (g/km):', 'co2') !!}
                            <span class="co2_profile_type" id="errmsg"></span>

                            <div class="form-group d-flex align-items-center">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center h-100">
                                        <label for="vehicle_insurance_cost" class="control-label align-self-center pt-0 w-100">Monthly insurance cost per vehicle:</label>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="input-group">
                                        <span class="input-group-addon">&pound;</span>
                                        <input type="text" name="vehicle_insurance_cost" id="vehicle_insurance_cost" readonly class="form-control vehicle_insurance_cost" value="{{ is_numeric($currentMonthVehicleInsuranceCost) ? number_format($currentMonthVehicleInsuranceCost, 2) : $currentMonthVehicleInsuranceCost }} ">
                                    </div>
                                </div>
                                <div class="col-md-1 align-items-center d-flex justify-content-center">
                                    <a title="Details" class="btn btn-xs grey-gallery tras_btn" data-target="#vehicle_insurance_cost_history" data-toggle="modal" href="#vehicle_insurance_cost_history"><i class="jv-icon jv-find-doc icon-big"></i></a>
                                    <a title="Edit" class="btn btn-xs grey-gallery tras_btn" data-target="#monthly_vehicle_insurance_cost" href="#monthly_vehicle_insurance_cost" data-toggle="modal"><i class="jv-icon jv-edit icon-big"></i></a>
                                    {{-- <a title="Edit" class="btn btn-xs grey-gallery tras_btn js-insurance-edit-modal" javascript="void(0)"><i class="jv-icon jv-edit icon-big"></i></a> --}}
                                </div>
                            </div>

                            <div class="form-group d-flex align-items-center">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center h-100">
                                        <label for="vehicle_tax_cost" class="control-label align-self-center pt-0 w-100">Monthly vehicle tax:</label>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="input-group">
                                        <span class="input-group-addon">&pound;</span>
                                        <input type="text" name="vehicle_tax_cost" id="vehicle_tax_cost" readonly class="form-control vehicle_tax_cost vehicleTaxCurrentCost" value="{{ is_numeric($currentMonthVehicleTaxCost) ? number_format($currentMonthVehicleTaxCost, 2) : $currentMonthVehicleTaxCost }} ">
                                    </div>
                                </div>
                                <div class="col-md-1 align-items-center d-flex justify-content-center">
                                    <a title="Details" class="btn btn-xs grey-gallery tras_btn" data-target="#vehicle_tax_cost_history" data-toggle="modal" href="#vehicle_tax_cost_history"><i class="jv-icon jv-find-doc icon-big"></i></a>
                                    <a title="Edit" class="btn btn-xs grey-gallery tras_btn" data-target="#monthly_vehicle_tax_cost" href="#monthly_vehicle_tax_cost" data-toggle="modal"><i class="jv-icon jv-edit icon-big"></i></a>
                                </div>
                            </div>
                            {!! BootForm::select('ADR test interval:', 'adr_test_date')
                            ->options(config('config-variables.adr_test'))->addClass('select2') !!}
                            {!! BootForm::select('Compressor service interval:', 'compressor_service_interval')->options(config('config-variables.compressorServiceInterval'))->addClass('select2')  !!}
                            {!! BootForm::select('Invertor service interval:', 'invertor_service_interval')->options(config('config-variables.invertorServiceInterval'))->addClass('select2')  !!}
                            {!! BootForm::select('LOLER test interval:', 'loler_test_interval')->options(config('config-variables.loler_test_interval'))->addClass('select2')  !!}
                            <div class="js-pmi-interval"> 
                                {!! BootForm::select('PMI interval:', 'pmi_interval')->options(config('config-variables.pmiIntervalService'))->addClass('select2')  !!}
                            </div>
                            
                            {!! BootForm::select('PTO service interval:', 'pto_service_interval')->options(config('config-variables.ptoServiceInterval'))->addClass('select2')  !!}

                            {!! BootForm::select('Service interval type:', 'service_interval_type')
                            ->options(config('config-variables.service_inspection_type'))->addClass('select2') !!}

                            <div class="form-group js-service-interval {{ $vehicleType->service_interval_type == null ? 'hide' : '' }}">
                                <label class="col-md-4 control-label" for="service_inspection_interval">Service interval:</label>
                                <div class="col-md-8">
                                    <select class="form-control select2" id="service_inspection_interval" name="service_inspection_interval" placeholder="Select">
                                        @foreach (config('config-variables.serviceInspection') as $key => $value)
                                            <option value="{{ $key }}" {{ $key == $vehicleType->service_inspection_interval ? 'selected' : '' }}>{{ $value}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {!! BootForm::select('Tank test interval:', 'tank_test_interval')
                            ->options(config('config-variables.tank_test_interval'))->addClass('select2') !!}
                        </div>
                </div>
            </div>
        </div>
        <div class="btn-group btn-group-justified" role="group" aria-label="action-buttons">
            <div class="btn-group" role="group">
                <a href="{{ $url }}" class="btn btn-white">Cancel</a>
            </div>
            <div class="btn-group" role="group">
                <button type="submit" class="btn red-rubine submit-button" id="submit-button">Update</button>
            </div>
        </div>
    </div>
    <div class="col-md-5 col-lg-4">
        <div class="portlet box vehicle--profile" style="margin-bottom: 0px;">
            <div class="portlet-title bg-red-rubine">
                <div class="caption">
                    Edit Vehicle Profile Images
                </div>
            </div>
            <style type="text/css">
                .bottom-pad{
                    padding-bottom: 10px;
                }
                .img-caption{
                    font-size: 13px;
                }

            </style>
            <div class="modal-body">
                <div class="portlet-body form">
                    <div class="form-body">
                        @foreach ($medialist as $key => $media)
                            @if(is_a($media,'Spatie\MediaLibrary\Media'))
                                <div class="input-cont form-group">
                                    <div class="row" style="border-bottom: solid 1px silver;">
                                        <div class="col-md-12 text-center bottom-pad img-caption">{{ ucfirst(strtolower($key)) }}</div>
                                        <div class="col-md-6 col-md-offset-3 bottom-pad">
                                            <img class="col-md-12" id="{{$media->collection_name}}_img" src="{{getPresignedUrl($media)}}">
                                            {!! BootForm::hidden($media->collection_name.'_del')->id($media->collection_name.'_del')->value(0) !!}
                                            {!! BootForm::hidden($media->collection_name.'_media_id')->id($media->collection_name.'_media_id')->value($media->id) !!}
                                            <!-- <input type="hidden" id="{{$media->collection_name}}_del" value="0" /> -->
                                            <!-- <input type="hidden" id="{{$media->collection_name}}_media_id" value="{{$media->id}}" /> -->
                                        </div>
                                        <div class="col-md-12 text-center bottom-pad">
                                            <span class="col-md-12" id="{{$media->collection_name}}_btn">
                                                <input type="button" class="btn red-rubine" value="Change" onclick="changeImage('{{$media->collection_name}}')" />
                                                <input type="button" class="btn red-rubine" value="Remove" onclick="removeImage('{{$media->collection_name}}')" />
                                            </span>
                                            <div class="col-md-12" id="{{$media->collection_name}}_fileinput" style="display:none;">
                                                <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                                  <div class="form-control" data-trigger="fileinput"><i class="glyphicon glyphicon-file fileinput-exists"></i> <span class="fileinput-filename"></span></div>
                                                  <span class="input-group-addon btn btn-file grey-gallery"><span class="fileinput-new">Select file</span><span class="fileinput-exists">Change</span><input type="file" name="{{$media->collection_name}}" style="height: 45px;"></span>
                                                  <a href="#" class="input-group-addon btn grey-gallery fileinput-exists" data-dismiss="fileinput">Remove</a>
                                                </div>
                                                <input type="button" class="btn red-rubine" value="Cancel" onclick="cancelChangeImage('{{$media->collection_name}}')" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="input-cont form-group">
                                    <div class="row" style="border-bottom: solid 1px silver;">
                                    {{-- <label class="col-md-3 control-label" style="padding-left: 0px" for="{{ $media->for }}">{{ ucfirst(strtolower($key)) }}</label> --}}
                                        <div class="col-md-12 text-center bottom-pad img-caption">{{ ucfirst(strtolower($key)) }}</div>
                                        <div class="col-md-12">
                                            <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                              <div class="form-control" data-trigger="fileinput"><i class="glyphicon glyphicon-file fileinput-exists"></i> <span class="fileinput-filename text-center">No image uploaded</span></div>
                                              <span class="input-group-addon btn btn-file grey-gallery"><span class="fileinput-new">Select file</span><span class="fileinput-exists">Change</span><input type="file" name="{{ $media->for }}" style="height: 45px;"></span>
                                              <a href="#" class="input-group-addon btn grey-gallery fileinput-exists" data-dismiss="fileinput">Remove</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="portlet box defect-list" style="margin-bottom: 0px;">
            <div class="portlet-title bg-red-rubine">
                <div class="caption">
                    Take Out / Return Defects
                </div>
            </div>
            <div class="portlet-body form">
                <div class="form-body">
                    <div class="row">
                        @foreach ($defectMasterList as $defect)
                            <label class="col-md-12">
                                @if (in_array($defect['order'], $vehicleDefectsArray))
                                <input type="checkbox" name="defects[]" data-val="{{ $defect['order'] }}" value="{{ $defect['order'] }}" class="defects-checkbox-edit group" checked="checked">{{ $defect['page_title'] }}
                                @else
                                <input type="checkbox" name="defects[]" data-val="{{ $defect['order'] }}" value="{{ $defect['order'] }}" class="defects-checkbox-edit group">{{ $defect['page_title'] }}
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="portlet box defect-list">
            <div class="portlet-title bg-red-rubine">
                <div class="caption">
                    Ad-hoc Defects
                </div>
            </div>
            <div class="portlet-body form">
                <div class="form-body">
                    <div class="row">
                        @foreach ($defectMasterDefectsOnlyList as $defect)
                            <label class="col-md-12">
                                @if (in_array($defect['order'], $vehicleDefectsArray))
                                    <input type="checkbox" name="defects[]" data-val="{{ $defect['order'] }}" value="{{ $defect['order'] }}" class="defects-checkbox-edit group" checked="checked">{{ $defect['page_title'] }}
                                @else
                                    <input type="checkbox" name="defects[]" data-val="{{ $defect['order'] }}" value="{{ $defect['order'] }}" class="defects-checkbox-edit group">{{ $defect['page_title'] }}
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!! BootForm::close() !!}
</div>

<div class="modal fade" id="profile_status_modal" tabindex="-1" data-keyboard="false" role="dialog" aria-labelledby="defect-info-modal-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Important Message</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body">
                <p style="font-size: 15px;">There are active vehicles using this vehicle profile type. The vehicle profile of these vehicles need to be updated before you can archive this profile. To edit affected vehicles, click <a href="{{ url('profiles/' . $vehicleType->id . '/vehicles') }}"><u>here</u></a>.</p>
            </div>
            
            <div class="modal-footer">
                <div class="btn-group pull-left width100">
                    <button type="button" class="btn white-btn btn-padding col-md-12 profile" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<form class="form-horizontal editVehicleTaxCostValue" role="form" id="editVehicleTaxCostValue" action="/vehicles/editVehicleTaxCost" method="POST" novalidate>
    @include('_partials.vehicle_types.vehicle_tax')
</form>
<form class="form-horizontal editMonthlyInsuranceCost" role="form" id="editMonthlyInsuranceCost" action="/vehicles/editMonthlyInsuranceCost" method="POST" novalidate>
    @include('_partials.vehicle_types.vehicle_insurance')
</form>
<div id="vehicle_tax_history_container">
    @include('_partials.vehicle_types.vehicle_tax_history')
</div>
<div id="vehicle_insurance_history_container">
    @include('_partials.vehicle_types.vehicle_insurance_history')
</div>
@endsection

@push('scripts')
<script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/repeater.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/types.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/vehicle_insurance.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/datatable/select2.min.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/jasny-bootstrap.min.js') }}" type="text/javascript"></script>
@endpush