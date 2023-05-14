<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Console\Command;
use App\Models\VehicleMaintenanceHistory;

class CheckProfileServiceInterval extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:profileServiceInterval
                            {--S|service= : Service Name}
                            {--P|profile= : Vehicle Profile Type}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will execute if any of service interval gets update in Vehicle Profile, it check - Service inspection interval, PTO service interval, PMI interval, Invertor service interval, Compressor service interval';

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
        $event = $this->option('service');
        $profileId = $this->option('profile');

        $serviceMapping = config('config-variables.service_column_mapping');
        $service = $serviceMapping[$event];

        $getColumn = $service['get_column'];
        $setColumn = $service['set_column'];

        $profile = VehicleType::withTrashed()->find($profileId);
        $serviceInterval = $profile->toArray()[$getColumn];

        $vehicles = Vehicle::where('vehicle_type_id', $profileId)->get();

        foreach ($vehicles as $key => $vehicle) {
            $condition = ['vehicle_id' => $vehicle->id, 'event_type' => $event];
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
