<?php

use Illuminate\Database\Seeder;
use App\Models\DefectMaster;

class DefectMasterTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
		DB::statement('SET FOREIGN_KEY_CHECKS=0');
		DB::table('defect_master')->truncate();  
		$filename = strtolower(env('BRAND_NAME')."/"."defects_master.txt");  	
		$file = fopen(base_path("database" . DIRECTORY_SEPARATOR . "seeds" . DIRECTORY_SEPARATOR . $filename), "r");
		$i=0;
		while(!feof($file)){
			$line = fgets($file);
			$line = rtrim($line);
			$dataArray = explode("\t", $line);
			if ($i>0){
				$defectMaster = new DefectMaster();
				$defectMaster->order = $dataArray[1];
				$defectMaster->type = $dataArray[2];
				$defectMaster->page_title = $dataArray[3];
				$defectMaster->app_question = $dataArray[4];
				$defectMaster->app_question_with_defect = $dataArray[5];
				$defectMaster->defect = $dataArray[6] !== '\N' ? $dataArray[6] : null;
				$defectMaster->has_not_applicable_option = $dataArray[7];
				$defectMaster->defect_order = $dataArray[8];
				$defectMaster->has_image = $dataArray[9];
				$defectMaster->has_text = $dataArray[10];
				$defectMaster->is_prohibitional = $dataArray[11];
				$defectMaster->show_warning = $dataArray[12];
				$defectMaster->warning_text = $dataArray[13] !== '\N' ? $dataArray[13] : null;
				$defectMaster->safety_notes = $dataArray[14] !== '\N' ? $dataArray[14] : null;
				$defectMaster->for_hgv = $dataArray[15];
				$defectMaster->{"for_non-hgv"} = $dataArray[16];
				$defectMaster->save();
			}
			$i++;
		}
		fclose($file);
		DB::statement('SET FOREIGN_KEY_CHECKS=1');		
    }
}
