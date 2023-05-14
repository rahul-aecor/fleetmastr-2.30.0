@extends('layouts.default')

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <div class="page-bar">

        {!! Breadcrumbs::render('telematics_zonedetails') !!}
        <div class="page-toolbar">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="portlet box">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">Zone Details</div>
                </div>
                <div class="portlet-body form">
                    <table class="table table-striped table-hover table-summary">
                        <tbody>
                        <tr>
                            <td>Name of zone:</td>
                            <td>{{ $zone->name }}</td>
                        </tr>
                        {{--<tr>
                            <td>Region:</td>
                            <td>{{ $zone->region->name }}</td>
                        </tr>--}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="portlet box">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">Zone Settings</div>
                </div>
                <div class="portlet-body form">
                    <table class="table table-striped table-hover table-summary">
                        <tbody>
                        <tr>
                            <td>Zone status:</td>
                            <td>{{ $zone->zone_status }}</td>
                        </tr>
                        <tr>
                            <td>Alert Setting:</td>
                            <td>{{ $zone->alert_setting }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
