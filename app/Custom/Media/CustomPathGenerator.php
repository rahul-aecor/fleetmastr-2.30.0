<?php

namespace App\Custom\Media;

use Log;
use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    /**
     * Get the path for the given media, relative to the root storage path.
     *
     * @param \Spatie\MediaLibrary\Media $media
     *
     * @return string
     */
    public function getPath(Media $media)
    {
        return $media->collection_name . '/' . date('Y/m',strtotime($media->created_at)).'/'.$media->id.'/';
    }

    /**
     * Get the path for conversions of the given media, relative to the root storage path.
     *
     * @param \Spatie\MediaLibrary\Media $media
     *
     * @return string
     */
    public function getPathForConversions(Media $media)
    {
        return $media->collection_name . '/' . date('Y/m',strtotime($media->created_at)).'/'.$media->id.'/c/';
    }
}