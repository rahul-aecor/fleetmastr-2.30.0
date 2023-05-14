<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'locations';

    protected $guarded = ['id'];

    /**
     * Location category.
     */
    public function category()
    {
        return $this->hasOne('App\Models\LocationCategory','id','location_category_id');
    }
}
