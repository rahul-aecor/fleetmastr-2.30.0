@extends('layouts.default')

@section('plugin-scripts')
    <script src="{{ elixir('js/jquery-easypiechart/jquery.easypiechart.min.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/flot/jquery.flot.min.js') }}"></script>
    <script src="{{ elixir('js/flot/jquery.flot.resize.min.js') }}"></script>
    <script src="{{ elixir('js/flot/jquery.flot.pie.min.js') }}"></script>
    <script src="{{ elixir('js/flot/jquery.flot.tooltip.min.js') }}"></script>
    <script src="{{ elixir('js/flot/jquery.flot.valuelabels.js') }}"></script>
    <script src="{{ elixir('js/flot/jquery.flot.tooltip.min.js') }}"></script>
    <script src="{{ elixir('js/flot/jquery.flot.categories.min.js') }}"></script>
@endsection

@section('scripts')
    <script src="{{ elixir('js/dashboard.js') }}"></script>
    <script src="{{ elixir('js/chart.js') }}"></script>
    <script type="text/javascript">
        window.IS_FLEET_COST_ENABLED = {{(int)setting('is_fleetcost_enabled')}};
    </script>
@endsection

@section('content')
<div class="tabbable-custom tabbable-rubine nav-justified margin-bottom0">
    {{-- @if(setting('is_fleetcost_enabled')) --}}
        <ul class="nav nav-tabs nav-justified">
            @if(\Auth::user()->isHavingBespokeAccess() && in_array(15, $userRoles))
            <li class="active">
                <a href="#dashboard-page" data-toggle="tab">
                Fleet Statistics </a>
            </li>
            @elseif(!\Auth::user()->isHavingBespokeAccess())
            <li class="active">
                <a href="#dashboard-page" data-toggle="tab">
                Fleet Statistics </a>
            </li>
            @endif
            {{-- @if(setting('is_fleetcost_enabled')) --}}
            @if($isFleetcostTabEnabled)
                @if(\Auth::user()->isHavingBespokeAccess() && in_array($fleetCost_id, $userRoles))
                <li id="fleet_costs" class="{{ in_array(15, $userRoles) ? '' : 'active' }}">
                    <a href="#dashboard-fleetcosts" data-toggle="tab">
                    Fleet Costs </a>
                </li>
                @elseif(!\Auth::user()->isHavingBespokeAccess())
                <li id="fleet_costs">
                    <a href="#dashboard-fleetcosts" data-toggle="tab">
                    Fleet Costs </a>
                </li>
                @endif
            @endif
        </ul>
    {{-- @endif --}}
    <div class="tab-content rl-padding">
        @if(\Auth::user()->isHavingBespokeAccess())
        <div class="tab-pane {{ in_array(15, $userRoles) ? 'active' : '' }}" id="dashboard-page">
        @else
        <div class="tab-pane active" id="dashboard-page">
        @endif
            {{-- New section --}}
            <div class="row">
                <div class="col-md-12">
                    <h4 class="block dashboard-section-name">Vehicle Fleet Statistics</h4>
                </div>
            </div>
            <div class="row gutters-tiny d-flex margin-bottom-20">
                <div class="col-md-9">
                    <div class="row gutters-tiny d-flex h-100">
                        <div class="col-md-3">
                            <div class="card h-100 fixed-height-card-h1">
                                <div class="card-header">
                                    <span class="card-title">Total vehicles</span>
                                </div>
                                <div class="card-body h-100">
                                    <div class="row align-items-center d-flex h-100">
                                        <div class="col-xs-12">
                                            <div class="align-items-center d-flex flex-column h-100 justify-content-center">
                                                <h1 class="count-number" id="total-vehicles-count"><i class="fa fa-spin fa-spinner"></i></h1>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <a href="/vehicles?show=roadworthy" class="text-decoration-none">
                                <div class="card h-100 fixed-height-card-h1">
                                    <div class="card-header">
                                        <span class="card-title">Roadworthy vehicles</span>
                                    </div>
                                    <div class="card-body h-100">
                                        <div class="row align-items-center d-flex h-100">
                                            <div class="col-xs-6 border-r">
                                                <div class="align-items-center d-flex flex-column h-100 justify-content-center">
                                                    <h1 class="count-number" id="roadworthy-vehicles-count"><i class="fa fa-spin fa-spinner"></i></h1>
                                                </div>
                                            </div>
                                            <div class="col-xs-6">
                                                <div class="pie-chart-wrapper align-items-center d-flex flex-column h-100 justify-content-center">
                                                    <div class="dashboard-pie-chart-stat d-flex align-items-center justify-content-center" id="roadworthy-pie-chart" data-percent="0">
                                                        <span class="d-flex align-items-center justify-content-center"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/vehicles?show=other" class="text-decoration-none">
                                <div class="card h-100 fixed-height-card-h1">
                                    <div class="card-header">
                                        <span class="card-title">Vehicles - other</span>
                                    </div>
                                    <div class="card-body h-100">
                                        <div class="row align-items-center d-flex h-100">
                                            <div class="col-xs-6 border-r">
                                                <div class="align-items-center d-flex flex-column h-100 justify-content-center">
                                                    <h1 class="count-number" id="vehicles-with-defects-count"><i class="fa fa-spin fa-spinner"></i></h1>
                                                </div>
                                            </div>
                                            <div class="col-xs-6">
                                                <div class="pie-chart-wrapper align-items-center d-flex flex-column h-100 justify-content-center">
                                                    <div class="dashboard-pie-chart-stat d-flex align-items-center justify-content-center" id="defects-pie-chart" data-percent="0">
                                                        <span class="d-flex align-items-center justify-content-center"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/vehicles?show=off-road" class="text-decoration-none">
                                <div class="card h-100 fixed-height-card-h1">
                                    <div class="card-header">
                                        <span class="card-title">Vehicles off road</span>
                                    </div>
                                    <div class="card-body h-100">
                                        <div class="row align-items-center d-flex h-100">
                                            <div class="col-xs-6 border-r">
                                                <div class="align-items-center d-flex flex-column h-100 justify-content-center">
                                                    <h1 class="count-number" id="vor-vehicle-counts"><i class="fa fa-spin fa-spinner"></i></h1>
                                                </div>
                                            </div>
                                            <div class="col-xs-6">
                                                <div class="pie-chart-wrapper align-items-center d-flex flex-column h-100 justify-content-center">
                                                    <div class="dashboard-pie-chart-stat d-flex align-items-center justify-content-center" id="vor-pie-chart" data-percent="0">
                                                        <span class="d-flex align-items-center justify-content-center"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body h-100 fixed-height-card-h1">
                            <div class="row align-items-center d-flex h-100">
                                <div class="col-xs-12 h-100">
                                    <div class="pie-chart-wrapper d-flex align-items-center flex-column h-100 justify-content-center">
                                        <div style="width: 100%;height: 120px">
                                            <div id="vehicles-fleet-chart" style="height: 120px; position: relative;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <h4 class="block dashboard-section-name">Today's Vehicle Checks</h4>
                </div>
            </div>
            <div class="row gutters-tiny d-flex margin-bottom-20">
                <div class="col-md-12">
                    <div class="row gutters-tiny d-flex vehicle-check-list">
                        <div class="col-md-2">
                            <a href="/vehicles?show=checked-today" class="checked-today text-decoration-none">
                                <div class="card h-100 fixed-height-card-h1">
                                    <div class="card-header">
                                        <span class="card-title">Checked today</span>
                                    </div>
                                    <div class="card-body h-100">
                                        <div class="row align-items-center d-flex h-100">
                                            <div class="col-xs-12">
                                                <div class="align-items-center d-flex flex-column h-100 justify-content-center">
                                                    <h1 class="count-number" id="total-checks-count"><i class="fa fa-spin fa-spinner"></i></h1>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="/vehicles?show=unchecked-today" class="unchecked-today text-decoration-none">
                                <div class="card h-100 fixed-height-card-h1">
                                    <div class="card-header">
                                        <span class="card-title">Not checked today</span>
                                    </div>
                                    <div class="card-body h-100">
                                        <div class="row align-items-center d-flex h-100">
                                            <div class="col-xs-12">
                                                <div class="align-items-center d-flex flex-column h-100 justify-content-center">
                                                    <h1 class="count-number" id="total-unchecks-count"><i class="fa fa-spin fa-spinner"></i></h1>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="/checks?show=roadworthy" class="text-decoration-none">
                                <div class="card h-100 fixed-height-card-h1">
                                    <div class="card-header">
                                        <span class="card-title">Roadworthy</span>
                                    </div>
                                    <div class="card-body h-100">
                                        <div class="row align-items-center d-flex h-100">
                                            <div class="col-xs-12">
                                                <div class="align-items-center d-flex flex-column h-100 justify-content-center">
                                                    <h1 style="color: #008000" class="count-number" id="roadworthy-checks-count"><i class="fa fa-spin fa-spinner"></i></h1>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="/checks?show=safe-to-operate" class="text-decoration-none">
                                <div class="card h-100 fixed-height-card-h1">
                                    <div class="card-header">
                                        <span class="card-title">Safe to operate</span>
                                    </div>
                                    <div class="card-body h-100">
                                        <div class="row align-items-center d-flex h-100">
                                            <div class="col-xs-12">
                                                <div class="align-items-center d-flex flex-column h-100 justify-content-center">
                                                    <h1 style="color: #ffa500" class="count-number" id="safe-to-operate-checks-count"><i class="fa fa-spin fa-spinner"></i></h1>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="/checks?show=unsafe-to-operate" class="text-decoration-none">
                                <div class="card h-100 fixed-height-card-h1">
                                    <div class="card-header">
                                        <span class="card-title">Unsafe to operate</span>
                                    </div>
                                    <div class="card-body h-100">
                                        <div class="row align-items-center d-flex h-100">
                                            <div class="col-xs-12">
                                                <div class="align-items-center d-flex flex-column h-100 justify-content-center">
                                                    <h1 style="color: #ff0000" class="count-number" id="unsafe-to-operate-checks-count"><i class="fa fa-spin fa-spinner"></i></h1>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-2">
                            <div class="card h-100 fixed-height-card-h1">
                                <div class="card-body h-100">
                                    <div class="row align-items-center d-flex h-100">
                                        <div class="col-xs-12 h-100">
                                            <div class="pie-chart-wrapper d-flex align-items-center flex-column h-100 justify-content-center">
                                                <div style="width: 100%;height: 120px;">
                                                    <div id="checks-chart" style="height: 120px; position: relative;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
