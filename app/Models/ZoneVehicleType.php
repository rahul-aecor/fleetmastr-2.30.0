<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon as Carbon;


class ZoneVehicleType extends Model 
{
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    //use SoftDeletes;
    public $timestamps = false;
    protected $table = 'zone_vehicle_type';
    protected $fillable = [];

    public function type()
    {
        return $this->belongsTo('App\Models\VehicleType', 'vehicle_type_id')->withTrashed();
    }

    public function zone()
    {
        return $this->belongsTo('App\Models\Zone', 'zone_id');
    }


}
