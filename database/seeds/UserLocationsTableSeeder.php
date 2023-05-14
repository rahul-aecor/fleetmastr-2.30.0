<?php

use Illuminate\Database\Seeder;
use App\Models\UserLocation;
use App\Models\UserRegion;
use App\Models\UserDivision;
use Illuminate\Database\Eloquent\Model;

class UserLocationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
		DB::table('user_locations')->truncate();
        $userfilePath = strtolower(env('BRAND_NAME')."/"."user_locations.csv");
        $file = fopen(base_path("database" . DIRECTORY_SEPARATOR . "seeds" . DIRECTORY_SEPARATOR . $userfilePath), "r");
        $cntr = 0;
        while(!feof($file)){
            $line = fgets($file);
            $dataArray = explode(",", $line);
            if($cntr > 0) 
            {
                
                $UserLocations = new UserLocation();
                if($dataArray[0]!='')
                {
                  $name=$dataArray[0];
                   if(env('IS_DIVISION_REGION_LINKED_IN_USER') && env('IS_REGION_LOCATION_LINKED_IN_USER'))
                  {
                    $UserDivisionId = UserDivision::where('name',$dataArray[0])->select('id')->first();
                      $UserRegionsId = UserRegion::where('name',$dataArray[1])->where('user_division_id',$UserDivisionId->id)->select('id')->first();
                      $UserLocations->user_region_id=$UserRegionsId->id;
                      $name=$dataArray[2];
                  }
                  else if(env('IS_REGION_LOCATION_LINKED_IN_USER'))
                  {
                      $UserRegionsId = UserRegion::where('name',$dataArray[0])->select('id')->first(); 
                      $UserLocations->user_region_id=$UserRegionsId->id;
                      $name=$dataArray[1];
                  }
                  $UserLocations->name = trim($name);

                  if($UserLocations->name != ''){
                  	$UserLocations->save();
                  }
                }
            }
            $cntr++;
        }
        fclose($file);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

    }
}
