<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\MaintenanceEvents;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class TelematicsProcessZoneData extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $value;
    protected $telematicService;

    public function __construct($value, $telematicService)
    {
        $this->value = $value;
        $this->telematicService = $telematicService;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->telematicService->processZoneData($this->value);
    }
}
