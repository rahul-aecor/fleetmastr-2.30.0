<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DefectMaster extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'defect_master';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order',
        'page_title',
        'app_question',
        'defect',
        'has_image',
        'has_text',
        'is_prohibitional',
        'safety_notes',
        'for_hgv',
        'for_non-hgv'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}
