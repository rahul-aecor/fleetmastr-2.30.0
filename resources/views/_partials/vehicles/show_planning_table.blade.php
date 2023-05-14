<table class="table table-striped table-hover custom-table-striped vehicle-planning-tab-table">
    <thead>
    <th>Maintenance Event</th>
    <th>Expiry/Next Inspection</th>
    <th>Last Inspection</th>
    </thead>
    <tbody>
    <tr>
        <td>ADR test:</td>
        <td class="{{is_past_date($vehicle->adr_test_date)}}">{{ isset($vehicle->adr_test_date) ? $vehicle->adr_test_date : "" }}</td>
        <td>{{ isset($maintenanceHistory->adr_test) ? $maintenanceHistory->adr_test->event_date : "" }}</td>
    </tr>
    <tr>
        <td>Annual service:</td>
        <td class="{{is_past_date($vehicle->dt_annual_service_inspection)}}">{{ isset($vehicle->dt_annual_service_inspection) ? $vehicle->dt_annual_service_inspection : "" }}</td>
        <td>{{ isset($maintenanceHistory->annual_service_inspection) ? $maintenanceHistory->annual_service_inspection->event_date : "" }}</td>
    </tr>
    <tr>
        <td>Compressor service:</td>
        <td class="{{is_past_date($vehicle->next_compressor_service)}}">{{ isset($vehicle->next_compressor_service) ? $vehicle->next_compressor_service : "" }}</td>
        <td>{{ isset($maintenanceHistory->compressor_inspection) ? $maintenanceHistory->compressor_inspection->event_date : "" }}</td>
    </tr>
    <tr>
        <td>Invertor service:</td>
        <td class="{{is_past_date($vehicle->next_invertor_service_date)}}">{{ isset($vehicle->next_invertor_service_date) ? $vehicle->next_invertor_service_date : "" }}</td>
        <td>{{ isset($maintenanceHistory->invertor_inspection) ? $maintenanceHistory->invertor_inspection->event_date : "" }}</td>
    </tr>
    <tr>
        <td>LOLER test:</td>
        <td class="{{is_past_date($vehicle->dt_loler_test_due)}}">{{ isset($vehicle->dt_loler_test_due) ? $vehicle->dt_loler_test_due : "" }}</td>
        <td>{{ isset($maintenanceHistory->loler_test) ? $maintenanceHistory->loler_test->event_date : "" }}</td>
    </tr>
    <tr>
        <td>Maintenance:</td>
        <td class="{{is_past_date($vehicle->dt_repair_expiry)}}">{{ isset($vehicle->dt_repair_expiry) ? $vehicle->dt_repair_expiry : "" }}</td>
        <td>NA</td>
    </tr>
    <tr>
        <td>MOT:</td>
        <td class="{{is_past_date($vehicle->dt_mot_expiry)}}">{{ isset($vehicle->dt_mot_expiry) ? $vehicle->dt_mot_expiry : "" }}</td>
        <td>{{ isset($maintenanceHistory->mot) ? $maintenanceHistory->mot->event_date : "" }}</td>
    </tr>

    <tr>
        <td>PMI:</td>
        {{-- @if(Carbon\Carbon::parse($currentDate->format('Y-m-d'))->lte(Carbon\Carbon::parse($vehicle->first_pmi_date)))
            @if($isFirstPmiEventComplete)
                <td class="next-inspection-pmi-date {{is_past_date($vehicle->next_pmi_date)}}">{{ isset($vehicle->next_pmi_date) ? $vehicle->next_pmi_date : "" }}<a class="text-decoration-none">
                        <i id="pmi_interval_icon" class="js-pmi-inspection-duration js-maintenance-tab-pmi-filter"></i></a>
                </td>
            @elseif($vehicle->first_pmi_date == null && $vehicle->next_pmi_date)
                <td class="next-inspection-pmi-date {{is_past_date($vehicle->next_pmi_date)}}">{{ isset($vehicle->next_pmi_date) ? $vehicle->next_pmi_date : "" }}<a class="text-decoration-none">
                        <i id="pmi_interval_icon" class="js-pmi-inspection-duration js-maintenance-tab-pmi-filter"></i></a>
                </td>
            @else
            <td class="next-inspection-pmi-date {{is_past_date($vehicle->first_pmi_date)}}">{{ isset($vehicle->first_pmi_date) ? $vehicle->first_pmi_date : "" }}<a class="text-decoration-none">
                    <i id="pmi_interval_icon" class="js-pmi-inspection-duration js-maintenance-tab-pmi-filter"></i></a>
            </td>
            @endif
        @elseif(Carbon\Carbon::parse($currentDate->format('Y-m-d'))->lte(Carbon\Carbon::parse($vehicle->next_pmi_date)))
            <td class="next-inspection-pmi-date {{is_past_date($vehicle->next_pmi_date)}}">{{ isset($vehicle->next_pmi_date) ? $vehicle->next_pmi_date : "" }}<a class="text-decoration-none">
                    <i id="pmi_interval_icon" class="js-pmi-inspection-duration js-maintenance-tab-pmi-filter"></i></a>
            </td>
        @else
            <td></td>
        @endif --}}

        <?php
            $serviceInterval = $vehicle->type->pmi_interval;
            $interval = \DateInterval::createFromDateString($serviceInterval);
            $nextPmiDate = $firstPmiDate = "";
            if($vehicle->next_pmi_date) {
                $nextPmiDate = \Carbon\Carbon::parse($vehicle->next_pmi_date);
                $firstPmiDate = $nextPmiDate->sub($interval);
            }
        ?>
        @if($firstPmiDate != '' && Carbon\Carbon::parse($currentDate->format('Y-m-d'))->lt($firstPmiDate))
            <td class="next-inspection-pmi-date {{ is_past_date($firstPmiDate) }}">{{ isset($firstPmiDate) ? $firstPmiDate->format('d M Y') : "" }}
                <a class="text-decoration-none">
                    <i id="pmi_interval_icon" class="js-pmi-inspection-duration js-maintenance-tab-pmi-filter"></i>
                </a>
            </td>
        @elseif($vehicle->next_pmi_date)
            <td class="next-inspection-pmi-date {{is_past_date($vehicle->next_pmi_date)}}">{{ $vehicle->next_pmi_date }}
                <a class="text-decoration-none">
                    <i id="pmi_interval_icon" class="js-pmi-inspection-duration js-maintenance-tab-pmi-filter"></i>
                </a>
            </td>
        @else
            <td></td>
        @endif
        <td class="last-inspection-pmi-date">{{ isset($maintenanceHistory->preventative_maintenance_inspection) && $maintenanceHistory->preventative_maintenance_inspection->event_date ? $maintenanceHistory->preventative_maintenance_inspection->event_date : "" }}
            <span style="display: none;">
                <input type="hidden" id="value-first-pmi-date" value="{{$vehicle->first_pmi_date}}">
                <input type="hidden" id="value-next-pmi-date" value="{{$vehicle->next_pmi_date}}">
                <input type="hidden" id="value-last-inspection-date" value="{{isset($maintenanceHistory->preventative_maintenance_inspection) ? $maintenanceHistory->preventative_maintenance_inspection->event_date : "NA" }}">
            </span>
        </td>
    </tr>
    <tr>
        <td>PTO service:</td>
        <td class="{{is_past_date($vehicle->next_pto_service_date)}}">{{ isset($vehicle->next_pto_service_date) ? $vehicle->next_pto_service_date : "" }}</td>
        <td>{{ isset($maintenanceHistory->pto_service_inspection) ? $maintenanceHistory->pto_service_inspection->event_date : "" }}</td>
    </tr>
    <tr>
        <td>Service:</td>
        @if ($vehicle->type->service_interval_type == 'Distance')
            <td class="{{ $vehicle->next_service_inspection_distance ? displayExpiryNextInspectionForDistance($vehicle->last_odometer_reading, $vehicle->next_service_inspection_distance) : '' }}">
                {{ $vehicle->next_service_inspection_distance && floor($vehicle->next_service_inspection_distance) == $vehicle->next_service_inspection_distance ? number_format($vehicle->next_service_inspection_distance, 0) : '' }} {{ isset($vehicleNextDistanceEvent->event_plan_date) ? '('. $vehicleNextDistanceEvent->event_plan_date.')' : '' }} {{ $vehicle->type->odometer_setting == 'km' ? 'KM' : 'Miles' }}
                @if($isDistanceBanIcon)
                    <i id="service_distance_icon" class="fa fa-ban js-pmi-inspection-duration js-maintenance-tab-pmi-filter"></i>
                @endif
            </td>
            <td>
                @if($vehicleCompletedNextDistanceEvent)
                    {{-- @if($maintenanceHistory->next_service_inspection_distance->event_planned_distance != null)
                        {{ number_format($maintenanceHistory->next_service_inspection_distance->event_planned_distance, 0)}}
                    @else
                        {{ number_format($maintenanceHistory->next_service_inspection_distance->odomerter_reading, 0)}}
                    @endif --}}

                    {{ number_format($vehicleCompletedNextDistanceEvent->odomerter_reading, 0)}}
                    {{ (isset($vehicleCompletedNextDistanceEvent->event_date) && $vehicleCompletedNextDistanceEvent->event_date) ? " ( ".$vehicleCompletedNextDistanceEvent->event_date .") " : '' }}
                @else
                   @if($isDistanceBanIcon)
                        {{ number_format($vehicleCompletedNextDistanceEventCheckIcon->event_planned_distance,0) }}
                        {{ (isset($vehicleCompletedNextDistanceEventCheckIcon->event_plan_date) && $vehicleCompletedNextDistanceEventCheckIcon->event_plan_date) ? " ( ".$vehicleCompletedNextDistanceEventCheckIcon->event_plan_date .") " : '' }}
                   @else
                       {{ '' }}
                   @endif
                @endif

                {{-- {{ floor($vehicle->next_service_inspection_distance) == $vehicle->next_service_inspection_distance ? number_format($vehicle->next_service_inspection_distance, 0) : number_format($vehicle->next_service_inspection_distance, 2) }} {{ isset($maintenanceHistory->next_service_inspection_distance->event_plan_date) ? '(' .$maintenanceHistory->next_service_inspection_distance->event_plan_date. ')' : '' }}--}}
            </td>
        @else
            <td class="{{is_past_date($vehicle->dt_next_service_inspection)}}">{{ isset($vehicle->dt_next_service_inspection) ? $vehicle->dt_next_service_inspection : "" }}</td>
            <td>{{ isset($maintenanceHistory->next_service_inspection) ? $maintenanceHistory->next_service_inspection->event_date : "" }}
            </td>
        @endif
    </tr>
    <tr>
        <td>Tacho calibration:</td>
        <td>
            @if ($vehicle->type->vehicle_category === 'hgv')
                <span class="{{is_past_date($vehicle->dt_tacograch_calibration_due)}}">
                                            {{ isset($vehicle->dt_tacograch_calibration_due) ? $vehicle->dt_tacograch_calibration_due : "" }}
                                            </span>
            @else
                NA
            @endif
        </td>
        <td>
            @if ($vehicle->type->vehicle_category === 'hgv')
                {{ isset($maintenanceHistory->tachograph_calibration) ? $maintenanceHistory->tachograph_calibration->event_date : "" }}
            @else
                NA
            @endif
        </td>
    </tr>
    <tr>
        <td>Tax:</td>
        <td class="{{is_past_date($vehicle->dt_tax_expiry)}}">{{ isset($vehicle->dt_tax_expiry) ? $vehicle->dt_tax_expiry : "" }}</td>
        <td>{{ isset($maintenanceHistory->vehicle_tax) ? $maintenanceHistory->vehicle_tax->event_date : "" }}</td>
    </tr>
    </tbody>
</table>