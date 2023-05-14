<?php

namespace App\Transformers;

use App\Models\MaintenanceEvents;
use App\Models\VehicleMaintenanceHistory;
use League\Fractal\TransformerAbstract;
use App\Models\Vehicle;
use Carbon\Carbon;

class VehicleTransformer extends TransformerAbstract {

    public function transform(Vehicle $vehicle)
    {
        $lastInspectionDistance = 0;
        $isShowMissedMaintenance = false;
        $isFirstPMICompleted = false;
        if ($vehicle->type->service_interval_type == 'Distance' && $vehicle->next_service_inspection_distance) {
            $distanceEvent = MaintenanceEvents::where('slug','next_service_inspection_distance')->first();
            $lastInspectionDistance = $vehicle->next_service_inspection_distance - (int)str_replace(",","",$vehicle->type->service_inspection_interval);
            $past = VehicleMaintenanceHistory::where('vehicle_id',$vehicle->id)
                ->where('event_type_id',$distanceEvent->id)
                ->where('event_planned_distance',$lastInspectionDistance)
                ->where('event_status','Incomplete')->first();

            if ($past) {
                $isShowMissedMaintenance = true;
            }
        }

        if($vehicle->first_pmi_date) {
            $firstPMI = $vehicle->pmiMaintenanceHistories()
                        ->where('event_plan_date', Carbon::createFromFormat('d M Y', $vehicle->first_pmi_date)->toDateString())
                        ->where('event_status','Complete')
                        ->get()
                        ->count();
            $isFirstPMICompleted = $firstPMI > 0 ? true : false;
        }

        $data = [
            'vehicle'=> [
                'vehicle_id' => $vehicle->id,
                'vehicle_category' => $vehicle->type->vehicle_category,
                'registration_number' => $vehicle->registration,
                'vehicle_type_id' => $vehicle->type->id,
                'type' => $vehicle->type->vehicle_type,
                'adblue_required' => ($vehicle->type->engine_type == "Post-Euro VI - AdBlue required")? 1 : 0,
                'manufacturer' => $vehicle->type->manufacturer,
                'model' => $vehicle->type->model,
                'status' => $vehicle->status,
                'odometer_reading' => $vehicle->last_odometer_reading ? (int)$vehicle->last_odometer_reading : 0,
                'odometer_unit' => $vehicle->type->odometer_setting,
                'last_check' => $vehicle->lastCheck->count()==0?'N/A':$vehicle->lastCheck->first()->report_datetime->setTimezone(config('config-variables.displayTimezone'))->format('H:i d M Y'),
                'dt_mot_expiry' => is_null($vehicle->dt_mot_expiry) ?'N/A': $vehicle->dt_mot_expiry,
                'dt_tax_expiry' => is_null($vehicle->dt_tax_expiry) ?'N/A': $vehicle->dt_tax_expiry,
                'dt_annual_service_inspection' => is_null($vehicle->dt_annual_service_inspection) ?'N/A': $vehicle->dt_annual_service_inspection,
                'dt_tacograch_calibration_due' => (is_null($vehicle->dt_tacograch_calibration_due) || $vehicle->type->vehicle_category != "hgv") ?'N/A': $vehicle->dt_tacograch_calibration_due,
                'dt_loler' => is_null($vehicle->dt_loler_test_due) ?'N/A': $vehicle->dt_loler_test_due,
                'next_compressor_service' => is_null($vehicle->next_compressor_service) ?'N/A': $vehicle->next_compressor_service,
                'next_invertor_service_date' => is_null($vehicle->next_invertor_service_date) ?'N/A': $vehicle->next_invertor_service_date,
                'first_pmi_date' => (is_null($vehicle->first_pmi_date) || $isFirstPMICompleted) ?'N/A': $vehicle->first_pmi_date,
                'next_pmi_date' => is_null($vehicle->next_pmi_date) ?'N/A': $vehicle->next_pmi_date,
                'next_pto_service_date' => is_null($vehicle->next_pto_service_date) ?'N/A': $vehicle->next_pto_service_date,
                'adr_test_date' => is_null($vehicle->adr_test_date) ?'N/A': $vehicle->adr_test_date,
                'dt_repair_expiry' => is_null($vehicle->dt_repair_expiry) ?'N/A': $vehicle->dt_repair_expiry,
                'is_next_service_distance_exceeded' => $isShowMissedMaintenance,
                'previous_next_service_distance' => $lastInspectionDistance,
                'updated_at' => $vehicle->updated_at->format('H:i:s j M Y'),

            ],
            //'history' => $checkList,
        ];

        if ($vehicle->type->service_interval_type == 'Distance') {
            $data['vehicle']['next_service_inspection_distance'] = is_null($vehicle->next_service_inspection_distance) ? null :  (int)$vehicle->next_service_inspection_distance;
        } else if ($vehicle->type->service_interval_type == 'Time') {
            $data['vehicle']['dt_next_service_inspection'] = is_null($vehicle->dt_next_service_inspection) ?'N/A': $vehicle->dt_next_service_inspection;
        }

        if (is_null($vehicle->next_service_inspection_distance)) {
            unset($data['vehicle']['next_service_inspection_distance']);
        }

        return $data;
    }
}
?>
