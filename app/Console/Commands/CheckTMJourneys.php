<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TelematicsJourneys;
use Carbon\Carbon as Carbon;
use App\Models\Vehicle;
use App\Services\TelematicsService;
use App\Repositories\TelematicsJourneyDetailsRepository;
use App\Repositories\TelematicsJourneysRepository;


class CheckTMJourneys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telematics:checkJourney';

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
        $filePath = base_path("storage") . '/logs/telematics/trackm8rawdata-' . Carbon::yesterday()->format('d-m-Y') . ".log";
        $startDate = Carbon::yesterday()->startOfDay()->format('Y-m-d H:i:s');
        $endDate = Carbon::yesterday()->endOfDay()->format('Y-m-d H:i:s');
        $incompleteJourneys = TelematicsJourneys::where('start_time','>=', $startDate)
                                    ->where('start_time','<=', $endDate)
                                    ->whereNull('end_time')
                                    ->get();
        $counter = 1;
        foreach ($incompleteJourneys as $incompleteJourney) {
            $nextJourney = TelematicsJourneys::where('vrn', $incompleteJourney->vrn)->where('journey_id', $incompleteJourney->journey_id+1)->first();
            if ($nextJourney){
                echo "Occation : " . $counter . "\n";
                echo "VRN : " . $incompleteJourney->vrn . "\n";
                echo "journey_id : " . $incompleteJourney->journey_id . "\n";
                echo "Events : \n";
                print_r("===================================================\n");
                $openFile = fopen($filePath, "r");
                while(!feof($openFile)) {
                    $line = fgets($openFile);
                    $dataString = trim(mb_substr($line, 42));
                    $dataString = trim(mb_substr($dataString, 0, strlen($dataString)-5));
                    $journeyDetails = json_decode($dataString);
                    if (!empty($journeyDetails)){
                        foreach($journeyDetails as $journeyDetail)
                        {
                            $value = (array) $journeyDetail;
                            if ( $value['vrn'] == trim($incompleteJourney->vrn) && $value['journey_id'] == trim($incompleteJourney->journey_id))
                            {
                                print_r(json_encode($value) . "\n");
                            }
                        }
                    }
                }
                fclose($openFile);
                print_r("===================================================\n\n\n");
            }
        }
    }
}
