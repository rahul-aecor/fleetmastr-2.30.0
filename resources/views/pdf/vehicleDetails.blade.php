@extends('layouts.pdf')

@section('pdf_title') 
  Vehicle Defect Note  
@endsection

@section('content')

    <div class="row" style="margin-top: 1px;">
        <div class="col-xs-6">
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td colspan="2"><h4>Vehicle Summary</h4></td>
                    </tr>
                    <tr>
                        <td>Registration:</td>
                        <td>{{ $vehicle->registration }}</td>
                    </tr>
                    <tr>
                        <td>Date added to fleet:</td>
                        <td>{{ $vehicle->dt_added_to_fleet }}</td>
                    </tr>
                    <tr>
                        <td>Category:</td>
                        <td>{{ $vehicle->type->present()->vehicle_category_to_display() }}</td>
                    </tr>
                    <tr>
                        <td>Type:</td>
                        <td>{{ $vehicle->type->vehicle_type }}</td>
                    </tr>
                    <tr>
                        <td>Manufacturer:</td>
                        <td>{{ $vehicle->type->manufacturer }}</td>
                    </tr>
                    <tr>
                        <td>Model:</td>
                        <td>{{ $vehicle->type->model }}</td>
                    </tr>
                    <tr>
                        <td>Odometer:</td>
                        <td>{{ number_format($vehicle->last_odometer_reading ) }} {{ $vehicle->type->odometer_setting }}</td>
                    </tr>
                    <tr>
                        <td>Vehicle status:</td>
                        <td><span class="label label-default {{ $vehicle->present()->label_class_for_status }} label-results">{{ $vehicle->status }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-xs-6">
            <table class="table  table-striped">
                <tbody>
                    <tr>
                        <td colspan="2"><h4>Vehicle Data</h4></td>
                    </tr>
                    <tr>
                        <td>Created By</td>
                        <td>{{ $vehicle->creator->first_name }} {{ $vehicle->creator->last_name }} (<a href="mailto:{{$vehicle->creator->email}}" class="font-blue">{{ $vehicle->creator->email }}</a>)</td>
                    </tr>
                    <tr>
                        <td>Created Date</td>
                        <td>{{ $vehicle->created_at->format('H:i:s j M Y') }}</td>
                    </tr>
                    <tr>
                        <td>Last modified by</td>
                        <td>{{ $vehicle->updater->first_name }} {{ $vehicle->updater->last_name }} (<a href="mailto:{{$vehicle->updater->email}}" class="font-blue">{{ $vehicle->updater->email }}</a>)</td>
                    </tr>
                    <tr>
                        <td>Last modified date</td>
                        <td>{{ $vehicle->updated_at->format('H:i:s j M Y') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-6">
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td colspan="2"><h4>Vehicle Administration</h4></td>
                    </tr>
                    <tr>
                        <td>Registration date:</td>
                        <td>{{ $vehicle->dt_registration }}</td>
                    </tr>
                    <tr>
                        <td>Chassis number:</td>
                        <td>{{ $vehicle->chassis_number }}</td>
                    </tr>
                    <tr>
                        <td>Contract ID:</td>
                        <td>{{ $vehicle->contract_id }}</td>
                    </tr>
                    <tr>
                        <td>Notes:</td>
                        <td>{{ $vehicle->notes }}</td>
                    </tr>
                    <tr>
                        <td>Vehicle division:</td>
                        <td>{{ $vehicle->vehicle_division }}</td>
                    </tr>
                    <tr>
                        <td>Vehicle region:</td>
                        <td>{{ $vehicle->dt_registration }}</td>
                    </tr>
                    <tr>
                        <td>Vehicle location:</td>
                        <td> 
                            @if (!empty($vehicle->location->name))
                                <td>{{ $vehicle->location->name }}</td>
                            @else
                                <td></td>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Vehicle lease expiry date:</td>
                        <td>{{ $vehicle->lease_expiry_date }}</td>
                    </tr>
                    <tr>
                        <td>Repair/Maintenance location:</td>
                        <td>
                            @if (!empty($vehicle->repair_location->name))
                                <td>{{ $vehicle->repair_location->name }}</td>
                            @else
                                <td></td>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Repair/Maintenance contract expiry:</td>
                        <td>{{ $vehicle->dt_repair_expiry }}</td>
                    </tr>
                    <tr>
                        <td>MOT expiry date:</td>
                        <td>{{ $vehicle->dt_mot_expiry }}</td>
                    </tr>
                    <tr>
                        <td>Next service inspection date:</td>
                        <td>{{ $vehicle->dt_next_service_inspection }}</td>
                    </tr>
                    <tr>
                        <td>Tacograph calibration due date (HGV only): </td>
                        <td>{{ $vehicle->dt_tacograch_calibration_due }}</td>
                    </tr>
                    <tr>
                        <td>Telematics</td>
                        <td>
                            @if($vehicle->masternaut)
                                Yes
                            @else
                                No
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-xs-6">
            <table class="table table-striped table-summary">
                <tbody>
                    <tr>
                        <td colspan="2"><h4>Vehicle Specifications</h4></td>
                    </tr>
                    <tr>
                        <td>Tyre size drive:</td>
                        <td>{{ $vehicle->type->tyre_size_drive }}</td>
                    </tr>
                    <tr>
                        <td>Tyre size steer:</td>
                        <td>{{ $vehicle->type->tyre_size_steer }}</td>
                    </tr>
                    <tr>
                        <td>Nut size:</td>
                        <td>{{ $vehicle->type->nut_size }}</td>
                    </tr>
                    <tr>
                        <td>Re-torque:</td>
                        <td>{{ $vehicle->type->re_torque }}</td>
                    </tr>
                    <tr>
                        <td>Tyre pressure drive:</td>
                        <td>{{ $vehicle->type->tyre_pressure_drive }}</td>
                    </tr>
                    <tr>
                        <td>Tyre pressure steer:</td>
                        <td>{{ $vehicle->type->tyre_pressure_steer }}</td>
                    </tr>
                    <tr>
                        <td>Bodybuilder:</td>
                        <td>{{ $vehicle->type->body_builder }}</td>
                    </tr>
                    <tr>
                        <td>Fuel type:</td>
                        <td>{{ $vehicle->type->fuel_type }}</td>
                    </tr>
                    <tr>
                        <td>Gross vehicle weight:</td>
                        <td>{{ $vehicle->type->gross_vehicle_weight }}</td>
                    </tr>

                    @if($vehicle->type->vehicle_category == 'non-hgv')
                        <tr>
                            <td>Service inspection interval:</td>
                            <td>{{ $vehicle->service_inspection_interval_non_hgv }}</td>
                        </tr>
                    @else
                        <tr>
                            <td>Service inspection interval:</td>
                            <td>{{ $vehicle->service_inspection_interval_hgv }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-6">
            <table class="table  table-striped table-summary">
                <tbody>
                    <tr>
                        <td colspan="3"><h4>Vehicle Documents</h4></td>
                    </tr>
                    @if(isset($files[0]))
                        <tr>
                            <th class="text-center">Document Name</th>
                            <th class="text-center">Size</th>
                            <th class="text-center">Date Uploaded</th>
                        </tr>
                        @foreach($files as $file)
                            <tr>
                                <td style="word-break: break-all">
                                    <a href="{{ url('/vehicles/downloadMedia/' .  $file->id) }}" class="btn-link">
                                        @if ($file->hasCustomProperty('caption') && !empty($file->getCustomProperty('caption'))) 
                                            {{ $file->getCustomProperty('caption') }}
                                        @else
                                            {{ $file->file_name }}
                                        @endif
                                    </a>
                                </td>
                                <td class="no-wrap">{{ $file->humanReadableSize }}</td>
                                <td class="no-wrap text-center">{{ $file->created_at->format('H:i:s j M Y') }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="2">No documents uploaded</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="col-xs-6">
            <table class="table table-striped table-summary">
                <thead>
                    <tr><td colspan="2"><h4>Vehicle Images</h4></td></tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">
                            <a href="http://lorempixel.com/100/100/transport" data-lightbox="img-defect">
                                <img class="img-rounded" src="http://lorempixel.com/100/100/transport" alt="">
                            </a>
                            <p>Front</p>
                        </td>
                        <td class="text-center">
                            <a href="http://lorempixel.com/100/100/transport" data-lightbox="img-defect">
                                <img class="img-rounded" src="http://lorempixel.com/100/100/transport" alt="">
                            </a>
                            <p>Rear</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center">
                            <a href="http://lorempixel.com/100/100/transport" data-lightbox="img-defect">
                                <img class="img-rounded" src="http://lorempixel.com/100/100/transport" alt="">
                            </a>
                            <p>Nearside</p>
                        </td>
                        <td class="text-center">
                            <a href="http://lorempixel.com/100/100/transport" data-lightbox="img-defect">
                                <img class="img-rounded" src="http://lorempixel.com/100/100/transport" alt="">
                            </a>
                            <p>Offside</p>
                        </td>
                    </tr>                    
                </tbody>
            </table>
        </div>        
    </div>
@endsection