<?php

namespace App\Models;

use Pingpong\Presenters\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

class IncidentHistory extends Model implements HasMedia
{
	use HasMediaTrait, SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'incident_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'incident_id',
        'comments',
        'user_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The model's presenter class
     *         
     * @var string
     */
    protected $presenter = \App\Presenters\IncidentHistoryPresenter::class;

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

    public function incident()
    {
        return $this->belongsTo('App\Models\Incident', 'incident_id');
    }
}
