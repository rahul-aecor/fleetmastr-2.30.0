<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Models\Vehicle;
use App\Models\TelematicsJourneys;
use App\Models\TelematicsJourneyDetails;

class updateVehicleLastLocation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicle:updateVehicleLastLocation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to update the vehicle Last location from Telematics Journey Details';

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

        $vehicles = Vehicle::where('is_telematics_enabled', 1)->get();
        foreach ($vehicles as $vehicle) {
            $tjdMax = TelematicsJourneyDetails::where('vrn',$vehicle->registration)->whereNotNull('odometer')->orderBy('id','desc')->first();

            if ($tjdMax)
            {
                print_r("Updating Vehicle : " . $vehicle->registration . " with NS : " . $tjdMax->ns . "\n");
                DB::table('vehicles')->where('registration', $vehicle->registration)
                                    ->update([  'telematics_ns'=>$tjdMax->ns, 
                                                'telematics_odometer'=>$tjdMax->odometer,
                                                'telematics_lat'=>$tjdMax->lat,
                                                'telematics_lon'=>$tjdMax->lon]);
            }
        }
    }
}
