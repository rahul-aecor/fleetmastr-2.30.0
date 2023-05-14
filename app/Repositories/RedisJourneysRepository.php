<?php
namespace App\Repositories;

use Auth;
use Carbon\Carbon;
use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;
use App\Models\TelematicsJourneys;

use Illuminate\Support\Facades\Redis;

class RedisJourneysRepository extends EloquentRepositoryAbstract {

    public function __construct()
    {
        
    }

    public function createJourney($data) {
        Redis::set(env('REDIS_PREFIX').'_'.$data['vehicle_id'].'_'.$data['uid'].'_'.$data['journey_id'], json_encode($data));
    }

    public function delJourney($key) {
        Redis::del(env('REDIS_PREFIX').'_'.$key);
    }

    public function isJourneyEntryExisting($key) {
        $val = Redis::get(env('REDIS_PREFIX').'_'.$key);
        if ($val) {
            return true;
        }
        return false;        
    }

    public function getJourneyEntry($key) {
        $val = Redis::get(env('REDIS_PREFIX').'_'.$key);
        if ($val) {
            return json_decode($val,true);
        }
        return null;        
    }
}
