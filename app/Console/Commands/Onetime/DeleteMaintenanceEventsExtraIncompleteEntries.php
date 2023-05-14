<?php

namespace App\Console\Commands\Onetime;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleMaintenanceHistory;
use App\Models\MaintenanceEvents;
use App\Models\VehicleType;
use Carbon\Carbon;

class DeleteMaintenanceEventsExtraIncompleteEntries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:extra-maintenance-events-entries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete extra maintenance events entries';

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
        $maintenanceEventsData = MaintenanceEvents::where('is_standard_event',1)->get()->lists('id','slug');
        $serviceMapping = collect(config('config-variables.service_column_mapping'));

        foreach($maintenanceEvents as $event) {
            if(!isset($maintenanceEventsData[$event['event']])) {
                continue;
            }
            $eventId = $maintenanceEventsData[$event['event']];
            $eventSlug = $event['event'];
            $vehicles = Vehicle::withTrashed()->get();
            foreach($vehicles as $vehicle) {
                $vehicleMaintenanceHistory = VehicleMaintenanceHistory::where('event_type_id', $eventId)
                                                                    ->where('event_status', 'Incomplete')
                                                                    ->where('vehicle_id', $vehicle->id)
                                                                    ->orderBy('event_plan_date')
                                                                    ->get();

                $this->info('checking for vehicle id: '.$vehicle->id.' and event: '.$event['event'].' and count: '.$vehicleMaintenanceHistory->count());
                if($vehicleMaintenanceHistory->count() > 1) {
                    foreach($vehicleMaintenanceHistory as $key => $maintenanceHistory) {
                        if($key == 0) {
                            continue;
                        }

                        $maintenanceHistory->delete();
                    }
                } else if($vehicleMaintenanceHistory->count() == 0) {
                    $getColumn = $serviceMapping[$eventSlug]['get_column'];
                    $setColumn = $serviceMapping[$eventSlug]['set_column'];
                    if($vehicle->$setColumn == '' || $vehicle->$setColumn == NULL) {
                        continue;
                    }
                    $vehicleType = $vehicle->type;

                    if (isset($getColumn) && trim($getColumn) != '' && $vehicleType->$getColumn != '' && $vehicleType->$getColumn) {
                        $interval = $vehicleType->$getColumn;
                        $this->info('if cond: vehicleTypeId: '.$vehicleType->id.' and getColumn: '.$getColumn.' and interval: '.$interval);
                    } else {
                        $interval = $serviceMapping[$eventSlug]['interval'];
                        $this->info('else cond: vehicleTypeId: '.$vehicleType->id.' and eventSlug: '.$eventSlug.' and interval: '.$interval);
                    }
                    if($interval == '' || $interval == NULL) {
                        continue;
                    }
                    $interval = \DateInterval::createFromDateString($interval);

                    $fetchLastCompletedEvent = VehicleMaintenanceHistory::where('event_type_id', $eventId)
                                                                    ->where('event_status', 'Complete')
                                                                    ->where('vehicle_id', $vehicle->id)
                                                                    ->orderBy('event_plan_date', 'desc')
                                                                    ->first();
                    if(isset($fetchLastCompletedEvent)) {
                        $eventDate = Carbon::parse($fetchLastCompletedEvent->event_plan_date);
                    } else {
                        $eventDate = Carbon::parse($vehicle->$setColumn);
                    }

                    $serviceDt = Carbon::parse($eventDate);
                    if($serviceDt->lt(Carbon::today())) {
                        do {
                            $nxtServiceDt = $serviceDt->add($interval);
                        }
                        while ($nxtServiceDt->lt(Carbon::today()));
                    } else {
                        $nxtServiceDt = $serviceDt;
                    }
                    
                    $vehicleHistory = new VehicleMaintenanceHistory();
                    $vehicleHistory->vehicle_id = $vehicle->id;
                    $vehicleHistory->event_type_id = $eventId;
                    $vehicleHistory->event_plan_date = $nxtServiceDt->format('d M Y');
                    $vehicleHistory->event_status = 'Incomplete';
                    $vehicleHistory->created_by = $user->id;
                    $vehicleHistory->updated_by = $user->id;
                    $this->info('Creating VehicleMaintenanceHistory for vehicle id: '.$vehicle->id.', event slug: '.$eventSlug. ', event_plan_date: '.$vehicleHistory->event_plan_date);
                    $vehicleHistory->save();

                    //Update vehicle date
                    $this->info('Updating vehicles for vehicle id: '.$vehicle->id.', old date: '.$vehicle->$setColumn. ', new date: '.$nxtServiceDt->format('d M Y'));
                    \Log::info('Updating vehicles for vehicle id: '.$vehicle->id.', old date: '.$vehicle->$setColumn. ', new date: '.$nxtServiceDt->format('d M Y'));
                    $vehicle->$setColumn = $nxtServiceDt->format('d M Y');
                    $vehicle->save();
                }
            }
        }

        $this->info('$$$$$$$$$ Now creating entry for DISTANCE EVENT $$$$$$$$$');
        \Log::info('$$$$$$$$$ Now creating entry for DISTANCE EVENT $$$$$$$$$');
        // Distance event
        $vehicleProfileWithDistance = VehicleType::where('service_interval_type', 'Distance')->get()->pluck('id')->toArray();

        if (count($vehicleProfileWithDistance) > 0) {
            $vehicles = Vehicle::whereIn('vehicle_type_id',$vehicleProfileWithDistance)->get();
            $nextServiceInspectionDistanceEvent = MaintenanceEvents::where('slug', 'next_service_inspection_distance')->first();

            if (isset($vehicles[0])) {
                foreach ($vehicles as $key => $value) {
                    $profile = $value->type;
                    $value = $value->toArray();
                    if (!$value['next_service_inspection_distance'] && !$profile->service_inspection_interval) {
                        continue;
                    }

                    if (!$value['next_service_inspection_distance']) {
                        $value['next_service_inspection_distance'] = str_replace(",", "", $profile->service_inspection_interval);
                        //Update vehicle value
                        Vehicle::where('id', $value['id'])->update(['next_service_inspection_distance' => $value['next_service_inspection_distance']]);
                    }

                    //Delete incomplete entries first
                    $incompleteEntries = VehicleMaintenanceHistory::where('vehicle_id', $value['id'])->where('event_type_id', $nextServiceInspectionDistanceEvent->id)->where('event_status', 'Incomplete')->get();
                    foreach($incompleteEntries as $entry) {
                        $entry->delete();
                    }

                    $vehicleMaintenanceHistoryData = VehicleMaintenanceHistory::where('vehicle_id', $value['id'])->where('event_planned_distance', $value['next_service_inspection_distance'])->orderBy('event_planned_distance')->get();

                    \Log::info('Checking for vehicle id: '.$value['id'].' and entry count is: '.$vehicleMaintenanceHistoryData->count());
                    $this->info('Checking for vehicle id: '.$value['id'].' and entry count is: '.$vehicleMaintenanceHistoryData->count());
                    if($vehicleMaintenanceHistoryData->count() > 1) {
                        \Log::info('Deleting entries for vehicle id: '.$value['id']);
                        $this->info('Deleting entries for vehicle id: '.$value['id']);
                        foreach($vehicleMaintenanceHistoryData as $key => $maintenanceHistory) {
                            if($key == 0) {
                                continue;
                            }

                            $maintenanceHistory->delete();
                        }
                    } else if($vehicleMaintenanceHistoryData->count() == 0) {
                        \Log::info('Creating entry for vehicle id: '.$value['id']);
                        $this->info('Creating entry for vehicle id: '.$value['id']);
                        $calculateDistance = $value['next_service_inspection_distance'] - $value['last_odometer_reading'];
                        // if ($calculateDistance < config('config-variables.minimum_service_interval')) {
                            $vehicleHistory = new VehicleMaintenanceHistory();
                            $vehicleHistory->vehicle_id = $value['id'];
                            $vehicleHistory->event_type_id = $nextServiceInspectionDistanceEvent->id;
                            $vehicleHistory->event_status = 'Incomplete';
                            $vehicleHistory->event_planned_distance = $value['next_service_inspection_distance'];
                            $vehicleHistory->odomerter_reading = $value['next_service_inspection_distance'];
                            $vehicleHistory->created_by = $user->id;
                            $vehicleHistory->updated_by = $user->id;
                            $vehicleHistory->save();
                        // }
                    }
                }
            }
        }
    }
}
