<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRegion extends Model
{
    protected $table = 'user_regions';
    public $timestamps = false;

    /**
     * Get the locations for the divisions post.
     */
    public function locations()
    {
        return $this->hasMany('App\Models\UserLocation','user_region_id','id')->orderBy('name', 'asc');
    }

    /**
     * Get the users for the regions post.
     */
    public function users()
    {
        return $this->hasMany('App\Models\User','user_region_id','id');
    }

    public static function extractRecipients($regionIds = [])
    {
        $regions = self::with('users')
            ->whereIn('id', $regionIds)
            ->get()
            ->toArray();


        $regionUsers = [];
        foreach ($regions as $region) {
            $regionUsers = array_merge($regionUsers, $region['users']);
        };

        return $regionUsers;
    }

    public function division()
    {
        return $this->belongsTo('App\Models\UserDivision', 'user_division_id');
    }
}
