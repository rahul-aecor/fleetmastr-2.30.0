<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Console\Command;
use App\Models\MaintenanceEvents;
use App\Models\VehicleMaintenanceHistory;

class DailyDistanceWiseMaintenanceCreationEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicle:automaticDistanceWiseMaintenanceEventsCreation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will automatic create distance wise maintenance events';

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
        $vehicleProfileWithDistance = VehicleType::where('service_interval_type', 'Distance')->get()->pluck('id')->toArray();

        if (count($vehicleProfileWithDistance) > 0) {
            $vehicles = Vehicle::whereIn('vehicle_type_id',$vehicleProfileWithDistance)->get()->toArray();
            $user = User::where('email', 'system@imastr.com')->orWhere('username','system@imastr.com')->first();
            $nextServiceInspectionDistanceEvent = MaintenanceEvents::where('slug', 'next_service_inspection_distance')->first();

            if ($vehicles) {
                foreach ($vehicles as $key => $value) {
                    if ($value['next_service_inspection_distance'] == null) {
                        continue;
                    }

                    $vehicleMaintenanceHistory = VehicleMaintenanceHistory::where('vehicle_id', $value['id'])->where('event_planned_distance', $value['next_service_inspection_distance'])->first();

                    if(!$vehicleMaintenanceHistory) {
                        $calculateDistance = $value['next_service_inspection_distance'] - $value['last_odometer_reading'];
                        if ($calculateDistance < config('config-variables.minimum_service_interval')) {
                            $vehicleHistory = new VehicleMaintenanceHistory();
                            $vehicleHistory->vehicle_id = $value['id'];
                            $vehicleHistory->event_type_id = $nextServiceInspectionDistanceEvent->id;
                            $vehicleHistory->event_status = 'Incomplete';
                            $vehicleHistory->event_planned_distance = $value['next_service_inspection_distance'];
                            $vehicleHistory->created_by = $user->id;
                            $vehicleHistory->updated_by = $user->id;
                            $vehicleHistory->save();
                        }    
                    }
                }
            }
        }
    }
}
