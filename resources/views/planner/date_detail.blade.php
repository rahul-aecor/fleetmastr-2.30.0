<style>
    .js-event-filter > .select2-choice > .select2-chosen {
        margin-right: 50px;
    }
</style>
<div class="row planner--section-description-toolbar test">
    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
        <select id="event" class="form-control select2me js-event-filter" data-placeholder="All events" name="event">
            @foreach($eventsForFilter as $slug => $event)
                <option value="{{$slug}}" @if($selectedEvent && $slug == $selectedEvent) selected @endif>{{$event}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-2 col-xs-12 text-center" style="padding: 0">
        <h3 class="planner--section-description-title-text">{{ Carbon\Carbon::parse($date)->format('d F Y') }}</h3>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-5 col-xs-12 text-right">
        <!-- <h2 class="planner--section-description-title-text"> -->
            <a id="print-btn" style="line-height: 27px; margin-top: 1px;" class="btn hidden-print btn-plain btn-h-45" href="{{ url('planner/exportDayEvents/' . $date) }}">
                <i class="jv-icon jv-download"></i> Export planner
            </a>
            <!-- <a id="print-btn" class="btn hidden-print btn-default" href="{{ url('planner/exportDayEvents/' . $date) }}">
                <i class="fa fa-file-pdf-o"></i>
            </a>  -->           
        <!-- </h2> -->
    </div>
</div>
<div class="row planner--section-description-panel">
    <div class="col-md-12">
        <div class="portlet box planner-detail-card">
            <div class="portlet-body">
                <div class="row">
                    @foreach($plannerEvents as $key => $event)
                    <div class="col-md-6 {{ $key }} event-block">
                        <div class="card">
                            
                            <!-- @if ($key == 'repairExpiry')
                            <span class="contract-expiry-square"></span>&nbsp;
                            @elseif ($key == 'motExpiry')
                            <span class="mot-expiry-square"></span>&nbsp;
                            @elseif ($key == 'taxExpiry')
                            <span class="tax-expiry-square"></span>&nbsp;
                            @elseif ($key == 'annualServiceInspection')
                            <span class="annual-service-square"></span>&nbsp;
                            @elseif ($key == 'nextServiceInspection')
                            <span class="next-service-square"></span>&nbsp;
                            @endif -->

                            <div class="card-header">
                                @if(count($dateDetails[$key]) <= 0)
                                    <h4 class="block font-weight-700">
                                    {{ $eventsForFilter[$key] }}
                                    </h4>
                                @else
                                    <h4 class="block font-weight-700 text-primary font-blue">
                                    <a href="#" class="font-blue {{ count($dateDetails[$key]) > 0 ? 'js-event-detail' : '' }}" data-key="{{ $key }}" data-date="{{ $date }}"> {{ $eventsForFilter[$key] }}</a>
                                    </h4>
                                @endif
                            </div>
                            <div class="card-body">                        
                                <div class="custom-responsive-table {{ count($dateDetails[$key]) > 0 ? 'js-event-detail' : '' }}" data-key="{{ $key }}" data-date="{{ $date }}">
                                    <table class="table table-condensed table-striped table-hover custom-table-striped">
                                        <tbody>
                                            @foreach($dateDetails[$key] as $val)
                                                <tr>
                                                    <td>{{ $val->registration }}</td>
                                                    <td>{{ $val->vehicle_region }}</td>
                                                    <td>{{ $val->type->vehicle_type }}</td>
                                                </tr>
                                            @endforeach                                    
                                        </tbody>
                                        <tfoot>
                                            @if(count($dateDetails[$key]) <= 0)
                                                <tr>
                                                    <th colspan="4">No events</th>
                                                </tr>
                                            @endif                                    
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>