<?php

namespace App\Models;

use Carbon\Carbon as Carbon;
use Illuminate\Database\Eloquent\Model;

class VehicleMaintenanceNotification extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];
}
