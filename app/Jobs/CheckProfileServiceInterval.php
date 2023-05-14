<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\MaintenanceEvents;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use Carbon\Carbon;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\VehicleMaintenanceHistory;

class CheckProfileServiceInterval extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $event;
    protected $profileId;

    public function __construct($event, $profileId)
    {
        $this->event = $event;
        $this->profileId = $profileId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $event = $this->event;
        $profileId = $this->profileId;

        $serviceMapping = config('config-variables.service_column_mapping');
        $service = $serviceMapping[$event];

        $getColumn = $service['get_column'];
        $setColumn = $service['set_column'];

        $profile = VehicleType::withTrashed()->find($profileId);
        $serviceInterval = $profile->toArray()[$getColumn];

        $vehicles = Vehicle::where('vehicle_type_id', $profileId)->get();

        $maintenanceEvent = MaintenanceEvents::where('slug',$event)->first();

        foreach ($vehicles as $key => $vehicle) {
            $condition = ['vehicle_id' => $vehicle->id, 'event_type_id' => $maintenanceEvent->id];
            $maintenance = VehicleMaintenanceHistory::where($condition)
                                ->orderBy('event_date', 'desc')
                                ->first();
            if($maintenance) {
                $serviceDt = $maintenance->event_date;
                $serviceDt = Carbon::parse($serviceDt);

                $interval = \DateInterval::createFromDateString($serviceInterval);
                $nxtServiceDt = $serviceDt->add($interval);
                $vehicle->$setColumn = $nxtServiceDt->format('d M Y');
                $vehicle->save();
            }
        }
    }
}
