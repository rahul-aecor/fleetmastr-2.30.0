<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReportDownload;
use App\Models\Report;
use App\Repositories\CustomReportRepository;
use DB;

class Reports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run report commands';

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
        $service = new CustomReportRepository();
        $reportDownloads = ReportDownload::whereNull('report_columns')->orWhere('report_columns', '')->get();
        if(isset($reportDownloads)) {
            foreach($reportDownloads as $reportDownload) {
                $reportDatasetColumns = [];
                $report = $reportDownload->report;
                if($report->is_custom_report == 1) {
                    $reportColumns = $report->reportColumns->pluck('report_dataset_id');
                    $dataSet = $service->getReportDataSetColumns($report->id, $reportColumns);
                    foreach($dataSet as $value) {
                        $reportDatasetColumns[] = $value->title. '|'. str_replace('App\\Models\\', '', $value->model_type);                 
                    }
                    $reportDatasetColumns = implode(",", $reportDatasetColumns);

                } else {
                    $dataSet = config('config-variables.standard_reports.'.$report->slug);
                    $reportDatasetColumns = implode(",", $dataSet);
                }
                $reportDownload->report_columns = $reportDatasetColumns;
                $reportDownload->save();
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('report_columns')->truncate();
        DB::table('report_download_report_dataset')->truncate();
        DB::table('report_dataset')->truncate();
        DB::table('report_categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $seeder = new \CreateReportCategoriesTableSeeder();
        $seeder->run();

        $seeder = new \CreateReportDataSetTableSeeder();
        $seeder->run();

        $seeder = new \CreateReportCategoryReportDataSetTableSeeder();
        $seeder->run();

        $seeder = new \StandardReportTableSeeder();
        $seeder->run();

    }
}
