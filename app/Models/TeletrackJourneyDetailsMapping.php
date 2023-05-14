<?php

namespace App\Models;

use Carbon\Carbon as Carbon;
use Illuminate\Database\Eloquent\Model;


class TeletrackJourneyDetailsMapping extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'teletrack_journey_details_mapping';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    public function telematicsJourneyDetails()
    {
        return $this->belongsTo('App\Models\TelematicsJourneyDetails', 'telematics_journey_details_id');
    }

}
