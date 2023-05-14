<?php

namespace App\Console\Commands\WebFleet;

use App\Custom\Client\GoogleMap;
use App\Custom\Client\Webfleet;
use App\Models\TelematicsJourneyDetails;
use App\Models\TelematicsJourneys;
use Carbon\Carbon;
use Illuminate\Console\Command;

class getAccelerationIncidents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webfleet:getAccelerationIncidents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'WebFleet get acceleration and cornering incidents.';

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

        $incidentNamespace = [
            'tm8.dfb2.acc.l',
            'tm8.dfb2.dec.l',
            'tm8.dfb2.cnrl.l',
            'tm8.dfb2.cnrr.l',
        ];

        $incompleteJourneys = TelematicsJourneys::with([
            'details' => function ($query) use ($incidentNamespace) {
                $query->whereIn('ns', $incidentNamespace);
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

                $data = $webfleetClient->getAccelerationIncident($startTime, $endTime, $journey->vrn, $journey->vehicle->webfleet_object_id);

                if (count($data) > 0) {
                    foreach ($data as $row) {
                        if ($row['accel_type'] == 0) {
                            // Acceleration
                            $ns = 'tm8.dfb2.acc.l';

                        } else if ($row['accel_type'] == 1) {
                            // Break
                            $ns = 'tm8.dfb2.dec.l';

                        } else if ($row['accel_type'] == 2) {
                            //2 - steering left
                            $ns = 'tm8.dfb2.cnrl.l';
                        } else if ($row['accel_type'] == 3) {
                            //3 - steering right
                            $ns = 'tm8.dfb2.cnrr.l';
                        }

                        $lat                                  = $webfleetClient->wgs84Toll($row['latitude']);
                        $lon                                  = $webfleetClient->wgs84Toll($row['longitude']);
                        // $address                              = $googleClient->getAddressFromll($lat, $lon);
                        $address                              = $googleClient->setJourneyAddress($row['postext']);
                        $newIncident                          = new TelematicsJourneyDetails();
                        $newIncident->ns                    = $ns;
                        $newIncident->time                  = Carbon ::parse($row['start_time'])->format('Y-m-d H:i:s');
                        $newIncident->telematics_journey_id = $journey->id;
                        $newIncident->vrn                   = $journey->vrn;
                        $newIncident->lat                   = $lat;
                        $newIncident->lon                   = $lon;
                        $newIncident->speed                 = $row['start_speed'];
                        $newIncident->street                = $address['street'];
                        $newIncident->town                  = $address['town'];
                        $newIncident->post_code             = $address['postal_code'];
                        $newIncident->raw_json              = json_encode($row);
                        $newIncident->save();


                    }
                }

                $journey->is_details_added = 3;
                $journey->save();

            }
        }
    }
}
