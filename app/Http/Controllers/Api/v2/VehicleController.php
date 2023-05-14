<?php

namespace App\Http\Controllers\Api\v2;

use App\Models\MaintenanceEvents;
use Illuminate\Support\Facades\DB;
use Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Check;
use App\Models\Defect;
use App\Services\VehicleService;
use App\Transformers\CheckTransformer;
use App\Transformers\DefectTransformer;
use App\Transformers\VehicleTransformer;
use App\Transformers\AllVehicleTransformer;
use App\Http\Controllers\Api\v1\APIController;

class VehicleController extends APIController
{
    /**
     * Vehicle service instance.
     *
     * @var object
     */
    protected $vehicleService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    public function allVehicles(Request $request)
    {
        $last_updated_timestamp = null;
        if (isset($request['last_updated_timestamp']) && $request['last_updated_timestamp'] != null && !empty($request['last_updated_timestamp'])) {
            $last_updated_timestamp = Carbon::createFromFormat('H:i:s j M Y', $request['last_updated_timestamp'], 'UTC');
        }

        return $this->vehicleService->allVehicles($last_updated_timestamp);
    }
}
