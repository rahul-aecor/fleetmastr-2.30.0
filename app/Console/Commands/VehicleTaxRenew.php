<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Vehicle;
use Illuminate\Console\Command;
use App\Models\VehicleMaintenanceNotification;
use App\Models\VehicleMaintenanceHistory;

class VehicleTaxRenew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicle:taxRenew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will renew vehicle tax';

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

        $events = config('config-variables.eventNotifications');
        $taxDt = Carbon::today()->subDay(1);

        $vehicles = Vehicle::whereNotNull('dt_tax_expiry')
                            ->where('dt_tax_expiry', '<=', $taxDt)
                            ->get();

        $nextDt = $taxDt->addYear();
        foreach ($vehicles as $vehicle) {
            $lastTaxDt = $vehicle->dt_tax_expiry;
            $vehicle->dt_tax_expiry = $nextDt->format('d M Y');
            $vehicle->save();

            $history = new VehicleMaintenanceHistory();
            $history->vehicle_id = $vehicle->id;
            $history->event_type = 'vehicle_tax';
            $history->event_date = $lastTaxDt;
            $history->comment = '-';
            $history->save();
        }
        $this->info(count($vehicles) . ' Vehicle(s) Tax Expiry Updated');
    }
}
