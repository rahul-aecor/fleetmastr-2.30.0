<?php

namespace App\Models;

use App\Traits\HasMediaUploads;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

class TemporaryImage extends Model implements HasMedia
{
    use HasMediaTrait, HasMediaUploads;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'temporary_images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id'
    ];

    /**
     * Create the polymorphic relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Set the polymorphic relation.
     *
     * @return mixed
     */
    public function temporaryImages()
    {
        return $this->morphMany('App\Models\TemporaryImage', 'model');
    }
}
