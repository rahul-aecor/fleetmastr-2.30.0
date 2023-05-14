<?php

namespace App\Http\Requests\Api;

use Dingo\Api\Http\FormRequest;

class ImageUploadRequest extends FormRequest
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
            'temp_id' => 'required',
            'category' => 'in:vehicle,survey,check,defect,incident,defecthistory',
            'image_string' => 'required',
            'image_exif' => 'required',
        ];
    }
}
