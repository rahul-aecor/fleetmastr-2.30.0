<table class="table table-hover table-striped">
    <tbody>
        <tr>
            <td>Profile status:</td>
            <td>{{ $vehicleTypeData->profile_status }}</td>
        </tr>
        <tr>
            <td>Category:</td>
            <td class='vehicle-category'>
                {{ $vehicleTypeData->present()->vehicle_category_to_display() }}
            </td>
        </tr>
        <tr>
            <td>Odometer setting:</td>
            <td>{{ $vehicleTypeOdometerSetting[$vehicleTypeData->odometer_setting] }}</td>
        </tr>
        @if($vehicleTypeData->vehicle_category == "non-hgv")
        <tr>
            <td>Sub category:</td>
            <td class='vehicle-sub-category'>
                {{ $vehicleTypeData->present()->vehicle_sub_category_to_display() }}
            </td>
        </tr>
        @endif
        <tr>
            <td>Usage:</td>
            <td class="global_profile_usage_type">{{ $vehicleTypeData->usage_type }}</td>
        </tr>
        <tr>
            <td>Manufacturer:</td>
            <td>{{ $vehicleTypeData->manufacturer }}</td>
        </tr>
        <tr>
            <td>Model:</td>
            <td>{{ $vehicleTypeData->model }}</td>
        </tr>
        <tr>
            <td>Bodybuilder:</td>
            <td>{{ $vehicleTypeData->body_builder }}</td>
        </tr>
        <tr>
            <td>Gross vehicle weight:</td>
            <td>{{ $vehicleTypeData->gross_vehicle_weight }}</td>
        </tr>
        <tr>
            <td>Tyre size drive:</td>
            <td>{{ $vehicleTypeData->tyre_size_drive }}</td>
        </tr>
        <tr>
            <td>Tyre size steer:</td>
            <td>{{ $vehicleTypeData->tyre_size_steer }}</td>
        </tr>
        <tr>
            <td>Type pressure drive:</td>
            <td>{{ $vehicleTypeData->tyre_pressure_drive }}</td>
        </tr>
        <tr>
            <td>Type pressure steer:</td>
            <td>{{ $vehicleTypeData->tyre_pressure_steer }}</td>
        </tr>
        <tr>
            <td>Nut size:</td>
            <td>{{ $vehicleTypeData->nut_size }}</td>
        </tr>
        <tr>
            <td>Re-torque:</td>
            <td>{{ $vehicleTypeData->re_torque }}</td>
        </tr>
        <tr>
            <td>Length (mm):</td>
            <td>{{ $vehicleTypeData->length ? number_format($vehicleTypeData->length) : '' }}</td>
        </tr>
        <tr>
            <td>Width (mm):</td>
            <td>{{ $vehicleTypeData->width ? number_format($vehicleTypeData->width) : '' }}</td>
        </tr>
        <tr>
            <td>Height (mm):</td>
            <td>{{  $vehicleTypeData->height ? number_format($vehicleTypeData->height) : '' }}</td>
        </tr>
        <tr>
            <td>Fuel type:</td>
            <td>{{ $vehicleTypeData->fuel_type }}</td>
        </tr>
        <tr>
            <td>Type of engine:</td>
            <td>
                {{ $vehicleTypeData->engine_type }}
            </td>
        </tr>
        <tr>
            <td>Engine size:</td>
            <td>{{ $vehicleTypeData->engine_size == null? '' : $vehicleTypeData->engine_size . ' cc'}} </td>
        </tr>
        <tr>
            <td>Oil grade:</td>
            <td>
                {{ $vehicleTypeData->oil_grade }}
            </td>
        </tr>
        <tr>
            <td>CO2:</td>
            <td>
                {{ $vehicleTypeData->co2 ? $vehicleTypeData->co2 . ' ' . config('config-variables.co2Unit') : ''}}
            </td>
        </tr>
        <tr>
            <td>Monthly vehicle tax:</td>
            <td>
                &pound; {{ isset($currentTaxYearValue) ? $currentTaxYearValue : 0 }}
            </td>
        </tr>
        <tr>
            <td>ADR test interval:</td>
            <td>{{ $vehicleTypeData->adr_test_date }}</td>
        </tr>
        <tr>
            <td>Compressor service interval:</td>
            <td>{{ $vehicleTypeData->compressor_service_interval }}</td>
        </tr>
        <tr>
            <td>Invertor service interval:</td>
            <td>{{ $vehicleTypeData->invertor_service_interval }}</td>
        </tr>
        <tr>
            <td>LOLER test interval:</td>
            <td>{{ $vehicleTypeData->loler_test_interval }}</td>
        </tr>
        <tr>
            <td>PMI interval:</td>
            <td>{{ $vehicleTypeData->pmi_interval }}</td>
        </tr>
        <tr>
            <td>PTO service interval:</td>
            <td>{{ $vehicleTypeData->pto_service_interval }}</td>
        </tr>
        <tr>
            <td>Service interval type:</td>
            <td>{{ $vehicleTypeData->service_interval_type }}</td>
        </tr>
        <tr>
            <td>Service interval:</td>
            <td>{{ $vehicleTypeData->service_interval_type == 'Time' ? $vehicleTypeData->service_inspection_interval : 'Every '.$vehicleTypeData->service_inspection_interval }}</td>
        </tr>
    </tbody>
    <input type="hidden" id="js_hgv_non_hgv" name="hgv" value="{{ $vehicleTypeData->odometer_setting == 'km' ? 'KM' : 'Miles' }}">
    <input type="hidden" id="js_first_pmi_interval_week" name="js_first_pmi_interval_week" value="{{$vehicleTypeData->pmi_interval}}"> 
</table>