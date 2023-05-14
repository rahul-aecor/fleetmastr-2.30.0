@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-editable/inputs-ext/address/address.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/timeline.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/lightbox.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('scripts')
    <script src="{{ elixir('js/bootstrap-daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
    <!-- <script src="{{ elixir('js/defects.js') }}" type="text/javascript"></script> -->
    <script src="{{ elixir('js/jquery.mockjax.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-editable/bootstrap-editable/js/bootstrap-editable.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-editable/inputs-ext/address/address.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/form-editable.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jasny-bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/lightbox.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/repeater.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/types.js') }}" type="text/javascript"></script>
@endsection

@section('content')
    <div class="page-bar">
        {!! Breadcrumbs::render('profile_details', $vehicleType->id) !!}
         <div class="page-toolbar">
            <a class="btn btn-plain hidden-print" href="{{ url('profiles/'.$vehicleType->id.'/edit') }}" id='edit-vehicle-btn'>
                <i class="jv-icon jv-edit"></i> Edit Vehicle Profile
            </a>

        </div>
    </div>
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <div class="row">
        <div class="col-md-7 col-lg-8">
            <div class="portlet box">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">Vehicle Profile Details</div>
                </div>
                <div class="portlet-body form">
                    <table class="table table-striped table-hover table-summary">
                        <tbody>
                        <tr>
                            <td>Profile status:</td>
                            <td>{{ $vehicleType->profile_status }}</td>
                        </tr>
                        <tr>
                            <td>Type:</td>
                            <td>{{ $vehicleType->vehicle_type }}</td>
                        </tr>
                        <tr>
                            <td>Category:</td>
                            <td>{{  $vehicleCategoryList[$vehicleType->vehicle_category] }}</td>
                        </tr>
                        <tr>
                            <td>Odometer setting:</td>
                            <td>{{  $vehicleTypeOdometerSetting[$vehicleType->odometer_setting] }}</td>
                        </tr>
                        @if($vehicleType->vehicle_category == "non-hgv")
                        <tr>
                            <td>Sub category:</td>
                            <td>{{  $vehicleSubCategoriesNonHGV[$vehicleType->vehicle_subcategory] }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td>Usage:</td>
                            <td>{{  $vehicleType->usage_type }}</td>
                        </tr>
                        <tr>
                            <td>Manufacturer:</td>
                            <td>{{  $vehicleType->manufacturer }}</td>
                        </tr>
                        <tr>
                            <td>Model:</td>
                            <td>{{  $vehicleType->model }}</td>
                        </tr>
                         <tr>
                            <td>Bodybuilder:</td>
                            <td>{{  $vehicleType->body_builder }}</td>
                        </tr>
                        <tr>
                            <td>Gross vehicle weight:</td>
                            <td>
                                @if($vehicleType->gross_vehicle_weight != '' && is_numeric($vehicleType->gross_vehicle_weight))
                                    {{ (floor($vehicleType->gross_vehicle_weight) == $vehicleType->gross_vehicle_weight) ? number_format($vehicleType->gross_vehicle_weight, 0) : number_format($vehicleType->gross_vehicle_weight, 2) }}
                                @else
                                    {{ $vehicleType->gross_vehicle_weight }}
                                @endif
                            </td>
                        </tr>
                         <tr>
                            <td>Tyre size drive:</td>
                            <td>{{  $vehicleType->tyre_size_drive }}</td>
                        </tr>
                        <tr>
                            <td>Tyre size steer:</td>
                            <td>{{  $vehicleType->tyre_size_steer }}</td>
                        </tr>
                        <tr>
                            <td>Type pressure drive:</td>
                            <td>{{  $vehicleType->tyre_pressure_drive }}</td>
                        </tr>
                         <tr>
                            <td>Type pressure steer:</td>
                            <td>{{  $vehicleType->tyre_pressure_steer }}</td>
                        </tr>
                         <tr>
                            <td>Nut size:</td>
                            <td>{{  $vehicleType->nut_size }}</td>
                        </tr>
                         <tr>
                            <td>Re-torque:</td>
                            <td>{{  $vehicleType->re_torque }}</td>
                        </tr>
                        <tr>
                            <td>Length (mm):</td>
                            <td>{{  $vehicleType->length ? number_format($vehicleType->length) : '' }}</td>
                        </tr>
                         <tr>
                            <td>Width (mm):</td>
                            <td>{{  $vehicleType->width ? number_format($vehicleType->width) : '' }}</td>
                        </tr>
                         <tr>
                            <td>Height (mm):</td>
                            <td>{{  $vehicleType->height ? number_format($vehicleType->height) : '' }}</td>
                        </tr>
                        <tr>
                            <td>Fuel type:</td>
                            <td>{{  $vehicleType->fuel_type }}</td>
                        </tr>
                        <tr>
                            <td>Type of engine:</td>
                            <td>{{  $vehicleType->engine_type }}</td>
                        </tr>
                        <tr>
                            <td>Engine size:</td>
                            <td>{{ $vehicleType->engine_size == null? '' : $vehicleType->engine_size . ' cc'}} </td>
                        </tr>
                        <tr>
                            <td>Oil grade:</td>
                            <td>{{  $vehicleType->oil_grade }}</td>
                        </tr>
                        <tr>
                            <td>CO2:</td>
                            <td>  {{ $vehicleType->co2 ?  $vehicleType->co2 . ' ' . config('config-variables.co2Unit') : '' }}</td>
                        </tr>
                        <tr>
                            <td>Monthly vehicle insurance cost:</td>
                            <td>&pound; {{ $currentInsuranceValue }}</td>
                        </tr>
                        <tr>
                            <td>Monthly vehicle tax:</td>
                            <td>&pound; {{ $currentTaxYearValue }}</td>
                        </tr>
                        <tr>
                            <td>ADR test interval:</td>
                            <td>{{  $vehicleType->adr_test_date }}</td>
                        </tr>
                        <tr>
                            <td>Compressor service interval:</td>
                            <td>{{  config('config-variables.compressorServiceInterval')[$vehicleType->compressor_service_interval] }}</td>
                        </tr>
                        <tr>
                            <td>Invertor service interval:</td>
                            <td>{{  config('config-variables.invertorServiceInterval')[$vehicleType->invertor_service_interval] }}</td>
                        </tr>
                        <tr>
                            <td>LOLER test interval:</td>
                            <td>{{  config('config-variables.loler_test_interval')[$vehicleType->loler_test_interval] }}</td>
                        </tr>
                        <tr>
                            <td>PMI interval:</td>
                            <td>{{  $vehicleType->pmi_interval }}</td>
                        </tr>
                        <tr>
                            <td>PTO service interval:</td>
                            <td>{{  config('config-variables.ptoServiceInterval')[$vehicleType->pto_service_interval] }}</td>
                        </tr>
                        <tr>
                            <td>Service interval type:</td>
                            <td>{{ $vehicleType->service_interval_type }}</td>
                        </tr>
                        <tr>
                            <td>Service interval:</td>
                            <td>{{  $vehicleType->service_interval_type == 'Time' ? $vehicleType->service_inspection_interval : 'Every '.$vehicleType->service_inspection_interval}}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-5 col-lg-4">
            <div class="portlet box vehicle--profile">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">
                       Vehicle Profile Images
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
                <div class="portlet-body text-center" style="height: 206px;">
                    @foreach ($medialist as $key => $media)
                        @if(is_a($media,'Spatie\MediaLibrary\Media'))
                            <div class="row">
                                <div class="col-md-12 text-center bottom-pad img-caption">{{ ucfirst(strtolower($key)) }}</div>
                                <div class="col-md-6 col-md-offset-3 bottom-pad">
                                    <a href="{{ asset(getPresignedUrl($media)) }}" data-lightbox="img-defect" data-title="{{ $key }}">
                                        <img class="col-md-12" id="{{$media->collection_name}}_img" src="{{getPresignedUrl($media)}}">
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="row">
                                <div class="col-md-12 text-center bottom-pad img-caption">{{ ucfirst(strtolower($key)) }}</div>
                                <div class="col-md-12 bottom-pad">
                                    <div class="no--image">
                                        <div class="no--image--title">
                                            No image uploaded
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        
       <!-- edit -->
    </div>
    <div class="row">
        <div class="col-md-7 col-lg-8">
            <div class="row">
                <div class="col-md-6">
                    <div class="portlet box defect-list">
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
                                            <input type="checkbox" name="defects[]" data-val="{{ $defect['order'] }}" value="{{ $defect['order'] }}" class="defects-checkbox-edit group" checked="checked" disabled="true">{{ $defect['page_title'] }}
                                            @else
                                            <input type="checkbox" name="defects[]" data-val="{{ $defect['order'] }}" value="{{ $defect['order'] }}" class="defects-checkbox-edit group" disabled="true">{{ $defect['page_title'] }}
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
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
                                            <input type="checkbox" name="defects[]" data-val="{{ $defect['order'] }}" value="{{ $defect['order'] }}" class="defects-checkbox-edit group" checked="checked" disabled="true">{{ $defect['page_title'] }}
                                            @else
                                            <input type="checkbox" name="defects[]" data-val="{{ $defect['order'] }}" value="{{ $defect['order'] }}" class="defects-checkbox-edit group" disabled="true">{{ $defect['page_title'] }}
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
@endsection