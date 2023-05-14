<?php 
namespace App\Traits;

use Storage;
use Image;

trait HasMediaUploads
{
    public function createImageFromString($imageString, $outputFileName) {
        if (strpos($imageString, 'data:') === 0) {
            list($ext, $data) = explode(',', $imageString, 2);
            $ext = str_replace('data:image/', '', $ext);
            $ext = str_replace(';base64', '', $ext);
            $ext = ".".$ext;
            $imageString = $data;

            $imgFileName = $outputFileName . $ext;
            Storage::put($imgFileName, base64_decode($imageString));
            return storage_path('app') . '/'. $imgFileName;            
        }   
        return false;     
    }

    public function resizeImageToStandardSize($pathToImage) {
        $img = Image::make($pathToImage);
        $img->resize(800, 600, function ($constraint) {
            $constraint->aspectRatio();
        })->save($pathToImage);
        return $pathToImage;
    }

    public function createImageOverlay($file, $text_line_1, $text_line_2, $text_line_3) {
        
        list($width, $height, $type) = getimagesize($file);

        switch($type) {
            case IMAGETYPE_GIF:
                $overlayImage = imagecreatefromgif($file);
                break;
            case IMAGETYPE_JPEG:
                $overlayImage = imagecreatefromjpeg($file);
                break;
            case IMAGETYPE_PNG:
                $overlayImage = imagecreatefrompng($file);
                break;
            default:
                throw new \Exception("Invalid image");
                break;
        }
        $boxColor = imagecolorallocatealpha($overlayImage, 0, 0, 0, 50);
        imagefilledrectangle($overlayImage, 0, 0, imagesx($overlayImage), 60, $boxColor);
        $white = imagecolorallocate($overlayImage, 255, 255, 255);
        //DroidSansMono.ttf atlantabook.ttf
        $font_path = public_path().'/fonts/DroidSansMono.ttf';        
        imagettftext($overlayImage, 10, 0, 10, 15, $white, $font_path, $text_line_1);        
        imagettftext($overlayImage, 10, 0, 10, 35, $white, $font_path, $text_line_2);        
        imagettftext($overlayImage, 10, 0, 10, 55, $white, $font_path, $text_line_3);        
        switch($type) {
            case IMAGETYPE_GIF:
                imagegif($overlayImage, $file);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($overlayImage, $file);
                break;
            case IMAGETYPE_PNG:
                imagepng($overlayImage, $file);
                break;
            default:
                return false;
                break;
        }         
        return $file;           
    }
}