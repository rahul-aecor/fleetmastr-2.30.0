<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertNotifications extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
    */
    protected $table = 'alert_notifications';

    public $timestamps = false;

    public function vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle', 'vehicle_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id')->withTrashed();
    }

    public function alerts()
    {
        return $this->belongsTo('App\Models\Alerts', 'alerts_id');
    }

}
