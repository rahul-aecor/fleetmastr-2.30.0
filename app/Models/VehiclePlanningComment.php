<?php

namespace App\Models;

use Carbon\Carbon as Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehiclePlanningComment extends Model implements HasMedia
{
	use HasMediaTrait, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'comment_datetime',
    ];

   	/**
     * The model's presenter class
     *
     * @var string
     */
    protected $presenter = \App\Presenters\VehiclePlanningCommentPresenter::class;

    /**
     * Get the user who created the check.
     */
    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    /**
     * Get the user who last updated the check.
     */
    public function updater()
    {
        return $this->belongsTo('App\Models\User', 'updated_by');
    }

    public function vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle', 'vehicle_id');
    }
}
