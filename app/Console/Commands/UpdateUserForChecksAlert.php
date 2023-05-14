<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Alerts;
use DB;
use App\Models\TelematicsJourneys;
use App\Models\AlertNotifications;



class UpdateUserForChecksAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telematics:updateUserForChecksAlert';

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
        
        $telematicsJourneysMap = TelematicsJourneys::where('user_id', '!=', 1)->where('start_time','>',Carbon::yesterday())->get()->keyBy('id');
        $alert = Alerts::where('slug', 'vehicle_check_incomplete')->first();
        $alertTelematicsJourneys = AlertNotifications::where('user_id', env('SYSTEM_USER_ID'))
                                                ->where('alerts_id', $alert->id)
                                                ->where('alert_date_time','>',Carbon::yesterday())
                                                ->get();
                                                
        foreach ($alertTelematicsJourneys as $alertJourney) {
            $telematicsJourney = TelematicsJourneys::where('id',$alertJourney->journey_id)->first();
            print_r("Processing with ID  . " . $telematicsJourney->id . "\n");

            if ($telematicsJourney != null && $telematicsJourney->user_id != 1) {
                $alertJourney->user_id = $telematicsJourney->user_id;
                $alertJourney->save();
            }
        }

    }
}
