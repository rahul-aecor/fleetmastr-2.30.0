<?php

namespace App\Console\Commands\WebFleet;

use App\Custom\Client\Webfleet;
use App\Models\TelematicsJourneys;
use App\Repositories\TelematicsJourneysRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;

class updateJourney extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webfleet:updateJourney';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Journey';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent ::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $out            = new \Symfony\Component\Console\Output\ConsoleOutput();
        $webfleetClient = new Webfleet();

        /*$startTime = Carbon ::parse('2021-08-24 10:19:59', config('config-variables.format.displayTimezone')) -> setTimezone('UTC') -> subMinute(2) -> format('Y-m-d H:i:s');
        $endTime   = Carbon ::parse('2021-08-24 10:19:59', config('config-variables.format.displayTimezone')) -> setTimezone('UTC') -> format('Y-m-d H:i:s');*/

        $startTime = Carbon::now()->subMinutes(5)->format('Y-m-d H:i:s');
        $endTime = Carbon::now()->format('Y-m-d H:i:s');

        $data = $webfleetClient->getJourney($startTime, $endTime);

        if (count($data) > 0) {
            \Log::info('updateJourney');
            \Log::info(json_encode($data));
            $tripIds = collect($data)->pluck('tripid')->toArray();

            $dbJourneys = TelematicsJourneys::whereIn('journey_id', $tripIds)->get()->keyBy('journey_id');

            foreach ($data as $journey) {
                $out->writeln('============================================================');
                $out->writeln('Searching for trip Id : ' . $journey['tripid']);

                $dbJourney = isset($dbJourneys[$journey['tripid']]) ? $dbJourneys[$journey['tripid']] : null;

                if ($dbJourney) {
                    $out->writeln('Journey found in database for trip Id : ' . $journey['tripid']);
                    $summary                        = $webfleetClient->getJourneyExtraDetails($journey['start_time'], $journey['end_time'], $journey['objectno'], $journey['objectuid']);
                    $summary                      = isset($summary[0]) ? $summary[0] : null;
                    $dbJourney->start_time        = $webfleetClient->timeToUtc($journey['start_time']);
                    $dbJourney->start_lat         = $webfleetClient->wgs84Toll($journey['start_latitude']);
                    $dbJourney->start_lon         = $webfleetClient->wgs84Toll($journey['start_longitude']);
                    $dbJourney->end_lat           = $webfleetClient->wgs84Toll($journey['end_latitude']);
                    $dbJourney->end_lon           = $webfleetClient->wgs84Toll($journey['end_longitude']);
                    $dbJourney->end_time          = $webfleetClient->timeToUtc($journey['end_time']);
                    $dbJourney->odometer          = $journey['end_odometer'];
                    $dbJourney->engine_duration   = $summary ? $summary->operatingtime : null;
                    $dbJourney->fuel              = $summary ? $summary->fuel_usage : null;;
                    $dbJourney->co2               = $journey['co2'];
                    $dbJourney->gps_idle_duration = $journey['idle_time'];
                    $dbJourney->gps_distance      = $journey['distance'];
                    $dbJourney->uid               = $journey['driverno'];
                    $dbJourney->raw_json          = json_encode($journey);
                    $dbJourney->is_details_added  = 0;
                    $dbJourney->save();

                    $out->writeln('Journey updated');

                    $telematicsJourneysRepository = new TelematicsJourneysRepository();
                    $calculatedFields = $telematicsJourneysRepository->getCalculatedFieldsOfJourney($dbJourney->id);
                    $dbJourney->max_speed = $calculatedFields['maxspeed'];
                    $dbJourney->avg_speed = $calculatedFields['avgspeed'];
                    $dbJourney->odometer_start = $calculatedFields['odoStart'];
                    $dbJourney->odometer_end = $calculatedFields['odoEnd'];

                    $dbJourney->incident_count = $calculatedFields['incidentCount'];
                    $dbJourney->harsh_breaking_count = $calculatedFields['harsh_breaking'];
                    $dbJourney->harsh_acceleration_count = $calculatedFields['harsh_acceleration'];
                    $dbJourney->harsh_cornering_count = $calculatedFields['harsh_cornering'];
                    $dbJourney->speeding_count = $calculatedFields['speeding'];
                    $dbJourney->speeding_incident_count = $calculatedFields['new_speeding_incidents'];
                    $dbJourney->rpm_count = $calculatedFields['rpm'];
                    $dbJourney->idling_count = $calculatedFields['idling'];

                    $dbJourney->save();
                    $telematicsJourneysRepository->updateVehicleTotalfuelSumAndDistanceSum($dbJourney->vrn);

                    ///////code to do
                    $vehicle = $dbJourney->vehicle;
                    $journeyDetails = $dbJourney->details()->orderBy('time','DESC')->first();
                    $vehicle->telematics_ns = $journeyDetails->ns;
                    $vehicle->telematics_odometer = $journeyDetails->odometer;
                    $vehicle->telematics_lat = $journeyDetails->lat;
                    $vehicle->telematics_lon = $journeyDetails->lon;
                    $vehicle->telematics_latest_location_time = $journeyDetails->time;
                    if (isset($journeyDetails->post_code)){
                        $vehicle->telematics_postcode = $journeyDetails->post_code;
                        $vehicle->telematics_street = $journeyDetails->street;
                        $vehicle->telematics_town = $journeyDetails->town;
                    }

                    $vehicle->telematics_latest_journey_id = $dbJourney->id;
                    $vehicle->telematics_latest_journey_time = $dbJourney->start_time;

                    $vehicle->save();

                } else {
                    $out->writeln('Trip not found.');
                }
                $out -> writeln('============================================================');
            }

        }
    }
}
