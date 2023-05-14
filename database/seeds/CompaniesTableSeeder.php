<?php

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class CompaniesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('companies')->truncate();
        $userfilePath = strtolower(env('BRAND_NAME')."/"."companies.txt");
        $file = fopen(base_path("database" . DIRECTORY_SEPARATOR . "seeds" . DIRECTORY_SEPARATOR . $userfilePath), "r");
        $cntr = 0;
        while(!feof($file)){
            $line = fgets($file);
            $line = rtrim($line);
            $dataArray = explode("\t", $line);
            $companies = new Company();
            $companies->name = trim($dataArray[0]);
            $companies->user_type = "Other";
            $companies->save();
        }
        fclose($file);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
