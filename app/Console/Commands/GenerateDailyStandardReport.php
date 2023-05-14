<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Report;
use App\Services\Report as ReportService;
use App\Repositories\CustomReportDownloadRepository;
use Carbon\Carbon;
use Log;

class GenerateDailyStandardReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:dailyStandardReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will automatic generate standard daily report';

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
        $reports = Report::where('is_custom_report', '0')->where('period', 'Daily')->get();
        $data = ['date_from' => Carbon::now()->startOfWeek()->toDateString(), 'date_to' => Carbon::today()->toDateString()];
        
        foreach($reports as $report) {
            Log::info('Report slug: '.$report->slug);
            
            if($report->slug == 'standard_last_login_report') {
                $localpath = $reportService->downloadLastLogin($data);
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
