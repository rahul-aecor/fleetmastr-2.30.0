<?php

namespace App\Console\Commands;

use Mail;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Services\VehicleService;
use Illuminate\Console\Command;
use App\Models\VehicleMaintenanceNotification;

class VehicleMaintenanceServiceDistanceNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicle:maintenanceServiceDistanceNotifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will send vehicle maintenance distance notification emails';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(VehicleService $vehicleService)
    {
    	$this->vehicleService = $vehicleService;
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
            $vehicles = Vehicle::with('type')
                                ->whereIn('vehicle_type_id', $vehicleProfileWithDistance)
                                ->whereNotNull('nominated_driver')
                                ->whereNotNull('next_service_inspection_distance')
                                ->get();

            $notification = VehicleMaintenanceNotification::where('event_type', 'next_service_inspection_distance')
                                ->where('is_enabled', true)
                                ->get()
                                ->keyBy('user_id');
                                                    
            foreach ($vehicles as $vehicle) {
            	$this->vehicleService->sendVehicleMaintenanceServiceDistanceNotification($vehicle, false, $notification);               
            }
        }
    }
}
