@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/bootstrap-daterangepicker/daterangepicker-bs3.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/datetimepicker/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" type="text/css"/>    
@endsection

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <div class="tabbable-custom tabbable-rubine nav-justified margin-bottom0">
        <ul class="nav nav-tabs nav-justified" role="tablist">
            <li class="{{ showVehicleSelectedTab($selectedTab, "alert_centres") }}" href="#alert" data-toggle="tab"
            id="alert_centre_tab">
                <a>Alert Centre</a>
            </li>
            @if(\Auth::user()->isSuperAdmin())
                <li class="{{ showVehicleSelectedTab($selectedTab, "alert_settings") }}" href="#alert_settings" data-toggle="tab" id="alert_setting_tab">
                    <a>Alert Settings</a>
                </li>
            @endif
        </ul>

        <div class="tab-content" id="alert_detail_content">
            {!! Form::text('daterange', $defaultDateRange, ['class' => 'form-control', 'id' => 'daterange', 'style'=>'display:none;']) !!}
            <div class="tab-pane {{ showVehicleSelectedTab($selectedTab, "alert_centres") }}" id="alert">
                @include('_partials.alertCenters.alert_centre')
            </div>
            <div class="tab-pane {{ showVehicleSelectedTab($selectedTab, "alert_settings") }}" id="alert_settings">
                @include('_partials.alertCenters.alert_setting')
            </div>
        </div>

        <div id="testAlertModal" class="modal fade default-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form class="form-horizontal" role="form" id="testAlertModalForm" data-upload-template-id="template-upload" data-download-template-id="template-download">
                        <div class="modal-header bg-red-rubine">
                            <button type="button" class="close closeBulkUploadTest" data-dismiss="modal" aria-label="Close" id="closeBulkUploadTest"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title btn-dark"><font color="black">Test Alert</font></h4>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group row d-flex align-items-center">
                                        <label class="col-md-3 col-form-label">Alert name*:</label>
                                        <div class="col-md-9 error-class">
                                            <input type="text" name="test_alert_name" id="test_alert_name" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group row d-flex align-items-center">
                                        <label class="col-md-3 col-form-label">Source*:</label>
                                        <div class="col-md-9 error-class">
                                            <select class="form-control select2me" id="test_alert_source" name="test_alert_source">
                                                @foreach ($alertSource as $key => $status)
                                                    <option value="{{$key}}">{{$status}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row d-flex align-items-center">
                                        <label class="col-md-3 col-form-label">Type*:</label>
                                        <div class="col-md-9 error-class">
                                            <select class="form-control select2me" id="test_alert_type" name="test_alert_type">
                                                @foreach ($alertType as $key => $status)
                                                    <option value="{{$key}}">{{$status}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row d-flex align-items-center">
                                        <label class="col-md-3 col-form-label">Code reference*:</label>
                                        <div class="col-md-9 error-class">
                                            <input type="text" name="test_code_reference" id="test_code_reference" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group row d-flex align-items-center">
                                        <label class="col-md-3 col-form-label">Apply to*:</label>
                                        <div class="col-md-9 error-class">
                                            <select class="form-control select2me select2-test-alert-apply-to" id="test_alert_apply_to" name="test_alert_apply_to">
                                                @foreach ($vehicleRegistrationArray as $key => $registration)
                                                    <option value="{{$key}}">{{$registration}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>  
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="btn-group pull-left width100">
                                <button id="closeBulkUpload" type="button" class="btn white-btn btn-padding col-md-6 closeBulkUploadTest" data-dismiss="modal">Cancel</button>
                                <button id="createTestAlert" type="button" class="btn red-rubine btn-padding col-md-6">Send</button>
                            </div>
                        </div>
                    </form>
                </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ elixir('js/bootstrap-daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/datetimepicker/bootstrap-datetimepicker.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/repeater.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/alert_centres.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/alert_settings.js') }}" type="text/javascript"></script>
@endsection