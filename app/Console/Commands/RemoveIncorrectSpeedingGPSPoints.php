<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use DB;
use App\Models\User;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;
use App\Repositories\TelematicsJourneysRepository;


class RemoveIncorrectSpeedingGPSPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    //telematics:RemoveIncorrectSpeedingGPSPoints --startDate='2022-10-29' --endDate='2022-10-28';
    protected $signature = 'telematics:RemoveIncorrectSpeedingGPSPoints {--startDate=null} {--endDate=null} {--journeyId=null}';

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
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
       $startDate = $this->option('startDate');
       $endDate = $this->option('endDate');
       $journeyId = $this->option('journeyId');
       $telematicsJourneysSql = TelematicsJourneys::with('user');
       if($startDate != 'null'){
            $telematicsJourneysSql = $telematicsJourneysSql->where('start_time','>=',$startDate);
       }
       if($endDate != 'null'){
            $telematicsJourneysSql = $telematicsJourneysSql->where('start_time','<=',$endDate);
       }
       if($journeyId != 'null'){
            $telematicsJourneysSql = $telematicsJourneysSql->where('id','=',$journeyId);
       }

       $telematicsJourneys = $telematicsJourneysSql->get();

       foreach($telematicsJourneys as $journey){
            $this->fixJourneyData($journey->id);
       }

    }


    private function fixJourneyData($telematicsJourneyId){
        
        $journeyDetails = TelematicsJourneyDetails::where('telematics_journey_id',$telematicsJourneyId)->get();
        
        /** 
         * Check speed and street speed and update ns
         */

        if(env('TELEMATICS_PROVIDER') != 'webfleet') {
            $journeyDetails = TelematicsJourneyDetails::withTrashed()->where('telematics_journey_id', $telematicsJourneyId)
                                                ->whereIn('ns', ['tm8.gps','tm8.dfb2.spd'])
                                                //->where('street_speed', '>', 4.47)
                                                ->whereRaw('speed > street_speed+(street_speed*0.2)')->get();
                                                

            $telematicsJourneyRepo = new TelematicsJourneysRepository();
            $updatedJourneyArr = [];


            $allJourneyDetails = TelematicsJourneyDetails::withTrashed()->where('telematics_journey_id', $telematicsJourneyId)
                    ->whereIn('ns', ['tm8.gps','tm8.dfb2.spd'])
                    ->select('id','telematics_journey_id','ns','speed','street_speed','time')->get();

            foreach($journeyDetails as $journey) {
                 if(env('CHECK_SPEEDING_RULES')){
                    $isSpeeding = $this->checkSpeedingRules($allJourneyDetails,$journey);
                    if (!$isSpeeding) {
                        $journey->delete();
                        if(!in_array($journey->telematics_journey_id, $updatedJourneyArr)) {
                            $updatedJourneyArr[] = $journey->telematics_journey_id;
                        }
                        continue;
                    }
                    if ($journey->speed >= (int)$journey->street_speed * (int)2) {
                        $journey->delete();
                        if(!in_array($journey->telematics_journey_id, $updatedJourneyArr)) {
                            $updatedJourneyArr[] = $journey->telematics_journey_id;
                        }
                        continue;
                    }
                }
                if ($journey->street_speed <= 4.47) {
                    continue;
                }
                $journey->ns = 'tm8.dfb2.spdinc';
                $journey->save();
                if(!in_array($journey->telematics_journey_id, $updatedJourneyArr)) {
                    $updatedJourneyArr[] = $journey->telematics_journey_id;
                }
            }

            $telematicsJourneys = TelematicsJourneys::whereIn('id', $updatedJourneyArr)->get();
            foreach($telematicsJourneys as $journey) {
                $calculatedFields = $telematicsJourneyRepo->getCalculatedFieldsOfJourney($journey->id);
                $journey->max_speed = $calculatedFields['maxspeed'];
                $journey->avg_speed = $calculatedFields['avgspeed'];
                $journey->incident_count = $calculatedFields['incidentCount'];
                $journey->harsh_breaking_count = $calculatedFields['harsh_breaking'];
                $journey->harsh_acceleration_count = $calculatedFields['harsh_acceleration'];
                $journey->harsh_cornering_count = $calculatedFields['harsh_cornering'];
                $journey->speeding_count = $calculatedFields['speeding'];
                $journey->speeding_incident_count = $calculatedFields['new_speeding_incidents'];
                $journey->rpm_count = $calculatedFields['rpm'];
                $journey->idling_count = $calculatedFields['idling'];

                //$journey->incident_count = $calculatedFields['incidentCount'];
                //$journey->speeding_incident_count = $calculatedFields['new_speeding_incidents'];
                $journey->save();
            }
        }

        
        \Log::info("processIncorrectSpeedingGPSJob end at :");
        \Log::info(Carbon::now());
        
    }

    private function checkSpeedingRules($allJourneyDetails, $journeyDetails){
        $isSpeedingFlag = true;
        
        $sortedJourneyDetails = $allJourneyDetails->sortBy('time')->values();
        $position = 0;
        foreach($sortedJourneyDetails as $jd){
            if($jd->id == $journeyDetails->id) break;
            $position++;
        }
        $beforeJourneyDetails = $sortedJourneyDetails->splice($position-2,2)->all();
        $afterJourneyDetails = $sortedJourneyDetails->splice($position+1,2)->all();
        $beforeAndAfterJourneyDetails = array_merge($beforeJourneyDetails,$afterJourneyDetails);
        
        if (count($beforeAndAfterJourneyDetails) == 4) {
            if ($beforeAndAfterJourneyDetails[0]->street_speed == $beforeAndAfterJourneyDetails[1]->street_speed && $beforeAndAfterJourneyDetails[1]->street_speed == $beforeAndAfterJourneyDetails[2]->street_speed && $beforeAndAfterJourneyDetails[2]->street_speed == $beforeAndAfterJourneyDetails[3]->street_speed) {
                if($beforeAndAfterJourneyDetails[0]->street_speed > $journeyDetails->street_speed){
                    //skip tagging this as speeding incident
                    $isSpeedingFlag = false;
                    //print_r("hhh");
                }
            }

            if ($beforeAndAfterJourneyDetails[0]->street_speed > (int)$journeyDetails->street_speed * (int)1.5 && $beforeAndAfterJourneyDetails[1]->street_speed > (int)$journeyDetails->street_speed * (int)1.5 && $beforeAndAfterJourneyDetails[2]->street_speed > (int)$journeyDetails->street_speed * (int)1.5 && $beforeAndAfterJourneyDetails[3]->street_speed > (int)$journeyDetails->street_speed * (int)1.5 ) {
                    $isSpeedingFlag = false;
                    //print_r("iii");
            }
        }
        return $isSpeedingFlag;
    }

}