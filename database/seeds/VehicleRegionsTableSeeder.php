<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\VehicleRegions;
use App\Models\VehicleDivisions;
use Illuminate\Database\Eloquent\Model;

class VehicleRegionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        if(env('IS_REGION_LOCATION_LINKED_IN_VEHICLE')) {
        	DB::table('vehicle_locations')->truncate();	
        }
        DB::table('vehicle_regions')->truncate();
        $userfilePath = strtolower(env('BRAND_NAME')."/"."vehicle_regions.csv");
        $file = fopen(base_path("database" . DIRECTORY_SEPARATOR . "seeds" . DIRECTORY_SEPARATOR . $userfilePath), "r");
        $cntr = 0;
        while(!feof($file)){
            $line = fgets($file);
            $dataArray = explode("\t", $line);
            if($cntr > 0) {
                $VehicleRegions = new VehicleRegions();
                $name=$dataArray[0];
                if($dataArray[0]!='') {
                    if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE')) {
                    	$VehicleDivisionsId = VehicleDivisions::where('name',$dataArray[0])->select('id')->first();	
                        if(isset($VehicleDivisionsId)) {
                            $VehicleRegions->vehicle_division_id=$VehicleDivisionsId->id;
                            $name=$dataArray[1];
                        }
                    }
                    $VehicleRegions->name = trim($name);
                    if($VehicleRegions->name != '')
                    {
                	   $VehicleRegions->save();
                    }
                }
            }
            $cntr++;
        }
        fclose($file);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
