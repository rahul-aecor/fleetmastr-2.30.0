<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class StoreVehiclePlanningHistoryRequest extends Request
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
        return [
            'vehicle_id' => 'required',
            'comments' => 'required_without:attachment'
        ];
    }

    public function messages()
    {
        return [
            'comments.required' => 'This field is required.'
        ];
    }
}
