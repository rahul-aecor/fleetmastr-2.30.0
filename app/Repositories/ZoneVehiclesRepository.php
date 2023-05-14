<?php
namespace App\Repositories;

use Auth;
use Carbon\Carbon;
use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;
use App\Models\ZoneAlerts;
use App\Models\ZoneVehicle;

use Illuminate\Support\Facades\Redis;

class ZoneVehiclesRepository extends EloquentRepositoryAbstract {

    public function __construct()
    {
        
    }

    public function createZoneVehicle($data) {
        Redis::set(env('REDIS_PREFIX').'-'.$data['vrn'].'-'.$data['zone_id'], '1');
    }

    public function delZoneVehicle($data) {
        Redis::del(env('REDIS_PREFIX').'-'.$data['vrn'].'-'.$data['zone_id']);
    }

    public function isZoneVehicleEntryExisting($data) {
        $val = Redis::get(env('REDIS_PREFIX').'-'.$data['vrn'].'-'.$data['zone_id']);
        if ($val) {
            return true;
        }
        return false;        
    }
}
