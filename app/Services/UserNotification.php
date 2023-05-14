<?php
namespace App\Services;

use DB;
use Auth;
use App\Models\Message;
use App\Models\TemporaryImage;
use App\Models\Template;
use App\Events\PushNotification;
use App\Contracts\UserNotificationService as UserNotificationContract;


class UserNotification implements UserNotificationContract
{
    public $request = [];
    public $push_notification_recipients = [];
    public $message_recipients = [];
    public $users = [];
    public $contacts = [];
    public $numbers = [];
    protected $message;


    public function __construct()
    {

    }

    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    public function toUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    public function toContacts($contacts)
    {
        $this->contacts = $contacts;
        foreach ($this->contacts as $contact) {
            array_push($this->message_recipients, [
                'name' => $contact['name'],
                'mobile' => $contact['mobile']
            ]);
        }

        return $this;
    }

    public function toNumbers($numbers)
    {
        $this->numbers = $numbers;
        foreach ($this->numbers as $ad_hoc_number) {
            array_push($this->message_recipients, [
                'name' => 'Ad Hoc',
                'mobile' => $ad_hoc_number
            ]);
        }

        return $this;
    }

    public function send()
    {
        $this->buildRecipientList();
        $this->removeDuplicateRecipients();
        $this->saveMessage();
        // $this->sendTextMessage();
        $this->sendPushNotifications();

        return Message::with('sender')->find($this->message->id);
    }

    protected function buildRecipientList()
    {
        $this->processUsersData();
        $this->processContactsData();
        $this->processNumbersData();
    }

    protected function removeDuplicateRecipients()
    {
        $this->message_recipients = collect($this->message_recipients)
            ->unique('mobile');

        // $this->push_notification_recipients = collect($this->push_notification_recipients)
        //     ->unique('push_registration_id');
        $this->push_notification_recipients = collect($this->push_notification_recipients)
            ->unique('username');
    }

    protected function processUsersData()
    {
        foreach ($this->users as $user) {
            // if (isset($user['push_registration_id'])) {
                array_push($this->push_notification_recipients, [
                    'name' => $user['first_name'] . ' ' . $user['last_name'],
                    'username' => $user['username'],
                    'push_registration_id' => $user['push_registration_id'],
                    'user_id' => $user['id'],
                ]);
            // }
            // else {
            //     array_push($this->message_recipients, [
            //         'name' => $user['first_name'] . ' ' . $user['last_name'],
            //         'mobile' => $user['mobile']
            //     ]);
            // }
        }
    }

    protected function processContactsData()
    {
        foreach ($this->contacts as $contact) {
            array_push($this->message_recipients, [
                'name' => $contact['name'],
                'mobile' => $contact['mobile']
            ]);
        }
    }

    protected function processNumbersData()
    {
        foreach ($this->numbers as $number) {
            array_push($this->message_recipients, [
                'name' => 'Ad Hoc',
                'mobile' => $number
            ]);
        }
    }

