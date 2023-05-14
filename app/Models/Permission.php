<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use SoftDeletes;

     /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "module",
        "name",
        "slug",
        "description"
    ];

    /**
     * The permissions that belong to the roles.
     */
    public function roles()
    {
        return $this->belongsToMany('App\Models\Role');
    }

}
