<?php

namespace App\Models;

use Pingpong\Presenters\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

class Check extends Model implements HasMedia
{
    use HasMediaTrait, SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'checks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vehicle_id',
        'type',
        'user_id',
        'status',
        'json',
        'defect_report_type',
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
    protected $dates = [
        'report_datetime',
    ];

    /**
     * The model's presenter class
     *         
     * @var string
     */
    protected $presenter = \App\Presenters\CheckPresenter::class;

    /**
     * Get the vehicle that belongs to the check.
     */
    public function vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle', 'vehicle_id')->withTrashed();
    }

    /**
     * Get the user who created the check.
     */
    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    /**
     * Get the user who last updated the check.
     */
    public function updater()
    {
        return $this->belongsTo('App\Models\User', 'updated_by');
    }

    /**
     * Scope a query to only checks to be displayed on the desktop (Safety check and Return check) 
     * and exclude defect checks.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutDefectChecks($query)
    {
        return $query->where('type', '<>', 'Report defect');
    }
}
