<?php

namespace App\Models;

use Carbon\Carbon as Carbon;
use Pingpong\Presenters\Model;

class P11dReport extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'p11d_report';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tax_year',
        'url',
        'freezed_date',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    
}
