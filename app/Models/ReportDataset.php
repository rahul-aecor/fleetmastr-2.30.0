<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportDataset extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'report_dataset';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "field_name",
        "title",
        "description",
        "model_type",
        "is_active",
        "created_at"
    ];

    public $timestamps = false;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function reportColumns()
    {
        return $this->hasMany('App\Models\ReportColumn', 'report_dataset_id');
    }
}
