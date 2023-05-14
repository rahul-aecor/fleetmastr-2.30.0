<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MessageRecipient extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'message_recipients';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message_id',
        'sent_to_user',
        'mobile',
        'status',
        'response',
        'response_received_at'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'error_json' => 'array',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['response_received_at', 'created_at', 'updated_at'];

    /**
     * The recipients that belong to the message.
     */
    public function message()
    {
        return $this->belongsTo('App\Models\Message');
    }

    public function receiver()
    {
        // return $this->belongsto('App\Models\User', 'sent_from')->withTrashed();
        return $this->belongsto('App\Models\User', 'sent_to_user')->withTrashed();
    }

    public function group()
    {
        // return $this->belongsto('App\Models\User', 'sent_from')->withTrashed();
        return $this->belongsto('App\Models\Group', 'sent_to_group')->withTrashed();
    }

    public function user()
    {
        return $this->belongsto('App\Models\User', 'user_id')->withTrashed();
    }

    /**
     * Get the started_at timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setResponseReceivedAt($value)
    {
        if ($value) {
            $date = Carbon::createFromFormat(config('config-variables.format.showDateTime'), $value, config('config-variables.format.displayTimezone'));
            $this->attributes['response_received_at'] = $date->setTimezone('UTC')->toDateTimeString(); 
        }
        else {
            $this->attributes['response_received_at'] = null;   
        }
    }

    /**
     * Get the started_at timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getResponseReceivedAt($value)
    {
        if ($value) {            
            return Carbon::parse($value)->setTimezone(config('config-variables.format.displayTimezone'))->format(config('config-variables.format.showDateTime'));
        }
        else {
            return $value;
        }        
    }
}
