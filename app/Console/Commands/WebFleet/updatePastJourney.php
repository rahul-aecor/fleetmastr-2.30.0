<?php

namespace App\Console\Commands\WebFleet;

use App\Custom\Client\Webfleet;
use App\Models\TelematicsJourneys;
use App\Repositories\TelematicsJourneysRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;

class updatePastJourney extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webfleet:updatePastJourney';

    /**
     * The console command description.
     * #6590 - speeding incident not updating due to missing code in updateJourney command and this script use to update specific data range's speeding incidents count.
     * @var string
     */
    protected $description = 'Update Past Journey';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent ::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $_startTime=$this->ask('startTime');
        $_endTime=$this->ask('endTime');
        if ($this->confirm('Do you wish to continue? [y|n]')) {
            $startTime = Carbon::parse($_startTime)->format('Y-m-d H:i:s');
            $endTime = Carbon::parse($_endTime)->format('Y-m-d H:i:s');
            
            \Log::info('updatePastJourney');
            
            $telematicsJourneysSql = TelematicsJourneys::with('user')
                                ->where('start_time','>=',$startTime)
                                ->where('start_time','<=',$endTime);
        
        $telematicsJourneys = $telematicsJourneysSql->get();
        
        $telematicsJourneyRepo = new TelematicsJourneysRepository();
        $totalExecution=0; $totalInputCount=0;
        foreach($telematicsJourneys as $journey) {
                $calculatedFields = $telematicsJourneyRepo->getCalculatedFieldsOfJourney($journey->id);
                if($calculatedFields['new_speeding_incidents']>0){
                    $totalInputCount++;
                }
                $journey->speeding_incident_count = $calculatedFields['new_speeding_incidents'];
                $journey->save();
                $totalExecution++;
            }
            \Log::info('Total Execution : '.$totalExecution);
            \Log::info('Total non zero input : '.$totalInputCount);
        }
    }
}
