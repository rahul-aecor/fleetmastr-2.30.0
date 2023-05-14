@extends('layouts.default')
@section('plugin-styles')
{{-- <link type="text/css" rel="stylesheet" href="https://cdn.jsdelivr.net/gh/alumuko/vanilla-datetimerange-picker@latest/dist/vanilla-datetimerange-picker.css"> --}}


    {{-- <link href="{{ asset('css/bootstrap-daterangepicker/daterangepicker-bs3.css') }}" rel="stylesheet" type="text/css"/> --}}
@endsection

@section('styles')
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/telematics.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/vanilla-datetimerange-picker/vanilla-datetimerange-picker.css') }}" rel="stylesheet" type="text/css"/>
    <style>
        #mapCanvasIncident {
            overflow-anchor:none;
        }
        #journey_map_canvas {
            overflow-anchor:none;
        }
        .journey-timeline .entry .title-danger:before {
            border: red;
            background-color: red;
        }
        input[readonly] {
            cursor: pointer !important;
            background: white !important;
        }
        .location-toggle-wrapper .btn {
            padding: 8px 14px 8px 14px;
            min-height: 34px;
        }
        .location-toggle-wrapper .toggle-off {
            padding-left: 24px;
        }
        .location-toggle-wrapper .toggle-on {
            padding-right: 24px;
        }
        :root{
            --js-telematics-search-form-height: 60px;
        }
        #zoneAlertJqGrid_duration {
            text-align: left;
        }
        .js-driver-analysis {
            color: #4d4e4e !important;
            font-weight: bold;
        }
        [aria-describedby="journeyJqGrid_map"], [aria-describedby="journeyJqGrid_registraion"], #journeyJqGrid_map, #journeyJqGrid_registraion {
            position: sticky !important;
            left: 0;
            z-index: 2;
            background-color: #fff !important;
        }
        [aria-describedby="journeyJqGrid_registraion"], #journeyJqGrid_registraion {
            left: 100px;
        }
        .table-striped>tbody>tr:nth-of-type(2n+1) [aria-describedby="journeyJqGrid_map"], .table-striped>tbody>tr:nth-of-type(2n+1) [aria-describedby="journeyJqGrid_registraion"] {
            background-color: #f2f2f2 !important;
        }

        .table-hover>tbody>tr:hover [aria-describedby="journeyJqGrid_map"], .table-hover>tbody>tr:hover [aria-describedby="journeyJqGrid_registraion"] {
            background-color: #dedddd !important;
        }

        .js-live-tab-driver-analysis {
            color: #4d4e4e !important;
            font-weight: bold;
        }

        /*#chartLiveTabDetailDriverAnalysis .canvasjs-chart-container {
            text-align: inherit !important;
            padding-right: 408px !important;
        }*/

        #liveTabSideBarMainHeader {
            padding: 7px 15px 8px 0 !important;
        }
    </style>
@endsection

@section('plugin-scripts')
    <script>
        var SERVER_ADDR = "{{ env('BROADCAST_SERVER_ADDRESS') }}";
        var SERVER_PORT = "{{ env('BROADCAST_SERVER_PORT') }}";
        var BROADCAST_CHANNEL = "{{ env('BROADCAST_CHANNEL') }}";
    </script>
    
    <script src="{{ elixir('js/jquery-easypiechart/jquery.easypiechart.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/flot/jquery.flot.min.js') }}"></script>
    <script src="{{ elixir('js/flot/jquery.flot.resize.min.js') }}"></script>
    <script src="{{ elixir('js/flot/jquery.flot.pie.min.js') }}"></script>
    <script src="{{ elixir('js/flot/jquery.flot.valuelabels.js') }}"></script>
    <script src="{{ elixir('js/flot/jquery.flot.tooltip.min.js') }}"></script>
    <script src="{{ elixir('js/flot/jquery.flot.categories.min.js') }}"></script>

    <script src="//maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_KEY') }}"></script>
    <script src="{{ elixir('js/bundles/telematics.bundle.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/chart.js') }}"></script>
    <script src="{{ elixir('js/vanilla-datetimerange-picker/vanilla-datetimerange-picker.js') }}"></script>

        <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js" type="text/javascript"></script>
{{-- <script src="https://cdn.jsdelivr.net/gh/alumuko/vanilla-datetimerange-picker@latest/dist/vanilla-datetimerange-picker.js"></script> --}}

@endsection

