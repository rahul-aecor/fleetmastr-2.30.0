<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon\Carbon as Carbon;
use App\Models\TelematicsJourneys;


class TelematicsBindUserDriverId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telematics:bind-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to Bind user to the journey using DriverID key summary call';

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
        $startDate = Carbon::yesterday()->startOfDay()->format('Y-m-d H:i:s');

        $journeys = DB::table('telematics_journeys')
                        ->join('telematics_journey_details', function ($join) {
                            $join->on('telematics_journeys.id', '=', 'telematics_journey_details.telematics_journey_id');
                        })
                        ->select('telematics_journeys.id', 'telematics_journeys.user_id', 'telematics_journeys.start_time', 'telematics_journeys.end_time', 'telematics_journeys.vrn', 'telematics_journeys.dallas_key', DB::raw("REPLACE(JSON_EXTRACT(telematics_journey_details.raw_json, '$.tag_id'),'\"','') as 'tag_id'"), DB::raw("REPLACE(JSON_EXTRACT(telematics_journey_details.raw_json, '$.driver1_id'),'\"','') as 'driver1_id'"), DB::raw("(select u.id from users u where u.is_disabled=0 and upper(u.driver_tag_key)=upper(REPLACE(JSON_EXTRACT(telematics_journey_details.raw_json, '$.driver1_id'),'\"','')) limit 1) as 'new_user_id'"))
                        ->where('telematics_journey_details.ns', 'tm8.jny.sum.ex1')
                        ->where('telematics_journeys.start_time', '>', $startDate)
                        // ->where('telematics_journeys.start_time', '>', '2022-04-01 00:00:00')
                        ->where(DB::raw("JSON_EXTRACT(telematics_journey_details.raw_json, '$.driver1_id')"), '<>', '')
                        ->havingRaw(DB::raw("user_id <> new_user_id"))
                        //->take(10)
                        ->get()
                        ;

        foreach ($journeys as $journey) {
	    \Log::info("Updated Journey " . $journey->id . " with user_id " . $journey->new_user_id . " and dallas_key " . $journey->driver1_id . "\n");
            $affected = DB::table('telematics_journeys')
                          ->where('id', $journey->id)
                          ->update(['user_id' => $journey->new_user_id, 'dallas_key' => $journey->driver1_id]);

        }
    }
}
