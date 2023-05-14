<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasMediaUploads;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

class Template extends Model implements HasMedia
{
    use SoftDeletes, HasMediaTrait, HasMediaUploads;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "name",
        "content",
        "type",
        "priority",
        "surveys",
        "questions",
        "created_by"
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
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'surveys' => 'array',
        'questions' => 'array',
    ];

    /**
     * Get the user that created the group.
     */
    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    /**
     * The site users that belong to the template.
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'template_users');
    }

    /**
     * The site groups that belong to the template.
     */
    public function groups()
    {
        return $this->belongsToMany('App\Models\Group', 'template_groups');
    }

    /**
     * The site user divisions that belong to the template.
     */
    public function userdivisions()
    {
        return $this->belongsToMany('App\Models\UserDivision', 'template_user_divisions');
    }
}
