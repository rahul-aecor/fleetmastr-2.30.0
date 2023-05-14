<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "name",
        "abbreviation"
        ];


    public function user_company()
    {
         return $this->hasMany('App\Models\User', 'company_id', 'id')->where('is_disabled', 0)
                    ->orWhere('is_disabled', 1);
    }

    public function defect_history_company()
    {
         return $this->hasMany('App\Models\DefectHistory', 'workshop_company_id', 'id');
    }
}
