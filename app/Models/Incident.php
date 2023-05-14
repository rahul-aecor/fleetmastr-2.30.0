<?php

namespace App\Models;

use Pingpong\Presenters\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

class Incident extends Model implements HasMedia
{
	use HasMediaTrait, SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'incidents';

    /**
     * The model's presenter class
     *         
     * @var string
     */
    protected $presenter = \App\Presenters\IncidentPresenter::class;

    /**
     * Get the user who created the defect.
     */
    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    /**
     * Get the user who last updated the defect.
     */
    public function updater()
    {
        return $this->belongsTo('App\Models\User', 'updated_by');
    }
        
    /**
     * Get the vehicle that belongs to the check.
     */
    public function vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle', 'vehicle_id')->withTrashed();
    }

    /**
     * Get the Defect history.
     */
    public function history()
    {
        return $this->hasMany('\App\Models\IncidentHistory','incident_id')->orderBy('id', 'desc');
    }    
}