    protected function saveMessage()
    {
        // save message to message table
        $message = new Message();

        $message->content = $this->request->content;
        $message->title = $this->request->title;
        $message->type = isset($this->request->template['type']) ?  $this->request->template['type'] : 'standard';
        $message->priority = isset($this->request->template['priority']) ?  $this->request->template['priority'] : NULL;
        $message->template_name = isset($this->request->template['name']) ?  $this->request->template['name'] : NULL;
        $message->template_id = isset($this->request->template['id']) ?  $this->request->template['id'] : NULL;
        $message->questions = $this->request->template['type'] === 'multiple_choice' ? $this->request->template['questions'] : NULL;
        $message->surveys = $this->request->template['type'] === 'survey' ? $this->request->template['surveys'] : NULL;
        $message->standard_message_media = $this->request->template['type'] === 'standard' ? $this->request->template['standard_message'] : NULL;
        $message->sent_by = Auth::id();
        $message->sent_at = date('Y-m-d H:i:s');
        // $message->credits_used = $this->message_recipients->count();
        $message->credits_used = 0;

        $message->acknowledgement_message = ($message->type == '' || $message->type == 'standard') ? $this->request->template['acknowledgement_message'] : NULL;
        $message->is_acknowledgement_required = ($message->type == '' || $message->type == 'standard') ? $this->request->template['is_acknowledgement_required'] : 0;
        $message->is_private_message = !empty($this->request->private_message) ? $this->request->private_message : 0;

        $this->message = $message;
        $message->save();

        // Move attachment from temporary images to media
        if(isset($this->request->attachments) && $this->request->attachments != '') {
            $tempIds = explode(",", $this->request->attachments);
            $newDocIds = [];
            if(isset($this->request->template['id']) && $this->request->template['id'] != null && $this->request->template['id'] != '') {

                $template = Template::find($this->request->template['id']);
                if(isset($template)) {
                    $media = $template->getMedia();
                    foreach($media as $doc) {
                        if(in_array($doc->name, $tempIds)) {
                            $message->addMediaFromUrl($doc->getUrl())
                            ->withCustomProperties($doc->custom_properties)
                            ->toCollectionOnDisk('message', 'S3_uploads');
                            $newDocIds[] = $doc->name;
                        }
                    }
                }
                $tempIds = array_diff($tempIds, $newDocIds);

            }
            $tempImages = TemporaryImage::whereIn('temp_id', $tempIds)->get();
            if ($tempImages) {
                foreach($tempImages as $tempImage) {
                    $media = $tempImage->getMedia();
                    $message->addMediaFromUrl($media[0]->getUrl())
                    ->withCustomProperties($media[0]->custom_properties)
                    ->toCollectionOnDisk('message', 'S3_uploads');
                    $tempImage->delete();
                }
            }
        }
    }

    // protected function sendTextMessage()
    // {
    //     \Log::info('full list of sms sent to');
    //     \Log::info($this->message_recipients);

    //     // Do not attempt to send SMS if the message type is not standard.
    //     if (! count($this->message_recipients) || $this->message->type !== 'standard') {
    //         // Update message count to 0 and return
    //         \Log::info('updating credits used to 0 because message is not standard');
    //         $this->message->credits_used = 0;
    //         $this->message->save();
    //         return;
    //     }

    //     $currentDate = date('Y-m-d H:i:s');
    //     $client = new \Services_Twilio(config('twilio.twilio.connections.twilio.sid'), config('twilio.twilio.connections.twilio.token'));        

    //     $total_segments = 0;
    //     $message_recipient_records = [];
    //     foreach ($this->message_recipients as $key => $recipient) {

    //         $message_recipient_records[$key]['message_id']  = $this->message->id;
    //         $message_recipient_records[$key]['mobile']  = $recipient['mobile'];
    //         $message_recipient_records[$key]['sent_via'] = 'sms';
    //         $message_recipient_records[$key]['created_at'] = $currentDate;
    //         $message_recipient_records[$key]['updated_at'] = $currentDate;

    //         try {
    //             $to_mobile = '+44' . substr($recipient['mobile'], 1);
    //             \Log::info('to mobile: ' . $to_mobile);
    //             // TODO: check for non-empty mobile numbers and if valid
    //             $response = $client->account->messages->create([
    //                 'To' => '+447966801049', // $recipient['mobile'] // sameer +918866886205 // mohin +919099349053 // richard +447966801049 chris +447989470698
    //                 'From' => config('twilio.twilio.connections.twilio.from'),
    //                 'Body' => $this->request->content, // $request->content
    //                 'StatusCallback' => config('twilio.twilio.connections.twilio.sms_callback'),
    //             ]);
    //             \Log::info('Trying SMS to ' . $recipient['mobile']);
    //             \Log::info($response);
    //             $message_recipient_records[$key]['sid']  = $response->sid;
    //             $message_recipient_records[$key]['status']  = $response->status;
    //             $total_segments += $response->num_segments;
    //             $message_recipient_records[$key]['error_json']  = $response;
    //         }
    //         catch(\Exception $e) {
    //             $message_recipient_records[$key]['status'] = 'error';
    //             $error['code'] = $e->getCode();
    //             $error['message'] = $e->getMessage();
    //             $message_recipient_records[$key]['error_json']  = json_encode($error);
    //             \Log::info('Failure SMS to ' . $recipient['mobile']);
    //             \Log::info('Code: ' . $e->getCode() . ' Message: ' . $e->getMessage());
    //         }
    //     }
    //     \Log::info('updating credits used to ' . $total_segments);
    //     $this->message->credits_used = $total_segments;
    //     $this->message->save();
    //     \Log::info('final message recipeitns');
    //     // \Log::info($message_recipient_records);

