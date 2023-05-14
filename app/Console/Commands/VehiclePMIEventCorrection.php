<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\MaintenanceEvents;
use App\Models\VehicleMaintenanceHistory;


use Illuminate\Console\Command;

class VehiclePMIEventCorrection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicle:vehiclePMIEventCorrection';

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
        $user = User::where('first_name', 'System')->first();
        $pmiEventId = MaintenanceEvents::where('slug', 'preventative_maintenance_inspection')->first()->id;
        $vehicles = Vehicle::selectRaw('id, first_pmi_date, next_pmi_date, DATEDIFF(next_pmi_date, first_pmi_date) as datediff')->get();
        $updatedPmiDate = "";
        foreach($vehicles as $vehicle) {
            $this->info('**** checking for vehicle id: '.$vehicle->id);
            \Log::info('**** checking for vehicle id: '.$vehicle->id);
            $days = ($vehicle->datediff / 42) + 1;
            $this->info($days);
            \Log::info($days);
            $updatedPmiDate = $vehicle->first_pmi_date;
            for($i = 0; $i < $days; $i++) {
                $this->info($updatedPmiDate);
                $pmiDate = $i == 0 ? Carbon::parse($updatedPmiDate)->toDateString() : Carbon::parse($updatedPmiDate)->addWeeks(6)->toDateString();
                $maintenanceHistory = VehicleMaintenanceHistory::where('vehicle_id', $vehicle->id)
                                                            ->where('event_type_id', $pmiEventId)
                                                            ->where('event_plan_date', $pmiDate);

                $maintenanceHistoryCount = $maintenanceHistory->count();
                $maintenanceHistoryData = $maintenanceHistory->get();
                $this->info('count for date: '.$pmiDate.' and vehicle_id: '.$vehicle->id." is ". $maintenanceHistoryCount);
                \Log::info('count for date: '.$pmiDate.' and vehicle_id: '.$vehicle->id." is ". $maintenanceHistoryCount);

                if(!isset($maintenanceHistoryData[0])) {
                    $this->info('creating entry for date: '.$pmiDate);
                    \Log::info('creating entry for date: '.$pmiDate);
                    $vehicleHistory = new VehicleMaintenanceHistory();
                    $vehicleHistory->vehicle_id = $vehicle->id;
                    $vehicleHistory->event_type_id = $pmiEventId;
                    $vehicleHistory->event_plan_date = $pmiDate;
                    $vehicleHistory->event_status = 'Incomplete';
                    $vehicleHistory->created_by = $user->id;
                    $vehicleHistory->updated_by = $user->id;
                    $vehicleHistory->save();
                } else {
                    if($maintenanceHistoryCount > 1) {
                        foreach($maintenanceHistoryData as $key => $entry) {
                            if($key == 0) {
                                continue;
                            }
                            if($entry->event_status == 'Incomplete') {
                                $this->info('deleting entry for date: '.$pmiDate.' and vehicle_id: '.$vehicle->id);
                                \Log::info('deleting entry for date: '.$pmiDate.' and vehicle_id: '.$vehicle->id);
                                $entry->delete();
                            }
                        }
                    }
                }

                $updatedPmiDate = $pmiDate;

            }
        }
    }
}