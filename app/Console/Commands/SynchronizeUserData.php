<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UserService;
use App\Services\ParseMailService;

class SynchronizeUserData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:user-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize skanska user data';

    /**
     * User service instance.
     *
     * @var object
     */
    protected $userService;

    /**
     * Parse mail instance.
     *
     * @var object
     */
    protected $parseMailService;

    /**
     * User update email subject.
     *
     * @var string
     */
    protected $userSyncEmailSubject;

    /**
     * User attachment file.
     *
     * @var string
     */
    protected $userAttachmentFileToDownload;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(UserService $userService, ParseMailService $parseMailService)
    {
        parent::__construct();
        $this->userService = $userService;
        $this->parseMailService = $parseMailService;
        $this->userSyncEmailSubject = config('branding.import_email_subject_prefix') . ' drivers CSV';
        $this->userAttachmentFileToDownload = 'Fleetmastr - User List.csv';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $attachmentDownloadResponse = $this->parseMailService->getEmailAttachment($this->userSyncEmailSubject, $this->userAttachmentFileToDownload);
            if($attachmentDownloadResponse['status'] === 'success') {
                $response = $this->userService->processUsers($attachmentDownloadResponse['filepath']);
            }
        } catch (Exception $e) {

        }
    }
}
