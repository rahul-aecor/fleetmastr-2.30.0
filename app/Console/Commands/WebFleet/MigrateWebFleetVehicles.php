<?php

namespace App\Console\Commands\WebFleet;

use App\Custom\Client\Webfleet;
use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MigrateWebFleetVehicles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webfleet:migrateVehicles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update is_telematics_enabled and webfleet_object_id';

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

        $out = new \Symfony\Component\Console\Output\ConsoleOutput();

        $webfleetClient = new Webfleet();
        $data = $webfleetClient->getVehicles();


        if (count($data) > 0) {
            $vrns = collect($data)->pluck('objectno');

            $vehicles = Vehicle::whereIn('webfleet_registration',$vrns->toArray())->get()->keyBy('webfleet_registration');
            foreach ($data as $vehicle) {
                $out->writeln('============================================================');
                $out->writeln('Finding Vehicle : '.$vehicle['objectno'].' in DB.');
                $vehicleDB = isset($vehicles[$vehicle['objectno']]) ? $vehicles[$vehicle['objectno']] : null;
                if ($vehicleDB) {
                    $out->writeln('Vehicle found : '.$vehicleDB->webfleet_registration.'');
                    if ($vehicleDB->is_telematics_enabled == 1 && $vehicleDB->webfleet_object_id == $vehicle['objectuid']) {
                        $out->writeln('Vehicle is up to date.');
                    } else {
                        $out->writeln('Enabling telematics with webfleet uid : '.$vehicle['objectuid'].'');
                        $vehicleDB->is_telematics_enabled = 1;
                        $vehicleDB->webfleet_object_id = $vehicle['objectuid'];
                        $vehicleDB->save();
                        $out->writeln('Vehicle updated.');
                    }

                } else {
                    $out->writeln('Vehicle not found.');
                }
                $out->writeln('============================================================');
            }
        }
    }
}
