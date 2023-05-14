<?php

namespace App\Models;

use Carbon\Carbon as Carbon;
use Pingpong\Presenters\Model;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleMaintenanceHistory extends Model implements HasMedia
{
	use HasMediaTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'vehicle_maintenance_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vehicle_id',
        'event_type_id',
        'event_plan_date',
        'event_type',
        'event_date',
        'mot_type',
        'mot_outcome',
        'comment',
        'event_status',
        'is_safety_inspection_in_accordance_with_dvsa',
        'event_planned_distance',
        'odomerter_reading'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['event_date'];
    
    /**
     * Get the vehicle type.
     */
    public function vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle', 'vehicle_id')->withTrashed();
    }
    /**
     * Get the event_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setEventDateAttribute($value)
    {
        if ($value) {
            $this->attributes['event_date'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['event_date'] = null;   
        }
    }
    /**
     * Get the event_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getEventDateAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');    
        }
        else {
            return $value;
        }        
    }

    /**
     * Get the event_plan_date timestamp.
     *
     * @param  string  $value
     * @return string
    */
    public function setEventPlanDateAttribute($value)
    {
        if ($value) {
            $this->attributes['event_plan_date'] = Carbon::parse($value)->toDateString();
        }
        else {
            $this->attributes['event_plan_date'] = null;   
        }
    }

    /**
     * Get the next_compressor_service timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getEventPlanDateAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');    
        }
        else {
            return $value;
        }        
    }

    public function eventType() {
        return $this->hasOne(MaintenanceEvents::class,'id','event_type_id')->withTrashed();
    }

    // /**
    //  * The model's presenter class
    //  *         
    //  * @var string
    //  */
    // protected $presenter = \App\Presenters\DefectHistoryPresenter::class;

    // /**
    //  * Get the user who created the check.
    //  */
    // public function creator()
    // {
    //     return $this->belongsTo('App\Models\User', 'created_by');
    // }

    // /**
    //  * Get the user who last updated the check.
    //  */
    // public function updater()
    // {
    //     return $this->belongsTo('App\Models\User', 'updated_by');
    // }

    // public function defect()
    // {
    //     return $this->belongsTo('App\Models\Defect', 'defect_id');
    // }
}
