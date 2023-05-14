<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TelematicsJourneyOngoing extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $payload;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($payload)
    {
        updateKPIDashboardData($payload['vehicle_id']);
        $this->payload = $payload;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [config('broadcasting.channel')];
    }
}
