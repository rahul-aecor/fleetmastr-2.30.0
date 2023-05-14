<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportDownload extends Model implements HasMedia
{
    use HasMediaTrait, SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'report_downloads';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "report_id",
        "date_from",
        "date_to",
        "filename",
        "created_at"
    ];

    public $timestamps = false;

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
    protected $dates = ['deleted_at'];

    /**
     * The regions that belongs to the report downloads.
     */
    public function regions()
    {
        return $this->belongsToMany('App\Models\VehicleRegions', 'report_download_vehicle_regions', 'report_download_id', 'vehicle_region_id');
    }

    /**
     * The dataset that belongs to the report downloads.
     */
    public function reportDataset()
    {
        return $this->belongsToMany('App\Models\ReportDataset', 'report_download_report_dataset');
    }

    /**
     * The dataset that belongs to the report downloads.
     */
    public function report()
    {
        return $this->belongsTo('App\Models\Report', 'report_id')->withTrashed();
    }
}
