<?php
namespace App\Traits;

use Storage;
use Image;
use App\Models\User;
use Spatie\MediaLibrary\Media;
// use App\Models\Media;
use App\Models\Defect;
use App\Models\DefectHistory;
use App\Models\TemporaryImage;

trait ProcessCheckJsonOld
{
    protected function processJson($json)
    {
        $check_json = json_decode($json);
        $check_json->total_defect = 0;
        foreach ($check_json->screens->screen as $screen) {
            if ($screen->_type == "yesno") {
            	$screen->defect_count = 0;
                $screen->prohibitional_defect_count = 0;
                foreach ($screen->defects->defect as $defect) {
                    $defect = $this->processDefect($defect);
                    if($defect->selected == "yes"){
                        if( $defect->prohibitional == "yes" ){
                            $screen->prohibitional_defect_count = $screen->prohibitional_defect_count + 1;
                        }
                        else{
                            $screen->defect_count = $screen->defect_count + 1;
                        }
                    	$check_json->total_defect = $check_json->total_defect + 1;
                    }
                } 
            }
            if ($screen->_type == "list") {
                foreach ($screen->options->optionList as $option) {
                	$option->defect_count = 0;
                    $option->prohibitional_defect_count = 0;
                    foreach ($option->defects->defect as $defect) {
                        $defect = $this->processDefect($defect);
	                    if($defect->selected == "yes"){
                            if( $defect->prohibitional == "yes" ){
                                $option->prohibitional_defect_count = $option->prohibitional_defect_count + 1;
                            }
                            else{
                                $option->defect_count = $option->defect_count + 1;
                            }
	                    	$check_json->total_defect = $check_json->total_defect + 1;
	                    }
                    } 
                }
            }
        }
        return json_encode($check_json);
    }

    protected function processDefect($defect)
    {
        if($defect->selected == "yes")
        {
            if(isset($defect->read_only) && trim($defect->read_only) !="")
            {
                $defect->selected = "no";
                $defect->imageString = "";
                $defect->image_exif = "";
                $defect->textString = "";
                unset($defect->read_only);
                unset($defect->defect_id);
            }
            else
            {
                if(isset($defect->defect_id) && trim($defect->defect_id) != "") // Update existing Defect
                {
                    $newDefect = Defect::find($defect->defect_id);
                    if(isset($defect->comments) && trim($defect->comments) != ""){
                        $newDefect->comments = $defect->comments;
                    }
                    $newDefect->updated_by = $this->user_id;
                    $newDefect->save();
                    $this->addDefectImage($newDefect, $defect, true);
                }
                else // Add a new defect
                {
                    $newDefect = new Defect();
                    $newDefect->vehicle_id = $this->vehicle_id;
                    $newDefect->check_id = $this->check_id;
                    $newDefect->defect_master_id = $defect->id;
                    $newDefect->description = "";
                    if(isset($defect->comments) && trim($defect->comments) != ""){
                        $newDefect->comments = $defect->comments;
                    }
                    $newDefect->status = "Reported";
                    $newDefect->created_by = $this->user_id;
                    $newDefect->updated_by = $this->user_id;
                    $newDefect->save();
                    $this->addDefectImage($newDefect, $defect);   
                    
                    ///Logic to mark defects as duplicate                 
                    $defectlist = Defect::where(['vehicle_id'=>$this->vehicle_id,'defect_master_id'=>$defect->id])->whereNotIn('status',['Resolved'])->get();
                    if ($defectlist->count() > 1) {
                        $duplicateDefectListIds = $defectlist->pluck('id');
                        Defect::whereIn('id',$duplicateDefectListIds)->update(['duplicate_flag'=>true]);
                    }
                    ////

                    $defectHistory = new DefectHistory();
                    $defectHistory->defect_id = $newDefect->id;
                    $defectHistory->type = "system";
                    $defectHistory->comments = "created defect";
                    $defectHistory->created_by = $this->user_id;
                    $defectHistory->updated_by = $this->user_id;
                    $defectHistory->save();

                    $defect->defect_id = $newDefect->id;
                }
            }
        }
        else if($defect->selected == "no"){
            if(isset($defect->defect_id) && trim($defect->defect_id) != "") // Update existing Defect
            {
            	$defectId = $defect->defect_id;
                Defect::destroy($defectId);

                $defect->imageString = "";
                $defect->comments = "";
                $defect->textString = "";
                unset($defect->defect_id);
            }
        }
        return $defect;
    }

