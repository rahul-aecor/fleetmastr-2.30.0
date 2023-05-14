<?php

namespace App\Models;

use App\Traits\HasMediaUploads;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

class Group extends Model implements HasMedia
{
    use HasMediaTrait, HasMediaUploads, SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "name",
        "created_by",
        "updated_by"
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Get the user that created the group.
     */
    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'created_by')->withTrashed();
    }

    /**
     * The site contacts that belong to the group.
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'group_users')->whereNull('group_users.deleted_at')->withTimestamps();
    }
    /**
     * The site contacts that belongs to the group even if removed or left.
     */
    public function allUsers()
    {
        return $this->belongsToMany('App\Models\User', 'group_users')->withTimestamps();
    }
   
    public static function extractRecipients($group_ids = [])
    {
        $groups = self::with('users')
            ->whereIn('id', $group_ids)
            ->get()
            ->toArray();


        $group_users = [];
        foreach ($groups as $group) {
            $group_users = array_merge($group_users, $group['users']);
        };

        return $group_users;
    }
}
