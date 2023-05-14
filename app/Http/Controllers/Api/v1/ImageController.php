<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\TemporaryImage;
use Illuminate\Http\Request;

use App\Http\Requests;
use PhpSpec\Exception\Exception;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ImageUploadRequest;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ImageController extends APIController
{
    public function uploadMedia(ImageUploadRequest $request)
    {

        \Log::info('Received image upload request');
        \Log::info($request->except(['image_string']));
        \Log::info(substr($request->input('image_string'), 0, 40));

        $categories = [
            'vehicle' => \App\Models\Vehicle::class,
            'survey' => \App\Models\Survey::class,
            'check' => \App\Models\Check::class,
            'defect' => \App\Models\Defect::class,
            'incident' => \App\Models\Incident::class,
            'defecthistory' => \App\Models\DefectHistory::class,
        ];

        $temp_id = $request->get('temp_id');
        $contents = $request->get('image_string');
        $exif = $request->get('image_exif');
        $ref_model_id = $request->get('ref_model_id');
        $category = $request->get('category');
        $email = $request->input('email');
        $reference_id = strtoupper($request->input('reference_id'));
        $defectHistoryImageRelatesTo = $request->get('relates_to');
        //$stage = $request->input('stage');

        $pos  = strpos($contents, ';');
        $type = explode(':', substr($contents, 0, $pos))[1];

        $custom_properties = [
            'image-exif' => $exif,
            'mime-type' => $type
            //'stage' => $stage
        ];
        if ($ref_model_id) {
            $custom_properties['ref_model_id'] = $ref_model_id;
        }

        if($defectHistoryImageRelatesTo) {
            $custom_properties['relates-to'] = $defectHistoryImageRelatesTo;
        }

        \Log::info('upload image starts for tempp_id ' . $temp_id);

        if (! starts_with($contents, 'data:image/')) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Invalid image data.');
        }

        // check if temporary image id already exists in table
        $tempImage = TemporaryImage::where('temp_id', $temp_id)->first();        
        if ($tempImage) {
            \Log::info('found image form temp id in temporary_images table');
            \Log::info($tempImage);
            //echo "<pre>";print_r($tempImage->model_type);echo "</pre>";exit;
            $model = call_user_func(array($tempImage->model_type, 'find'), $tempImage->model_id);            
            //echo "<pre>model";print_r($model);echo "</pre>";exit;
            // DEBUG
            if (! $model) {
                \Log::info('ALERT: CHECK THIS:');
                \Log::info('Tried to find ' . $tempImage->model_type . ' with ID ' . $tempImage->model_id );
                if($tempImage->model_id == 0){
                    \Log::info('Duplicate Image request Received with temp_id ' . $temp_id);
                    return $this->response->array([
                        'temp_id' => $temp_id,
                        'message' => "Duplicate Image Received.",
                        "status_code" => 200
                    ]);
                }
            }
            else {
                \Log::info('ALERT: CHECK THIS:');
                \Log::info('Trying to find media attached to ' . $tempImage->model_type . ' with ID ' . $tempImage->model_id );
                $checkForImage = $model->getMedia()->where('name', $temp_id)->first();
                if($checkForImage) {
                    \Log::info('Duplicate Image request Received with temp_id ' . $temp_id);
                    return $this->response->array([
                        'temp_id' => $temp_id,
                        'message' => "Duplicate Image Received.",
                        "status_code" => 200
                    ]);
                }
            }
            // DEBUG END
            
            // associate image to the actual model
            $temp_image_path = $tempImage->createImageFromString($contents, $temp_id);

            if($category !== 'defecthistory')
            {
                $temp_image_path = $tempImage->resizeImageToStandardSize($temp_image_path);
                // $image_text = implode(", ", [$reference_id, trim($exif, ","), $email]);
                list($created_date, $gps) = explode(',', trim($exif));
    	        $gps = str_replace(";",",",$gps);
                $text_line_1_array = [];
                if (isset($reference_id) && !empty($reference_id)) {
                    array_push($text_line_1_array, $reference_id);
                }
                array_push($text_line_1_array, $created_date);
                $text_line_1 = implode(", ", $text_line_1_array);
                $text_line_2 = $gps;
                $text_line_3 = $email;
                \Log::info('writing text to image ' . $text_line_1 . ' ' . $text_line_2 . ' ' . $text_line_3);
                $temp_image_path = $tempImage->createImageOverlay($temp_image_path, $text_line_1, $text_line_2, $text_line_3);

                \Log::info('createdimagefromstring returns :');
                \Log::info($temp_image_path);
                
               \Log::info('File Size : ' . filesize($temp_image_path));
               \Log::info('File Size Limit : ' . config('laravel-medialibrary.max_file_size'));
            }

            $media = $model->addMedia($temp_image_path)
                ->withCustomProperties($custom_properties)
                ->toCollectionOnDisk($category, 'S3_uploads');

            \Log::info(gettype($tempImage->model_type));
                
            // if model is defect, update the json in checks with the media url            
            if ($tempImage->model_type == 'App\Models\Defect') {
                \Log::info('inside if');
                $check = call_user_func(array(\App\Models\Check::class, 'find'), $model->check_id);
                $survey_json = json_decode($check->json);
                \Log::info('check json');
                \Log::info($check);
                $survey_array = (array)$survey_json->screens;
                foreach ($survey_array['screen'] as $survey_key => $answer) {
                    if ($answer->_type == "yesno") {
                        \Log::info("Screen Type yesno");
                        $screen = $answer;
                        if (isset($screen->defects) && isset($screen->defects->defect)) {
                            foreach ($screen->defects->defect as $defect_key => $defect) {
                                //if (isset($defect->imageString) && $defect->imageString == $temp_id) {  
                                \Log::info('comparing :' . $defect->imageString . ' with: ' .$temp_id);
                                if (isset($defect->imageString) && strpos($defect->imageString, $temp_id) !== false ) {  
                                    \Log::info('comparing :' . $defect->imageString . ' with: ' . $temp_id.' replacing with '.$media->getUrl());
                                    //$survey_json->screens->screen[$survey_key]->defects->defect[$defect_key]->imageString = $media->getUrl();
                                    $survey_json->screens->screen[$survey_key]->defects->defect[$defect_key]->imageString = str_replace($temp_id, $media->getUrl(), $survey_json->screens->screen[$survey_key]->defects->defect[$defect_key]->imageString);
                                }
                            };
                        }
                    }
                    if ($answer->_type == "list") {
                       \Log::info("Screen Type list"); 
                       if (isset($answer->options) && isset($answer->options->optionList)) {
                            foreach ($answer->options->optionList as $screen_key => $screen) {
                                if (isset($screen->defects->defect)) {
                                    foreach ($screen->defects->defect as $defect_key => $defect) {
                                        //if (isset($defect->imageString) && $defect->imageString == $temp_id) { 
                                        if (isset($defect->imageString) && strpos($defect->imageString, $temp_id) !== false ) { 
                                            \Log::info('comparing :' . $defect->imageString . ' with: ' . $temp_id.' replacing with '.$media->getUrl());
                                            //$survey_json->screens->screen[$survey_key]->options->optionList[$screen_key]->defects->defect[$defect_key]->imageString = $media->getUrl();
                                            $survey_json->screens->screen[$survey_key]->options->optionList[$screen_key]->defects->defect[$defect_key]->imageString = str_replace($temp_id, $media->getUrl(), $survey_json->screens->screen[$survey_key]->options->optionList[$screen_key]->defects->defect[$defect_key]->imageString);
                                        }
                                    }
                                }                                
                            }
                        }
                    }                    

                }
                $check->json = json_encode($survey_json);
                $check->save();
            }
            // remove TemporaryImage model

            if ($tempImage->model_type == 'App\Models\Check') {
                \Log::info('inside if');
                $check = call_user_func(array(\App\Models\Check::class, 'find'), $model->id);
                $survey_json = json_decode($check->json);
                \Log::info('check json');
                \Log::info($check);
                $survey_array = (array)$survey_json->screens;
                foreach ($survey_array['screen'] as $survey_key => $answer) {
                    if ($answer->_type == "media" || $answer->_type == "media_based_on_selection") {
                        \Log::info("Screen Type yesno");
                        $screen = $answer;
                        if (isset($screen->images)) {
                            \Log::info('comparing :' . $screen->images . ' with: ' .$temp_id);
                            if (isset($screen->images) && strpos($screen->images, $temp_id) !== false ) {  
                                \Log::info('comparing :' . $screen->images . ' with: ' . $temp_id.' replacing with '.$media->getUrl());
                                //$survey_json->screens->screen[$survey_key]->defects->defect[$defect_key]->imageString = $media->getUrl();
                                $survey_json->screens->screen[$survey_key]->images = str_replace($temp_id, $media->getUrl(), $survey_json->screens->screen[$survey_key]->images);
                            }
                        }
                    }
                    if ($answer->_type == "list") {
                       \Log::info("Screen Type list"); 
                       if (isset($answer->options) && isset($answer->options->optionList)) {
                            foreach ($answer->options->optionList as $screen_key => $screen) {
                                if (($screen->question_type == "media" || $screen->question_type == "media_based_on_selection")) {
                                    if (isset($screen->images) && strpos($screen->images, $temp_id) !== false ) { 
                                        \Log::info('comparing :' . $screen->images . ' with: ' . $temp_id.' replacing with '.$media->getUrl());
                                        $survey_json->screens->screen[$survey_key]->options->optionList[$screen_key]->images = str_replace($temp_id, $media->getUrl(), $survey_json->screens->screen[$survey_key]->options->optionList[$screen_key]->images);
                                    }
                                }                                
                            }
                        }
                    }                    

                }
                $check->json = json_encode($survey_json);
                $check->save();
            }

        }
        else {
            \Log::info('NO image found for temp id in temporary_images table');
            
            $tempImage = new TemporaryImage();
            $tempImage->model_id = 0;
            $tempImage->model_type = $categories[$category];
            $tempImage->temp_id = $temp_id;
            $tempImage->save();
            \Log::info('Added row in temporary images table');
            \Log::info($tempImage);
            // associate image to the TemporaryImage model
            $temp_image_path = $tempImage->createImageFromString($contents, $temp_id);

            if($category !== 'defecthistory')
            {
                $temp_image_path = $tempImage->resizeImageToStandardSize($temp_image_path);

                // $image_text = implode(", ", [$reference_id, trim($exif, ","), $email]);
                list($created_date, $gps) = explode(',', trim($exif));
    	        $gps = str_replace(";",",",$gps);
                $text_line_1_array = [];
                if (isset($reference_id) && !empty($reference_id)) {
                    array_push($text_line_1_array, $reference_id);
                }
                array_push($text_line_1_array, $created_date);
                $text_line_1 = implode(", ", $text_line_1_array);
                $text_line_2 = $gps;
                $text_line_3 = $email;
                \Log::info('writing text to image ' . $text_line_1 . ' ' . $text_line_2 . ' ' . $text_line_3);
                $temp_image_path = $tempImage->createImageOverlay($temp_image_path, $text_line_1, $text_line_2, $text_line_3);
                
                \Log::info('createdimagefromstring returns :');
                \Log::info($temp_image_path);
    	        \Log::info('File Size : ' . filesize($temp_image_path));
    	        \Log::info('File Size Limit : ' . config('laravel-medialibrary.max_file_size'));
            }

            $media = $tempImage->addMedia($temp_image_path)
                ->withCustomProperties($custom_properties)
                ->toCollectionOnDisk($category, 'S3_uploads');
            \Log::info('Added row in media table');
            \Log::info($media);
        }
        \Log::info('upload image ends at url ' . $media->getUrl());
        return $this->response->array([
            'temp_id' => $temp_id,
            'media_url' => getPresignedUrl($media),
            'message' => "Image has been saved successfully",
            "status_code" => 200
        ]);
    }
}
