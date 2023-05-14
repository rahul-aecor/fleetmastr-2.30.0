<?php

use Illuminate\Database\Seeder;
use App\Models\DefectMasterVehicleTypes;

class DefectMasterVehicleTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('defect_master_vehicle_types')->truncate();
        $userfilePath = strtolower(env('BRAND_NAME')."/"."defect_master_vehicle_types.txt");
        $file = fopen(base_path("database" . DIRECTORY_SEPARATOR . "seeds" . DIRECTORY_SEPARATOR . $userfilePath), "r");
        $cntr = 0;
        while(!feof($file)){
            $line = fgets($file);
            $dataArray = explode("\t", $line);

            $defectMasterVehicleTypes = new DefectMasterVehicleTypes();
            $defectMasterVehicleTypes->vehicle_type_id = trim($dataArray[0]);
            $defectMasterVehicleTypes->vehicle_type_name = trim($dataArray[1]);
            $defectMasterVehicleTypes->defect_list = trim($dataArray[2]);
            $defectMasterVehicleTypes->save();
        }
        fclose($file);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
