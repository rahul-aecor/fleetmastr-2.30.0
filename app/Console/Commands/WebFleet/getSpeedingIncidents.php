<?php

namespace App\Console\Commands\WebFleet;

use App\Custom\Client\GoogleMap;
use App\Custom\Client\Webfleet;
use App\Models\TelematicsJourneyDetails;
use App\Models\TelematicsJourneys;
use Carbon\Carbon;
use Illuminate\Console\Command;

class getSpeedingIncidents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webfleet:getSpeedingIncidents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $webfleetClient = new Webfleet();

        $googleClient = new GoogleMap();

        $incompleteJourneys = TelematicsJourneys ::with([
            'details' => function ($query) {
                $query->where('ns', 'tm8.dfb2.spd');
                $query->orderBy('time', 'desc');
            }
        ])->get();

        if (count($incompleteJourneys) > 0) {

            foreach ($incompleteJourneys as $journey) {
                $startTime = $journey->start_time;
                if ($journey->details->count() > 0) {
                    $startTime = $journey->details->first()->time;
                }

                $endTime = Carbon::now()->format('Y-m-d H:i:s');

                $data = $webfleetClient->getSpeedingIncident($startTime, $endTime, $journey->vrn, $journey->vehicle->webfleet_object_id);

                if (count($data) > 0) {

                    foreach ($data as $row) {

                        $lat     = $webfleetClient->wgs84Toll($row['start_latitude']);
                        $lon     = $webfleetClient->wgs84Toll($row['start_longitude']);
                        // $address = $googleClient->getAddressFromll($lat, $lon);
                        $address = $googleClient->setJourneyAddress($row['start_postext']);

                        $newSpeedingIncident                          = new TelematicsJourneyDetails();
                        $newSpeedingIncident->telematics_journey_id = $journey->id;
                        $newSpeedingIncident->time                  = Carbon ::parse($row['start_time'])->format('Y-m-d H:i:s');
                        // $newSpeedingIncident->ns                    = 'tm8.dfb2.spd';
                        $newSpeedingIncident->ns                    = 'tm8.dfb2.spdinc';
                        $newSpeedingIncident->vrn                   = $journey->vrn;
                        $newSpeedingIncident->lat                   = $lat;
                        $newSpeedingIncident->lon                   = $lon;
                        $newSpeedingIncident->speed                 = $row['max_speed'];
                        $newSpeedingIncident->street_speed          = $row['road_speedlimit'];
                        $newSpeedingIncident->street                = $address['street'];
                        $newSpeedingIncident->town                  = $address['town'];
                        $newSpeedingIncident->post_code             = $address['postal_code'];
                        $newSpeedingIncident->raw_json              = json_encode($row);
                        $newSpeedingIncident->save();

                    }
                }

                $journey->is_details_added = 4;
                $journey->save();
            }
        }
    }
}
