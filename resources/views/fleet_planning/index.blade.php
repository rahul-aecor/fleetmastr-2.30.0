@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/fullcalendar.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/fleet-calendar.css') }}" rel="stylesheet" type="text/css"/>
    <style>
        .event-count {
            color: white !important;
            font-style: normal !important;
            padding-left: 3px;
            font-size: 10px;
        }
        [aria-describedby="jqGrid_registration"], #jqGrid_registration {
            position: sticky !important;
            left: 0;
            z-index: 2;
            background-color: #fff !important;
        }
        .table-striped>tbody>tr:nth-of-type(2n+1) [aria-describedby="jqGrid_registration"] {
            background-color: #f2f2f2 !important;
        }

        .table-hover>tbody>tr:hover [aria-describedby="jqGrid_registration"] {
            background-color: #dedddd !important;
        }

    </style>
@endsection

@section('plugin-scripts')
    <script src="{{ elixir('js/fullcalendar.min.js') }}" type="text/javascript"></script>
@endsection

@section('scripts')
    <script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/vehicles_planning.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/planner.js') }}" type="text/javascript"></script>
@endsection

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <div class="tabbable-custom tabbable-rubine nav-justified margin-bottom0">
        
        <ul class="nav nav-tabs nav-justified">
            <li class="active">
                <a href="#vehicles_planning" data-toggle="tab">
                Vehicle Planning </a>
            </li>
            <li class="js-planner">
                <a href="#planner" data-toggle="tab">
                Calendar </a>
            </li>            
        </ul>

        <div class="tab-content" id="tabs">
            @include('_partials.fleet_planning.vehicle_planning_tab')
            @include('_partials.fleet_planning.calendar_tab')
        </div>
    </div>
@endsection