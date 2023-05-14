<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;
use App\Repositories\TelematicsJourneysRepository;

class AddNewIncidentCountAndUpdateTotalCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:new-incident-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new incident count and update total count';

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
    	$journeyDetails = TelematicsJourneyDetails::whereRaw('speed > street_speed+(street_speed*0.2)')
    											->where('street_speed', '>', 0)
                                                ->where('ns', 'tm8.gps')
                                                ->get();
        $updatedJourneyArr = [];
        foreach($journeyDetails as $journey) {
            $journey->ns = 'tm8.dfb2.spdinc';
            $journey->save();
            if(!in_array($journey->telematics_journey_id, $updatedJourneyArr)) {
                $updatedJourneyArr[] = $journey->telematics_journey_id;
            }
        }

        array_unique($updatedJourneyArr, SORT_NUMERIC);
        $telematicsJourneys = TelematicsJourneys::whereIn('id', $updatedJourneyArr)->get();

        foreach($telematicsJourneys as $journey) {
	        $incident = TelematicsJourneyDetails::selectRaw('SUM(CASE WHEN ns = "tm8.dfb2.dec.l" THEN 1 WHEN ns = "tm8.dfb2.acc.l" THEN 1 WHEN ns = "tm8.dfb2.cnrl.l" THEN 1 WHEN ns = "tm8.dfb2.cnrr.l" THEN 1 WHEN ns = "tm8.dfb2.spd" THEN 1 WHEN ns = "tm8.dfb2.spdinc" THEN 1 WHEN ns = "tm8.dfb2.rpm" THEN 1 WHEN ns = "tm8.gps.idle.start" THEN 1 ELSE 0 END) AS incident_count,
	            SUM(CASE WHEN ns = "tm8.dfb2.spdinc" THEN 1 ELSE 0 END) AS new_speeding_incidents')
	            ->where('telematics_journey_id', $journey->id)
	            ->first();

	        $journey->incident_count = $incident->incident_count;
            $journey->speeding_incident_count = $incident->new_speeding_incidents;
            $journey->save();
	    }
    }
}
