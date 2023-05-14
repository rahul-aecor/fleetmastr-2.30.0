<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'reports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "name",
        "description",
        "report_category_id",
        "last_downloaded_at",
        "created_by",
        "updated_by",
        "is_custom_report",
        "period"
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
    protected $dates = ['deleted_at'];

    public function reportColumns()
    {
        return $this->hasMany('App\Models\ReportColumn', 'report_id');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\ReportCategory', 'report_category_id');
    }

    public static function checkTelematicsIsEnableAndReportAvailable($slug) {
        $telematicsReports = config('config-variables.telematics_reports');
        if(setting('is_telematics_enabled') == 1) {
            return true;
        }
        return !in_array($slug, $telematicsReports);
    }
}
