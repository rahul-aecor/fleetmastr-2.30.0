<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppAccessLog extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'app_access_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "email",
        "app_version",
        "is_valid"
    ];
}
