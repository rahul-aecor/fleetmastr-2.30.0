<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vehicle;
use App\Models\MaintenanceEvents;
use App\Models\VehicleMaintenanceHistory;
use Carbon\Carbon;

class AddPmiDateEntriesMaintennaceHistoryTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:vehicle-pmi-entries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add pmi inetries in vehicle maintenance history table';

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
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $today = Carbon::today();
        $dateAfterOneMonth = Carbon::today()->addMonth(1);
        $pmiEventId = MaintenanceEvents::where('slug', 'preventative_maintenance_inspection')->first()->id;
        \DB::statement('DELETE FROM vehicle_maintenance_history where event_type_id = '.$pmiEventId);
        $vehicles = Vehicle::whereNotNull('first_pmi_date')->get();

        foreach($vehicles as $vehicle) {
        	$firstPmiDate = $vehicle->first_pmi_date;
        	$interval = $vehicle->type->pmi_interval;
        	$intervalArr = explode(" ", $interval);

        	//Create entry for first date
        	$vehicleHistory = new VehicleMaintenanceHistory();
            $vehicleHistory->vehicle_id = $vehicle->id;
            $vehicleHistory->event_type_id = $pmiEventId;
            $vehicleHistory->event_plan_date = $firstPmiDate;
            $vehicleHistory->event_status = 'Incomplete';
            $vehicleHistory->created_by = 1;
            $vehicleHistory->updated_by = 1;
            $vehicleHistory->save();
        	
        	$nextPmiDate = Carbon::parse($firstPmiDate)->addWeeks($intervalArr[0]);
        	$totalWeekDays = $intervalArr[0] * 7;
        	$dayDiff = $nextPmiDate->diffInDays($today);

        	$count = (int)($dayDiff / $totalWeekDays) + 1;

        	$this->info($vehicle->id."----".$firstPmiDate."----".$nextPmiDate->toDateString()."----".$dayDiff);
        	//Create entry for next dates
        	for($i = 0; $i < $count; $i++) {
        		if($i > 1) {
        			$nextPmiDate = Carbon::parse($nextPmiDate)->addWeeks($intervalArr[0]);
        		}
        		if($nextPmiDate->lt($dateAfterOneMonth)) {
		        	$vehicleHistory = new VehicleMaintenanceHistory();
		            $vehicleHistory->vehicle_id = $vehicle->id;
		            $vehicleHistory->event_type_id = $pmiEventId;
		            $vehicleHistory->event_plan_date = $nextPmiDate->toDateString();
		            $vehicleHistory->event_status = 'Incomplete';
		            $vehicleHistory->created_by = 1;
		            $vehicleHistory->updated_by = 1;
		            $vehicleHistory->save();
			        $vehicle->next_pmi_date = Carbon::parse($nextPmiDate->addWeeks($intervalArr[0]))->format('d M Y');
        		} else {
        			$vehicle->next_pmi_date = Carbon::parse($nextPmiDate)->format('d M Y');
        		}
        	}
        	$vehicle->save();
        }
    }
}
