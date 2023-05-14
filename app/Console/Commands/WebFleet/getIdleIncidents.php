<?php

namespace App\Console\Commands\WebFleet;

use App\Custom\Client\GoogleMap;
use App\Custom\Client\Webfleet;
use App\Models\TelematicsJourneyDetails;
use App\Models\TelematicsJourneys;
use Carbon\Carbon;
use Illuminate\Console\Command;

class getIdleIncidents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webfleet:getIdleIncidents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Idle incidents of journey and store in details table.';

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
        $googleClient   = new GoogleMap();

        $idleIncidents = [
            'tm8.gps.idle.start',
            'tm8.gps.idle.end',
        ];

        $incompleteJourneys = TelematicsJourneys::with([
            'details' => function ($query) use ($idleIncidents) {
                $query->whereIn('ns', $idleIncidents);
                $query->orderBy('time', 'desc');
            }
        ])->get();

        if ($incompleteJourneys->count() > 0) {
            foreach ($incompleteJourneys as $journey) {
                $startTime = $journey->start_time;
                if ($journey->details->count() > 0) {
                    $startTime = $journey->details->first()->time;
                }
                $endTime   = Carbon::now()->format('Y-m-d H:i:s');
                $details   = $webfleetClient->getIdleIncident($startTime, $endTime, $journey->vrn, $journey->vehicle->webfleet_object_id);
                    if (count($details) > 0) {

                    foreach ($details as $detail) {

                        if ($detail) {

                            $detail['start_time'] = $detail['start_time'] . ':00';
                            $detail['end_time']   = $detail['end_time'] . ':00';
                            //Idle Start
                            $lat                                 = $webfleetClient->wgs84Toll($detail['latitude']);
                            $lon                                 = $webfleetClient->wgs84Toll($detail['longitude']);
                            // $address                             = $googleClient->getAddressFromll(sprintf("%f", $lat), sprintf("%f", $lon));
                            $address                             = $googleClient->setJourneyAddress($detail['postext']);
                            $indleStart                          = new TelematicsJourneyDetails();
                            $indleStart->telematics_journey_id = $journey->id;
                            $indleStart->vrn                   = $journey->vrn;
                            $indleStart->ns                    = 'tm8.gps.idle.start';
                            $indleStart->time                  = $webfleetClient->timeToUtc($detail['start_time']);
                            $indleStart->lat                   = $lat;
                            $indleStart->lon                   = $lon;
                            $indleStart->street                = $address['street'];
                            $indleStart->town                  = $address['town'];
                            $indleStart->post_code             = $address['postal_code'];
                            $indleStart->save();

                            //Idle End
                            $indleEnd                          = new TelematicsJourneyDetails();
                            $indleEnd->telematics_journey_id = $journey->id;
                            $indleEnd->vrn                   = $journey->vrn;
                            $indleEnd->ns                    = 'tm8.gps.idle.end';
                            $indleEnd->time                  = $webfleetClient->timeToUtc($detail['end_time']);
                            $indleEnd->idle_duration         = $detail['idle_duration'];
                            $indleEnd->lat                   = $lat;
                            $indleEnd->lon                   = $lon;
                            $indleEnd->street                = $address['street'];
                            $indleEnd->town                  = $address['town'];
                            $indleEnd->post_code             = $address['postal_code'];
                            $indleEnd->save();
                        }
                    }
                }

                $journey->is_details_added = 2;
                $journey->save();
            }
        }

    }
}