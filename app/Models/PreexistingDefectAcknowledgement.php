<?php

namespace App\Models;

use Pingpong\Presenters\Model;

class PreexistingDefectAcknowledgement extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'preexisting_defect_acknowledgement';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'defect_id',
        'check_id',
        'status'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public $timestamps = false;
    /**
     * Get the defect that belongs to the entry.
     */
    public function defect()
    {
        return $this->belongsTo('App\Models\Defect', 'defect_id')->withTrashed();
    }
    /**
     * Get the check that belongs to the entry.
     */
    public function check()
    {
        return $this->belongsTo('App\Models\Check', 'check_id')->withTrashed();
    }

}
