<?php
namespace App\Services;

use Carbon\Carbon;
use Webklex\IMAP\Facades\Client;

class ParseMailService
{
	/**
     * Create a new parse mail service.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Process vehicles.
     *
     * @return mixed
     */
    public function getEmailAttachment($subject, $attachmentFileToDownload, $test = false)
    {
        $messageStatusFlag = 'error';

        $fileName = null;

        $filePathForDownload = storage_path('importfiles');

        if($test){
            return ['status' => 'success', 'filepath' => $filePathForDownload. '/test.csv'];
        }

        // Alternative by using the Facade
        $oClient = Client::account('default');

        //Connect to the IMAP Server
        $oClient->connect();

        //Get all Mailboxes
        /** @var \Webklex\IMAP\Support\FolderCollection $inboxFolder */
        $inboxFolder = $oClient->getFolder('INBOX');

        //Get all Messages of the current Mailbox $oFolder
        /** @var \Webklex\IMAP\Support\MessageCollection $aMessage */
        $messages = $inboxFolder->messages()
                            ->unseen()
                            ->leaveUnread()
                            ->on(Carbon::now())
                            ->setFetchFlags(false)
                            ->limit(1)
                            ->whereSubject($subject)
                            ->get();


        /** @var \Webklex\IMAP\Message $oMessage */
        foreach($messages as $message) {
            if($message->getAttachments()->count() === 1) {
                $attachments = $message->getAttachments();
                foreach($attachments as $attachment) {
                    if(strtolower($attachment->getName()) === strtolower($attachmentFileToDownload)) {
                        $fileName = md5(time() . mt_rand(1,1000000)) . '.' . $attachment->getExtension();
                    
                        // $mimeType = $attachment->getMimeType();
                        // if(in_array($mimeType, ['text/plain', 'text/csv'])) {
                            $attachment->save($filePathForDownload, $fileName);
                        // }

                        $messageStatusFlag = 'success';
                    }
                };
            }

            /** @var \Webklex\IMAP\Message $message */
            $message->setFlag(['Seen']);
        }

        return ['status' => $messageStatusFlag, 'filepath' => $filePathForDownload. '/' . $fileName];
    }
}