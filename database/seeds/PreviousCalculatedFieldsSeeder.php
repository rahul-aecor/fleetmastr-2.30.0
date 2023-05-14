<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\User;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;

class PreviousCalculatedFieldsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $telematicsJourneys = TelematicsJourneys::whereNotNull('end_time')->get();
        foreach($telematicsJourneys as $journey){
            $telematicsJourneyDetails = TelematicsJourneyDetails::select(DB::raw("MAX(speed) AS mxmph,AVG(speed) AS avgmph,MIN(odometer) AS journeyStart,MAX(odometer) AS journeyEnd, SUM(CASE WHEN ns = 'tm8.dfb2.dec.l' THEN 1 
            WHEN ns = 'tm8.dfb2.acc.l' THEN 1 
            WHEN ns = 'tm8.dfb2.spd' THEN 1 
            WHEN ns = 'tm8.dfb2.spdinc' THEN 1
            WHEN ns = 'tm8.dfb2.cnrl.l' THEN 1 
            WHEN ns = 'tm8.dfb2.cnrr.l' THEN 1 
            WHEN ns = 'tm8.dfb2.rpm' THEN 1 
            WHEN ns = 'tm8.gps.heartbeat' THEN 1 
            WHEN ns = 'tm8.gps.idle.start' THEN 1 
            ELSE 0 END) AS incident_count"))->where('telematics_journey_id',$journey->id)->first();
            //$calculatedFields = $telematicsJourneyRepo->getCalculatedFieldsOfJourney($journey->id);
            $journey->max_speed = $telematicsJourneyDetails->mxmph;
            $journey->avg_speed = $telematicsJourneyDetails->avgmph;
            $journey->odometer_start = $telematicsJourneyDetails->journeyStart;
            $journey->odometer_end = $telematicsJourneyDetails->journeyEnd;
            $journey->incident_count = $telematicsJourneyDetails->incident_count;
            $journey->save();
        //\Log::info($journey);
        }
        
    }
}
