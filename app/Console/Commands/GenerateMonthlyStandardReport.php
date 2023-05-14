<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Report;
use App\Services\Report as ReportService;
use App\Repositories\CustomReportDownloadRepository;
use Carbon\Carbon;
use Log;

class GenerateMonthlyStandardReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:monthlyStandardReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will automatic generate standard monthly report';

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
    public function handle(ReportService $reportService, CustomReportDownloadRepository $reportDownloadRepository)
    {
        $reports = Report::where('is_custom_report', '0')->where('period', 'Monthly')->get();
        // $reports = Report::where('slug', 'standard_defect_report')->get();
        $data = ['date_from' => Carbon::now()->startOfMonth()->toDateString(), 'date_to' => Carbon::today()->toDateString()];
        
        foreach($reports as $report) {
            Log::info('Report slug: '.$report->slug);
            $localpath = "";
            if($report->slug == 'standard_defect_report') {
                $localpath = $reportService->downloadReport('a', "curr", $data);
            } else if($report->slug == 'standard_fleet_cost_report') {
                $localpath = $reportService->downloadFleetCostReport($data);
            } else if($report->slug == 'standard_driving_events_report') {
                $localpath = $reportService->downloadDrivingEvents($data);
            } else if($report->slug == 'standard_speeding_report') {
                $localpath = $reportService->downloadSpeedingReport($data);
            } else if($report->slug == 'standard_journey_report') {
                $localpath = $reportService->downloadJourneyReport($data);
            } else if($report->slug == 'standard_fuel_usage_and_emission_report') {
                $localpath = $reportService->downloadFuelUsageAndEmissionReport($data);
            } else if($report->slug == 'standard_driver_behaviour_report') {
                $localpath = $reportService->downloadDriverBehaviorReport($data);
            } else if($report->slug == 'standard_vehicle_behaviour_report') {
                $localpath = $reportService->downloadVehicleBehaviorReport($data);
            }


            if($localpath != '') {
                Log::info($localpath);
                $data['report_id'] = $report->id;
                $data['created_at'] = Carbon::now();
                $data['is_auto_download'] = 1;
                $reportDownload = $reportDownloadRepository->store($data);
                
                $reportDownload->addMedia($localpath)->toCollectionOnDisk('custom_reports', 'S3_uploads');

                $s3Url = $reportDownload->getMedia()->first()->getUrl();
                Log::info('S3 file name: '.$s3Url);
                $reportDownload->filename = $s3Url;
                $reportDownload->save();
            }
        }
    }
}
