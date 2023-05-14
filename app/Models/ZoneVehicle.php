<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon as Carbon;


class ZoneVehicle extends Model 
{
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    //use SoftDeletes;
    public $timestamps = false;
    protected $table = 'zone_vehicle';
    protected $fillable = [];

    public function zone()
    {
        return $this->belongsTo('App\Models\Zone', 'zone_id');
    }


}
