<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Report;
use App\Services\Report as ReportService;
use App\Repositories\CustomReportDownloadRepository;
use Carbon\Carbon;
use Log;

class GenerateWeeklyStandardReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:weeklyStandardReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will automatic generate standard weekly report';

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
        $reports = Report::where('is_custom_report', '0')->where('period', 'Weekly')->get();
        // $reports = Report::where('slug', 'standard_last_login_report')->get();
        $data = ['date_from' => Carbon::now()->startOfWeek()->toDateString(), 'date_to' => Carbon::today()->toDateString()];
        
        foreach($reports as $report) {
            Log::info('Report slug: '.$report->slug);
            
            if($report->slug == 'standard_activity_report') {
                $localpath = $reportService->downloadReport('j', "curr", $data);
            } else if($report->slug == 'standard_vor_report') {
                $localpath = $reportService->downloadReport('d', "curr", $data);
            } else if($report->slug == 'standard_vor_defect_report') {
                $localpath = $reportService->downloadReport('b', "curr", $data);
            }

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
