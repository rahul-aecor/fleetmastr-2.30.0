<?php

namespace App\Models;

use Carbon\Carbon as Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;


class UserLogoutState extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_logout_state';
    // public $timestamps = true;

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id')->withTrashed();
    }

    /**
     * Get the vehicle.
     */
    public function vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle', 'vehicle_id')->withTrashed();
    }

}
