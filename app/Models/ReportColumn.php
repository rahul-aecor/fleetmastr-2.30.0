<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportColumn extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'report_columns';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "report_id",
        "report_dataset_id"
    ];

    public $timestamps = false;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}
