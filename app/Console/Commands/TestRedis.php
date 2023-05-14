<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\ZoneVehiclesRepository;

class TestRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fm:redischeck';

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
	    $zoneVehiclesRepository = new ZoneVehiclesRepository();
	    $data['vrn'] = 'TEST1234';
	    $data['zone_id'] = 1;
	
	    if ($zoneVehiclesRepository->isZoneVehicleEntryExisting($data))
	    {
		    print_r("Zone entry for " . json_encode($data) . "\n");
	    }
	    else
	    {
		    print_r("No Zone entry for " . json_encode($data) . "\n");
		    print_r("Creating the entry \n");
	    	$zoneVehiclesRepository->createZoneVehicle($data);
	    }

	    if ($zoneVehiclesRepository->isZoneVehicleEntryExisting($data))
	    {
		    print_r("Zone entry for " . json_encode($data) . "\n");
		    print_r("Removing Entry \n");
		    $zoneVehiclesRepository->delZoneVehicle($data);
	    }
	    else
	    {
		    print_r("No Zone entry for " . json_encode($data) . "\n");
	    }

    }
}
