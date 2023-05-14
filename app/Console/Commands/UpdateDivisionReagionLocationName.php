<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleDivisions;
use App\Models\VehicleRegions;
use App\Models\VehicleLocations;
use App\Models\UserDivision;
use App\Models\UserRegion;
use App\Models\UserLocation;
use DB;

class UpdateDivisionReagionLocationName extends Command 
{
    protected $signature = 'update:divisonregionlocationname';
    protected $description = 'insert divison - region - location  name into  User and Vehicle table for triio.';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $vehicleFile = storage_path('vehicle/unmatch_vehicle_data'.date('YmdHis').'.csv');
        $userFile = storage_path('user/unmatch_user_data'.date('YmdHis').'.csv');
        $vehicleHandle = fopen($vehicleFile, 'w');
        $userHandle = fopen($userFile, 'w');
        fputcsv($vehicleHandle, [
            'Vehicle Registration',
        ]);
        fputcsv($userHandle, [
            'UserName',
        ]);

        //***vehicle*****
        $filename = strtolower(env('BRAND_NAME')."/"."vehicle_list.csv");        
        $file = fopen(base_path("database" . DIRECTORY_SEPARATOR . "seeds" . DIRECTORY_SEPARATOR . $filename), "r");
        $cntr = 0;
        while(!feof($file)){
            $line = fgets($file);
            $dataArray = explode("\t", $line);

             if($cntr > 0)
            {
                if($dataArray[0]!='')
                {
                    $Vehicle = Vehicle::where('registration',$dataArray[0])->select('id')->first();
                    $VehicleDivisionsId = VehicleDivisions::where('name',$dataArray[14])->select('id')->first();
                    if($VehicleDivisionsId)
                    {
                        $VehicleRegionsId = VehicleRegions::where('name',$dataArray[15])->where('vehicle_division_id',$VehicleDivisionsId->id)->select('id')->first();
                        $updateVehicle['vehicle_division_id']=$VehicleDivisionsId->id;
                    }
                    if($VehicleRegionsId)
                    {
                        $VehicleLocationId = VehicleLocations::where('name',$dataArray[16])->where('vehicle_region_id',$VehicleRegionsId->id)->select('id')->first();
                        $updateVehicle['vehicle_region_id']=$VehicleRegionsId->id;
                    }
                    if($VehicleLocationId)
                    {
                            $updateVehicle['vehicle_location_id']=$VehicleLocationId->id;
                    }
                    if($VehicleDivisionsId && $VehicleRegionsId && $VehicleLocationId && $Vehicle )
                    {
                            $Vehicle->update($updateVehicle);
                    }
                    else
                    {
                        fputcsv($vehicleHandle, [$dataArray[0]]);
                    }
                }
            }

            $cntr++;
        }
        fclose($file);
        fclose($vehicleHandle);
        //*********** users*************
        $filename = strtolower(env('BRAND_NAME')."/"."user_list.csv");        
        $file = fopen(base_path("database" . DIRECTORY_SEPARATOR . "seeds" . DIRECTORY_SEPARATOR . $filename), "r");
        $cntr = 0;
        while(!feof($file)){
            $line = fgets($file);
            $dataArray = explode("\t", $line);
            if($cntr > 0)
            {
                if($dataArray[0]!='')
                {
                    $User = User::where('username',$dataArray[3])->select('id')->first();
                    $UserDivisionsId = UserDivision::where('name',$dataArray[4])->select('id')->first();
                    if($UserDivisionsId)
                    {
                        $updateUser['vehicle_division_id']=$UserDivisionsId->id;
                        $UserRegionsId = UserRegion::where('name',$dataArray[5])->where('user_division_id',$UserDivisionsId->id)->select('id')->first();
                    }
                    if($UserRegionsId)
                    {
                        $updateUser['vehicle_region_id']=$UserRegionsId->id;
                        $UserLocationId = UserLocation::where('name',$dataArray[6])->where('user_region_id',$UserRegionsId->id)->select('id')->first();
                    }
                    if($UserLocationId)
                    {
                        $updateUser['vehicle_location_id']=$UserLocationId->id;
                    }

                    if($UserDivisionsId && $UserRegionsId && $UserLocationId && $User){
                            $User->update($updateUser);
                    }
                    else
                    {
                        fputcsv($userHandle, [$dataArray[0]]);
                    }
                }
            }
            $cntr++;
        }
        fclose($file);
        fclose($userHandle);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
