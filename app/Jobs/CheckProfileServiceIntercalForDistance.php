<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Custom\Helper\Common;

class CheckProfileServiceIntercalForDistance extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $event;
    protected $profileId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
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

        $common = new Common();
        
        $profile = VehicleType::withTrashed()->find($profileId);
        $vehicles = Vehicle::where('vehicle_type_id', $profileId)->get();
        foreach ($vehicles as $key => $vehicle) {            
            $nextServiceInspectionDistance = $common->getNextServiceInspectionDistance($vehicle->last_odometer_reading, $profile->service_inspection_interval);
            $vehicle->next_service_inspection_distance = $nextServiceInspectionDistance;
            $vehicle->save();
        }
    }
}
