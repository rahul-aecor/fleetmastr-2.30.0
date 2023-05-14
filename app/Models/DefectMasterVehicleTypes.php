<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DefectMasterVehicleTypes extends Model
{
	protected $primaryKey = 'vehicle_type_id';
    public $incrementing = false;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'defect_master_vehicle_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vehicle_type_id',
        'defect_list',
        'vehicle_type_name'
    ];

}
