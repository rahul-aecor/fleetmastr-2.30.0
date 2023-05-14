<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FuelCostService;
use App\Services\ParseMailService;

class SynchronizeFuelCostData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:fuel-cost-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize fuel cost data';

    /**
     * Fuel Costs service instance.
     *
     * @var object
     */
    protected $fuelCostService;

    /**
     * Parse mail instance.
     *
     * @var object
     */
    protected $parseMailService;

    /**
     * Fuel Costs update email subject.
     *
     * @var string
     */
    protected $fuelCostsSyncEmailSubject;

    /**
     * Fuel Costs attachment file.
     *
     * @var string
     */
    protected $fuelCostsAttachmentFileToDownload;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(FuelCostService $fuelCostService, ParseMailService $parseMailService)
    {
        parent::__construct();
        $this->fuelCostService = $fuelCostService;
        $this->parseMailService = $parseMailService;
        $this->fuelCostsSyncEmailSubject = config('branding')[env('BRAND_NAME')]['import_email_subject_prefix'] . ' fuel costs CSV';
        $this->fuelCostsAttachmentFileToDownload = config('branding')[env('BRAND_NAME')]['import_email_subject_prefix'] . ' fuel costs.csv';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $attachmentDownloadResponse = $this->parseMailService->getEmailAttachment($this->fuelCostsSyncEmailSubject, $this->fuelCostsAttachmentFileToDownload);
            if($attachmentDownloadResponse['status'] === 'success') {
                $response = $this->fuelCostService->processData($attachmentDownloadResponse['filepath']);
            }
        } catch (Exception $e) {

        }
    }
}
