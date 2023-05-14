<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use DB;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;
use App\Repositories\TelematicsJourneysRepository;


class FixTelematicsJourneyIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telematics:FixTelematicsJourneyIds {--startDate=null} {--endDate=null}';

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
       //$journeyId = $this->option('journeyId');
       //$telematicsJourneysSql = TelematicsJourneys::with('user');
       $telematicsJourneyDetailsSql = TelematicsJourneyDetails::where('telematics_journey_id',0);
       if($startDate != 'null'){
            $telematicsJourneyDetailsSql = $telematicsJourneyDetailsSql->where('time','>=',$startDate);
       }
       if($endDate != 'null'){
            $telematicsJourneyDetailsSql = $telematicsJourneyDetailsSql->where('time','<=',$endDate);
       }
       $telematicsJourneyDetails = $telematicsJourneyDetailsSql->get();
       // print_r($telematicsJourneys);exit;
        $vehiclesMap = Vehicle::get()->keyBy('registration');
        //print_r($vehiclesMap);
        //print_r('\n');
       foreach($telematicsJourneyDetails as $journeyDetail){
//            print_r($journeyDetail->raw_json);
            $raw_json = json_decode($journeyDetail->raw_json);
            $vehicle = $vehiclesMap[$raw_json->vrn];
            $journey_id = $raw_json->journey_id;
            $uid = $raw_json->uid;
            $telematics_journey = TelematicsJourneys::withTrashed()->where(['vehicle_id'=>$vehicle->id,'journey_id'=>$journey_id,'uid'=>$uid])->first();
            $journeyDetail->telematics_journey_id = $telematics_journey->id;
            $journeyDetail->save();
       }

    }


}