<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Models\Vehicle;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;


class webfleetUpdateVehicleLastJourney extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicle:webfleetUpdateVehicleLastJourney';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to update the Last Journey Details for all the vehicles for WebFleet Provider';

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
        $vehiclesToUpdate = DB::table('vehicles')
                            ->select('vehicles.id','vehicles.registration', 'vehicles.telematics_latest_journey_time', 'vehicles.telematics_latest_journey_id', DB::raw('(select start_time from telematics_journeys where vehicle_id=vehicles.id and is_details_added=1 order by start_time desc limit 1) as vehicle_last_journey'), DB::raw('(select id from telematics_journeys where vehicle_id=vehicles.id and is_details_added=1 order by start_time desc limit 1) as vehicle_last_journey_id'))
                            ->whereNull('vehicles.deleted_at')
                            ->where('vehicles.is_telematics_enabled',1)
                            ->whereNotNull('vehicles.telematics_ns')
                            ->groupBy('vehicles.id')                            
                            ->havingRaw('vehicles.telematics_latest_journey_time<>vehicle_last_journey')
                            ->get();

        foreach ($vehiclesToUpdate as $veh) {
            print_r("Updating Vehicle : " . $veh->registration . "\n");
            $vehicle = Vehicle::find($veh->id);
            $vehicle->telematics_latest_journey_id = $veh->vehicle_last_journey_id;
            $vehicle->telematics_latest_journey_time = $veh->vehicle_last_journey;
            $vehicle->save(['timestamps'=>false]);
        }
    }
}
