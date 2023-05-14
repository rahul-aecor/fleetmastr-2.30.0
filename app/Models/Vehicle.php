<?php

namespace App\Models;

use Carbon\Carbon as Carbon;
use App\Custom\Helper\Common;
use Pingpong\Presenters\Model;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Defect;
use App\Models\Settings;

class Vehicle extends Model implements HasMedia
{
    use HasMediaTrait, SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'vehicles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'registration',
        'vehicle_category',
        'vehicle_type_id',
        'status',
        'archived_date',
        'dt_added_to_fleet',
        'last_odometer_reading',
        'dt_registration',
        'operator_license',
        'chassis_number',
        'contract_id',
        'vehicle_location_id',
        'dt_first_use_inspection',
        'dt_vehicle_disposed',
        #'vehicle_region',
        'vehicle_repair_location_id',
        'dt_repair_expiry',
        'dt_mot_expiry',
        'dt_next_service_inspection',
        'dt_tacograch_calibration_due',
        'dt_loler_test_due',
        'dt_tax_expiry',
        'adr_test_date',
        'dt_annual_service_inspection',
        'service_inspection_interval_hgv',
        'service_inspection_interval_non_hgv',
        'on_road',
        'lease_expiry_date',
        'nominated_driver',
        'vehicle_division_id',
        'vehicle_region_id',
        #'vehicle_division',
        'annual_maintenance_cost',
        'annual_vehicle_cost',
        'annual_insurance',
        'annual_telematice_cost',
        'manual_cost_adjustment',
        'miles_per_month',
        'fuel_use',
        'oil_use',
        'adblue_use',
        'screen_wash_use',
        'fleet_livery_wash',
        'monthly_lease_cost',
        'permitted_annual_mileage',
        'excess_cost_per_mile',
	    'is_telematics_enabled',
	    'webfleet_object_id',
        'next_pto_service_date',
        'next_invertor_service_date',
        'next_pmi_date',
        'next_compressor_service',
        'first_pmi_date',
        'is_insurance_cost_override',
        'next_service_inspection_distance',
        'last_service_distance_notification_odometer',
        'tank_test_date',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The model's presenter class
     *
     * @var string
     */
    protected $presenter = \App\Presenters\VehiclePresenter::class;

    /**
     * Get the vehicle that belongs to the check.
     */
    public function checks()
    {
        return $this->hasMany('App\Models\Check', 'vehicle_id')->orderBy('report_datetime','desc');
    }

    /**
     * Get the last check performed on this vehicle.
     */
    public function lastCheck()
    {
        return $this->hasMany('App\Models\Check', 'vehicle_id')->orderBy('report_datetime','desc')->take(1);
    }

    /**
     * Get the last check performed on this vehicle.
     */
    public function lastTelematicsJourney()
    {
        // return $this->hasMany('App\Models\UserTelematicsJourney', 'vehicle_id')->with('user')->orderBy('start_time','desc')->take(1);
        return $this->hasMany('App\Models\TelematicsJourneys', 'vehicle_id')->with('user')->orderBy('start_time','desc')->take(1);
    }

    /**
     * Get the last check performed on this vehicle.
     */
    public function lastTelematicsJourneyDetails()
    {
        return $this->hasMany('App\Models\TelematicsJourneyDetails', 'vrn')->whereNotNull('post_code')->orderBy('time','desc')->take(1);
    }

    /**
     * Get the all maintenanceHistories.
     */
    public function maintenanceHistories()
    {
        return $this->hasMany('App\Models\VehicleMaintenanceHistory', 'vehicle_id','id');
    }

    /**
     * Get the all PMI maintenanceHistories.
     */
    public function pmiMaintenanceHistories()
    {
        $pmiEvent = MaintenanceEvents::where('slug','preventative_maintenance_inspection')->first();
        return $this->hasMany('App\Models\VehicleMaintenanceHistory', 'vehicle_id','id')
            ->where('event_type_id',$pmiEvent->id);
    }

