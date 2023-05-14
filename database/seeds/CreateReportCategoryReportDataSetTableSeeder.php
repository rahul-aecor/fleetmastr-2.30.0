<?php

use Illuminate\Database\Seeder;
use App\Models\ReportDataset;
use App\Models\ReportCategory;

class CreateReportCategoryReportDataSetTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataset = ReportDataset::all()->pluck('id', 'title');
        $categories = ReportCategory::all()->pluck('id', 'name');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('report_category_report_dataset')->truncate();

        $userfilePath = strtolower("report_category_dataset.csv");
        $file = fopen(base_path("database" . DIRECTORY_SEPARATOR . "seeds" . DIRECTORY_SEPARATOR . $userfilePath), "r");
        
        $data = [];
        $counter = 0;
        $notExists = [];
        $categoryNotExists = [];
        while(!feof($file)) {
            $line = fgets($file);
            $dataArray = explode(",", $line);
            $reportName = explode(" ", $dataArray[0]);
            $dataArray[0] = $reportName[0]." ".strtolower($reportName[1]);
            if(isset($categories[$dataArray[0]])) {
                $categoryId = $categories[$dataArray[0]];

                for($i = 1; $i < count($dataArray); $i++) {
                    $datasetTitle = str_replace('"', '', trim($dataArray[$i]));
                    $datasetTitle1 = ucfirst(strtolower($datasetTitle));

                    if($datasetTitle == 'Type') {
                        $datasetTitle = 'Vehicle Type';
                    }
                    $data[$counter]['report_category_id'] = $categoryId;
                    if(isset($dataset[$datasetTitle])) {
                        $data[$counter]['report_dataset_id'] = $dataset[$datasetTitle];
                        $counter++;
                    } else if(isset($dataset[$datasetTitle1])) {
                        $data[$counter]['report_dataset_id'] = $dataset[$datasetTitle1];
                        $counter++;
                    } else {
                        $notExists[] = $datasetTitle;
                    }
                }
            } else {
                $categoryNotExists[] = $dataArray[0];
            }
        }

        fclose($file);

        DB::table('report_category_report_dataset')->insert($data);
    }
}
