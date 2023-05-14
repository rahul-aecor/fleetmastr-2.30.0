<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Vehicle;
use DB;

class UpdateDivisonRegionLinkings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:divisonregionlocation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate User and Vehicle as per divison - region - location linking.';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // process user as per env configuration
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $vehicleFile = storage_path('vehicle/vehicle-division-region-location-not-found'.date('YmdHis').'.csv');
        $userFile = storage_path('user/user-division-region-location-not-found'.date('YmdHis').'.csv');
        $userAccessibleFile = storage_path('user/user-accessible-region'.date('YmdHis').'.csv');
        $vehicleHandle = fopen($vehicleFile, 'w');
        $userHandle = fopen($userFile, 'w');
        $userAccessibleHandle = fopen($userAccessibleFile, 'w');
        fputcsv($vehicleHandle, [
            'Vehicle Id', 'Vehicle Registration', 'comment',
        ]);
        fputcsv($userHandle, [
            'user Id', 'User Email', 'comment',
        ]);
        fputcsv($userAccessibleHandle, [
            'user Id','Region',
        ]);
        User::withTrashed()->chunk(50, function ($users) use ($userHandle,$userAccessibleHandle) {
            foreach ($users as $user) {
                $update = [];
                $existingDivision = (isset($user->division) && !is_null($user->division)) ? trim($user->division) : '';
                if($existingDivision != '') {
                    $userDivison = DB::table('user_divisions')->where('name', $existingDivision)->first();
                    if(!empty($userDivison)) {
                        $update['user_division_id'] = $userDivison->id;
                    }
                    else{
                        fputcsv($userHandle, [$user->id, $user->email,'Division not found.']);
                    }
                }
                $existingRegion = (isset($user->region) && !is_null($user->region)) ? trim($user->region) : '';
                if($existingRegion != '') {

                    if(env('IS_DIVISION_REGION_LINKED_IN_USER') && isset($userDivison->id)) {
                        $userRegion = DB::table('user_regions')->where('name', $existingRegion)->where('user_division_id', $userDivison->id)->first();
                    } else {
                        $userRegion = DB::table('user_regions')->where('name', $existingRegion)->first();
                    }
                    if(!empty($userRegion)) {
                        $update['user_region_id'] = $userRegion->id;
                    }
                    else{
                        fputcsv($userHandle, [$user->id, $user->email,'Region not found.']);
                    }
                }

                $existingLocation = (isset($user->base_location) && !is_null($user->base_location)) ? trim($user->base_location) : '';
                if($existingLocation != '') {

                    if(env('IS_REGION_LOCATION_LINKED_IN_USER') && isset($userRegion->id)) {
                        $userLocation = DB::table('user_locations')->where('name', $existingLocation)->where('user_region_id', $userRegion->id)->first();
                    } else {
                        $userLocation = DB::table('user_locations')->where('name', $existingLocation)->first();
                    }
                    if(!empty($userLocation)) {
                        $update['user_locations_id'] = $userLocation->id;
                    }
                    else{
                        fputcsv($userHandle, [$user->id, $user->email,'Location not found.']);
                    }
                }

                // accessible_regions
                $accessibleRegions = (!empty($user->accessible_regions) && $user->accessible_regions !== null && $user->accessible_regions) ? json_decode($user->accessible_regions) : [];
                if(is_array($accessibleRegions) && count($accessibleRegions) > 0) {
                    $userAccessibleDivisions = [];
                    $userAccessibleRegions = [];
                    
                    foreach ($accessibleRegions as $region) {
                        if($region != '') {

                            if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE')) {
                                $vehicleRegion = DB::table('vehicle_regions')->where('name', $region)->first();
                                if($vehicleRegion && $vehicleRegion->vehicle_division_id) {
                                    $userAccessibleDivisions[] = $vehicleRegion->vehicle_division_id;
                                }
                            } else {
                                $vehicleRegion = DB::table('vehicle_regions')->where('name', $region)->first();
                            }

                            if(!empty($vehicleRegion)) {
                                $userAccessibleRegions[] = $vehicleRegion->id;
                            }
                            fputcsv($userAccessibleHandle, [$user->id, $region]);
                        }
                    }
                }

                if(!empty($update)) {
                    $user->update($update);
                }

                if(!empty($userAccessibleRegions)) {
                    $user->regions()->sync($userAccessibleRegions);
                }

                if(!empty($userAccessibleDivisions)) {
                    $user->divisions()->sync($userAccessibleDivisions);
                }
            }
        });

        // process user as per env configuration
        Vehicle::withTrashed()->chunk(50, function ($vehicles) use ($vehicleHandle){
            foreach ($vehicles as $vehicle) {
                $updateVehicle = [];
                $existingDivision = (isset($vehicle->vehicle_division) && !is_null($vehicle->vehicle_division)) ? trim($vehicle->vehicle_division) : '';
                if($existingDivision != '') {
                    $vehicleDivison = DB::table('vehicle_divisions')->where('name', $existingDivision)->first();
                    if(!empty($vehicleDivison)) {
                        $updateVehicle['vehicle_division_id'] = $vehicleDivison->id;
                    }
                     else{
                        fputcsv($vehicleHandle, [$vehicle->id, $vehicle->registration,'Division not found.']);
                    }
                }

                $existingRegion = (isset($vehicle->vehicle_region) && !is_null($vehicle->vehicle_region)) ? trim($vehicle->vehicle_region) : '';
                if($existingRegion != '') {

                    if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE') && isset($vehicleDivison->id)) {
                        $vehicleRegion = DB::table('vehicle_regions')->where('name', $existingRegion)->where('vehicle_division_id', $vehicleDivison->id)->first();
                    } else {
                        $vehicleRegion = DB::table('vehicle_regions')->where('name', $existingRegion)->first();
                    }
                    if(!empty($vehicleRegion)) {
                        $updateVehicle['vehicle_region_id'] = $vehicleRegion->id;
                    }
                    fputcsv($vehicleHandle, [$vehicle->id, $vehicle->registration,'Region not found.']);
                }

                $existingLocation = (isset($vehicle->vehicle_location_id) && !is_null($vehicle->vehicle_location_id)) ? trim($vehicle->vehicle_location_id) : '';
                if($existingLocation != '') {
                    $vehicleLocationResult = DB::table('vehicle_locations_old')->where('id', $existingLocation)->first();
                    if(!empty($vehicleLocationResult) && isset($vehicleLocationResult->name)) {
                        if(env('IS_REGION_LOCATION_LINKED_IN_VEHICLE') && isset($vehicleRegion->id)) {
                            $vehicleLocation = DB::table('vehicle_locations')->where('name', $vehicleLocationResult->name)->where('vehicle_region_id', $vehicleRegion->id)->first();   
                        } else {
                            $vehicleLocation = DB::table('vehicle_locations')->where('name', $vehicleLocationResult->name)->first();
                        }
                    }

                    if(!empty($vehicleLocation)) {
                        $updateVehicle['vehicle_location_id'] = $vehicleLocation->id;
                    }
                    fputcsv($vehicleHandle, [$vehicle->id, $vehicle->registration,'location not found.']);
                }

                if(!empty($updateVehicle)) {
                    $vehicle->update($updateVehicle);
                }
            }
        });
        fclose($userHandle);
        fclose($userAccessibleHandle);
        fclose($vehicleHandle);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
