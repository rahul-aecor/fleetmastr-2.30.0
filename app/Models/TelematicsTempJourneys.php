<?php

namespace App\Models;

use Carbon\Carbon as Carbon;
use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
//use Illuminate\Database\Eloquent\SoftDeletes;

class TelematicsTempJourneys extends Model
{
    //use SoftDeletes;
    

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'telematics_temp_journeys';

    //public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    
}
