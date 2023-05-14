<?php

namespace App\Console\Commands\WebFleet;

use App\Custom\Client\GoogleMap;
use App\Custom\Client\Webfleet;
use App\Models\TelematicsJourneyDetails;
use App\Models\TelematicsJourneys;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Services\TelematicsService;
use App\Repositories\TelematicsJourneysRepository;
use App\Jobs\ProcessStreetSpeed;

class getJourneyDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webfleet:getJourneyDetails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get Journey Details';

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
        $telematicsService = new TelematicsService();
        $incompleteJourneys = TelematicsJourneys::with(['details' => function($query){
            $query->orderBy('time', 'desc');
        }, 'vehicle'])->where('is_details_added','=',0)->get();
        $webfleetClient = new Webfleet();

        $googleClient = new GoogleMap();

        if ($incompleteJourneys->count() > 0) {

            foreach ($incompleteJourneys as $journey) {

                $journey->is_details_added = 10;
                $journey->save();

                //Journey End detail entry
                $newStartJourney = TelematicsJourneyDetails::where(
                    [
                        'telematics_journey_id' => $journey->id,
                        'ns' => 'tm8.gps.jny.start',
                    ]
                )->first();

                if (!$newStartJourney) {
                    $lat = $journey->start_lat;
                    $lon = $journey->start_lon;

                    // $address = $googleClient->getAddressFromll($lat,$lon);

                    $newStartJourney = new TelematicsJourneyDetails();

                    $startTime = $journey->start_time;

                    $journeyStartJson = json_decode($journey->raw_json);

                    //Journey Start detail entry
                    $newStartJourney = new TelematicsJourneyDetails();
                    $newStartJourney->telematics_journey_id = $journey->id;
                    $newStartJourney->vrn = $journey->vrn;
                    $newStartJourney->ns = 'tm8.gps.jny.start';
                    $newStartJourney->time = $journey->start_time;
                    $newStartJourney->odometer = $journeyStartJson->start_odometer;
                    $newStartJourney->lat = $lat;
                    $newStartJourney->lon = $lon;
                    $newStartJourney->speed = $webfleetClient->kmphToMps($journeyStartJson->avg_speed); //kmph to mps
                    $newStartJourney->gps_odo = $journeyStartJson->start_odometer;

                    // $newStartJourney->street = $address['street'];
                    // $newStartJourney->town = $address['town'];
                    // $newStartJourney->post_code = $address['postal_code'];
                    $newStartJourney->street = $journey->start_street;
                    $newStartJourney->town = $journey->start_town;
                    $newStartJourney->post_code = $journey->start_post_code;

                    $newStartJourney->raw_json = $journey->raw_json;
                    $newStartJourney->save();
                }

                $startTime = $journey->start_time;
                if($journey->details->count() > 0) {
                    $journeyDetails = $journey->details->filter(function ($detail) {
                                    return $detail->ns === 'tm8.gps';
                                });
                    if($journeyDetails->count() > 0) {
                        $startTime = $journeyDetails->first()->time;
                    }
                }
                // $endTime = Carbon::now()->format('Y-m-d H:i:s');
                $endTime = $journey->end_time;
                if(!$endTime || $endTime == '') {
                    $endTime = Carbon::now()->format('Y-m-d H:i:s');
                }

                $details = $webfleetClient->getJourneySummary($startTime,$endTime,$journey->vrn,$journey->vehicle->webfleet_object_id);
                // print_r($details);
                if(isset($details['errorCode'])) {
                    continue;
                }

                if (count($details) > 0) {
                    foreach ($details as $row) {
                        \Log::info('********** RAW DATA *************');
                        \Log::info($row);
                        \Log::info('********** RAW DATA *************');

                        $lat = isset($row['latitude']) ? $webfleetClient->wgs84Toll($row['latitude']) : NULL;
                        $lon = isset($row['longitude']) ? $webfleetClient->wgs84Toll($row['longitude']) : NULL;

                        // $address = $googleClient->getAddressFromll($lat,$lon);

                        $journeyDetail = new TelematicsJourneyDetails();
                        $journeyDetail->ns = 'tm8.gps';
                        $journeyDetail->telematics_journey_id = $journey->id;
                        $journeyDetail->vrn = $journey->vrn;
                        $journeyDetail->odometer = $row['odometer'];
                        $journeyDetail->lat = $lat;
                        $journeyDetail->lon = $lon;
                        $journeyDetail->time = isset($row['pos_time']) ? $webfleetClient->timeToUtc($row['pos_time']) : null;
                        $journeyDetail->speed = $webfleetClient->kmphToMps($row['speed']);
                        $journeyDetail->gps_odo = $row['odometer'];
                        $journeyDetail->heading = isset($row['course']) ? $row['course'] : null;
                        // $journeyDetail->street = $address['street'];
                        // $journeyDetail->town = $address['town'];
                        // $journeyDetail->post_code = $address['postal_code'];
                        $journeyDetail->raw_json = json_encode($row);
                        $journeyDetail->save();

                        //Create zone alert
                        $zoneAlertData = $journeyDetail->toArray();
                        $zoneAlertData['journey_details_id'] = $journeyDetail->id;
                        $zoneAlertData['postcode'] = $journeyDetail->post_code;
                        $telematicsService->processZoneData($zoneAlertData);
                    }

                    $journeyEndJson = json_decode($journey->raw_json);

                    // Journey End detail entry
                    $newEndJourney = TelematicsJourneyDetails::where(
                        [
                            'telematics_journey_id' => $journey->id,
                            'ns' => 'tm8.gps.jny.end',
                        ]
                    )->first();

                    if (!$newEndJourney) {
                        $newEndJourney = new TelematicsJourneyDetails();
                    }

                    $lat = $journey->end_lat;
                    $lon = $journey->end_lon;
                    // $address = $googleClient->getAddressFromll($lat,$lon);

                    $newEndJourney->telematics_journey_id = $journey->id;
                    $newEndJourney->ns = 'tm8.gps.jny.end';
                    $newEndJourney->vrn = $journey->vrn;
                    $newEndJourney->time = $journey->end_time;
                    $newEndJourney->odometer = $journeyEndJson->end_odometer;
                    $newEndJourney->lat = $lat;
                    $newEndJourney->lon = $lon;
                    $newEndJourney->speed = $webfleetClient->kmphToMps($journeyEndJson->avg_speed);
                    $newEndJourney->gps_odo = $journeyEndJson->end_odometer;
                    // $newEndJourney->street = $address['street'];
                    // $newEndJourney->town = $address['town'];
                    // $newEndJourney->post_code = $address['postal_code'];

                    $newEndJourney->street = $journey->end_street;
                    $newEndJourney->town = $journey->end_town;
                    $newEndJourney->post_code = $journey->end_post_code;

                    $newEndJourney->raw_json = $journey->raw_json;
                    $newEndJourney->save();

                    $telematicsJourneyRepo = new TelematicsJourneysRepository();
                    //$telematicsJourneyRepo->updateVehicleTotalfuelSumAndDistanceSum($journey->vrn);
                    //$journey->incident_count = $telematicsJourneyRepo->getTotalIncidentCountOfJourney($journey->id);
                    /*$calculatedFields = $telematicsJourneyRepo->getCalculatedFieldsOfJourney($journey->id);
                    $journey->max_speed = $calculatedFields['maxspeed'];
                    $journey->avg_speed = $calculatedFields['avgspeed'];
                    $journey->odometer_start = $calculatedFields['odoStart'];
                    $journey->odometer_end = $calculatedFields['odoEnd'];
                    */
                    $journey->save();

                    $this->getIdleIncidents($journey, $webfleetClient, $googleClient);
                    $this->getAccelerationIncidents($journey, $webfleetClient, $googleClient);
                    $this->getSpeedingIncidents($journey, $webfleetClient, $googleClient);

                    ///update calculated fields
                    $telematicsJourneysRepository = new TelematicsJourneysRepository();
                    $calculatedFields = $telematicsJourneysRepository->getCalculatedFieldsOfJourney($journey->id);
                    $journey->max_speed = $calculatedFields['maxspeed'];
                    $journey->avg_speed = $calculatedFields['avgspeed'];
                    $journey->odometer_start = $journeyEndJson->start_odometer;
                    $journey->odometer_end = $journeyEndJson->end_odometer;

                    $journey->incident_count = $calculatedFields['incidentCount'];
                    $journey->harsh_breaking_count = $calculatedFields['harsh_breaking'];
                    $journey->harsh_acceleration_count = $calculatedFields['harsh_acceleration'];
                    $journey->harsh_cornering_count = $calculatedFields['harsh_cornering'];
                    $journey->speeding_count = $calculatedFields['speeding'];
                    $journey->rpm_count = $calculatedFields['rpm'];
                    $journey->idling_count = $calculatedFields['idling'];

                    $journey->save();
                    $telematicsJourneysRepository->updateVehicleTotalfuelSumAndDistanceSum($journey->vrn);

                    ///////code to do
                    $vehicle = $journey->vehicle;
                    $journeyDetails = $journey->details()->orderBy('time','DESC')->first();
                    /*$vehicle->telematics_ns = $journeyDetails->ns;
                    $vehicle->telematics_odometer = $journeyDetails->odometer;
                    $vehicle->telematics_lat = $journeyDetails->lat;
                    $vehicle->telematics_lon = $journeyDetails->lon;
                    $vehicle->telematics_latest_location_time = $journeyDetails->time;
                    if (isset($journeyDetails->post_code)){
                        $vehicle->telematics_postcode = $journeyDetails->post_code;
                        $vehicle->telematics_street = $journeyDetails->street;
                        $vehicle->telematics_town = $journeyDetails->town;
                    }
                    */
                    $vehicle->telematics_latest_journey_id = $journey->id;
                    $vehicle->telematics_latest_journey_time = $journey->start_time;

                    $vehicle->save();


                    //--tbc --- $telematicsService->processScore($journey);

                    dispatch(new ProcessStreetSpeed($journey->id));
                }

                $journey->is_details_added = 1;
                $journey->save();
            }
        }
    }

    /**
     * Get idle incidents.
     *
     */
    public function getIdleIncidents($journey, $webfleetClient, $googleClient)
    {
        $idleIncidents = [
            'tm8.gps.idle.start',
            'tm8.gps.idle.end',
        ];
        $startTime = $journey->start_time;
        if ($journey->details->count() > 0) {
            $journeyDetails = $journey->details->filter(function ($detail) use ($idleIncidents) {
                            return $detail->ns === in_array($detail->ns, $idleIncidents);
                        });
            if($journeyDetails->count() > 0) {
                $startTime = $journeyDetails->first()->time;
            }
        }
        // $endTime = Carbon::now()->format('Y-m-d H:i:s');
        $endTime = $journey->end_time;
        if(!$endTime || $endTime == '') {
            $endTime = Carbon::now()->format('Y-m-d H:i:s');
        }
        $details   = $webfleetClient->getIdleIncident($startTime, $endTime, $journey->vrn, $journey->vehicle->webfleet_object_id);
        if (count($details) > 0) {
            \Log::info('******************** getIdleIncident *******************');
            \Log::info($details);
            \Log::info('******************** getIdleIncident *******************');
            foreach ($details as $detail) {
                if ($detail) {
                    $detail['start_time'] = $detail['start_time'] . ':00';
                    $detail['end_time']   = $detail['end_time'] . ':00';
                    //Idle Start
                    $lat                                 = isset($detail['latitude']) ? $webfleetClient->wgs84Toll($detail['latitude']) : NULL;
                    $lon                                 = isset($detail['longitude']) ? $webfleetClient->wgs84Toll($detail['longitude']) : NULL;
                    // $address                             = $googleClient->getAddressFromll(sprintf("%f", $lat), sprintf("%f", $lon));
                    $address = ['street'=>'', 'town'=>'', 'postal_code'=>''];
                    if (isset($detail['postext'])) {
                        $address                             = $googleClient->setJourneyAddress($detail['postext']);
                    }

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

    /**
     * Get idle incidents.
     *
     */
    public function getAccelerationIncidents($journey, $webfleetClient, $googleClient)
    {
        $incidentNamespace = [
            'tm8.dfb2.acc.l',
            'tm8.dfb2.dec.l',
            'tm8.dfb2.cnrl.l',
            'tm8.dfb2.cnrr.l',
        ];

        $startTime = $journey->start_time;
        if ($journey->details->count() > 0) {
            $journeyDetails = $journey->details->filter(function ($detail) use ($incidentNamespace) {
                            return $detail->ns === in_array($detail->ns, $incidentNamespace);
                        });
            if($journeyDetails->count() > 0) {
                $startTime = $journeyDetails->first()->time;
            }
        }

        // $endTime = Carbon::now()->format('Y-m-d H:i:s');
        $endTime = $journey->end_time;
        if(!$endTime || $endTime == '') {
            $endTime = Carbon::now()->format('Y-m-d H:i:s');
        }

        $data = $webfleetClient->getAccelerationIncident($startTime, $endTime, $journey->vrn, $journey->vehicle->webfleet_object_id);

        if (count($data) > 0) {
            \Log::info('******************** getAccelerationIncidents *******************');
            \Log::info($data);
            \Log::info('******************** getAccelerationIncidents *******************');
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

                $lat                                  = isset($row['latitude']) ? $webfleetClient->wgs84Toll($row['latitude']) : NULL;
                $lon                                  = isset($row['longitude']) ? $webfleetClient->wgs84Toll($row['longitude']) : NULL;
                // $address                              = $googleClient->getAddressFromll($lat, $lon);

                $address = ['street'=>'', 'town'=>'', 'postal_code'=>''];
                if (isset($row['postext'])) {
                    $address                              = $googleClient->setJourneyAddress($row['postext']);
                }
                $newIncident                          = new TelematicsJourneyDetails();
                $newIncident->ns                    = $ns;
                $newIncident->time                  = Carbon::parse($row['start_time'])->format('Y-m-d H:i:s');
                $newIncident->telematics_journey_id = $journey->id;
                $newIncident->vrn                   = $journey->vrn;
                $newIncident->lat                   = $lat;
                $newIncident->lon                   = $lon;
                $newIncident->speed                 = $webfleetClient->kmphToMps($row['start_speed']);
                $newIncident->street                = $address['street'];
                $newIncident->town                  = $address['town'];
                $newIncident->post_code             = $address['postal_code'];
                if(isset($row['roadspeedlimit'])) {
                    $newIncident->street_speed          = $webfleetClient->kmphToMps($row['roadspeedlimit']);
                }
                $newIncident->raw_json              = json_encode($row);
                $newIncident->save();
            }
        }
        $journey->is_details_added = 3;
        $journey->save();
    }

    /**
     * Get idle incidents.
     *
     */
    public function getSpeedingIncidents($journey, $webfleetClient, $googleClient)
    {
        $startTime = $journey->start_time;
        if ($journey->details->count() > 0) {
            // $journeyDetails = $journey->details->where('ns', 'tm8.dfb2.spd');
            $journeyDetails = $journey->details->where('ns', 'tm8.dfb2.spdinc');
            if($journeyDetails->count() > 0) {
                $startTime = $journeyDetails->first()->time;
            }
        }
        // $endTime = Carbon::now()->format('Y-m-d H:i:s');
        $endTime = $journey->end_time;
        if(!$endTime || $endTime == '') {
            $endTime = Carbon::now()->format('Y-m-d H:i:s');
        }

        $data = $webfleetClient->getSpeedingIncident($startTime, $endTime, $journey->vrn, $journey->vehicle->webfleet_object_id);

        if (count($data) > 0) {
            \Log::info('******************** getSpeedingIncidents *******************');
            \Log::info($data);
            \Log::info('******************** getSpeedingIncidents *******************');

            foreach ($data as $row) {
                $lat     = isset($row['start_latitude']) ? $webfleetClient->wgs84Toll($row['start_latitude']) : NULL;
                $lon     = isset($row['start_longitude']) ? $webfleetClient->wgs84Toll($row['start_longitude']) : NULL;
                // $address = $googleClient->getAddressFromll($lat, $lon);

                $address = ['street'=>'', 'town'=>'', 'postal_code'=>''];
                if (isset($row['start_postext'])) {
                    $address = $googleClient->setJourneyAddress($row['start_postext']);
                }

                $newSpeedingIncident                          = new TelematicsJourneyDetails();
                $newSpeedingIncident->telematics_journey_id = $journey->id;
                $newSpeedingIncident->time                  = Carbon::parse($row['start_time'])->format('Y-m-d H:i:s');
                // $newSpeedingIncident->ns                    = 'tm8.dfb2.spd';
                $newSpeedingIncident->ns                    = 'tm8.dfb2.spdinc';
                $newSpeedingIncident->vrn                   = $journey->vrn;
                $newSpeedingIncident->lat                   = $lat;
                $newSpeedingIncident->lon                   = $lon;
                $newSpeedingIncident->speed                 = $webfleetClient->kmphToMps($row['max_speed']);
                $newSpeedingIncident->street_speed          = $webfleetClient->kmphToMps($row['road_speedlimit']);
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
