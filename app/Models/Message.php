<?php

namespace App\Models;

use Carbon\Carbon;
use App\Traits\HasMediaUploads;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

class Message extends Model implements HasMedia
{
    use HasMediaTrait, HasMediaUploads;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sent_by',
        'sent_at',
        'type',
        'content',
        'surveys',
        'questions',
        'priority',
        'credits_used',
        'is_private_message'
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
        'surveys' => 'array',
        'questions' => 'array',
    ];    

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['sent_at', 'created_at', 'updated_at'];

    /**
     * The recipients that belong to the message.
     */
    public function receiver()
    {
        // return $this->belongsto('App\Models\User', 'sent_to_user')->withTrashed();
         return $this->hasMany('App\Models\MessageRecipient')->whereIn('user_id', Self::getAuthUserMessagePermissionUsers());
    }

    /**
     * The sender that sent to the message.
     */
    public function sender()
    {
        // return $this->belongsto('App\Models\User', 'sent_from')->withTrashed();
        return $this->belongsto('App\Models\User', 'sent_by')->withTrashed();
    }

    /**
     * The group that sent to the message.
     */
    public function group()
    {
        return $this->belongsto('App\Models\Group', 'sent_to_group')->withTrashed();
    }

    public function getReadRecieverCount() 
    {
        return $this->receiver->where('status', 'read')->count();
    }

    public function getUnReadRecieverCount() 
    {
        return $this->receiver()->whereIn('status', ['sent', 'delivered'])->count();
    }

    public function getContentAttribute($value)
    {
        return html_entity_decode($value);
    }

    /**
     * Get the sent_at timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getSentAtAttribute($value)
    {
        if ($value) {            
            return Carbon::parse($value)->setTimezone(config('config-variables.format.displayTimezone'))->format(config('config-variables.format.showDateTime'));
        }
        else {
            return $value;
        }        
    }

    public static function getAuthUserMessagePermissionUsers()
    {
        if(\Auth::check()) {
            $authUserMessageRegions = \Auth::user()->messageRegions->pluck('id');
            $users = User::whereIn('user_region_id', $authUserMessageRegions)->get()->pluck('id');
        } else {
            $users = User::all()->pluck('id');
        }
        return $users;
    }
}
