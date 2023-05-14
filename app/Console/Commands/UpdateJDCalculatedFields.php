<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use DB;
use App\Models\User;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;


class UpdateJDCalculatedFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telematics:updateIncidentCounts';

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
        $incidentCounts = TelematicsJourneyDetails::selectRaw('telematics_journey_id, SUM(CASE WHEN ns = "tm8.dfb2.dec.l" THEN 1 WHEN ns = "tm8.dfb2.acc.l" THEN 1 WHEN ns = "tm8.dfb2.cnrl.l" THEN 1 WHEN ns = "tm8.dfb2.cnrr.l" THEN 1 WHEN ns = "tm8.dfb2.spd" THEN 1 WHEN ns = "tm8.dfb2.spdinc" THEN 1 WHEN ns = "tm8.dfb2.rpm" THEN 1 WHEN ns = "tm8.gps.idle.start" THEN 1 ELSE 0 END) AS incident_count,
            SUM(CASE WHEN ns = "tm8.dfb2.dec.l" THEN 1 ELSE 0 END) AS harsh_breaking,
            SUM(CASE WHEN ns = "tm8.dfb2.acc.l" THEN 1 ELSE 0 END) AS harsh_acceleration,
            SUM(CASE WHEN ns = "tm8.dfb2.cnrl.l" THEN 1 WHEN ns = "tm8.dfb2.cnrr.l" THEN 1  ELSE 0 END) AS harsh_cornering,
            SUM(CASE WHEN ns = "tm8.dfb2.spd" THEN 1 ELSE 0 END) AS speeding,
            SUM(CASE WHEN ns = "tm8.dfb2.spdinc" THEN 1 ELSE 0 END) AS new_speeding_incidents,
            SUM(CASE WHEN ns = "tm8.dfb2.rpm" THEN 1 ELSE 0 END) AS rpm,
            SUM(CASE WHEN ns = "tm8.gps.idle.start" THEN 1 ELSE 0 END) AS idling,
            max(speed) as maxspeed, avg(speed) as avgspeed, min(odometer) as odoStart, max(odometer) as odoEnd')
                    ->where('telematics_journey_id','>',0)
                    ->groupBy('telematics_journey_id')
                    // ->take(10)
                    ->get();
                    // ->get()->toArray();


        // print_r($incidentCounts->whereLoose('telematics_journey_id',2)->toArray());

        $telematicsJourneys = TelematicsJourneys::where('id', '>', 0)->get();
        foreach ($telematicsJourneys as $telematicsJourney) {
            print_r("Processing with ID  . " . $telematicsJourney->id . "\n");

            $calculatedFieldsObj = $incidentCounts->whereLoose('telematics_journey_id',$telematicsJourney->id)->first();

            if ($calculatedFieldsObj)
            {
                $calculatedFields = $calculatedFieldsObj->toArray();

                // print_r($calculatedFields);
                $telematicsJourney->max_speed = $calculatedFields['maxspeed'];
                $telematicsJourney->avg_speed = $calculatedFields['avgspeed'];
                $telematicsJourney->odometer_start = $calculatedFields['odoStart'];
                $telematicsJourney->odometer_end = $calculatedFields['odoEnd'];

                $telematicsJourney->incident_count = $calculatedFields['incident_count'];
                $telematicsJourney->harsh_breaking_count = $calculatedFields['harsh_breaking'];
                $telematicsJourney->harsh_acceleration_count = $calculatedFields['harsh_acceleration'];
                $telematicsJourney->harsh_cornering_count = $calculatedFields['harsh_cornering'];
                $telematicsJourney->speeding_count = $calculatedFields['speeding'];
                $telematicsJourney->speeding_incident_count = $calculatedFields['new_speeding_incidents'];
                $telematicsJourney->rpm_count = $calculatedFields['rpm'];
                $telematicsJourney->idling_count = $calculatedFields['idling'];

                $telematicsJourney->timestamps = false;
                $telematicsJourney->save();
            }
        }

    }
}