//$vehicleRegions = config('config-variables.vehicleRegionsForSelect');
        ?>
        <div class="row gutters-tiny graph-data-section margin-bottom-20">
            <div class="col-md-12">
                <h4 class="block dashboard-section-name">Vehicles Off Road</h4>
            </div>
            <div class="col-md-3">
                <form class="form">
                    <div class="form-group">
                        {!! Form::select('region', $vehicleListing, null, ['id' => 'region', 'class' => 'form-control select2me', 'data-placeholder' => 'All regions', 'autocomplete' => 'off']) !!}
                    </div>
                </form>
            </div>
            <div class="col-md-12 bar_chart_section">
                <div class="card regional-data-wrapper vor-data-wrapper">
                    <div class="card-header">
                        <div class="row d-flex align-items-center">
                            <div class="col-md-6">
                                <span class="card-title">Fleet to VOR comparison</span>
                            </div>
                            <div class="col-md-6 text-right">
                                <div>Total vehicles: <span id="total-vehicles"><i class="fa fa-ellipsis-h"></i></span></div>
                                <div>VOR: <span id="vor-vehicle"><i class="fa fa-ellipsis-h"></i></span></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="padding-bottom: 15px;overflow: auto;" id="fleetVORComparisonScroll">
                            <div id="vor-bar-chart" class="fleetVorComparison" style="height: 300px;width:100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row gutters-tiny">
            <div class="col-md-12">
                <h4 class="block dashboard-section-name">Upcoming Inspections</h4>
            </div>
            <div class="col-md-3">
                <form class="form">
                    <div class="form-group">
                        {!! Form::select('region-for-inspections', $vehicleListing, null, ['id' => 'region-for-inspections', 'class' => 'form-control select2me', 'autocomplete' => 'off']) !!}
                    </div>
                </form>
            </div>
        </div>
        <section class="upcoming-inspection margin-bottom-20">
            <div class="upcomming_box">
                <section class="upcoming-inspection margin-bottom-20">
                    <div class="upcomming_box">
                        <div class="row gutters-tiny inspection-data-section dashboard_upcomming_inspections">
                            @foreach($periods as $periodKey => $period)
                                <div class="col-md-3">
                                    <div class="card h-100 fix-height-inspection-card mb15">
                                        <div class="card-header">
                                            <span class="card-title">{{ $period['text'] }}</span>
                                        </div>
                                        <div class="card-body h-100">
                                            <div class="row gutters-tiny h-100 {{ $period['type'] }}-inspection-stat">
                                                @foreach($inspection_fields as $fieldKey => $field)
                                                    <div class="col-xs-4">
                                                        <a class="text-decoration-none inspectionRegionCount" href="/fleet_planning?field={{ $fieldKey }}&period={{ $periodKey }}&region=All">
                                                            <div class="inspection-stat {{ $field['type'] }}-inspection-stat text-center d-flex flex-column justify-content-center w-100">
                                                                <h4 class="count-number">...</h4>
                                                                <p class="text-dashboard-label">{{ $field['text'] }}</p>
                                                            </div>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </section>
            </div>
        </section>
        <div class="row gutters-tiny">
            <div class="col-md-12">
                <h4 class="block dashboard-section-name">Upcoming Expires</h4>
            </div>
            <div class="col-md-3">
                <form class="form">
                    <div class="form-group">
                        {!! Form::select('region-for-expiry', $vehicleListing, null, ['id' => 'region-for-expiry', 'class' => 'form-control select2me', 'autocomplete' => 'off']) !!}
                    </div>
                </form>
            </div>
        </div>
        <section class="upcoming-expiry margin-bottom-20">
            <div class="upcomming_box">
                    <div class="row gutters-tiny inspection-data-section  dashboard_upcomming_inspections">
                        @foreach($periods as $periodKey => $period)
                            <div class="col-md-3">
                                <div class="card h-100 fixed-height-card-h2">
                                    <div class="card-header">
                                        <span class="card-title">{{ $period['text'] }}</span>
                                    </div>
                                    <div class="card-body h-100">
                                        <div class="row gutters-tiny d-flex h-100 {{ $period['type'] }}-inspection-stat">
                                            @foreach($expiry_fields as $expiryFieldKey => $field)
                                                <div class="col-xs-4">
                                                    <a class="text-decoration-none expiresRegionCount" href="/fleet_planning?field={{ $expiryFieldKey }}&period={{ $periodKey }}&region=All">
                                                        <div class="inspection-stat {{ $field['type'] }}-inspection-stat text-center d-flex flex-column justify-content-center w-100">
                                                            <h4 class="count-number">...</h4>
                                                            <p class="text-dashboard-label">{{ $field['text'] }}</p>
                                                        </div>
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
            </div>
        </section>
    </div>
    @if(\Auth::user()->isHavingBespokeAccess())                      
    <div class="tab-pane {{ !in_array(15, $userRoles) && in_array($fleetCost_id, $userRoles) ? 'active' : '' }}" id="dashboard-fleetcosts">
    @else
    <div class="tab-pane" id="dashboard-fleetcosts">
    @endif
        <div class="row">
            <div class="col-md-12">
                <h4 class="block dashboard-section-name">Vehicle Fleet Costs</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <input type="hidden" name="primary-color" id="primary-color" value="{{ $primary_colour }}">
                    <div class="col-md-12"> @include('_partials.dashboard.fleetcosts') </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection