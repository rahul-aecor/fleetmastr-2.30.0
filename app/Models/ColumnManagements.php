<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColumnManagements extends Model
{
    protected $table = 'column_management';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "user_id",
        "section",
        "data"
        ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'json',
    ];
}
