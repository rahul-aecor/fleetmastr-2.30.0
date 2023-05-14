<?php

namespace App\Models;

use Carbon\Carbon as Carbon;
use Pingpong\Presenters\Model;  
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class Defect extends Model implements HasMedia
{
	use HasMediaTrait, SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'defects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vehicle_id',
        'title',
        'check_id',
        'defect_master_id',
        'description',
        'comments',
        'status',
        'rejectreason',
        'workshop',
        'est_completion_date',
        'invoice_date'
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
    protected $presenter = \App\Presenters\DefectPresenter::class;
    /**
     * Get the user who last updated the defect.
     */
    public function defectMaster()
    {
        return $this->belongsTo('App\Models\DefectMaster', 'defect_master_id');
    }
    /**
     * Get the vehicle that belongs to the check.
     */
    public function vehicle()
    {
        //return $this->belongsTo('App\Models\Vehicle', 'vehicle_id')->select(['id'])->withTrashed();
        return $this->belongsTo('App\Models\Vehicle', 'vehicle_id')->withTrashed();
    }
    /**
     * Get the user who created the defect.
     */
    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'created_by')->withTrashed()->orWhere('is_disabled', 1);
    }

    /**
     * Get the user who created the defect.
     */
    public function check()
    {
        return $this->belongsTo('App\Models\Check', 'check_id');
    }

    /**
     * Get the user who last updated the defect.
     */
    public function updater()
    {
        return $this->belongsTo('App\Models\User', 'updated_by')->withTrashed()->orWhere('is_disabled', 1);
    }

    /**
     * Get the user who last updated the defect.
     */
    public function workshop()
    {
        return $this->belongsTo('App\Models\Company', 'workshop');
    }

    /**
     * Get the est_completion_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getEstCompletionDateAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');    
        }
        else {
            return $value;
        }        
    }

    /**
     * Get the est_completion_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getInvoiceDateAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');    
        }
        else {
            return $value;
        }        
    }

    /**
     * Get the Defect history.
     */
    public function history()
    {
        return $this->hasMany('\App\Models\DefectHistory','defect_id')->orderBy('id', 'desc');
    }

    /**
     * Get the resolved_datetime timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getResolvedDatetimeAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('H:m:s d M Y');
        }
        else {
            return $value;
        }
    }
    public function getResolvedDatetimeOriginalAttribute($value)
    {
            return $this->getOriginal('resolved_datetime');
    }
}
