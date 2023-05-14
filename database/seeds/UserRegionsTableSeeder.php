<?php

use Illuminate\Database\Seeder;
use App\Models\UserRegion;
use App\Models\UserDivision;
use Illuminate\Database\Eloquent\Model;

class UserRegionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        if(env('IS_REGION_LOCATION_LINKED_IN_USER'))
        {
        	DB::table('user_locations')->truncate();	
        }
        DB::table('user_regions')->truncate();
        $userfilePath = strtolower(env('BRAND_NAME')."/"."user_regions.csv");
        $file = fopen(base_path("database" . DIRECTORY_SEPARATOR . "seeds" . DIRECTORY_SEPARATOR . $userfilePath), "r");
        $cntr = 0;
        while(!feof($file)){
            $line = fgets($file);
            $dataArray = explode("\t", $line);
            if($cntr > 0) 
            {
                $UserRegions = new UserRegion();
                $name=$dataArray[0];
                if($dataArray[0]!='')
                {
                    if(env('IS_DIVISION_REGION_LINKED_IN_USER'))
                    {
                	   $UserDivisionsId = UserDivision::where('name',$dataArray[0])->select('id')->first();
                        if(isset($UserDivisionsId)) {
                            $UserRegions->user_division_id=$UserDivisionsId->id;
                            $name=$dataArray[1];
                        }
                    }
                }

                $UserRegions->name = trim($name);
                if($UserRegions->name != '')
                {
            	   $UserRegions->save();
                }
            }
            $cntr++;
        }
        fclose($file);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

