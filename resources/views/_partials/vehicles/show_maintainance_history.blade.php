<div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
    <h4 class="modal-title">Maintenance Event</h4>
    <a class="font-red-rubine" data-dismiss="modal" aria-label="Close" id="editMaintenanceHistoryClose">
        <i class="jv-icon jv-close"></i>
    </a>
</div>

<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group row">
                <div class="col-md-4">Event type:</div>
                <div class="col-md-8">
                    {{ $maintenanceEvent->eventType->name }}
                    {{-- <span id="eventType"></span> --}}
                </div>
            </div>
            {{-- FLEE-6674
            @if ($maintenanceEvent->eventType->slug == 'next_service_inspection_distance' && $maintenanceEvent->vehicle->type->service_interval_type == 'Distance' && $maintenanceEvent->created_by == 1)
                <div class="form-group row">
                    <div class="col-md-4">Planned distance:</div>
                    <div class="col-md-8">
                        {{ isset($maintenanceEvent->event_planned_distance) ? number_format($maintenanceEvent->event_planned_distance) : 'NA' }}
                        <span id="status"></span>
                    </div>
                </div>
            @endif --}}

            {{-- @if($maintenanceEvent->created_by == 1 || $maintenanceEvent->eventType->slug  == 'preventative_maintenance_inspection') --}}
            <div class="form-group row">
                <div class="col-md-4">Planned event:</div>
                <div class="col-md-8">
                    @if ($maintenanceEvent->eventType->slug == 'next_service_inspection_distance' && $maintenanceEvent->vehicle->type->service_interval_type == 'Distance')
                        {{ number_format($maintenanceEvent->event_planned_distance, 0) }} {{ $maintenanceEvent->vehicle->type->odometer_setting == 'km' ? 'KM' : 'Miles' }}
                    @else
                        {{ isset($maintenanceEvent->event_plan_date) ? $maintenanceEvent->event_plan_date : '' }}
                    @endif
                    {{-- <span id="eventType"></span> --}}
                </div>
            </div>
            {{-- @endif --}}

            <div class="form-group row">
                <div class="col-md-4">Event date:</div>
                <div class="col-md-8">
                    {{ isset($maintenanceEvent->event_date) ? $maintenanceEvent->event_date : '' }}
                    <span id="eventDate"></span>
                </div>
            </div>
            {{-- @if (($maintenanceEvent->eventType->slug == 'next_service_inspection_distance' && $maintenanceEvent->vehicle->type->service_interval_type == 'Distance') || ($maintenanceEvent->eventType->slug == 'preventative_maintenance_inspection' )) --}}
                <div class="form-group row">
                    <div class="col-md-4">Odometer reading:</div>
                    <div class="col-md-8">
                        {{ isset($maintenanceEvent->odomerter_reading) ? number_format($maintenanceEvent->odomerter_reading) : '' }}
                        <span id="status"></span>
                    </div>
                </div>
            {{-- @endif --}}
            @if($maintenanceEvent->eventType->slug == 'mot')
            <div class="form-group row">
                <div class="col-md-4">Type:</div>
                <div class="col-md-8">
                    {{ isset($maintenanceEvent->mot_type) ? $maintenanceEvent->mot_type: 'NA' }}
                    <span id="motType"></span>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-4">Outcome:</div>
                <div class="col-md-8">
                    {{ isset($maintenanceEvent->mot_outcome) ? $maintenanceEvent->mot_outcome: 'NA' }}
                    <span id="motOutcome"></span>
                </div>
            </div>
            @endif
            
            <div class="form-group row">
                <div class="col-md-4">Status:</div>
                <div class="col-md-8">
                    {{ isset($maintenanceEvent->event_status) ? $maintenanceEvent->event_status : '-' }}                            
                    <span id="status"></span>
                </div>
            </div>
            
            @if ($isDVSAConfigurationTabEnabled && $maintenanceEvent->eventType->slug  == 'preventative_maintenance_inspection')
                <div class="form-group row">
                    <div class="col-md-4">Acknowledgment:</div>
                    <div class="col-md-8">
                        {{ $maintenanceEvent->is_safety_inspection_in_accordance_with_dvsa == null ? '' : ( $maintenanceEvent->is_safety_inspection_in_accordance_with_dvsa == '1' ? 'Yes' : 'No') }}
                        <span id="status"></span>
                    </div>
                </div>
            @endif

            @if ($isDVSAConfigurationTabEnabled && $maintenanceEvent->eventType->slug == 'preventative_maintenance_inspection_distance')
                <div class="form-group row">
                    <div class="col-md-4">Acknowledgment:</div>
                    <div class="col-md-8">
                        {{ $maintenanceEvent->is_safety_inspection_in_accordance_with_dvsa == '1' ? 'Yes' : 'No' }}
                        <span id="status"></span>
                    </div>
                </div>
            @endif

            <div class="form-group row">
                <div class="col-md-4">Comment:</div>
                <div class="col-md-8">
                    {{ isset($maintenanceEvent->comment) ? $maintenanceEvent->comment : '-' }}                            
                    <span id="comment"></span>
                </div>
            </div>

            @if($maintenanceEvent->hasMedia())
                <table role="presentation" class="table table-striped table-hover custom-table-striped clearfix maintenanceEventDetail" id="upload-media-table-1">
                    <thead>
                        <th>Document Name</th>
                    </thead>
                    <tbody class="files">
                        @foreach($maintenanceEvent->getMedia() as $file)
                            <tr>
                                <?php

                                if ($file->hasCustomProperty('caption') && !empty($file->custom_properties['caption'])) {
                                    $fileName = $file->custom_properties['caption'] .".".pathinfo($file->file_name, PATHINFO_EXTENSION);
                                } else {
                                    $fileName = $file->name.".".pathinfo($file->file_name, PATHINFO_EXTENSION);
                                }

                                ?>
                                <td><a target="_blank" href="{{ getPresignedUrl($file)}}" title="{{ $file->name.".".pathinfo($file->file_name, PATHINFO_EXTENSION) }}" download="{{ $file->name.".".pathinfo($file->file_name, PATHINFO_EXTENSION) }}">{{ $fileName }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
<div class="modal-footer">
    <div class="col-md-offset-2 col-md-8 ">
        <div class="btn-group pull-left width100">
            <button type="button" class="btn white-btn btn-padding col-md-12" 
            id="showMaintenanceHistoryCancle" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>