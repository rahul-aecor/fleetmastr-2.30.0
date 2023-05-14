<?php

namespace App\Models;

use Pingpong\Presenters\Model;
/*use Illuminate\Database\Eloquent\Model;*/
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

class VehicleType extends Model implements HasMedia
{
    use SoftDeletes, HasMediaTrait;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'vehicle_types';

    /**
     * The model's presenter class
     *
     * @var string
     */
    protected $presenter = \App\Presenters\VehicleTypePresenter::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vehicle_type',
        'vehicle_category',
        'vehicle_subcategory',
        'manufacturer',
        'model',
        'model_picture',
        'tyre_size_drive',
        'tyre_size_steer',
        'tyre_pressure_drive',
        'tyre_pressure_steer',
        'nut_size',
        're_torque',
        'body_builder',
        'fuel_type',
        'gross_vehicle_weight',
        'length',
        'width',
        'height',
        'engine_type',
        'oil_grade',
        'profile_status',
        'co2',
        'service_inspection_interval',
        'engine_size',
        'hmrc_co2',
        'vehicle_tax',
        'annual_insurance_cost',
        'pto_service_interval',
        'invertor_service_interval',
        'pmi_interval',
        'compressor_service_interval',
        'odometer_setting',
        'usage_type',
        'loler_test_interval',
        'service_interval_type',
        'adr_test_date',
        'tank_test_date',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Get vehicles for the type
     */
    public function vehicles()
    {
        return $this->hasMany('\App\Models\Vehicle');
    }

    public function getMediaList(){

        $medialist = $this->getMedia();
        $associatedMediaList = array();
        foreach ($medialist as $key => $media) {
            $key = "";
            if($media->collection_name == "frontview"){
                $key = "Front View";
            }
            else if($media->collection_name == "backview"){
                $key = "Back View";
            }
            else if($media->collection_name == "leftview"){
                $key = "Left View";
            }
            else if($media->collection_name == "rightview"){
                $key = "Right View";
            }
            $associatedMediaList[$key] = $media;
        }
        if(!array_key_exists("Front View", $associatedMediaList)){
            $associatedMediaList['Front View'] = (object)['for'=>'frontview'];
        }
        if(!array_key_exists("Back View", $associatedMediaList)){
            $associatedMediaList['Back View'] = (object)['for'=>'backview'];
        }
        if(!array_key_exists("Left View", $associatedMediaList)){
            $associatedMediaList['Left View'] = (object)['for'=>'leftview'];
        }
        if(!array_key_exists("Right View", $associatedMediaList)){
            $associatedMediaList['Right View'] = (object)['for'=>'rightview'];
        }

        ksort($associatedMediaList);
        return $associatedMediaList;
    }

}
