@extends('layouts.pdf')

<style type="text/css">
    .pdf-main-div:nth-child(odd){
        clear: both;
    }
</style>

@section('pdf_title') 
  Planner
@endsection

@section('content')
    <div class="" style="margin-top: 20px;">
        <div class="portlet box">
            <div class="portlet-title">
                <div class="caption">
                    <!-- <a class="btn btn-blue-color">
                        <i class="fa fa-angle-left"></i>
                    </a> -->
                    <span style="text-align: center;">{{ Carbon\Carbon::parse($date)->format('d F Y') }}</span>
                </div>
            </div>
            <div class="portlet-body">
                <div class="row">
                    @foreach($plannerEvents as $key => $event)
                        @if(!empty($dateDetails[$key]))
                        <div class="col-xs-6 pdf-main-div">
                            <div class="portlet-title bg-red-rubine">
                                <div class="caption">{{ isset($maintenanceEventAll[$key]->name) ? $maintenanceEventAll[$key]->name : '' }}</div>
                            </div>
                            <!-- <h4 class="block">{{ isset($maintenanceEventAll[$key]->name) ? $maintenanceEventAll[$key]->name : '' }}</h4> -->
                            <div class="custom-responsive-table">
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
                                </table>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

@endsection