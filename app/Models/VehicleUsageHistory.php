<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleUsageHistory extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'vehicle_usage_history';

    public function vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle', 'vehicle_id');
    }
    public function vehicle_history()
    {
        return $this->belongsTo('App\Models\Vehicle', 'vehicle_id')->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
