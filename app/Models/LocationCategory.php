<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocationCategory extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'location_categories';

    protected $guarded = ['id'];

    public function location()
    {
        return $this->hasMany('App\Models\Location', 'location_category_id');
    }
    
}
