<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TelematicsJourneyDetails;
use Log;

class DeleteInvalidTelematicsJourneyDetailsEntries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:invalid-telematics-joueny-details-entries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete invalid entries (if time greater than journey end time)';

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
        Log::info('DeleteInvalidTelematicsJourneyDetailsEntries Logs Start..');
        $journeys = TelematicsJourneyDetails::where('ns', 'tm8.gps.jny.end')
                                            ->get(['time', 'telematics_journey_id']);

        foreach($journeys as $journey) {
            $telematicJourneys = TelematicsJourneyDetails::where('telematics_journey_id', $journey->telematics_journey_id)
                                                    ->where('time', '>', $journey->time)
                                                    ->get();

            foreach($telematicJourneys as $journeyDetail) {
                Log::info('Deleting journey id: '.$journey->telematics_journey_id.' and id: '.$journeyDetail->id);
                $this->info('Deleting journey id: '.$journey->telematics_journey_id.' and id: '.$journeyDetail->id);
                $journeyDetail->delete();
            }
        }

        Log::info('DeleteInvalidTelematicsJourneyDetailsEntries Logs End..');
    }
}
