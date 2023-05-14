<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\VehicleDivisions;
use Illuminate\Database\Eloquent\Model;

class VehicleDivisionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE')) {
            DB::table('vehicle_regions')->truncate();
        }

        DB::table('vehicle_divisions')->truncate();
        $userfilePath = strtolower(env('BRAND_NAME')."/"."vehicle_divisions.csv");
        $file = fopen(base_path("database" . DIRECTORY_SEPARATOR . "seeds" . DIRECTORY_SEPARATOR . $userfilePath), "r");
        
        $cntr = 0;
        while(!feof($file)) {
            $line = fgets($file);
            $dataArray = explode("\t", $line);
            if($cntr > 0) {
                $VehicleDivisions = new VehicleDivisions();
                $VehicleDivisions->name = trim($dataArray[0]);
                if($VehicleDivisions->name != '') {
            	   $VehicleDivisions->save();
                }
            }
            $cntr++;
        }
        fclose($file);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
