@extends('layouts.pdf')

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
                    <div class="col-md-6">
                        <div class="portlet-title bg-red-rubine">
                            <div class="caption">{{ isset($maintenanceEvents[$filter]->name) ? $maintenanceEvents[$filter]->name : '' }}</div>
                        </div>
                        <!-- <h4 class="block">{{ isset($maintenanceEvents[$filter]->name) ? $maintenanceEvents[$filter]->name : '' }}</h4> -->
                        <div class="custom-responsive-table">
                            <table class="table table-condensed table-striped table-hover custom-table-striped">
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
                                    @foreach($data as $val)
                                    <tr>
                                        <td>{{ $val->registration }}</td>
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
    

@endsection