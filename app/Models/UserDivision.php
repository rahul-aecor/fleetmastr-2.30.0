<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDivision extends Model
{
    protected $table = 'user_divisions';
    public $timestamps = false;

    /**
     * Get the regions for the divisions post.
     */
    public function regions()
    {
        return $this->hasMany('App\Models\UserRegion','user_division_id','id')->orderBy('name', 'asc');
    }

    /**
     * Get the users for the divisions post.
     */
    public function users()
    {
        return $this->hasMany('App\Models\User','user_division_id','id');
    }

    public static function extractRecipients($divisionIds = [])
    {
        $divisions = self::with('users')
            ->whereIn('id', $divisionIds)
            ->get()
            ->toArray();


        $divisionUsers = [];
        foreach ($divisions as $division) {
            $divisionUsers = array_merge($divisionUsers, $division['users']);
        };

        return $divisionUsers;
    }
}
