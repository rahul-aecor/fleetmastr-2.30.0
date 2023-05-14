<?php

use Illuminate\Database\Seeder;
use App\Models\VehicleLocations;
use App\Models\VehicleRegions;
use App\Models\VehicleDivisions;
use Illuminate\Database\Eloquent\Model;

class VehicleLocationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::statement('SET FOREIGN_KEY_CHECKS=0');
		  DB::table('vehicle_locations')->truncate();
        $userfilePath = strtolower(env('BRAND_NAME')."/"."vehicle_locations.csv");
        $file = fopen(base_path("database" . DIRECTORY_SEPARATOR . "seeds" . DIRECTORY_SEPARATOR . $userfilePath), "r");
        $cntr = 0;
        while(!feof($file))
        {
            $line = fgets($file);
            $dataArray = explode(",", $line);
            if($cntr > 0) 
            {
              $vehicleLocations = new VehicleLocations();
              if($dataArray[0]!='')
              {
                  $name=$dataArray[0];
                  if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE') && env('IS_REGION_LOCATION_LINKED_IN_VEHICLE'))
                  {
                    $vehicleDivisionId = VehicleDivisions::where('name',$dataArray[0])->select('id')->first();
                    $VehicleRegionsId = VehicleRegions::where('name',$dataArray[1])->where('vehicle_division_id',$vehicleDivisionId->id)->select('id')->first();
                    $vehicleLocations->vehicle_region_id=$VehicleRegionsId->id;
                    $name=$dataArray[2];
                  }
                  else if(env('IS_REGION_LOCATION_LINKED_IN_VEHICLE'))
                  {
                    $VehicleRegionsId = VehicleRegions::where('name',$dataArray[0])->select('id')->first();
                    $vehicleLocations->vehicle_region_id=$VehicleRegionsId->id;
                    $name=$dataArray[1];
                  }
                  $vehicleLocations->name = trim($name);
                  if($vehicleLocations->name != ''){
                  	$vehicleLocations->save();
                  }
                }
              }
            $cntr++;
        }
        fclose($file);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

  //       DB::connection('mysql_new')->table('vehicle_locations')->delete();
  //       DB::connection('mysql_new')->table('vehicle_locations')->insert([
		// 	['name' => 'Aldermaston'],
		// 	['name' => 'Backford North'],
		// 	['name' => 'Hallen'],
		// 	['name' => 'Home (Remote)'],
		// 	['name' => 'Inverness PSD'],
		// 	['name' => 'Killingholme'],
		// 	['name' => 'London Office'],
		// 	['name' => 'Maintenance Central'],
		// 	['name' => 'Maintenance East'],
		// 	['name' => 'Maintenance North'],
		// 	['name' => 'Maintenance South'],
		// 	['name' => 'Maintenance West'],
		// 	['name' => 'Misterton'],
		// 	['name' => 'Rawcliffe'],
		// 	['name' => 'Redcliffe Bay'],
		// 	['name' => 'Saffron Walden'],
		// 	['name' => 'Thames B'],
		// 	['name' => 'Thetford'],
		// 	['name' => 'Purton'],
		// 	['name' => 'Sandy'],
		// 	['name' => 'Redmile']
		// ]);
    }
}
