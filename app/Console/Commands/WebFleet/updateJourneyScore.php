<?php
namespace App\Console\Commands\WebFleet;

use App\Models\TelematicsJourneys;
use App\Services\TelematicsService;
use Illuminate\Console\Command;

class updateJourneyScore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telematics:updateJourneyScore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To update journey scores.';

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
        $telematicsJourneys = TelematicsJourneys::all();

        foreach ($telematicsJourneys as $journey) {
            $telematicsService = new TelematicsService();
            $telematicsService->processScore($journey);
        }
    }
}
