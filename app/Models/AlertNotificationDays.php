<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertNotificationDays extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
    */
    protected $table = 'alert_notification_days';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "alerts_id",
        "day",
        "is_all_day",
        "is_on",
    ];
}