    private function addDefectImage($newDefect, $defect, $update=false)
    {
        if ($defect->_image == "yes" || $defect->imageString !== "")
        {
            $temp_id_string = $defect->imageString;
            $temp_ids = [];
            if (!empty($temp_id_string)) {
                $temp_ids = explode("|", $temp_id_string);
            }

            foreach ($temp_ids as $temp_id) {
                /**
                 * Check if temp id already exists in media table
                 *
                 * This is necessary when the risk assessment is updated, the temp_id of the unchanged 
                 * images would already have been processed in the previous risk assessment.
                 */
                $media_record = Media::where('name', $temp_id)->where('model_type', Defect::class)->first();  
                if ($media_record) {
                    \Log::info('already uploaded and processed image');
                    \Log::info($media_record);
                    //$newDefect->imageString = getPresignedUrl($media_record);
                    //$defect->imageString = getPresignedUrl($media_record);
                    $defect->imageString = str_replace($temp_id, getPresignedUrl($media_record), $defect->imageString);
                }
                else{
                    // check if temporary image id already exists in temporary image table
                    $tempImage = TemporaryImage::where('temp_id', $temp_id)->first(); 
                    if ($tempImage) {
                        // corresponding image has already been uploaded, simply update 
                        // the model id and model type in media table to reflect the defect id
                        \Log::info('found image form temp id in temporary_images table');
                        \Log::info($tempImage);

                        $media = $tempImage->getMedia()->first();
                        \Log::info('$media');
                        \Log::info($media);
                        if (!$media) {
                            return $this->response->error('There was an error while saving the image.', 404);
                        }
                        $media->model_id = $newDefect->id;
                        $media->model_type = Defect::class;
                        $media->save();                      
                        //$newDefect->imageString = getPresignedUrl($media);
                        //$defect->imageString = getPresignedUrl($media);
                        $defect->imageString = str_replace($temp_id, getPresignedUrl($media), $defect->imageString);
                        // remove TemporaryImage model
                    }
                    else {
                        //add an entry in temporary images table without adding image.
                        \Log::info('NO image found for temp id in temporary_images table');                
                        $tempImage = new TemporaryImage();
                        $tempImage->model_id = $newDefect->id;
                        $tempImage->model_type = Defect::class;
                        $tempImage->temp_id = $temp_id;
                        $tempImage->save();
                        \Log::info('Added row in temporary images table');
                        \Log::info($tempImage);               
                        
                    }
                }
            }
        }
    }
    /*private function addDefectImage($newDefect, $defect, $update=false)
    {
        if ($defect->_image == "yes" || $defect->imageString !== "")
        {
            $temp_id = $defect->imageString;

            /**
             * Check if temp id already exists in media table
             *
             * This is necessary when the risk assessment is updated, the temp_id of the unchanged 
             * images would already have been processed in the previous risk assessment.
             
            $media_record = Media::where('name', $temp_id)->where('model_type', Defect::class)->first();  
            if ($media_record) {
                \Log::info('already uploaded and processed image');
                \Log::info($media_record);
                //$newDefect->imageString = getPresignedUrl($media_record);
                $defect->imageString = getPresignedUrl($media_record);
            }
            else{
                // check if temporary image id already exists in temporary image table
                $tempImage = TemporaryImage::where('temp_id', $temp_id)->first(); 
                if ($tempImage) {
                    // corresponding image has already been uploaded, simply update 
                    // the model id and model type in media table to reflect the defect id
                    \Log::info('found image form temp id in temporary_images table');
                    \Log::info($tempImage);

                    $media = $tempImage->getMedia()->first();
                    \Log::info('$media');
                    \Log::info($media);
                    if (!$media) {
                        return $this->response->error('There was an error while saving the image.', 404);
                    }
                    $media->model_id = $newDefect->id;
                    $media->model_type = Defect::class;
                    $media->save();
                  
                    //$newDefect->imageString = getPresignedUrl($media);
                    $defect->imageString = getPresignedUrl($media);
                    // remove TemporaryImage model
                }
                else {
                    //add an entry in temporary images table without adding image.
                    \Log::info('NO image found for temp id in temporary_images table');                
                    $tempImage = new TemporaryImage();
                    $tempImage->model_id = $newDefect->id;
                    $tempImage->model_type = Defect::class;
                    $tempImage->temp_id = $temp_id;
                    $tempImage->save();
                    \Log::info('Added row in temporary images table');
                    \Log::info($tempImage);               
                    
                }
            }



            
        }
    }*/

/*    private function addDefectImage($newDefect, $defect, $update=false)
    {
        if ($defect->_image == "yes" || $defect->imageString !== "")
        {
        	if (strpos($defect->imageString, 'http') === 0) {
        	}
        	else
        	{
				if($update){
	        		$media = $newDefect->getMedia();
	        		$media[0]->delete();
				}        			
	            $imageString = $defect->imageString;
                
                // Fetch Exif data.
                $dateTaken="";
                $lat = "";
                $long = "";
                $imei = "";
                if(isset($defect->imei) && !empty($defect->imei)){
                    $imei = $defect->imei;
                }
                if(isset($defect->image_exif) && !empty($defect->image_exif)){
                    $imageExifData = explode(';', $defect->image_exif);
                    $dateTaken = $imageExifData[0] . ";";
                    $lat = (!empty($imageExifData[1]))?number_format((double)$imageExifData[1],6) . ";":"";
                    $long = (!empty($imageExifData[2]))?number_format((double)$imageExifData[2],6) . ";":"";
                }

	            $ext = ".png";
	            $mimeType = "image/png";
	            if (strpos($imageString, 'data:') === 0) {
	                list($ext, $data) = explode(',', $imageString, 2);
					if (preg_match("/data:(.*);base64/", $ext, $matches)) {
						$mimeType =  $matches[1]; 
					}
	                $ext = str_replace('data:image/', '', $ext);
	                $ext = str_replace(';base64', '', $ext);
	                $ext = ".".$ext;
	                $imageString = $data;
	            }
                $localImgFileName = $newDefect->id . "_" . $this->check_id . "_local" . $ext;
	            $imgFileName = $newDefect->id . "_" . $this->check_id . $ext;
                $outputFile = storage_path('app') . '/' . $imgFileName;
	            Storage::put($localImgFileName, base64_decode($imageString)); 
                $inputFile = storage_path('app') . '/' . $localImgFileName;
                // resize image to standard size
                $img = Image::make($inputFile);                
                $img->resize(640, 640, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($outputFile);
                Storage::delete($localImgFileName); 
                $overlayOnImage = env('OVERLAY_ON_IMAGE', true);
                if($overlayOnImage){
                    $userInfo = User::find($newDefect->created_by);
                    if (preg_match('#^image/(jpe?g|png|gif)$#i', $mimeType)) {
                        list($width, $height, $type) = getimagesize(storage_path('app').'/'.$imgFileName);
                        switch($type) {
                            case IMAGETYPE_GIF:
                                $overlayImage = imagecreatefromgif(storage_path('app').'/'.$imgFileName);
                                break;
                            case IMAGETYPE_JPEG:
                                $overlayImage = imagecreatefromjpeg(storage_path('app').'/'.$imgFileName);
                                break;
                            case IMAGETYPE_PNG:
                                $overlayImage = imagecreatefrompng(storage_path('app').'/'.$imgFileName);
                                break;
                            default:
                                return false;
                                break;
                        }
                    }
                    // $overlayImage = imagecreatefrompng(storage_path('app').'/'.$imgFileName);
                    $boxColor = imagecolorallocatealpha($overlayImage, 0, 0, 0, 50);
                    imagefilledrectangle($overlayImage, 0, 0, imagesx($overlayImage), 45, $boxColor);
                    $white = imagecolorallocate($overlayImage, 255, 255, 255);
                    $font_path = public_path().'/fonts/atlantabook.ttf';
                    $text = "$newDefect->id;  $dateTaken  $lat  $long";
                    $textIMEI = "$userInfo->email";                    
                    // $textIMEI = "$userInfo->email; imei: $imei";                    
                    imagettftext($overlayImage, 11, 0, 10, 20, $white, $font_path, $text);
                    imagettftext($overlayImage, 11, 0, 10, 40, $white, $font_path, $textIMEI);
                    
                    switch($type) {
                        case IMAGETYPE_GIF:
                            imagegif($overlayImage, storage_path('app').'/'.$imgFileName);
                            break;
                        case IMAGETYPE_JPEG:
                            imagejpeg($overlayImage, storage_path('app').'/'.$imgFileName);
                            break;
                        case IMAGETYPE_PNG:
                            imagepng($overlayImage, storage_path('app').'/'.$imgFileName);
                            break;
                        default:
                            return false;
                            break;
                    }                    
                }
	            $newDefect->addMedia(storage_path('app').'/'.$imgFileName)
	            	->withCustomProperties(['mime-type' => $mimeType, 'created-date'=>$dateTaken, 'latitude'=>$lat, 'longitude'=>$long])
	            	->toCollectionOnDisk('defects', 'S3_uploads');

	            $media = $newDefect->getMedia();
	            $mediaUrl = getPresignedUrl($media[0]);
	            // Log::info("========================================================================");
	            // Log::info($mediaUrl);
	            // Log::info("========================================================================");
	            $defect->imageString = $mediaUrl;
        	}
        }
    }*/

}
?>
