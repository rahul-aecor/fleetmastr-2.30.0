<?php
namespace App\Console\Commands\WebFleet;

use App\Models\Vehicle;
use App\Models\TelematicsJourneyDetails;
use Illuminate\Console\Command;
use App\Custom\Client\GoogleMap;
use App\Custom\Client\Webfleet;
use App\Events\TelematicsJourneyEnd;
use App\Events\TelematicsJourneyStart;
use App\Events\TelematicsJourneyIdling;
use App\Events\TelematicsJourneyOngoing;

class UpdateVehiclesForLiveTab extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webfleet:updateVehiclesForLiveTab';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update telematics related data for vehicles.';

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
        $webfleetClient = new Webfleet();
        $googleClient = new GoogleMap();

        $data = $webfleetClient->getVehicles();

        if (count($data) > 0) {
            $vrns = collect($data)->pluck('objectno');

            $vehicles = Vehicle::whereIn('webfleet_registration',$vrns->toArray())->get()->keyBy('webfleet_registration');
            foreach ($data as $vehicleTemp) {

                if(!isset($vehicleTemp['objectno'])) {
                    \Log::info('****** IGNORED ENTRY IN UpdateVehiclesForLiveTab *****');
                    \Log::info($vehicleTemp);
                    \Log::info('***********');
                    continue;
                }

                $vehicleDB = isset($vehicles[$vehicleTemp['objectno']]) ? $vehicles[$vehicleTemp['objectno']] : null;
                if ($vehicleDB) {
                    if ($vehicleDB->is_telematics_enabled == 1 && $vehicleDB->webfleet_object_id == $vehicleTemp['objectuid']) {
                        //$out->writeln('Vehicle is up to date.');
                        $iginitionStatus = $vehicleTemp['ignition'];
                        $standstillStatus = $vehicleTemp['standstill'];
			$lat = $webfleetClient->wgs84Toll($vehicleTemp['latitude_mdeg']);
                        $lon = $webfleetClient->wgs84Toll($vehicleTemp['longitude_mdeg']);

                        if ($iginitionStatus == 0 && $vehicleDB->telematics_ns == 'tm8.gps.ign.off') {
                            // means vehicle was stopped and is still stopped so need not update that this minute
                            continue;
                        }
                        if ($iginitionStatus == 1 && $standstillStatus == 1 && $vehicleDB->telematics_ns == 'tm8.gps.idle.start') {
                            // means vehicle was idling and is still idling so need not update that this minute
                            continue;
                        }

                        if($iginitionStatus == 0){
                            $vehicleDB->telematics_ns = 'tm8.gps.ign.off';
                            $payload = [
                                'vehicle_id' => $vehicleDB['registration'],
                            ];
                            event(new TelematicsJourneyEnd($payload));
                        }
                        else{
                            if ($standstillStatus == 0) {
                                $vehicleDB->telematics_ns = 'tm8.gps';
                                $payload = [
                                    'vehicle_id' => $vehicleDB['registration'],
                                    'lat' => $lat,
                                    'lng' => $lon,
                                ];
                                event(new TelematicsJourneyOngoing($payload));
                            }
                            else{
                                $vehicleDB->telematics_ns = 'tm8.gps.idle.start';
                                $payload = [
                                    'vehicle_id' => $vehicleDB['registration'],
                                ];
                                event(new TelematicsJourneyIdling($payload));
                            }
                        }

                        //$vehicleDB->telematics_ns = $latestVehicleData['telematics_ns'];
                        $vehicleDB->telematics_odometer = $vehicleTemp['odometer'];
                        $vehicleDB->telematics_lat = $lat;
                        $vehicleDB->telematics_lon = $lon;
                        $vehicleDB->telematics_latest_location_time = isset($vehicleTemp['pos_time']) ? $webfleetClient->timeToUtc($vehicleTemp['pos_time'].':00') : null;

                        if($vehicleTemp['postext'] && $vehicleTemp['postext'] != '') {
                            $address = $googleClient->setJourneyAddress($vehicleTemp['postext']);
                        } else {
                            $address = $googleClient->getAddressFromll($lat,$lon);
                        }
                        $vehicleDB->telematics_street = $address['street'];
                        $vehicleDB->telematics_town = $address['town'];
                        $vehicleDB->telematics_postcode = $address['postal_code'];
                        $vehicleDB->save();
                    } 

                } else {
                    //$out->writeln('Vehicle not found.');
                }
            }
        }
    }
}

