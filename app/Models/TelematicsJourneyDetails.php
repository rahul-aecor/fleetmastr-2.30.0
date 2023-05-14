<?php

namespace App\Models;

use Carbon\Carbon as Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TelematicsJourneyDetails extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'telematics_journey_details';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    public function telematicsJourneys()
    {
        return $this->belongsTo('App\Models\TelematicsJourneys', 'telematics_journey_id');
    }

}
