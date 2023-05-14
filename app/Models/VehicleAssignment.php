<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class VehicleAssignment extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'vehicle_assignment';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id'
    ];

    /**
     * Get the from_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setFromDateAttribute($value)
    {
        if ($value) {
            $this->attributes['from_date'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['from_date'] = null;   
        }
    }

    /**
     * Get the from_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getFromDateAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');    
        }
        else {
            return $value;
        }        
    }

    /**
     * Get the to_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setToDateAttribute($value)
    {
        if ($value) {
            $this->attributes['to_date'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['to_date'] = null;   
        }
    }

    /**
     * Get the to_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getToDateAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');    
        }
        else {
            return $value;
        }        
    }
}
