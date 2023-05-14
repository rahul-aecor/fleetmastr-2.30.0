<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertNotificationDaySlots extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
    */
    protected $table = 'alert_notification_day_slots';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "alert_notification_days_id",
        "from_time",
        "to_time",
        "is_on",
    ];
}
