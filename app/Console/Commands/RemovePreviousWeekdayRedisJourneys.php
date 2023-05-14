<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\TelematicsJourneys;
use App\Repositories\RedisJourneysRepository;

class RemovePreviousWeekdayRedisJourneys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fm:removePreviousWeekdayRedisJourneys';

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
	    $removeJourneysDated = Carbon::now()->subDays(7);
	    $telematicsJourneysToBeRemoved = TelematicsJourneys::where('start_time','<=',$removeJourneysDated)->get();
	    foreach($telematicsJourneysToBeRemoved as $tj) {
	    	$key = strtoupper($tj->vehicle_id.'_'.$tj->uid.'_'.$tj->id);
	    	$redisJourneysRepository->delJourney($key);
	    }
	
    }
}
