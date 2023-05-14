<div class="row planner--section-description-toolbar align-items-center">
    <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
        <div class="planner--section-description-title-text back-link font-blue">
            <i class="fa fa-angle-left"></i>&nbsp;&nbsp;<a class="font-blue" id="eventDate" data-value="{{ Carbon\Carbon::parse($date)->format('Y-m-d') }}" onclick="getEventDetail('{{ Carbon\Carbon::parse($date)->format('Y-m-d') }}')">Back
            </a>
        </div>
    </div>
    <div class="col-lg-5 col-md-5 col-sm-3 col-xs-12 text-center">
        <h2 class="planner--section-description-title-text">{{ Carbon\Carbon::parse($date)->format('d F Y') }}</h2>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-5 col-xs-12 text-right">
        <h2 class="planner--section-description-title-text">
            <a id="print-btn" class="btn hidden-print btn-plain" href="{{ url('planner/exportSelectedEvents/' . $date.'/'.$selectedEvent) }}">
                <i class="jv-icon jv-download"></i> Export planner
            </a>
            <!-- <a class="btn hidden-print btn-default"  href="{{ url('planner/exportSelectedEvents/' . $date.'/'.$selectedEvent) }}">
                <i class="fa fa-file-pdf-o"></i>
            </a> -->
        </h2>
    </div>
</div>
<div class="row planner--section-description-panel">
    <div class="col-md-12">
        <div class="portlet box planner-detail-card">
            <!-- <div class="portlet-title">
                <div class="caption">
                    <a class="btn btn-blue-color" onclick="getEventDetail('{{ Carbon\Carbon::parse($date)->format('Y-m-d') }}')">
                        <i class="fa fa-angle-left"></i>
                    </a>
                    <span>{{ Carbon\Carbon::parse($date)->format('d M') }}</span>
                </div>
            </div> -->
            <div class="portlet-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="block">{{ $plannerEvents[$selectedEvent]}}</h4>
                            </div>
                            <div class="card-body">
                                <div class="custom-responsive-table custom-responsive-table-detail">
                                    <table class="table table-condensed table-striped table-hover custom-table-striped tbl-equal-column">
                                        <thead>
                                            <tr>
                                                <th>Registration</th>
                                                <th>Region</th>
                                                <!-- <th>Category</th> -->
                                                <th>Type</th>
                                                <!-- <th>Manufacturer</th> -->
                                                <th>Model</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data as $key => $val)
                                            <tr>
                                                <td><a href="{{ url('/vehicles', $val->id ) }}" class="font-blue">{{ $val->registration }}</a></td>
                                                <td>{{ $val->region->name }}</td>
                                                <td>{{ $val->type->vehicle_type }}</td>
                                                <!-- <td>{{ $val->type->manufacturer }}</td> -->
                                                <td>{{ $val->type->model }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
