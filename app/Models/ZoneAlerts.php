<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon as Carbon;


class ZoneAlerts extends Model 
{
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    //use SoftDeletes;
    protected $table = 'zone_alerts';
    protected $fillable = [];

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


}
