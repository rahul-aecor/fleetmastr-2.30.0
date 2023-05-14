<?php

namespace App\Models;

use Pingpong\Presenters\Model;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class DefectHistory extends Model implements HasMedia
{
	use HasMediaTrait, SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'defect_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'defect_id',
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
    protected $presenter = \App\Presenters\DefectHistoryPresenter::class;

    /**
     * Get the user who created the check.
     */
    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'created_by')->withTrashed()->orWhere('is_disabled', 1);
    }

    /**
     * Get the user who last updated the check.
     */
    public function updater()
    {
        return $this->belongsTo('App\Models\User', 'updated_by')->withTrashed()->orWhere('is_disabled', 1);
    }

    public function defect()
    {
        return $this->belongsTo('App\Models\Defect', 'defect_id');
    }
}
