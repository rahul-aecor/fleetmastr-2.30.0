<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TelematicsJourneys;
use App\Repositories\RedisJourneysRepository;

class PopulateCurrentOpenJourneysToRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fm:populateCurrentOpenJourneysToRedis';

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
	    $telematicsJourneys = new TelematicsJourneys();
	    $redisJourneysRepository = new RedisJourneysRepository();
	    $this->telematicsJourneysMap = TelematicsJourneys::whereNull('end_time')->select('id','journey_id','uid','vrn','vehicle_id')->get()->keyBy(function ($item) {
            return strtoupper($item['vehicle_id'].'_'.$item['uid'].'_'.$item['journey_id']);
        })->toArray();
        ///discuss following with Nitin bhai
        $redisJourneysRepository = new RedisJourneysRepository();
        foreach($this->telematicsJourneysMap as $tj) {
            //print_r($tj);
        $key = strtoupper($tj['vehicle_id'].'_'.$tj['uid'].'_'.$tj['journey_id']);
            if (!$redisJourneysRepository->isJourneyEntryExisting($key)) {
                $redisJourneysRepository->createJourney($tj);
            }
        }
	
    }
}
