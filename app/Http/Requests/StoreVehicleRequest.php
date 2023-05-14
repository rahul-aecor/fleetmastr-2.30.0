<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class StoreVehicleRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->id;
        return [
            //'registration' => 'required|unique:vehicles,registration,'.$id,
            // 'vehicle_category' => 'required',
            'vehicle_type_id' => 'required',
            // 'status' => 'required',
            'dt_added_to_fleet' => 'required|date',
            //'last_odometer_reading' => 'required',
            // 'odometer_reading_unit' => 'required',
            //'dt_registration' => 'required',
            'operator_license' => 'alpha_num',
            'chassis_number' => 'unique:vehicles,chassis_number,'.$id,
            //'vehicle_location_id' => 'required',
            //'vehicle_region' => 'required',
            //'vehicle_division' => 'required',
            //'vehicle_repair_location_id' => 'required',
            // 'dt_repair_expiry' => 'required',
            //'dt_mot_expiry' => 'required',
            // 'dt_next_service_inspection' => 'required',
            // 'dt_tacograch_calibration_due' => 'required'
            // 'service_inspection_interval_hgv' => 'required',
            // 'service_inspection_interval_non_hgv' => 'required'
        ];
    }
}
