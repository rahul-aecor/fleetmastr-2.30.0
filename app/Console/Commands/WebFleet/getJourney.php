<?php
namespace App\Console\Commands\WebFleet;

use App\Models\TelematicsJourneys;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Alerts;
use App\Services\TelematicsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Custom\Client\GoogleMap;
use App\Custom\Client\Webfleet;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class getJourney extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webfleet:getJourney';

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
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $file = "telematics/webfleetrawdata-".Carbon::today()->format("d-m-Y").".log";
        $log = new Logger('telematicsData');
        $log->pushHandler(new StreamHandler(storage_path().'/'.$file, Logger::INFO));

        $webfleetClient = new Webfleet();
        $googleClient = new GoogleMap();

        // $startTime = Carbon::now()->subMinute(5)->format('Y-m-d H:i:s');
        $startTime = Carbon::now()->startOfDay()->toDateTimeString();
        $endTime = Carbon::now()->toDateTimeString();

        $data = $webfleetClient->getJourney($startTime,$endTime);

        $systemUser = User::where('email','system@imastr.com')->first();

        if (count($data) > 0) {

            $data = collect($data);

            $log->info('getJourney - data');
            $log->info(json_encode($data));

            $registations = $data->pluck('objectno')->toArray();
            $usersNo = $data->pluck('driverno')->toArray();

            $vehicles = Vehicle::whereIn('webfleet_registration',$registations)->get()->keyBy('webfleet_registration');

            $journeyIds = TelematicsJourneys::distinct('journey_id')->get(['journey_id'])->pluck('journey_id')->toArray();

            $users = User::whereIn('driver_tag_key',$usersNo)->get()->keyBy('driver_tag_key');

            $vehicleNominatedDrivers = Vehicle::whereNotNull('nominated_driver')->get(['nominated_driver'])->pluck('nominated_driver');
            $nominatedDriverUsers = User::whereIn('id', $vehicleNominatedDrivers)->get()->keyBy('id');

            foreach ($data as $journey) {

                if($journey['tripmode'] != 2) {
                    continue;
                }

                if(!isset($journey['start_latitude']) || !isset($journey['start_longitude']) || !isset($journey['end_latitude']) || !isset($journey['end_longitude'])) {
                    \Log::info('****** IGNORED ENTRY IN getJourney *****');
                    \Log::info($journey);
                    \Log::info('***********');
                    continue;
                }

                if(in_array($journey['tripid'], $journeyIds)) {
                    continue;
                }

                $vehicle =  isset($vehicles[$journey['objectno']]) ? $vehicles[$journey['objectno']] : null;

                /*
                * FLEE-6835 - Farzan
                */
                // $user = (isset($journey['driverno']) && isset($users[$journey['driverno']])) ? $users[$journey['driverno']] : null;
                if(isset($journey['driverno']) && isset($users[$journey['driverno']])) {
                    $user = $users[$journey['driverno']];
                } else if($vehicle && $vehicle->nominated_driver) {
                    $user = $nominatedDriverUsers[$vehicle->nominated_driver];
                } else {
                    $user = null;
                }

                if(!$vehicle) {
                    continue;
                }

                try {
                    $summary = $webfleetClient->getJourneyExtraDetails($journey['start_time'],$journey['end_time'],$journey['objectno'],$journey['objectuid']);

                    $log->info('getJourney - summary');
                    $log->info(json_encode($summary));

                    if(isset($summary['errorCode'])) {
                        $summary = null;
                    } else {
                        $summary = isset($summary[0]) ? (array) $summary[0] : null;
                    }

                    $startAddress = null;
                    $endAddress = null;
                    if(isset($journey['start_postext'])) {
                        $startAddress = $googleClient->setJourneyAddress($journey['start_postext']);
                    }
                    if(isset($journey['end_postext'])) {
                        $endAddress = $googleClient->setJourneyAddress($journey['end_postext']);
                    }

                    $newJourney = new TelematicsJourneys();
                    $newJourney->journey_id = $journey['tripid'];
                    $newJourney->vehicle_id =  $vehicle ?  $vehicle->id : 0;
                    $newJourney->user_id = $user ? $user->id : $systemUser->id ;
                    $newJourney->vrn = $vehicle->registration;
                    $newJourney->start_time = $webfleetClient->timeToUtc($journey['start_time']);
                    $newJourney->start_lat = $webfleetClient->wgs84Toll($journey['start_latitude']);
                    $newJourney->start_lon = $webfleetClient->wgs84Toll($journey['start_longitude']);
                    $newJourney->end_lat = $webfleetClient->wgs84Toll($journey['end_latitude']);
                    $newJourney->end_lon = $webfleetClient->wgs84Toll($journey['end_longitude']);
                    $newJourney->end_time = $webfleetClient->timeToUtc($journey['end_time']);
                    $newJourney->odometer = $journey['end_odometer'];
                    $newJourney->engine_duration = $summary ? $summary['operatingtime'] : null;
                    $newJourney->fuel = $summary ? $summary['fuel_usage'] : null;
                    $newJourney->co2 = isset($journey['co2']) ? number_format($journey['co2'] * 0.001, 2) : null;//grams to kg
                    $newJourney->gps_idle_duration = $journey['idle_time'];
                    $newJourney->gps_distance = $journey['distance'];
                    $newJourney->uid = isset($journey['driverno']) ? $journey['driverno'] : null;
                    $newJourney->is_details_added = 0;
                    if(isset($startAddress)) {
                        $newJourney->start_street = $startAddress['street'];
                        $newJourney->start_town = $startAddress['town'];
                        $newJourney->start_post_code = $startAddress['postal_code'];
                    }
                    if(isset($endAddress)) {
                        $newJourney->end_street = $endAddress['street'];
                        $newJourney->end_town = $endAddress['town'];
                        $newJourney->end_post_code = $endAddress['postal_code'];
                    }
                    $newJourney->raw_json = json_encode($journey);
                    $newJourney->save();

                    $telematicsService = new TelematicsService();
                    $value['journey_details_id'] = $newJourney->id;
                    $value['vehicle_id'] = $newJourney->vehicle_id;
                    $value['user_id'] = $newJourney->user_id;
                    $value['journey_id'] = $newJourney->journey_id;
                    $value['journey_start_date'] = $newJourney->start_time;
                    $alert = Alerts::where('slug', 'vehicle_check_incomplete')->first();
                    if($alert->is_active == 1 && $newJourney->gps_distance > 50) {
                        $telematicsService->checkEntryAndCreateAlert($value, $alert);
                    }

                    $journeyIds[] = $journey['tripid'];

                } catch (\Exception $e) {
                    \Log::info('**** Exception in getJourney command *****');
                    \Log::info($journey);
                }
            }
        }

    }
}
