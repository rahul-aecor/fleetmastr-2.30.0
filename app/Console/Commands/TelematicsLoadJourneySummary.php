<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TelematicsJourneys;
use Carbon\Carbon as Carbon;
use App\Models\Vehicle;
use App\Services\TelematicsService;
use App\Repositories\TelematicsJourneyDetailsRepository;
use App\Repositories\TelematicsJourneysRepository;


class TelematicsLoadJourneySummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telematics:load_journey_summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to load Journey Summary from the TrackM8 Log files';

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
        $telematicsService = new TelematicsService();

        $dir_path = base_path("database") . '/tmlog';

        $files = \File::allFiles($dir_path);
        sort($files);
        foreach( $files as $file ) {

            print_r($file->getPathname() . "\n");

            $openFile = fopen($file->getPathname(), "r");
            $cntr = 0;
            while(!feof($openFile)) {
                $line = fgets($openFile);
                $dataString = trim(mb_substr($line, 42));
                $dataString = trim(mb_substr($dataString, 0, strlen($dataString)-5));
                $journeyDetails = json_decode($dataString);
                if (!empty($journeyDetails)){
                    foreach($journeyDetails as $journeyDetail)
                    {
                        if ($journeyDetail->ns=='tm8.jny.sum'){

                            $value = (array) $journeyDetail;

                            $telematicsJourney = new TelematicsJourneysRepository();
                            $telematicsJourney = $telematicsJourney->update($value);

                            if($telematicsJourney) {
                                
                                // if($telematicsJourney->user_id == env('SYSTEM_USER_ID')){
                                //     $telematicsService->bindUser($value);
                                // }
                                $telematicsService->processScore($telematicsJourney);
                            }
                            $cntr++;
                        }
                        else if($journeyDetail->ns == 'tm8.jny.sum.ex1'){
                            $value = (array) $journeyDetail;
                            if (isset($value['journey_id'])) {
                                $telematicsService->bindUser($value);
                            }
                        }
                    }
                    // exit;
                }
            }
            print_r("Count : $cntr \n");
            fclose($openFile);
            // exit;
        }
    }
}
