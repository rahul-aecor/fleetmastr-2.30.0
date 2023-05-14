<?php

namespace App\Console\Commands;

use Storage;
use Carbon\Carbon;
use App\Models\Notification;
use Illuminate\Console\Command;
use App\Models\P11dReport;
use App\Custom\Helper\P11dReportHelper;

class CheckAndFinalizeP11dReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finalize:p11dreport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and finalize p11d report';

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
        //$commonHelper = new Common();
        $p11dReportHelper = new P11dReportHelper();

        $currTaxYear = $p11dReportHelper->calcTaxYear();
        $currTaxYearParts = explode('-', $currTaxYear);
         //this will become prev to prev in a minute as this report is run at 23:59 on last day of tax year.
        $prevTaxYearParts = [$currTaxYearParts[0]-1, $currTaxYearParts[1]-1];
        $prevTaxYear = implode('-', $prevTaxYearParts);

        //checking prev year as it will be prev to prev in a minute.
        $isFinalisedYear = P11dReport::where(['tax_year'=>$prevTaxYear])->get();
        if ($isFinalisedYear->isEmpty()) {
//print_r('$inside');exit;
            $p11dreport = new P11dReport();
            $p11dreport->freezed_date = Carbon::now();
            $p11dreport->tax_year = $prevTaxYear;

            $reportfile = $p11dReportHelper->generateReport($prevTaxYear);
            $filename = "P11D_Benefits_in_Kind".$prevTaxYear;
            Storage::disk('S3_uploads')->put('p11dReports/'.$filename.'.xlsx', file_get_contents($reportfile));
            unlink($reportfile);
            $url = config('filesystems.disks.S3_uploads.domain') . '/p11dReports/'.$filename.'.xlsx';

            $p11dreport->url = $url;
            $p11dreport->save();
        }
    }
        
}
