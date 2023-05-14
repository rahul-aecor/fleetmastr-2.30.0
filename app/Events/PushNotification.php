<?php

namespace App\Events;

use App\Events\Event;
use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class PushNotification extends Event
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($payload, $msgtitle="", $deviceIds=[])
    {
        \Log::info("push_notification Event fired");
        //$this->payload = $payload;
        //$content = $this->payload;
        //$content = $payload;
        /*$message = new \PHP_GCM\Message('random-collapse-key-1', ['data' => json_encode([
            'body'  => $content,
        ])]);*/
        $deviceRegistrationIds = [];
        \Log::info("Triggering Action :". json_encode($payload));
        if(!empty($deviceIds)){
            $deviceRegistrationIds = $deviceIds;
        }
        else{
            $users = User::whereNotNull('push_registration_id')->get();
            $deviceRegistrationIds = $users->pluck('push_registration_id')->toArray();        
        }
        // $numberOfRetryAttempts = 5;        
        
        // $sender = new \PHP_GCM\Sender(config('services.google.api_key'));
        // Send the message downstream
        try {
            \Log::info('trying to send to device ' . json_encode($deviceRegistrationIds));
            // $response = $sender->sendMulti($message, $deviceRegistrationIds, $numberOfRetryAttempts);
            $title = "";
            if(isset($msgtitle) && $msgtitle!=""){
                $title = $msgtitle; 
            }
            if (is_array($payload)){
                $message = $payload['body'];
                $response = $this->sendNotification($deviceRegistrationIds, $title, $message, $payload);
            }
            else{
                $message = $payload;
                $response = $this->sendNotification($deviceRegistrationIds, $title, $message, []);
            }
            // $response = $this->sendNotification($deviceRegistrationIds, $title, $message, $payload);

            \Log::info('response' . json_encode($response));
            \Log::info("FCM Response");
            \Log::info("numberSuccess ".$response->numberSuccess());
            \Log::info("numberFailure ".$response->numberFailure());
            \Log::info("numberModification ".$response->numberModification());
            \Log::info("====");
            return $response;            
        } catch (\Exception $e) {
            \Log::info('message could not be sent');
            // message could not be sent
            \Log::info($e);
        }
    }

    public function sendNotification($tokens=[], $title="Title Data", $bodyText="Body Text", $data=[])
    {
        \Log::info("sendNotification");
        $optionBuiler = new OptionsBuilder();
        $optionBuiler->setTimeToLive(60*20);
        $optionBuiler->setPriority('high');
        $optionBuiler->setContentAvailable(true);        

        if(empty($data)){
            $dataBuilder = new PayloadDataBuilder();
            $data = array(
                'action' => $bodyText
            );
            $dataBuilder->addData($data);

            $option = $optionBuiler->build();
            $data = $dataBuilder->build();

            $downstreamResponse = FCM::sendTo($tokens, $option, null, $data);
        }else{
            $notificationBuilder = new PayloadNotificationBuilder($title);
            $notificationBuilder->setBody($bodyText)
                                ->setSound('default');
            
            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData($data);

            $option = $optionBuiler->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();

            $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);
        }

        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();

        $downstreamResponse->tokensToDelete(); 
        $downstreamResponse->tokensToModify(); 
        $downstreamResponse->tokensToRetry();

        return $downstreamResponse;

        /*return $this->response->array([
                    'data' => $downstreamResponse,
                    'message' => "success",
                    "status_code" => 200
                ]);*/
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
