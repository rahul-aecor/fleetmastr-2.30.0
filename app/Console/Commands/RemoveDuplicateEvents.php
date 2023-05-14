<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;
use DB;
use Carbon\Carbon as Carbon;

class RemoveDuplicateEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telematics:remove-duplicate-events  {--startDate=null} {--endDate=null} {--journeyId=null}';

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
       
       $startDateParam = $this->option('startDate');
       $endDateParam = $this->option('endDate');
       $journeyIdParam = $this->option('journeyId');
       $telematicsJourneysSql = TelematicsJourneys::with('user');
       if($startDateParam != 'null'){
            $startDate = $startDateParam;
       }
       else{
            $startDate = Carbon::yesterday()->startOfDay()->format('Y-m-d H:i:s');
       }
       if($endDateParam != 'null'){
            $endDate = $endDateParam;
       }
       else{
            $endDate = Carbon::yesterday()->endOfDay()->format('Y-m-d H:i:s');
       }
       $duplicateJnys = [];
       if($journeyIdParam != 'null'){
            $duplicateJnys = [$journeyIdParam];
       }
       else{
            $jnyWithDuplicateEvents = DB::table('telematics_journeys')
                            ->select('telematics_journeys.id', 'telematics_journeys.journey_id', 'telematics_journeys.vrn', DB::raw('count(telematics_journey_details.id) as cnt'))
                            ->join('telematics_journey_details', 'telematics_journeys.id', '=', 'telematics_journey_details.telematics_journey_id')
                            ->where('telematics_journeys.start_time','>=', $startDate)
                            ->where('telematics_journeys.start_time','<', $endDate)
                            ->where('telematics_journey_details.telematics_journey_id', '>', 0)
                            ->whereNotIn('telematics_journey_details.ns', ['tm8.gps.ign.on','tm8.gps.ign.off'])
                            ->groupBy('telematics_journeys.id')
                            ->groupBy('telematics_journey_details.vrn')
                            ->groupBy('telematics_journey_details.ns')
                            ->groupBy('telematics_journey_details.time')
                            ->groupBy('telematics_journey_details.lat')
                            ->groupBy('telematics_journey_details.lon')
                            ->having('cnt',">",1)
                            ->get();

            foreach ($jnyWithDuplicateEvents as $jny) {
                if (!in_array($jny->id, $duplicateJnys))
                {
                    array_push($duplicateJnys, $jny->id);
                }
            }
       }

        // print_r($duplicateJnys);
        // exit;

        foreach ($duplicateJnys as $jny) {
            print_r("Removing Duplicate Events for Journey ID : " . $jny . "\n");
            $jnyDupEvents = DB::table('telematics_journey_details')
                            ->select('vrn', 'ns', 'time', 'lat', 'lon', DB::raw('count(id) as cnt'), DB::raw('group_concat(id order by id) as ids'))
                            ->where('telematics_journey_id', $jny)
                            ->groupBy('vrn')
                            ->groupBy('ns')
                            ->groupBy('time')
                            ->groupBy('lat')
                            ->groupBy('lon')
                            ->having('cnt','>',1)
                            ->orderBy('time')
                            ->get();

            $idsToRemove = "";
            foreach ($jnyDupEvents as $jnyDup) {
                $idList = explode(',', $jnyDup->ids);
                unset($idList[0]);
                if ($idsToRemove == "")
                {
                    $idsToRemove = implode(',', $idList);
                }
                else 
                {
                    $idsToRemove = $idsToRemove . "," . implode(',', $idList);
                }
            }

            // $rowsAffected = DB::table('telematics_journey_details')->whereIn('id', explode(',', $idsToRemove))->count();
            $rowsAffected = DB::table('telematics_journey_details')->whereIn('id', explode(',', $idsToRemove))->delete();
            // print_r($rowsAffected);


            // Code to Update Incident Counts
            $calculatedFields = $this->getCalculatedFieldsOfJourney($jny);
            $telematicsJourney = TelematicsJourneys::find($jny);
            $telematicsJourney->incident_count = $calculatedFields['incidentCount'];
            $telematicsJourney->harsh_breaking_count = $calculatedFields['harsh_breaking'];
            $telematicsJourney->harsh_acceleration_count = $calculatedFields['harsh_acceleration'];
            $telematicsJourney->harsh_cornering_count = $calculatedFields['harsh_cornering'];
            $telematicsJourney->speeding_count = $calculatedFields['speeding'];
            $telematicsJourney->speeding_incident_count = $calculatedFields['new_speeding_incidents'];
            $telematicsJourney->rpm_count = $calculatedFields['rpm'];
            $telematicsJourney->idling_count = $calculatedFields['idling'];
            $telematicsJourney->save(['timestamps' => false]);

        }

    }

    private function getCalculatedFieldsOfJourney($journey_id) {
        $incidentCount = 0;
        if($journey_id != null) {
           $incidentData =  DB::table('telematics_journey_details')
            ->selectRaw('
            SUM(CASE WHEN ns = "tm8.dfb2.dec.l" THEN 1 WHEN ns = "tm8.dfb2.acc.l" THEN 1 WHEN ns = "tm8.dfb2.cnrl.l" THEN 1 WHEN ns = "tm8.dfb2.cnrr.l" THEN 1 WHEN ns = "tm8.dfb2.spdinc" THEN 1 WHEN ns = "tm8.dfb2.rpm" THEN 1 WHEN ns = "tm8.gps.idle.start" THEN 1 ELSE 0 END) AS incident_count,
            SUM(CASE WHEN ns = "tm8.dfb2.dec.l" THEN 1 ELSE 0 END) AS harsh_breaking,
            SUM(CASE WHEN ns = "tm8.dfb2.acc.l" THEN 1 ELSE 0 END) AS harsh_acceleration,
            SUM(CASE WHEN ns = "tm8.dfb2.cnrl.l" THEN 1 WHEN ns = "tm8.dfb2.cnrr.l" THEN 1  ELSE 0 END) AS harsh_cornering,
            SUM(CASE WHEN ns = "tm8.dfb2.spd" THEN 1 ELSE 0 END) AS speeding,
            SUM(CASE WHEN ns = "tm8.dfb2.spdinc" THEN 1 ELSE 0 END) AS new_speeding_incidents,
            SUM(CASE WHEN ns = "tm8.dfb2.rpm" THEN 1 ELSE 0 END) AS rpm,
            SUM(CASE WHEN ns = "tm8.gps.idle.start" THEN 1 ELSE 0 END) AS idling')
            ->where('telematics_journey_id','=',$journey_id)
            ->first();

            $data = [];
            $data['incidentCount'] = ($incidentData->incident_count != '') ? $incidentData->incident_count : 0;
            $data['harsh_breaking'] = $incidentData->harsh_breaking;
            $data['harsh_acceleration'] = $incidentData->harsh_acceleration;
            $data['harsh_cornering'] = $incidentData->harsh_cornering;
            $data['speeding'] = $incidentData->speeding;
            $data['new_speeding_incidents'] = $incidentData->new_speeding_incidents;
            $data['rpm'] = $incidentData->rpm;
            $data['idling'] = $incidentData->idling;
        }
        return $data;
    }

}
