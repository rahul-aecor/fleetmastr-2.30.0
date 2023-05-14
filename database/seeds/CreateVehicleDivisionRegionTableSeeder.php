<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\VehicleRegions;
use App\Models\VehicleDivisions;
use Illuminate\Database\Eloquent\Model;

class CreateVehicleDivisionRegionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // division
        $vehicleDivisionsCount = DB::table('vehicle_divisions')->count();
        $vehicleAccessibleDivisions = DB::table('user_accessible_divisions')
                                        ->groupBy('user_id')
                                        ->select(DB::raw("count(*) as divisionCount, user_id, vehicle_division_id"))
                                        ->get();

        $userIdsWithAllDivisions = [];
        foreach ($vehicleAccessibleDivisions as $key => $value) {
            if ($value->divisionCount == $vehicleDivisionsCount) {
                array_push($userIdsWithAllDivisions, $value->user_id);
            }
        }

        // region
        $vehicleRegionsCount = DB::table('vehicle_regions')->count();
        $vehicleAccessibleRegions = DB::table('user_accessible_regions')
                                        ->groupBy('user_id')
                                        ->select(DB::raw("count(*) as regionCount, user_id, vehicle_region_id"))
                                        ->get();

        $userIdsWithAllRegions = [];
        foreach ($vehicleAccessibleRegions as $key => $value) {
            if ($value->regionCount == $vehicleRegionsCount) {
                array_push($userIdsWithAllRegions, $value->user_id);
            }
        }   

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // division
        if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE')) {
            DB::table('vehicle_regions')->truncate();
        }

        DB::table('vehicle_divisions')->truncate();
        $userfilePath = strtolower(env('BRAND_NAME')."/"."vehicle_divisions.csv");
        $file = fopen(base_path("database" . DIRECTORY_SEPARATOR . "seeds" . DIRECTORY_SEPARATOR . $userfilePath), "r");
        
        $cntr = 0;
        while(!feof($file)) {
            $line = fgets($file);
            $dataArray = explode("\t", $line);
            if($cntr > 0) {
                $VehicleDivisions = new VehicleDivisions();
                $VehicleDivisions->name = trim($dataArray[0]);
                if($VehicleDivisions->name != '') {
                   $VehicleDivisions->save();
                }
            }
            $cntr++;
        }
        fclose($file);


        // region
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
                        $VehicleRegions->vehicle_division_id=$VehicleDivisionsId->id;
                        $name=$dataArray[1];
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

        // division sync user id
        $vehicleDivisionsIds = VehicleDivisions::lists('id')->toArray();        
        foreach ($userIdsWithAllDivisions as $key => $userId) {
            $user = User::withDisabled()->where('id', $userId)->first();
            $user->divisions()->sync($vehicleDivisionsIds);
        }

        // region sync user id
        $vehicleRegionsIds = VehicleRegions::lists('id')->toArray();
        foreach ($userIdsWithAllRegions as $key => $userId) {
            $user = User::withDisabled()->where('id', $userId)->first();
            $user->regions()->sync($vehicleRegionsIds);
        }
    }
}
