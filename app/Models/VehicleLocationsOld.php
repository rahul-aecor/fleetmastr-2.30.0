<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleLocationsOld extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'vehicle_locations_old';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public $timestamps = false;

    /**
     * Get vehicles for the type
     */
    public function vehicles()
    {
        return $this->hasMany('\App\Models\Vehicle');
    }
}
