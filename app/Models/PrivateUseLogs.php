<?php

namespace App\Models;

use Carbon\Carbon;
use Pingpong\Presenters\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrivateUseLogs extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'private_use_logs';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['start_date', 'end_date'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'vehicle_id',
        'tax_year',
        'start_date',
        'end_date',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle', 'vehicle_id');
    }
    public function vehicle_trashed()
    {
        return $this->belongsTo('App\Models\Vehicle', 'vehicle_id')->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    /**
     * Get the start_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setStartDateAttribute($value)
    {
        if ($value) {
            $this->attributes['start_date'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['start_date'] = null;   
        }
    }

    /**
     * Get the dt_repair_expiry timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getStartDateAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');    
        }
        else {
            return $value;
        }        
    }

    /**
     * Get the end_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setEndDateAttribute($value)
    {
        if ($value) {
            $this->attributes['end_date'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['end_date'] = null;   
        }
    }

    /**
     * Get the dt_repair_expiry timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getEndDateAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');    
        }
        else {
            return $value;
        }        
    }

}
