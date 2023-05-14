<?php

namespace App\Events;

use App\Events\Event;
use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class push_notification extends Event
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($payload)
    {
        \Log::info("push_notification Event fired");
        //$this->payload = $payload;
        \Log::info("Triggering Action :".$payload);
        $users = User::whereNotNull('push_registration_id')->get();        
        $deviceRegistrationIds = $users->pluck('push_registration_id')->toArray();        
        $numberOfRetryAttempts = 5;
        //$content = $this->payload;
        $content = $payload;
        
        $sender = new \PHP_GCM\Sender(config('config-variables.services.google.api_key'));
        //"message": "This is a GCM Topic Message!",
        $message = new \PHP_GCM\Message('random-collapse-key-1', ['data' => json_encode([
            'body'=> $payload
            ]
            )]);
        /*$message = new \PHP_GCM\Message('random-collapse-key-1', ['data' => json_encode([
            'body'  => $content,
        ])]);*/
        
        // Send the message downstream
        try {
            \Log::info('trying to send to device ' . json_encode($deviceRegistrationIds));
            $response = $sender->sendMulti($message, $deviceRegistrationIds, $numberOfRetryAttempts);
            $results = $response->getResults();
            foreach ($results as $result) {
                $gcm_message_id = $result->getMessageId();
                if ($gcm_message_id) {
                    \Log::info('Sent');
                    
                }
                else {
                    \Log::info('Failed');                    
                }
            }
        } catch (\Exception $e) {
            \Log::info('message could not be sent');
            // message could not be sent
            \Log::info($e);
        }
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