    /**
     * Get the vehicle that belongs to the check.
     */
    public function defects()
    {
        return $this->hasMany('App\Models\Defect', 'vehicle_id')->orderBy('report_datetime','desc');
    }

    /**
     * Get the vehicle type.
     */
    public function type()
    {
        return $this->belongsTo('App\Models\VehicleType', 'vehicle_type_id')->withTrashed();
    }

    /**
     * Get the vehicle Off Road Logs.
     */
    public function vorLogs(){
        return $this->hasMany('App\Models\VehicleVORLog', 'vehicle_id')->orderBy('id','desc');
    }

    /**
     * Get the private usage data.
     */
    public function privateUseData(){
        return $this->hasMany('App\Models\VehiclePrivateUse', 'vehicle_id')->orderBy('created_at','desc');
    }

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['dt_added_to_fleet', 'dt_registration', 'dt_repair_expiry', 'dt_mot_expiry', 'dt_next_service_inspection', 'dt_tacograch_calibration_due', 'dt_tax_expiry', 'dt_annual_service_inspection','dt_first_use_inspection','dt_vehicle_disposed','lease_expiry_date','dt_loler_test_due','next_invertor_service_date','next_pto_service_date','next_pmi_date','next_compressor_service','first_pmi_date'];

    /**
     * Get the dt_vehicle_disposed timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setDtVehicleDisposedAttribute($value)
    {
        if ($value) {
            $this->attributes['dt_vehicle_disposed'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['dt_vehicle_disposed'] = null;
        }
    }
    /**
     * Get the dt_vehicle_disposed timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getDtVehicleDisposedAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }
    /**
     * Get the dt_first_use_inspection timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setDtFirstUseInspectionAttribute($value)
    {
        if ($value) {
            $this->attributes['dt_first_use_inspection'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['dt_first_use_inspection'] = null;
        }
    }

    /**
     * Get the dt_first_use_inspection timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getDtFirstUseInspectionAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }

    /**
     * Get the dt_added_to_fleet timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setDtAddedToFleetAttribute($value)
    {
        if ($value) {
            $this->attributes['dt_added_to_fleet'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['dt_added_to_fleet'] = null;
        }
    }

    /**
     * Get the dt_added_to_fleet timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getDtAddedToFleetAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }
    /**
     * Get the dt_registration timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setDtRegistrationAttribute($value)
    {
        if ($value) {
            $this->attributes['dt_registration'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['dt_registration'] = null;
        }
    }

    /**
     * Get the dt_registration timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getDtRegistrationAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }

    /**
     * Get the dt_repair_expiry timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setDtRepairExpiryAttribute($value)
    {
        if ($value) {
            $this->attributes['dt_repair_expiry'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['dt_repair_expiry'] = null;
        }
    }

    /**
     * Get the dt_repair_expiry timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getDtRepairExpiryAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }

    /**
     * Get the lease_expiry_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setLeaseExpiryDateAttribute($value)
    {
        if ($value) {
            $this->attributes['lease_expiry_date'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['lease_expiry_date'] = null;
        }
    }

    /**
     * Get the dt_repair_expiry timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getLeaseExpiryDateAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }

    /**
     * Get the dt_mot_expiry timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setDtMotExpiryAttribute($value)
    {
        if ($value) {
            $this->attributes['dt_mot_expiry'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['dt_mot_expiry'] = null;
        }
    }

    /**
     * Get the dt_mot_expiry timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getDtMotExpiryAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }

    /**
     * Get the dt_next_service_inspection timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setDtNextServiceInspectionAttribute($value)
    {
        if ($value) {
            $this->attributes['dt_next_service_inspection'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['dt_next_service_inspection'] = null;
        }
    }

    /**
     * Get the dt_next_service_inspection timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getDtNextServiceInspectionAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }

    /**
     * Get the dt_tacograch_calibration_due timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setDtTacograchCalibrationDueAttribute($value)
    {
        if ($value) {
            $this->attributes['dt_tacograch_calibration_due'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['dt_tacograch_calibration_due'] = null;
        }
    }

    /**
     * Get the dt_tacograch_calibration_due timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getDtTacograchCalibrationDueAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }

    /**
     * Set the dt_tax_expiry timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setDtTaxExpiryAttribute($value)
    {
        if ($value) {
            $this->attributes['dt_tax_expiry'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['dt_tax_expiry'] = null;
        }
    }

    /**
     * Set the adr_test_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setAdrTestDateAttribute($value)
    {
        if ($value) {
            $this->attributes['adr_test_date'] = Carbon::parse($value)->format('Y-m-d');
        }
        else {
            $this->attributes['adr_test_date'] = null;
        }
    }

    /**
     * Get the dt_tax_expiry timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getDtTaxExpiryAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }

    /**
     * Get the adr_test_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getAdrTestDateAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }

    /**
     * Set the dt_annual_service_inspection timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setDtAnnualServiceInspectionAttribute($value)
    {
        if ($value) {
            $this->attributes['dt_annual_service_inspection'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['dt_annual_service_inspection'] = null;
        }
    }

    /**
     * Get the dt_annual_service_inspection timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getDtAnnualServiceInspectionAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }
    /**
     * Set the dt_loler_test_due timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setDtLolerTestDueAttribute($value)
    {
        if ($value) {
            $this->attributes['dt_loler_test_due'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['dt_loler_test_due'] = null;
        }
    }

    /**
     * Get the dt_loler_test_due timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getDtLolerTestDueAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }

    /**
     * Get the deleted_at timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getDeletedAtAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }

    /**
     * Set the next_pto_service_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setNextPtoServiceDateAttribute($value)
    {
        if ($value) {
            $this->attributes['next_pto_service_date'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['next_pto_service_date'] = null;
        }
    }

     /**
     * Get the next_pto_service_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getNextPtoServiceDateAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }

    /**
     * Set the next_invertor_service_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setNextInvertorServiceDateAttribute($value)
    {
        if ($value) {
            $this->attributes['next_invertor_service_date'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['next_invertor_service_date'] = null;
        }
    }

    /**
     * Get the next_invertor_service_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getNextInvertorServiceDateAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }

    /**
     * Set the next_pmi_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setNextPmiDateAttribute($value)
    {
        if ($value) {
            $this->attributes['next_pmi_date'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['next_pmi_date'] = null;
        }
    }

    /**
     * Get the next_pmi_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getNextPmiDateAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }

     /**
     * Set the first_pmi_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setFirstPmiDateAttribute($value)
    {
        if ($value) {
            $this->attributes['first_pmi_date'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['first_pmi_date'] = null;
        }
    }

    /**
     * Get the first_pmi_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getFirstPmiDateAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }

    /**
     * Get the next_compressor_service timestamp.
     *
     * @param  string  $value
     * @return string
    */
    public function setNextCompressorServiceAttribute($value)
    {
        if ($value) {
            $this->attributes['next_compressor_service'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['next_compressor_service'] = null;
        }
    }
    /**
     * Get the next_compressor_service timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getNextCompressorServiceAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }

    /**
     * Set the tank_test_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function setTankTestDateAttribute($value)
    {
        if ($value) {
            $this->attributes['tank_test_date'] = Carbon::createFromFormat('d M Y', $value)->toDateTimeString();
        }
        else {
            $this->attributes['tank_test_date'] = null;
        }
    }

     /**
     * Get the tank_test_date timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getTankTestDateAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->format('d M Y');
        }
        else {
            return $value;
        }
    }

    /**
     * Get the service_inspection_interval_hgv timestamp.
     *
     * @param  string  $value
     * @return string
     */
    /*public function setServiceInspectionIntervalHgvAttribute($value)
    {
        $this->attributes['service_inspection_interval_hgv'] = "Every 15,000 miles or when indicated";
    }*/

    /**
     * Get the service_inspection_interval_non_hgv timestamp.
     *
     * @param  string  $value
     * @return string
     */
    /*public function setServiceInspectionIntervalNonHgvAttribute($value)
    {
        $this->attributes['service_inspection_interval_non_hgv'] = "Every 8 weeks";
    }*/

    /**
     * Get the user who is nominated driver for vehicle.
     */
    public function nominatedDriver()
    {
        return $this->belongsTo('App\Models\User', 'nominated_driver');
    }

    /**
     * Get the user who created the defect.
     */
    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'created_by')->where('is_disabled', 0)
                    ->orWhere('is_disabled', 1);
    }

    /**
     * Get the user who last updated the defect.
     */
    public function updater()
    {
        return $this->belongsTo('App\Models\User', 'updated_by')->where('is_disabled', 0)
                    ->orWhere('is_disabled', 1);
    }

    /**
     * Get the location of the vehicle.
     */
    public function location()
    {
        return $this->belongsTo('App\Models\VehicleLocations', 'vehicle_location_id');
    }

    /**
     * Get the repair location of the vehicle.
     */
    public function repair_location()
    {
        return $this->belongsTo('App\Models\VehicleRepairLocations', 'vehicle_repair_location_id');
    }
     /**
     * Get the location of the vehicle.
     */
    public function division()
    {
        return $this->belongsTo('App\Models\VehicleDivisions', 'vehicle_division_id');
    }
     /**
     * Get the location of the vehicle.
     */
    public function region()
    {
        return $this->belongsTo('App\Models\VehicleRegions', 'vehicle_region_id');
    }

    /**
    Format a vehicle list for offline API
    */
    public function format(){
        $returnVal = array();
        $defectList = array();
        $isFirstPMICompleted = false;

        if($this->first_pmi_date) {
            $firstPMI = $this->pmiMaintenanceHistories()
                        ->where('event_plan_date', Carbon::createFromFormat('d M Y', $this->first_pmi_date)->toDateString())
                        ->where('event_status','Complete')
                        ->get()
                        ->count();
            $isFirstPMICompleted = $firstPMI > 0 ? true : false;
        }

        $vehicleData = [
            'vehicle_id' => $this->id,
            'registration_number' => $this->registration,
            'vehicle_category' => $this->type->vehicle_category,
            'vehicle_type_id' => $this->type->id,
            'type' => $this->type->vehicle_type,
            'adblue_required' => ($this->type->engine_type == "Post-Euro VI - AdBlue required")? 1 : 0,
            'manufacturer' => $this->type->manufacturer,
            'model' => $this->type->model,
            'status' => $this->status,
            'odometer_reading' => $this->last_odometer_reading ? (int)$this->last_odometer_reading : 0,
            'odometer_unit' => $this->type->odometer_setting,
            'last_check' => $this->lastCheck->isEmpty() ?'N/A': $this->lastCheck->first()->report_datetime->setTimezone(config('config-variables.displayTimezone'))->format('H:i d M Y'),
            'dt_mot_expiry' => is_null($this->dt_mot_expiry) ?'N/A': $this->dt_mot_expiry,
            'dt_tax_expiry' => is_null($this->dt_tax_expiry) ?'N/A': $this->dt_tax_expiry,
            'dt_annual_service_inspection' => is_null($this->dt_annual_service_inspection) ?'N/A': $this->dt_annual_service_inspection,
            'dt_tacograch_calibration_due' => (is_null($this->dt_tacograch_calibration_due) || $this->type->vehicle_category != "hgv") ?'N/A': $this->dt_tacograch_calibration_due,
            'first_pmi_date' => (is_null($this->first_pmi_date) || $isFirstPMICompleted) ?'N/A': $this->first_pmi_date,
            'next_pmi_date' => is_null($this->next_pmi_date) ?'N/A': $this->next_pmi_date,
            'is_telematics_enabled' => $this->is_telematics_enabled,
            'is_next_service_distance_exceeded' => false,
            'adr_test_date' => is_null($this->adr_test_date) ?'N/A': $this->adr_test_date,
            'previous_next_service_distance' => 0,
            'tank_test_date' => is_null($this->tank_test_date) ?'N/A': $this->tank_test_date,
            'updated_at' => $this->updated_at->format('H:i:s j M Y'),
	    ];

        if ($this->type->service_interval_type == 'Distance') {
            $vehicleData['next_service_inspection_distance'] = is_null($this->next_service_inspection_distance) ? null : (int)$this->next_service_inspection_distance;
        } else if ($this->type->service_interval_type == 'Time') {
            $vehicleData['dt_next_service_inspection'] = is_null($this->dt_next_service_inspection) ?'N/A': $this->dt_next_service_inspection;
        }

        if (is_null($this->next_service_inspection_distance)) {
            unset($vehicleData['next_service_inspection_distance']);
        }
        
        foreach ($this->defects as $defect) {
            $media = $defect->getMedia();
            $mediaUrl = "";
            if(isset($media[0])){
                $mediaUrl = env('MEDIA_SERVER', '') . getPresignedUrl($media[0]);
            }
            $data = [
                "id" => $defect->defectMaster->id,
                "_image" => ($defect->defectMaster->has_image)?"yes":"no",
                "_text" => ($defect->defectMaster->has_text)?"yes":"no",
                "imageString" => $mediaUrl,
                "image_exif" => "",
                "selected" => "yes",
                "text" => $defect->defectMaster->defect,
                "textString" => "",
                "prohibitional" => ($defect->defectMaster->is_prohibitional)?"yes":"no",
                "safety_notes" => $defect->defectMaster->safety_notes,
                "defect_id" => $defect->id,
                "read_only" => "yes"
            ];
            array_push($defectList,$data);
        }

        $meta = [
                "survey_type" => ($this->type->id == 3) ? "parcelvan":"all",
                "defects_list_title" => "Defects Alert",
                "defects_list_message" => "This vehicle has defects",
                //"defects_list" => $defectList
        ];

        $returnVal["data"] = $vehicleData;
        $returnVal["meta"] = $meta;
        return $returnVal;
    }

    public function calcGivenMonthFixedCost($givenMonth, $vehicleId, $vehicleArchiveHistory, $vehicleDtAddedToFleet, $isInsuranceCostOverride, $isTelematicsCostOverride, $fleetCost, $vehicle)
    {
        $vehicleType = $vehicle->type;
        $fixedCost = 0;
        $commonHelper = new Common();

        //$this->staus_owned_leased != 'Owned' && 
        if ($this->lease_cost != null) {
            $fixedCost = $fixedCost + $commonHelper->calcMonthlyCurrentData($this->lease_cost, $givenMonth, $vehicleId,$vehicleArchiveHistory,null,null,null,null,null,null,$fleetCost)['currentCost'];

        }
        if($this->monthly_depreciation_cost != null){
            $fixedCost = $fixedCost + $commonHelper->calcMonthlyCurrentData($this->monthly_depreciation_cost, $givenMonth,$vehicleId,$vehicleArchiveHistory,null,null,null,null,null,null,$fleetCost)['currentCost'];
        }
        // if($this->staus_owned_leased == "Owned" || $this->staus_owned_leased == "Leased" || $this->staus_owned_leased == "Hire purchase") {
        if ($this->maintenance_cost != null) {
            $fixedCost = $fixedCost + $commonHelper->calcMonthlyCurrentData($this->maintenance_cost, $givenMonth,$vehicleId,$vehicleArchiveHistory,null,null,null,null,null,null,$fleetCost)['currentCost'];
        }
        // }
        // if ($this->annual_vehicle_cost != null && $this->annual_vehicle_cost != 0) {
        //     //$fixedCost = $fixedCost + $this->annual_vehicle_cost/12;
        //     $fixedCost = $fixedCost + $this->annual_vehicle_cost;
        // }
        ///vehicle_tax start

        $vehicleTaxValue = 0;
        if(!is_null($vehicleType->vehicle_tax) && $vehicleType->vehicle_tax != ''){
            $vehicleTaxCurrentData = $commonHelper->calcMonthlyCurrentData($vehicleType->vehicle_tax, $givenMonth,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,'N/A','N/A',null,null,null,$fleetCost);
            $vehicleTaxValue = $vehicleTaxCurrentData['currentCost'];
        }
        $fixedCost = $fixedCost + $vehicleTaxValue;
        ///vehicle_tax end

        //////insurance cost start
        $insuranceCost = 0 ;
        $insuranceJsonValue = $this->getGlobalOrOverridenJson('insurance_cost',$fleetCost,$vehicle);
        if ($insuranceJsonValue != '') {

            $insuranceCost = $commonHelper->calcMonthlyCurrentData($insuranceJsonValue,$givenMonth,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,$isInsuranceCostOverride,'N/A',null,null,null,$fleetCost)['currentCost'];
        }

        $fixedCost = $fixedCost + $insuranceCost;
        //////insurance cost end

        //////telematics cost start
        $telematicsCost = 0 ;
        $telematicsJson = $this->getGlobalOrOverridenJson('telematics_cost',$fleetCost,$vehicle);
        if ($telematicsJson != '' && $this->is_telematics_enabled == 1) {
            $telematicsCost = $commonHelper->calcMonthlyCurrentData($telematicsJson,$givenMonth,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,'N/A',$isTelematicsCostOverride,null,null,null,$fleetCost)['currentCost'];
        }
        $fixedCost = $fixedCost + $telematicsCost;

        //////insurance cost end
        // if ($this->is_telematics_enabled == 1 && $this->telematics_cost != null) {
        //     //$fixedCost = $fixedCost + $this->annual_telematice_cost/12;
        //     $fixedCost = $fixedCost + $commonHelper->calcMonthlyCurrentData($this->telematics_cost, $givenMonth)['currentCost'];
        // }

        // $depreciationCostValue = 0;
        // if(!is_null($this->monthly_depreciation_cost) && $this->monthly_depreciation_cost != ''){
        //     $depreciationCurrentData = $commonHelper->calcMonthlyCurrentData($this->monthly_depreciation_cost, $givenMonth);
        //     $depreciationCostValue = $depreciationCurrentData['currentCost'];
        //     $fixedCost = $fixedCost + $depreciationCostValue;
        // }
        $vehicleFixedCostAdditionAllField = $fixedCost;
        $vehicleFixedCost = $vehicleFixedCostAdditionAllField;
        return $vehicleFixedCost;
    }

    private function getGlobalOrOverridenJson($keyName, $fleetCostSettingsData, $vehicle)
    {
        $fleetCostDataJson = $fleetCostSettingsData->value;
        $fleetCostData = json_decode($fleetCostDataJson, true);
        $returnJson = '';
        if ($keyName == 'insurance_cost') {
            $vehicleType = $vehicle->type;
            if($this->is_insurance_cost_override != 1 || ($this->is_insurance_cost_override == 1 && $this->insurance_cost == '')) {
                if (isset($vehicle->type->annual_insurance_cost)) {
                    $returnJson = $vehicleType->annual_insurance_cost;
                }
            } else if($this->is_insurance_cost_override == 1 && $this->insurance_cost != ''){
                $returnJson = $this->insurance_cost;
            } else {
                if (isset($vehicleType->annual_insurance_cost)) {
                    $returnJson = $vehicleType->annual_insurance_cost;
                }
            }
        }

        if ($keyName == 'telematics_cost') {
            if($this->is_telematics_cost_override != 1 || ($this->is_telematics_cost_override == 1 && $this->telematics_cost == '')) {
                if (isset($fleetCostData['telematics_insurance_cost'])) {
                    $returnJson = json_encode($fleetCostData['telematics_insurance_cost']);
                }
            } else if($this->is_telematics_cost_override == 1 && $this->telematics_cost != ''){
                $returnJson = $this->telematics_cost;
            } else {
                if (isset($fleetCostData['telematics_insurance_cost'])) {
                    $returnJson = json_encode($fleetCostData['telematics_insurance_cost']);
                }
            }
        }
        return $returnJson;
    }
    public function calcGivenMonthVariableCost($givenMonth,$vehicleId,$vehicleArchiveHistory,$givenDate1, $givenDate2,$fleetCost=null,$vehicle = 0)
    {

        //$currentMonth = Carbon::now()->format("M Y");
        $fuelUseValue = 0;
        $oilUseValue = 0;
        $adBlueUseValue = 0;
        $screenWashUseValue = 0;
        $fleetLiveryUseValue = 0;
        $milesPerMonthValue = 0;
        $costPerMileValue = 0;
        $commonHelper = new Common();
        $vehicleStatusOwnedLeased = $this->staus_owned_leased;

        $selectedDate = $givenMonth;
        $startOfMonth = $givenDate1->startOfMonth()->format('Y-m-d');
        $endOfMonth = $givenDate2->endOfMonth()->format('Y-m-d');

        if (!empty($vehicle) != 0) {
            $defects = $vehicle->defects;
        } else {
            $defects = Defect::where('vehicle_id','=', $vehicleId)->get();
        }

        $totalDefectCost = 0;
        $defectCostValue = '';
        /*foreach ($defects as $defect) {
            $defectCostValue = $defect->actual_defect_cost_value ? $defect->actual_defect_cost_value : 0;
            if($defect->report_datetime != null && Carbon::parse($defect->report_datetime)->gte(Carbon::parse($startOfMonth)) && Carbon::parse($defect->report_datetime)->lte(Carbon::parse($endOfMonth))){
                $totalDefectCost = $totalDefectCost + $defectCostValue;
            }
        }*/

        // Fuel use
        $fuelUseValue = 0;
        $fuelUseArray = [];
        $vehicleFuelValue = json_decode($this->fuel_use, true);
        if(isset($vehicleFuelValue)){
            $fuelCurrentData = $commonHelper->calcCurrentMonthBasedOnPeriod($vehicleFuelValue,$selectedDate,$vehicleId,$vehicleArchiveHistory);
            if(!isset($fuelUseArray[$fuelCurrentData['currentDate']])) {
                $fuelUseArray[$fuelCurrentData['currentDate']] = 0;
            }
            $fuelUseArray[$fuelCurrentData['currentDate']] += $fuelCurrentData['currentCost'];
            $fuelUseValue = $fuelCurrentData['currentCost'];
        }
        // Oil use
        $oilUseValue = 0;
        $vehicleOilArray = [];
        $vehicleOilValue = json_decode($this->oil_use, true);
        if(isset($vehicleOilValue)){
            $oilCurrentData = $commonHelper->calcCurrentMonthBasedOnPeriod($vehicleOilValue,$selectedDate,$vehicleId,$vehicleArchiveHistory);
            if(!isset($vehicleOilArray[$oilCurrentData['currentDate']])) {
                $vehicleOilArray[$oilCurrentData['currentDate']] = 0;
            }
            $vehicleOilArray[$oilCurrentData['currentDate']] += $oilCurrentData['currentCost'];
            $oilUseValue = $oilCurrentData['currentCost'];
        }

        // adBlue use
        $adBlueUseValue = 0;
        $vehicleAdblueArray = [];
        $vehicleAdblueValue = json_decode($this->adblue_use, true);
        if(isset($vehicleAdblueValue)){
            $adBlueCurrentData = $commonHelper->calcCurrentMonthBasedOnPeriod($vehicleAdblueValue,$selectedDate,$vehicleId,$vehicleArchiveHistory);
            if(!isset($vehicleAdblueArray[$adBlueCurrentData['currentDate']])) {
                $vehicleAdblueArray[$adBlueCurrentData['currentDate']] = 0;
            }
            $vehicleAdblueArray[$adBlueCurrentData['currentDate']] += $adBlueCurrentData['currentCost'];
            $adBlueUseValue = $adBlueCurrentData['currentCost'];
        }
        // screenWash use
        $screenWashUseValue = 0;
        $vehicleScreenWashArray = [];
        $vehicleScreenWashValue = json_decode($this->screen_wash_use, true);
        if(isset($vehicleScreenWashValue)){
            $screenWashCurrentData = $commonHelper->calcCurrentMonthBasedOnPeriod($vehicleScreenWashValue,$selectedDate,$vehicleId,$vehicleArchiveHistory);
            if(!isset($vehicleScreenWashArray[$screenWashCurrentData['currentDate']])) {
                $vehicleScreenWashArray[$screenWashCurrentData['currentDate']] = 0;
            }
            $vehicleScreenWashArray[$screenWashCurrentData['currentDate']] += $screenWashCurrentData['currentCost'];
            $screenWashUseValue = $screenWashCurrentData['currentCost'];
        }

        // fleetlivery use
        $fleetLiveryUseValue = 0;
        $vehicleFleetLiveryArray = [];
        $vehicleFleetLiveryValue = json_decode($this->fleet_livery_wash, true);
        if(isset($vehicleFleetLiveryValue)){
            $fleetLiveryCurrentData = $commonHelper->calcCurrentMonthBasedOnPeriod($vehicleFleetLiveryValue,$selectedDate,$vehicleId,$vehicleArchiveHistory);
            if(!isset($vehicleFleetLiveryArray[$fleetLiveryCurrentData['currentDate']])) {
                $vehicleFleetLiveryArray[$fleetLiveryCurrentData['currentDate']] = 0;
            }
            $vehicleFleetLiveryArray[$fleetLiveryCurrentData['currentDate']] += $fleetLiveryCurrentData['currentCost'];
            $fleetLiveryUseValue = $fleetLiveryCurrentData['currentCost'];
        }


        // Global manual cost adjustment
        /*$fleetCost = $fleetCost == null ? Settings::where('key', 'fleet_cost_area_detail')->first() : $fleetCost;
        $fleetCostSettingsData = json_decode($fleetCost->value, true);
        $currentMonthManualCostAdjustment = 0;
        if (isset($fleetCostSettingsData['manual_cost_adjustment'])) {
            // $currentMonthManualCostAdjustment = $commonHelper->calcCurrentMonthBasedOnPeriod($fleetCostSettingsData['manual_cost_adjustment'],$selectedDate);
        }*/

        //IVP manual cost adjustment
        $costAdjustment = 0;
        $costArray = [];
        $vehicleManualCostAdjustmentValue = json_decode($this->manual_cost_adjustment, true);
        if(isset($vehicleManualCostAdjustmentValue)){
            $manualCostAdjustmentCurrentData = $commonHelper->calcCurrentMonthBasedOnPeriod($vehicleManualCostAdjustmentValue,$selectedDate,$vehicleId,$vehicleArchiveHistory);
            if(!isset($costArray[$manualCostAdjustmentCurrentData['currentDate']])) {
                $costArray[$manualCostAdjustmentCurrentData['currentDate']] = 0;
            }
            $costArray[$manualCostAdjustmentCurrentData['currentDate']] += $manualCostAdjustmentCurrentData['currentCost'];
            $costAdjustment = $manualCostAdjustmentCurrentData['currentCost'];
        }

        $vehicleVeriableCostAdditionAllField = $milesPerMonthValue + $fuelUseValue + $oilUseValue + $adBlueUseValue + $screenWashUseValue + $fleetLiveryUseValue + $costAdjustment + $totalDefectCost;

        $vehicleVariableCost = $vehicleVeriableCostAdditionAllField;
        return $vehicleVariableCost;
    }

    private function vehicleCostCurrentMonthCalc($costJson,$selectedDate,$vehicleId,$vehicleArchiveHistory=null,$vehicleDtAddedToFleet=null,$isInsuranceCostOverride=null,$isTelematicsCostOverride=null){
        $commonHelper = new Common();
        return $commonHelper->calcMonthlyCurrentData($costJson,$selectedDate,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,$isInsuranceCostOverride,$isTelematicsCostOverride);
    }

    public function archiveHistory() {
        return $this->hasOne(VehicleArchiveHistory::class,'vehicle_id','id')->orderBy('id','DESC')->limit(1);
    }
}
