<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerts extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'alerts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "name",
        "description",
        "severity",
        "type",
        "source",
        "is_active",
        "vehicle_id",
        "user_id",
    ];
}
