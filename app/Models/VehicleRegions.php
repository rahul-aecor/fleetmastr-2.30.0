<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleRegions extends Model
{
     /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'vehicle_regions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id'
    ];
    public $timestamps = false;
    /**
     * Get vehicles for the type
     */
    public function vehicles()
    {
        return $this->hasMany('\App\Models\Vehicle');
    }
    public function division()
    {
        return $this->belongsTo('App\Models\VehicleDivisions', 'vehicle_division_id');
    }
    public function vehicleLocations()
    {
        //return $this->hasMany('\App\Models\VehicleLocations');
        return $this->hasMany('App\Models\VehicleLocations','vehicle_region_id','id');
    }

}
