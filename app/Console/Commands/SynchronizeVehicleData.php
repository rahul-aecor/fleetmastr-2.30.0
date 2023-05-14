<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VehicleService;
use App\Services\ParseMailService;

class SynchronizeVehicleData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:vehicle-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize vehicle data';

    /**
     * Vehicle service instance.
     *
     * @var object
     */
    protected $vehicleService;

    /**
     * Parse mail instance.
     *
     * @var object
     */
    protected $parseMailService;

    /**
     * Vehicle update email subject.
     *
     * @var string
     */
    protected $vehicleSyncEmailSubject;

    /**
     * Vehicle attachment file.
     *
     * @var string
     */
    protected $vehicleAttachmentFileToDownload;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(VehicleService $vehicleService, ParseMailService $parseMailService)
    {
        parent::__construct();
        $this->vehicleService = $vehicleService;
        $this->parseMailService = $parseMailService;
        $this->vehicleSyncEmailSubject = config('branding.import_email_subject_prefix') . " vehicles CSV";
        $this->vehicleAttachmentFileToDownload = "Fleetmastr - Vehicle List.csv";
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $attachmentDownloadResponse = $this->parseMailService->getEmailAttachment($this->vehicleSyncEmailSubject, $this->vehicleAttachmentFileToDownload);
            if ($attachmentDownloadResponse['status'] === 'success') {
                $response = $this->vehicleService->processVehicles($attachmentDownloadResponse['filepath']);
            }
        } catch (Exception $e) {
            $importVehicleResponseEmailToDev = explode(",", env('IMPORT_VEHICLE_RESPONSE_EMAIL_TO_DEV'));
        }
    }
}
