<?php

namespace App\Console\Commands;

use App\Models\MaintenanceEvents;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleMaintenanceHistory;
use Illuminate\Console\Command;

class DailyCreationMaintenanceEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicle:automaticCreationMaintenanceEvents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will automatic create maintenance events';

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
        $maintenanceEvents = config('config-variables.automaticMaintenanceEvent');
        $user = User::where('first_name','System')->first();

        foreach ($maintenanceEvents as $key => $eventData) {
            $startDate = Carbon::today()->addDays(29)->format('Y-m-d');
            if($eventData['event'] == 'adr_test') {
                $startDate = Carbon::today()->addDays(89)->format('Y-m-d');
            }
            $vehicleData = Vehicle::where($eventData['date'], '=', $startDate)
                            ->get()->toArray();

            if($vehicleData){
                foreach ($vehicleData as $key => $value) {
                    $maintenanceEvent = MaintenanceEvents::where('slug',$eventData['event'])->first();
                    if ($maintenanceEvent) {
                        $vehicleMaintenanceHistory = VehicleMaintenanceHistory::where('vehicle_id', $value['id'])
                                                            ->where('event_type_id', $maintenanceEvent->id)
                                                            ->where('event_plan_date', Carbon::parse($value[$eventData['date']])->format('Y-m-d'))
                                                            ->first();

                        if(!isset($vehicleMaintenanceHistory)) {
                            \Log::info('Creating entry for event '.$eventData['event'].' and start date is '.$startDate);
                            $vehicleHistory = new VehicleMaintenanceHistory();
                            $vehicleHistory->vehicle_id = $value['id'];
                            $vehicleHistory->event_type_id = $maintenanceEvent->id;
                            $vehicleHistory->event_plan_date = $value[$eventData['date']];
                            $vehicleHistory->event_status = 'Incomplete';
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