    //      DB::table('message_recipients')->insert($message_recipient_records);
    // }

    protected function sendPushNotifications()
    {
        if (! count($this->push_notification_recipients)) {
            \Log::info('no push recipients');
            return;
        }

        $push_receivers_id = [];
        $push_recipient_records = [];
        $currentDate = date('Y-m-d H:i:s');

        foreach ($this->push_notification_recipients as $key => $recipient) {
            \Log::info('test log');
            \Log::info($recipient['name']);            
            $push_recipient_records[$key]['message_id']  = $this->message->id;
            $push_recipient_records[$key]['name'] = ($recipient['name']) ? $recipient['name'] : '';
            $push_recipient_records[$key]['user_id'] = $recipient['user_id'];
            $push_recipient_records[$key]['created_at'] = $currentDate;
            $push_recipient_records[$key]['updated_at'] = $currentDate;
            $push_recipient_records[$key]['sent_via'] = 'message';
            $push_recipient_records[$key]['status'] = 'delivered';
            if(isset($recipient['push_registration_id'])){
                array_push($push_receivers_id, $recipient['push_registration_id']);
            }
        }

        $icon_url = asset('img/android/icons/1.jpg');

/// To be removed after release on live

        // $numberOfRetryAttempts = 5;

        // $sender = new \PHP_GCM\Sender(config('services.google.api_key'));
        // $gcm_message = new \PHP_GCM\Message('random-collapse-key-' . $this->message->id, [
        //     'data' => json_encode([
        //         'body'  => $this->request->content,
        //         'icon'  => $icon_url,
        //         'message_id' => $this->message->id
        //     ]),
        //     'notification' => json_encode([
        //         'title' => "",
        //         'body'  => $this->request->content,
        //         'icon'  => $icon_url,
        //         'message_id' => $this->message->id
        //     ]), 
        //     'priority' => 'high',
        //     "delay_while_idle" => 0,
        //     "content_available" => true
        // ]);

        // try {
        //     \Log::info('trying to send to device ' . json_encode($push_receivers_id));
        //     \Log::info(print_r($gcm_message,true));
        //     $sender->sendMulti($gcm_message, $push_receivers_id, $numberOfRetryAttempts);
        // } catch (\Exception $e) {
        //     \Log::info('push notification could not be sent');
        //     \Log::info($e);
        // }

/// To be removed after release on live
	$messageType = isset($this->request->template['type']) ?  $this->request->template['type'] : 'standard';
    $payloadBody = 'You have received a message from fleetmastr.';
	// $payloadBody = $this->request->content;
	// if(strtolower($messageType) == "standard"){
	// 	$payloadBody = 'You have received a message from fleetmastr.';
	// }
        
        $payload = array (
            'title' => config('branding.' . env('BRAND_NAME') . '.title'),
            //'body'  => $this->request->content,
            'body'  => $payloadBody,
            'icon'  => $icon_url,
            'message_id' => $this->message->id
        );

        try{
            event(new PushNotification($payload, $payload['title'], $push_receivers_id));
        } catch (\Exception $e) {
            \Log::info('push notification could not be sent');
            \Log::info($e);
        }

        DB::table('message_recipients')->insert($push_recipient_records);
    }
}
