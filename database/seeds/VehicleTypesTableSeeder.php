<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\VehicleType;
use App\Models\DefectMasterVehicleTypes;

class VehicleTypesTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('vehicle_types')->truncate();
        $filename = strtolower(env('BRAND_NAME')."/"."vehicles_type.txt");
        $file = fopen(base_path("database" . DIRECTORY_SEPARATOR . "seeds" . DIRECTORY_SEPARATOR . $filename), "r");
        $i=0;
        while(!feof($file)){
            $line = fgets($file);
            // $line = rtrim($line);
            $dataArray = explode("\t", $line);
            if ($i>0){
                $vehicleType = new VehicleType();
                $vehicleType->vehicle_type = trim($dataArray[0]);
                $vehicleType->vehicle_category = trim($dataArray[1]);
                $vehicleType->vehicle_subcategory = strtolower($dataArray[2]);
                $vehicleType->odometer_setting = strtolower($dataArray[3]);
                $vehicleType->usage_type = trim($dataArray[4]);
                $vehicleType->manufacturer = trim($dataArray[5]);
                $vehicleType->model = trim($dataArray[6]);
                $vehicleType->body_builder = (isset($dataArray[7]) && $dataArray[7]) ? $dataArray[7] : '';
                $vehicleType->gross_vehicle_weight = $dataArray[8] ? $dataArray[8] : '';
                $vehicleType->tyre_size_drive = (isset($dataArray[9]) && $dataArray[9]) ? $dataArray[9] : '';
                $vehicleType->tyre_size_steer = (isset($dataArray[10]) && $dataArray[10]) ? $dataArray[10] : '';
                $vehicleType->tyre_pressure_drive = (isset($dataArray[11]) && $dataArray[11]) ? $dataArray[11] : '';
                $vehicleType->tyre_pressure_steer = trim($dataArray[12]) ? $dataArray[12] : '';
                $vehicleType->nut_size = trim($dataArray[13]) ? $dataArray[13] : '';
                $vehicleType->re_torque = trim($dataArray[14]) ? $dataArray[14] : '';
                $vehicleType->length = trim($dataArray[15]) ? $dataArray[15] : null;
                $vehicleType->width = trim($dataArray[16]) ? $dataArray[16] : null;
                $vehicleType->height = trim($dataArray[17]) ? $dataArray[17] : null;
                $vehicleType->fuel_type = trim($dataArray[18]);
                $vehicleType->engine_type = trim($dataArray[19]);
                $vehicleType->engine_size = trim($dataArray[20]) ? $dataArray[20] : null;
                $vehicleType->oil_grade = trim($dataArray[21]) ? trim($dataArray[21]) : null;
                $vehicleType->co2 = trim($dataArray[22]) ? $dataArray[22] : NULL;
                $vehicleType->vehicle_tax = trim($dataArray[23]) ? $dataArray[23] : null;
                $vehicleType->compressor_service_interval = trim($dataArray[24]) ? $dataArray[24] : null;
                $vehicleType->invertor_service_interval = trim($dataArray[25]) ? $dataArray[25] : null;
                $vehicleType->loler_test_interval = trim($dataArray[26]) ? $dataArray[26] : null;
                $vehicleType->pmi_interval = trim($dataArray[27]) ? $dataArray[27] : null;
                $vehicleType->pto_service_interval = trim($dataArray[28]) ? $dataArray[28] : null;
                $vehicleType->service_interval_type = trim($dataArray[29]) ? $dataArray[29] : null;
                if(strtolower(trim($dataArray[29])) === "distance") {
                    $vehicleType->service_inspection_interval = trim($dataArray[30]) ? $dataArray[30] : '';
                } else {
                    $vehicleType->service_inspection_interval = trim($dataArray[31]) ? trim($dataArray[31]) : '';
                }
                $vehicleType->adr_test_date = trim(isset($dataArray[32])) ? trim($dataArray[32]) : null;
                $vehicleType->tank_test_interval = trim(isset($dataArray[33])) ? trim($dataArray[33]) : null;
                $vehicleType->save();
            }
            $i++;
        }
        fclose($file);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}