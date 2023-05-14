@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal-bs3patch.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('plugin-scripts')
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modal.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modalmanager.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/tinymce/tinymce.js') }}" type="text/javascript"></script>    
    <script src="{{ elixir('js/tinymce/jquery.tinymce.min.js') }}" type="text/javascript"></script>
@endsection

@section('content')
<div class="alert alert-success" id="notificationAlert" style="display: none;">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    Data has been saved successfully.
</div>
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
        @if(isset($activateTab))
        <input type="hidded" name="activateTab" value="{{$activateTab}}">
        @endif
    </div>
    @if (count($errors) > 0)
        <div class="alert alert-danger bg-red-rubine">
            <!-- <p><strong>You have some form errors. Please check below.</strong></p>  -->
            <p><strong>Please complete the errors highlighted below.</strong></p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="portlet box user-list-portlet marginbottom0">
                {{-- <div class="portlet-body"> --}}
                    <div class="tabbable-custom tabbable-rubine nav-justified margin-bottom0">
                        <ul class="nav nav-tabs nav-justified">
                            <li class="{{ selectedSettingTab($selectedTab, "display_setting") }}" href="#display_setting" data-toggle="tab">
                                <a>Display</a>
                            </li>
                            <li class="{{ selectedSettingTab($selectedTab, "hmrcco2_setting") }}" href="#hmrcco2_setting" data-toggle="tab">
                                <a>HMRC CO2 %</a>
                            </li>
                            <li class="{{ selectedSettingTab($selectedTab, "fuelbenefit_setting") }}" href="#fuelbenefit_setting" data-toggle="tab">
                                <a>Fuel Benefit</a>
                            </li>
                            <li class="{{ selectedSettingTab($selectedTab, "p11d_report") }}" href="#p11d_report" data-toggle="tab">
                                <a>P11D Report</a>
                            </li>
                            {{-- @if(setting('is_fleetcost_enabled')) --}}
                            @if($isFleetcostTabEnabled)
                                <li class="{{ selectedSettingTab($selectedTab, "fleet_costs") }}" href="#fleet_costs" data-toggle="tab">
                                    <a>Fleet Costs</a>
                                </li>
                            @endif
                            <li class="{{ selectedSettingTab($selectedTab, "accident_insurance") }}" href="#accident_insurance" data-toggle="tab">
                                <a>Insurance</a>
                            </li>
                            <li class="{{ selectedSettingTab($selectedTab, "notifications_setting") }}" href="#notifications_setting" data-toggle="tab">
                                <a>Notifications</a>
                            </li>

                            @if($isDVSAConfigurationTabEnabled)
                            <li class="{{ selectedSettingTab($selectedTab, "dvsa_setting") }}" href="#dvsa_setting" data-toggle="tab">
                                <a>DVSA</a>
                            </li>
                            @endif

                            @if($isConfigurationTabEnabled)
                                <li class="{{ selectedSettingTab($selectedTab, "configuration_setting") }}" href="#configuration_setting" data-toggle="tab">
                                    <a>Configuration</a>
                                </li>
                            @endif
                        </ul>
                        <div class="tab-content rl-padding">
                            
                            <div class="tab-pane{{ selectedSettingTab($selectedTab, "display_setting") }}" id="display_setting">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">@include('_partials.settings.display')</div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane{{ selectedSettingTab($selectedTab, "hmrcco2_setting") }}" id="hmrcco2_setting">
                                <div class="row">
                                    <div class="col-md-12">@include('_partials.settings.hmrcCo2')</div>
                                </div>
                                
                            </div>
                            <div class="tab-pane{{ selectedSettingTab($selectedTab, "fuelbenefit_setting") }}" id="fuelbenefit_setting">
                                <div class="row">
                                    <div class="col-md-12">@include('_partials.settings.fuel_benefit')</div>
                                </div>
                                
                            </div>
                            <div class="tab-pane{{ selectedSettingTab($selectedTab, "fleet_costs") }}" id="fleet_costs">
                                <div class="row">
                                    <div class="col-md-12">@include('_partials.settings.fleet_costs')</div>
                                </div>
                            </div>
                            <div class="tab-pane{{ selectedSettingTab($selectedTab, "accident_insurance") }}" id="accident_insurance">
                                <div class="row">
                                    <div class="col-md-12">@include('_partials.settings.accident_insurance')</div>
                                </div>
                            </div>

                            <div class="tab-pane{{ selectedSettingTab($selectedTab, "notifications_setting") }}" id="notifications_setting">
                                <div class="row">
                                    <div class="col-md-12">@include('_partials.settings.notifications')</div>
                                </div>                                
                            </div>

                            <div class="tab-pane{{ selectedSettingTab($selectedTab, "p11d_report") }}" id="p11d_report">
                                <div class="row">
                                    <div class="col-md-12">@include('_partials.settings.p11d_report')</div>
                                </div>                                
                            </div>

                            @if($isDVSAConfigurationTabEnabled)
                            <div class="tab-pane{{ selectedSettingTab($selectedTab, "dvsa_setting") }}" id="dvsa_setting">
                                <div class="row">
                                    <div class="col-md-12">@include('_partials.settings.dvsa_configuration')</div>
                                </div>                                
                            </div>
                            @endif

                            @if($isConfigurationTabEnabled)
                                <div class="tab-pane{{ selectedSettingTab($selectedTab, "configuration_setting") }}" id="configuration_setting">
                                    <div class="row">
                                        <div class="col-md-12">@include('_partials.settings.site_configuration', ['isConfigurationTabEnabled' => $isConfigurationTabEnabled])</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                {{-- </div> --}}
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/repeater.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/settings.js') }}" type="text/javascript"></script>
@endpush