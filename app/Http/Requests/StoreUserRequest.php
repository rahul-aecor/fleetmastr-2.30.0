<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class StoreUserRequest extends Request
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
        if (Request::Input('id')  == NULL){
            $email  = 'email|unique:users,email,'.$this->id;
            $company_id = 'required';
            // $password = Request::Input('company_id') != 1  ? 'required|min:6' : '';
        }
        else {
            $email ='';
            // $password = '';
            $company_id = '';
        }
        Request::Input('enable_login') == 1 ? $roles = 'required' : $roles = '';

        return [
            'first_name' => 'required',
            'last_name' => 'required',
            'company_id' => $company_id,
            'roles' => $roles,
            // 'password' => $password,
            'mobile' => 'numeric',
            'landline' => 'numeric'
        ];
    }
    public function messages()
    {
        return [
            'comments.required' => 'This field is required.'
        ];
    }
}
