<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Console\Command;

class UpdateVehicleNextPmiDateInterval extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicle:updateNextPmiDateInterval';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command updates the NextPmiDate Interval.';

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
        $vehicles = Vehicle::with('type')->get();
        foreach ($vehicles as $key => $vehicle) {
            $vehicleId = $vehicle->id;

            if (isset($vehicle->type) && $vehicle->type != null) {
                $vehicleProfile = $vehicle->type;
                $pmiInterval = $vehicleProfile->pmi_interval;
                $nextPmiDate = $vehicle->next_pmi_date;
                $currentDate = Carbon::now()->format('d M Y');

                if($pmiInterval && $nextPmiDate) {
                    while (strtotime($nextPmiDate) < strtotime($currentDate)) {
                        $nextPmiDate = date ("d M Y", strtotime($pmiInterval, strtotime($nextPmiDate)));
                    }
                }
                $vehicle->next_pmi_date = $nextPmiDate;
                $vehicle->save();
            }
            /*$vehicleProfileData = VehicleType::where('id', $vehicle->vehicle_type_id)->get();

            foreach ($vehicleProfileData as $key => $vehicleProfile) {

            }*/
        }
    }
}
