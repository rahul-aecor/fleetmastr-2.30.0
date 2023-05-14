<?php

namespace App\Console\Commands\Teletracnavman;

use Illuminate\Console\Command;
use App\Models\Vehicle;
use GuzzleHttp\Client;

class GetVehiclesData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teletrac:GetVehiclesData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch vehicles data';

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
        $queryString = "vehicles?status=ALL&pruning=B2B";
        $resp = callTeletracJourneysApi($queryString);
        foreach($resp as $vehicle){
            $v_name = str_replace(' ', '', $vehicle['name']);
            // $v_registration = str_replace(' ', '', $vehicle['registration']);
            $vehicleData = Vehicle::where('registration', $v_name)->first();
            if(isset($vehicleData)) {
                $vehicleData->webfleet_object_id = $vehicle['id'];
                $vehicleData->save();
            }
        }
    }
}
