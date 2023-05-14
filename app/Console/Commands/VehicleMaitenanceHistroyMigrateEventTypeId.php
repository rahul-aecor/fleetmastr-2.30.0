<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VehicleMaitenanceHistroyMigrateEventTypeId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrateEventTypeId';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign Even Id in vehicle_maintenance_history and remove event_type from vehicle_maintenance_history table.';

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
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $maintenanceEvents = \App\Models\MaintenanceEvents::all()->keyBy('slug');

        $maintenanceHistories = \App\Models\VehicleMaintenanceHistory::all();

        if(count($maintenanceHistories) > 0) {
            foreach ($maintenanceHistories as $key => $maintenanceHistory) {
                $eventTypeId = isset($maintenanceEvents[$maintenanceHistory->event_type]) ? $maintenanceEvents[$maintenanceHistory->event_type]->id : 0;

                if ($eventTypeId != 0) {
                    $maintenanceHistory->event_type_id = $eventTypeId;
                    $maintenanceHistory->save();
                }

            }
        }
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
