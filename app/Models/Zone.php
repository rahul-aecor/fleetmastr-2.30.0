<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon as Carbon;


class Zone extends Model 
{
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    use SoftDeletes;
    protected $table = 'zones';
    public $timestamps = true;

    /**
     * Get the location of the vehicle.
     */
    public function region()
    {
        return $this->belongsTo('App\Models\VehicleRegions', 'region_id');
    }

    /**
     * Get the location of the vehicle.
     */
    public function zone_alert_sessions()
    {
        return $this->hasMany('App\Models\ZoneAlertSession');
    }
}
