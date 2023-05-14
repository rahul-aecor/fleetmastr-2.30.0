<?php

use Illuminate\Database\Seeder;
use App\Models\VehicleRepairLocations;
use Illuminate\Database\Eloquent\Model;

class VehicleRepairLocationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::statement('SET FOREIGN_KEY_CHECKS=0');
		DB::table('vehicle_repair_locations')->truncate();
        $userfilePath = strtolower(env('BRAND_NAME')."/"."vehicle_repair_locations.txt");
        $file = fopen(base_path("database" . DIRECTORY_SEPARATOR . "seeds" . DIRECTORY_SEPARATOR . $userfilePath), "r");
        $cntr = 0;
        while(!feof($file)){
            $line = fgets($file);
            $dataArray = explode("\t", $line);

            $vehicleRepairLocations = new VehicleRepairLocations();
            $vehicleRepairLocations->name = trim($dataArray[0]);
            $vehicleRepairLocations->save();
        }
        fclose($file);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

  //       DB::connection('mysql_new')->table('vehicle_repair_locations')->delete();
  //       DB::connection('mysql_new')->table('vehicle_repair_locations')->insert([
		// 		['name' => 'Parks Ford Inverness'],
		// 		['name' => 'Bristol Street Ford Gloucester'],
		// 		['name' => 'Immingham Ford'],
		// 		['name' => 'Pride Park Derby Ford'],
		// 		['name' => 'Evans Halshaw Ford Gainsborough'],
		// 		['name' => 'M53 Ford Ellesmere Port'],
		// 		['name' => 'Buckingham Ford'],
		// 		['name' => 'TC Harrison Ford Huntingdon'],
		// 		['name' => 'Essex Auto Group Dunton Ford'],
		// 		['name' => 'Think Ford Wokingham'],
		// 		['name' => 'Trust Ford Cripps Causeway'],
		// 		['name' => 'Rates Ford Grays'],
		// 		['name' => 'Transit Centre Dagenham'],
		// 		['name' => 'Ames Ford Thetford'],
		// 		['name' => 'Bear Street Ford Wooton Under Edge'],
		// 		['name' => 'Anchor Ford Padworth'],
		// 		['name' => 'Think Ford Reading'],
		// 		['name' => 'Stoneacre Goole Ford'],
		// 		['name' => 'Clevedon Ford'],
		// 		['name' => 'Trust Ford Patchway Bristol'],
		// 		['name' => 'TC Harrison Peterborough'],
		// 		['name' => 'Busseys Ford Attleborough'],
		// 		['name' => 'Evans Halshaw Ford Bedford'],
		// 		['name' => 'Busseys Ford East Dereham'],
		// 		['name' => 'Stoke Ford'],
		// 		['name' => 'Trust Ford Runcorn'],
		// 		['name' => 'Evans Halshaw Ford Chester'],
		// 		['name' => 'John Grose Ford Ipswich'],
		// 		['name' => 'Bristol Street Ford Cheltenham'],
		// 		['name' => 'Gillingham Ford'],
		// 		['name' => 'P J Nicholls Ford Evesham'],
		// 		['name' => 'Gates Ford Bishops Stortford'],
		// 		['name' => 'Busseys Ford Norwich'],
		// 		['name' => 'Allen Ford West Swindon']
		// ]);
    }
}
