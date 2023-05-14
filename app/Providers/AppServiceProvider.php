<?php

namespace App\Providers;

use App\Custom\Helper\Common;
use App\Http\Kernel;
use App\Http\Middleware\XssSanitizer;
use App\Services\VehicleService;
use Illuminate\Support\Facades\DB;
use Log;
use Carbon\Carbon;
use App\Models\Vehicle;
use App\Models\VehicleVORLog;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(UrlGenerator $url)
    {
        if (env('APP_ENV') === 'production') {
            $url->forceSchema('https');
        }

        $classicUsers = env('CLASSIC_USERS') !== NULL ? explode(",", env('CLASSIC_USERS')) : [];
        if((isset($_REQUEST['email']) && in_array($_REQUEST['email'], $classicUsers)) ||
            (isset($_REQUEST['identity']) && in_array($_REQUEST['identity'], $classicUsers)) ||
            (isset($_COOKIE['classic-user']) && $_COOKIE['classic-user'] == 'yes') ||
            (isset($_SERVER['HTTP_EMAIL']) && in_array($_SERVER['HTTP_EMAIL'], $classicUsers))) {
            $schemaName = env('DB_DATABASE_CLASSIC_USERS');
            $mysqlConn = \DB::connection();
            $mysqlConn->getPdo()->exec("USE $schemaName;");
            $mysqlConn->setDatabaseName($schemaName);
            if(!isset($_COOKIE['classic-user'])) {
                setcookie('classic-user', 'yes', time() + (86400 * 30), "/");
            }
        }

        Vehicle::updating(function($new_vehicle){

            $old_vehicle = Vehicle::with('type')->withTrashed()->find($new_vehicle->id);
            $newStatus = $new_vehicle->status;
            $oldStatus = $old_vehicle->status;

            //Handle Skip odometer notification case
            if ($old_vehicle->type->service_interval_type == 'Distance') {
                $newOdometer = (int)$new_vehicle->last_odometer_reading;
                $oldNextServiceInspection = (int)$old_vehicle->next_service_inspection_distance;
                $odometerToCheck = (int)$old_vehicle->next_service_inspection_distance - 1000;
                if ($newOdometer > $oldNextServiceInspection) {
                    if ((int)$old_vehicle->last_service_distance_notification_odometer <= $odometerToCheck) {
                        $vehicleService = new VehicleService();
                        $vehicleService->sendVehicleMaintenanceServiceDistanceNotification($old_vehicle, true);
                    }
                }
            }

            //Update next_service_inspection_distance on vehicle update
            //FLEE-6674 - Ignore following code
            // if ($new_vehicle->last_odometer_reading != $old_vehicle->last_odometer_reading) {

            //     if ($new_vehicle->last_odometer_reading) {
            //         $vehicleType = $new_vehicle->type;
            //         if($vehicleType->service_interval_type && $vehicleType->service_inspection_interval != "") {
            //             $commonHelper = new Common();
            //             $nextServiceInspectionDistance = $commonHelper->getNextServiceInspectionDistance($new_vehicle->last_odometer_reading,$vehicleType->service_inspection_interval);
            //             if ($nextServiceInspectionDistance > $new_vehicle->next_service_inspection_distance) {
            //                 DB::table('vehicles')->where('id',$new_vehicle->id)->update(['next_service_inspection_distance' => $nextServiceInspectionDistance]);
            //             }
            //         }
            //     } else {
            //         DB::table('vehicles')->where('id',$new_vehicle->id)->update(['next_service_inspection_distance' => null]);
            //     }
            // }

            if(starts_with($newStatus,'VOR')){
               $dateOffRoad = (isset(request()->vor_date)) ? Carbon::createFromFormat('d M Y', request()->vor_date)->toDateTimeString() : date('Y-m-d H:i:s');
            }

            if(!starts_with($oldStatus,'VOR') && starts_with($newStatus, 'VOR')){
                // Insert into VehicleVORLogs
                $vehicleLog = new VehicleVORLog();
                $vehicleLog->vehicle_id = $new_vehicle->id;
                $vehicleLog->dt_off_road = $dateOffRoad;
                $vehicleLog->created_by = $new_vehicle->updated_by;
                $vehicleLog->updated_by = $new_vehicle->updated_by;
                $vehicleLog->save();
            }
            else if (starts_with($oldStatus, 'VOR') &&  !starts_with($newStatus, 'VOR')){
                // Update VehicleVORLogs
                $vehicleLog = VehicleVORLog::where('vehicle_id',$new_vehicle->id)->whereNull('dt_back_on_road')->orderBy('created_at', 'desc')->first();
                if(!empty($vehicleLog)){
                    $vehicleLog->dt_back_on_road = date('Y-m-d H:i:s');
                    $vehicleLog->updated_by = $new_vehicle->updated_by;
                    $vehicleLog->save();
                }
            } else if (starts_with($oldStatus, 'VOR')) {
                $vehicleLog = VehicleVORLog::where('vehicle_id',$new_vehicle->id)->whereNull('dt_back_on_road')->orderBy('created_at', 'desc')->first();
                if(!empty($vehicleLog)){
                    $vehicleLog->dt_off_road = $dateOffRoad;
                    $vehicleLog->updated_by = $new_vehicle->updated_by;
                    $vehicleLog->save();
                } else {
                    $vehicleLog = new VehicleVORLog();
                    $vehicleLog->vehicle_id = $new_vehicle->id;
                    $vehicleLog->dt_off_road = $dateOffRoad;
                    $vehicleLog->created_by = $new_vehicle->updated_by;
                    $vehicleLog->updated_by = $new_vehicle->updated_by;
                    $vehicleLog->save();
                }
            }
                  
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
