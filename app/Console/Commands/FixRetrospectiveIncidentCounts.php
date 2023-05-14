<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use DB;
use App\Models\User;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;
use App\Repositories\TelematicsJourneysRepository;


class FixRetrospectiveIncidentCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telematics:FixRetrospectiveIncidentCounts';

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
        $monthStart = Carbon::now()->startOfMonth()->startOfDay();
        $monthEnd = Carbon::now()->endOfMonth()->endOfDay();
        $month = 1;
        while ($month <= 12) {
            $journeysToRecalculate = TelematicsJourneys::leftjoin('telematics_journey_details','telematics_journey_details.telematics_journey_id','=','telematics_journeys.id')
                                    ->select('telematics_journeys.id','telematics_journeys.updated_at','telematics_journeys.incident_count',
                                        DB::raw('SUM(CASE WHEN ns = "tm8.dfb2.dec.l" THEN 1 WHEN ns = "tm8.dfb2.acc.l" THEN 1 WHEN ns = "tm8.dfb2.cnrl.l" THEN 1 WHEN ns = "tm8.dfb2.cnrr.l" THEN 1 WHEN ns = "tm8.dfb2.spdinc" THEN 1 WHEN ns = "tm8.dfb2.rpm" THEN 1 WHEN ns = "tm8.gps.idle.start" THEN 1 ELSE 0 END) AS incident_count_recent'))
                                    ->whereBetween("start_time",[$monthStart,$monthEnd])
                                    ->groupBy('telematics_journeys.id')
                                    ->havingRaw('incident_count_recent <> incident_count');
                                    //->get();
 // print_r($journeysToRecalculate->toSql());
 // print_r($journeysToRecalculate->getBindings());
 $journeysToRecalculate = $journeysToRecalculate->get();
 // print_r($journeysToRecalculate);
 
           $telematicsJourneyRepo = new TelematicsJourneysRepository();
           foreach($journeysToRecalculate as $journey) {
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
                $journey->updated_at = $journey->updated_at;
                // $journey->timestamps = false;
                $journey->save();
            }

            $monthStart = Carbon::now()->subMonths($month)->startOfMonth()->startOfDay();
            $monthEnd = Carbon::now()->subMonths($month)->endOfMonth()->endOfDay();
            $month++;
        }
                                //->get();
       /* print_r($journeysToRecalculate->toSql());print_r($journeysToRecalculate->get());print_r($journeysToRecalculate->getBindings());exit;

       $journeysToRecalculate = DB::select('SELECT t.id, incident_count AS incident_count_1,SUM(CASE WHEN ns = "tm8.dfb2.dec.l" THEN 1 WHEN ns = "tm8.dfb2.acc.l" THEN 1 WHEN ns = "tm8.dfb2.cnrl.l" THEN 1 WHEN ns = "tm8.dfb2.cnrr.l" THEN 1 WHEN ns = "tm8.dfb2.spdinc" THEN 1 WHEN ns = "tm8.dfb2.rpm" THEN 1 WHEN ns = "tm8.gps.idle.start" THEN 1 ELSE 0 END) AS incident_count FROM telematics_journeys t, telematics_journey_details td WHERE t.id=td.telematics_journey_id AND t.start_time>=? AND t.start_time<? GROUP BY t.id HAVING incident_count_1<>incident_count', [$monthStart,$monthEnd]);
       print_r($journeysToRecalculate);print_r($journeysToRecalculate->getBindings());exit;*/

    }
    
}