@section('scripts')
    <script src="{{ elixir('js/canvasjs/jquery.canvasjs.min.js') }}"></script>
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modal.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modalmanager.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/own-custom.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/telematics_behaviour.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/telematics_vehicles.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/telematics-front.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/telematics_journeys.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/telematics_locations.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/telematics_incidents.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/telematics_zones.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/telematics_zone_alerts.js') }}" type="text/javascript"></script> 
    <script src="{{ elixir('js/datatable/select2.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jasny-bootstrap.min.js') }}" type="text/javascript"></script>


@endsection

@section('content')
<div class="alert alert-danger bg-red-rubine" style="display:none;" id="searchErrorDiv">
   <button class="close" data-close="alert"></button>
   <p><strong>This vehicle does not have any associated telematics data.</strong></p>
</div>

<div id="telematics-data">
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <div class="tabbable-custom tabbable-rubine nav-justified margin-bottom0">
        <ul class="nav nav-tabs nav-justified telematics_tabs">
            <li class='{{ showTelematicsSelectedTab($selectedTab, "live_tab") }}'  id="live_tab">
                <a href="#live" class='liveTab' data-toggle="tab" >Map</a>
            </li>
            <li class='{{ showTelematicsSelectedTab($selectedTab, "vehicles_tab") }}' id="vehicles_tab">
                <a href="#vehicles" class='vehiclesTab' data-toggle="tab" >Vehicles</a>
            </li>
            <li class='{{ showTelematicsSelectedTab($selectedTab, "journeys_tab") }}' id="journeys_tab">
                <a href="#journey" class='journeysTab' data-toggle="tab" >Journeys</a>
            </li>

            <li class='{{ showTelematicsSelectedTab($selectedTab, "incidents_tab") }}' id="incidents_tab">
                <a href="#incidents" class='incidentsTab' data-toggle="tab" >Incidents</a>
            </li>
            @if(Auth::user()->isSuperAdmin())
                <li class='{{ showTelematicsSelectedTab($selectedTab, "locations_tab") }}' id="locations_tab">
                    <a href="#locations" class='locationsTab' data-toggle="tab" >Locations</a>
                </li>
            @endif
             <li class='{{ showTelematicsSelectedTab($selectedTab, "zone_tab") }}'  id="zone_tab">
                <a href="#zones" class='zoneTab' data-toggle="tab" >Zones</a>
            </li>
            <li class='{{ showTelematicsSelectedTab($selectedTab, "behaviours_tab") }}' id="behaviours_tab">
                <a href="#behaviour" class='behavioursTab' data-toggle="tab" >Behaviour</a>
            </li>
        </ul>

        <div class="tab-content rl-padding">
{{--             {!! Form::text('commonDaterange', $defaultDateRange, ['class' => 'form-control', 'id' => 'commonDaterange', 'style'=>'display:none;']) !!} --}}
            {!! Form::text('zoneDaterange', $todayDateRange, ['class' => 'form-control', 'id' => 'zoneDaterange', 'style'=>'display:none;']) !!}
            {!! Form::hidden('allowViewingColumnsForDebug', Auth::user()->isDebugColumnVisibleToUser(), ['class' => 'form-control', 'id' => 'allowViewingColumnsForDebug', 'style'=>'display:none;']) !!}
            <div class="tab-pane {{ showTelematicsSelectedTab($selectedTab, 'behaviours_tab') }}" id="behaviour">
                @include('_partials.telematics.behaviour')
            </div>

            <div class="tab-pane {{ showTelematicsSelectedTab($selectedTab, 'vehicles_tab') }}" id="vehicles">
                @include('_partials.telematics.vehicles')
            </div>

            <div class="tab-pane {{ showTelematicsSelectedTab($selectedTab, 'incidents_tab') }}" id="incidents">
                @include('_partials.telematics.incidents')
            </div>

            <div class="tab-pane {{ showTelematicsSelectedTab($selectedTab, 'journeys_tab') }}" id="journey">
                @include('_partials.telematics.journey')
            </div>
            @if(Auth::user()->isSuperAdmin())
                <div class="tab-pane {{ showTelematicsSelectedTab($selectedTab, 'locations_tab') }}" id="locations">
                    @include('_partials.telematics.locations')
                </div>
            @endif
            <div class="tab-pane {{ showTelematicsSelectedTab($selectedTab, 'zone_tab') }}" id="zones">
                @include('_partials.telematics.zones')
            </div>
            <div class="tab-pane margin-top-minus-8 {{ showTelematicsSelectedTab($selectedTab, 'live_tab') }}" id="live">
                @include('_partials.telematics.live')
            </div>
        </div>
    </div>
</div>
@endsection