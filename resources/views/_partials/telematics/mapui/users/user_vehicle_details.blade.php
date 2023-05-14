@include('_partials.telematics.mapui.header.user_header')
<div class="end-border-bottom" id="eebDivLiveTabUserVehicleDetailsBlock"></div>
<div class="journey-timeline-wrapper-sidebar-body padding-4 divLiveTabUserVehicleDetailsBlock">
    @if($data)
    <div class="static-data-grid data-grid-2">
        <div class="grid-block">
            <div class="static-label">Driver</div>
            <div class="static-value">{{$data->registration}}</div>
        </div>
        <div class="grid-block">
            <div class="static-label">Vehicle status</div>
            <div class="static-value">{{$data->vehicleStatus}}</div>
        </div>
        <div class="grid-block">
            <div class="static-label">Vehicle type</div>
            <div class="static-value">{{$data->vehicle_category}}</div>
        </div>
        <div class="grid-block">
            <div class="static-label">Region</div>
            <div class="static-value">{{$data->vehicle_region_name}}</div>
        </div>
        <div class="grid-block">
            <div class="static-label">Location</div>
            <div class="static-value">{{$data->last_telematics_location}}</div>
        </div>
        <div class="grid-block">
            <div class="static-label">Speed</div>
            <div class="static-value">{{$data->speed}}</div>
        </div>
    </div>
    <div class="s-panel">
        <div class="s-panel-header col-md-12">
            <div class="s-panel-title col-md-2 margin-top-15 padding0">Journeys</div>
            <div class="s-panel-toolbar col-md-10 padding0">
                {{-- <select name="journey-type" id="journey-type" class="journeyFilterByTime">
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="last-7-days">Last 7 days</option>
                </select> --}}

                <div class="form-group margin-bottom0">
                    <div class="input-group">
                        {!! Form::text('journeyFilterByTime', null, ['class' => 'form-control no-right-padding', 'id' => 'journeyFilterByTimePicker', 'placeholder' => 'Select date range' , 'readonly']) !!}
                        <span class="input-group-addon js-daterangepicker-button" id="basic-addon1"><i class="jv-icon jv-calendar font-weight-700"></i></span>
                    </div>
                </div>

            </div>
        </div>
        <div class="clearfix"></div>
        <div class="s-panel-body">
            <div class="chart-area liveTabInDetailPageChart"></div>
        </div>
    </div>
    <div class="s-panel">
        <div class="s-panel-header">
            <div class="s-panel-title">Journey summary</div>
            <div class="pull-right"><a href="javascript:void(0);" class="js-view-journeys text-primary underline">View journeys ></a></div>
        </div>
        <div class="s-panel-body">
            <div class="static-data-grid data-grid-2">
                <div class="grid-block">
                    <div class="static-label">Total journeys</div>
                    <div class="static-value" id="journeyCountVal">{{$data->journeyCount}}</div>
                </div>
                <div class="grid-block">
                    <div class="static-label">Distance driven (miles)</div>
                    <div class="static-value" id="gps_distanceVal">{{$data->gps_distance}}</div>
                </div>
                <div class="grid-block">
                    <div class="static-label">Driving time (HH:MM)</div>
                    <div class="static-value" id="total_driving_timeVal">{{$data->total_driving_time}}</div>
                </div>
                <!-- <div class="grid-block">
                    <div class="static-label">Fuel Used (Litres)</div>
                    <div class="static-value" id="fuelVal">{{$data->fuel}}</div>
                </div>
                <div class="grid-block">
                    <div class="static-label">CO2 Emissions (Kg)</div>
                    <div class="static-value" id="co2Val">{{$data->co2}}</div>
                </div> -->
                <div class="grid-block">
                    <div class="static-label">Incidents</div>
                    <div class="static-value" id="incident_countVal">{{$data->incident_count}}</div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="text-center margin-top-5">No record found</div>
    @endif
</div>
