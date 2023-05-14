<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Vehicle;

class AllVehicleTransformer extends TransformerAbstract {

    public function transform(Vehicle $vehicle)
    {

        $defectList = array();
        foreach ($vehicle->defects as $defect) {
            //$defects = Defect::where('vehicle_id',$vehicleId)->get();
            $media = $defect->getMedia();
            $mediaUrl = "";
            if(isset($media[0])){
                $mediaUrl = env('MEDIA_SERVER', '') . getPresignedUrl($media[0]);
            }
            $data = [
                "id" => $defect->defectMaster->id,
                "_image" => ($defect->defectMaster->has_image)?"yes":"no",
                "_text" => ($defect->defectMaster->has_text)?"yes":"no",
                "imageString" => $mediaUrl,
                "image_exif" => "",
                "selected" => "yes",
                "text" => $defect->defectMaster->defect,
                "textString" => "",
                "prohibitional" => ($defect->defectMaster->is_prohibitional)?"yes":"no",
                "safety_notes" => $defect->defectMaster->safety_notes,
                "defect_id" => $defect->id,
                "read_only" => "yes"
            ];
            array_push($defectList,$data);

        }

        return [
                'vehicle'=> [
                    'vehicle_id' => $vehicle->id,
                    'registration_number' => $vehicle->registration,
                    'vehicle_category' => $vehicle->type->vehicle_category,
                    'type' => $vehicle->type->vehicle_type,
                    'manufacturer' => $vehicle->type->manufacturer,
                    'model' => $vehicle->type->model,
                    'status' => $vehicle->status,
                    'odometer_reading' => $vehicle->last_odometer_reading,
                    'odometer_unit' => $vehicle->type->odometer_setting,
                    'last_check' => $vehicle->lastCheck->isEmpty() ?'N/A': $vehicle->lastCheck->first()->report_datetime->setTimezone(config('config-variables.displayTimezone'))->format('H:i d M Y'),                    
                    'defects' => $defectList,
                ],                
            ];
    }
}
?>