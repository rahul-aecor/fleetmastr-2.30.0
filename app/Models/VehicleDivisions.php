<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleDivisions extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'vehicle_divisions';

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
    public function vehicleRegions()
    {
        //return $this->hasMany('\App\Models\VehicleRegions');
         return $this->hasMany('App\Models\VehicleRegions','vehicle_division_id','id');
    }

}
