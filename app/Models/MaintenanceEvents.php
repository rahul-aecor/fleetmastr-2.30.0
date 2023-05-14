<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceEvents extends Model
{
    //
    use SoftDeletes;
    protected $guarded = ['id'];

    public function maintenanceHistory()
    {
         return $this->hasMany('App\Models\VehicleMaintenanceHistory', 'event_type_id', 'id');
    }    
}
