<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon as Carbon;


class ZoneAlertSession extends Model 
{
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    //use SoftDeletes;
    protected $table = 'zone_alerts_sessions';
    public $timestamps = true;

    public function vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle', 'vehicle_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function zone()
    {
        return $this->belongsTo('App\Models\Zone', 'zone_id');
    }

    /**
     * Get the location of the vehicle.
     */
    public function zone_alerts()
    {
        return $this->hasMany('App\Models\ZoneAlerts');
    }


}
