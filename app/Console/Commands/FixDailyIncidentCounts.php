<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use DB;
use App\Models\User;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;
use App\Repositories\TelematicsJourneysRepository;


class FixDailyIncidentCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telematics:FixDailyIncidentCounts';

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
       $startDate = Carbon::yesterday()->startOfDay();
       $endDate = Carbon::yesterday()->endOfDay();;

       $telematicsJourneysSql = TelematicsJourneys::with('user')
                                ->where('start_time','>=',$startDate)
                                ->where('start_time','<=',$endDate);
       //print_r($telematicsJourneysSql->toSql());
       //print_r($telematicsJourneysSql->getBindings());exit;
       $telematicsJourneys = $telematicsJourneysSql->get();
       // print_r($telematicsJourneys);exit;
       $telematicsJourneyRepo = new TelematicsJourneysRepository();
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
            $journey->save();
        }

      // $journeyDetails = TelematicsJourneyDetails::where('telematics_journey_id',$telematicsJourneyId)->get();
       //print_r($startDate);
       //print_r($endDate);
       //print_r($journeyId);exit();

    }
    
}
