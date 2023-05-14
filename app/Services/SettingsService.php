<?php
namespace App\Services;

use Storage;
use File;
use Intervention\Image\ImageManager;

class SettingsService 
{
    /**
     * @var ImageManager
     */
    protected $images;

    /**
     * Create a new service instance.
     */
    function __construct(ImageManager $images)
    {
        $this->images = $images;
    }

    public function writeBrandingStylesForColour($color)
    {
        $scss = new \Leafo\ScssPhp\Compiler;
        $scss->addImportPath(base_path('resources/assets/sass'));
        $color = trim($color, '#');
        
        Storage::disk('public')->put(
            'css/brand/main.css', 
            $scss->compile('
                @import "partials/variables.scss";
                $primary-colour: #' . $color . ';
                $primary_close_img: url(../../img/remove-icon-small.svg);
                @import "partials/common.scss";
            ')
        );

        $fileVersion = "";
        $files = Storage::disk('public')->allFiles('build/css/brand');
        foreach($files as $file) {
            if(strpos($file, 'main') !== false){
                $fileVersion = str_replace('build/css/brand/main-', '', $file);
                $fileVersion = str_replace('.css', '', $fileVersion);
            }
        }

        File::copy(public_path().'/css/brand/main.css', public_path().'/build/css/brand/main-'.$fileVersion.'.css');

        Storage::disk('public')->put(
            'css/brand/pdf.css', 
            $scss->compile('
                @import "partials/variables.scss";
                $primary-colour: #' . $color . ';
                $primary_close_img: url(../../img/remove-icon-small.svg);
                @import "partials/common_pdf.scss";
            ')
        );

    }

    public function uploadLogo($request)
    {
        $filename = $this->cropLogo($request);
        $s3path = 'settings/logo/'.$filename;
        $localpath  = storage_path() . '/temporary/'.$filename;

        // We will store the market main image on the "s3" disk
        // and return the URL for the image.
        $disk = Storage::disk('S3_uploads');
        $disk->put($s3path, $this->formatLogo($localpath), 'public');

        $destination = storage_path() . '/temporary/';

        return config('filesystems.disks.S3_uploads.domain') .  '/' . $s3path;
    }

    /**
     * Upload a image to and crop.
     *
     * @param Request $request
     * @return string file name
     */
    public function cropLogo($request)
    {
        $image = $request->image;
        $extension = $image->guessExtension();
        $filename = uniqid().'_'.time().'_'.date('Ymd') . '.' . $extension;
        $destination = storage_path() . '/temporary/';
        $localpath = $destination . $filename;

        $image = $this->images->make($image);
        $crop_box_start_x = intval($request->x);
        $crop_box_start_y = intval($request->y);
        $crop_box_width = intval($request->w);
        $crop_box_height = intval($request->h);
        $image = $image->crop($crop_box_width, $crop_box_height, $crop_box_start_x, $crop_box_start_y);
        $image->save($localpath);

        return $filename;
    }

    public function formatLogo($path)
    {
        return (string) $this->images->make($path)->fit(200, 200)->encode();
    }
}
?>