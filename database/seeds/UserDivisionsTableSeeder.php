<?php

use Illuminate\Database\Seeder;
use App\Models\UserDivision;
use Illuminate\Database\Eloquent\Model;


class UserDivisionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      	DB::statement('SET FOREIGN_KEY_CHECKS=0');
      	if(env('IS_DIVISION_REGION_LINKED_IN_USER'))
        {
            DB::table('user_regions')->truncate();
        }
        DB::table('user_divisions')->truncate();
        $userfilePath = strtolower(env('BRAND_NAME')."/"."user_divisions.csv");
        $file = fopen(base_path("database" . DIRECTORY_SEPARATOR . "seeds" . DIRECTORY_SEPARATOR . $userfilePath), "r");
        $cntr = 0;
        while(!feof($file)){
            $line = fgets($file);
            $dataArray = explode("\t", $line);
            if($cntr > 0) 
            {
	            $UserDivisions = new UserDivision();
	            $UserDivisions->name = trim($dataArray[0]);
	            if($UserDivisions->name != '')
	            {
	            	$UserDivisions->save();
	            }
            }
            $cntr++;
        }
        fclose($file);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
