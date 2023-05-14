<?php
namespace App\Traits;

use Storage;
use Image;
use Mail;
use DB;
use App\Models\User;
use App\Models\Role;
use Spatie\MediaLibrary\Media;
// use App\Models\Media;
use StdClass;
use Carbon\Carbon;
use App\Models\Check;
use App\Models\Defect;
use App\Models\Survey;
use App\Models\DefectHistory;
use App\Models\TemporaryImage;

trait ProcessCheckJson
{
    protected function processJson($json, $check, $reportDateTime = null)
    {
        // \Log::info("inside processJson");
        $isTrailerAttached = "no";
        $trailerReferenceNumber = null;
        $check_json = json_decode($json);
        $check_json->total_defect = 0;
        $check_json->preexisting_defect = 0;
        $processedJson = new StdClass;
        // Set screen related data to be saved in the check
        $processedJson->status = $check_json->status;
        $processedJson->screens = new StdClass;
        $processedJson->screens->screen = [];
        $processedJson->total_defect = $check_json->total_defect;   


        foreach ($check_json->screens->screen as $screen) {

            // \Log::info("inside foreach");
            $processedScreen = new StdClass;
            $processedScreen->_number = $screen->_number;
            $processedScreen->_type = $screen->_type;
            $processedScreen->answer = isset($screen->answer) ? $screen->answer : "";
            $processedScreen->defect_count = 0;
            $processedScreen->prohibitional_defect_count = 0;
            $processedScreen->defects = new StdClass;
            $processedScreen->defects->defect = [];
            $processedScreen->options = new StdClass;
            $processedScreen->options->optionList = [];

            if($screen->_type == "confirm_with_input") {
                $isTrailerAttached = $screen->answer;
                $processedScreen->input_answer = $trailerReferenceNumber = ($screen->input_answer ? $screen->input_answer : null);
            }
            if ($screen->_type == "yesno") {
                $screen->defect_count = 0;
                $screen->prohibitional_defect_count = 0;
                if(isset($screen->defects->defect)) {
                    foreach ($screen->defects->defect as $defect) {
                        $processedDefect = new StdClass;
                        if(isset($defect->read_only) && trim($defect->read_only) !=""){
                            $check_json->preexisting_defect = $check_json->preexisting_defect + 1;
                        }
                        $dynamicDefectId = isset($screen->defects->dynamic_defect_id) ? $screen->defects->dynamic_defect_id : "";
                        $defect = $this->processDefect($defect, $reportDateTime, $trailerReferenceNumber,$dynamicDefectId);
                        // Set defect related data to be saved in the check
                        $processedDefect->id = $defect->id;
                        $processedDefect->imageString = $defect->imageString;
                        $processedDefect->image_exif = $defect->image_exif;
                        // $processedDefect->safety_notes = $defect->safety_notes;
                        $processedDefect->selected = $defect->selected;
                        if (isset($defect->defect_id)) {
                            $processedDefect->defect_id = $defect->defect_id;
                        }

                        if($defect->selected == "yes"){
                            if( $defect->prohibitional == "yes" ){
                                $screen->prohibitional_defect_count = $screen->prohibitional_defect_count + 1;
                            }
                            else{
                                $screen->defect_count = $screen->defect_count + 1;
                            }
                            $processedScreen->prohibitional_defect_count = $screen->prohibitional_defect_count;
                            $processedScreen->defect_count = $screen->defect_count;
                            $check_json->total_defect = $check_json->total_defect + 1;
                        }
                        // Push processed defect to the processed screen object
                        array_push($processedScreen->defects->defect, $processedDefect);
                    } 
                }
            }
            if ($screen->_type == "list") {
                // \Log::info("screen type list");
                foreach ($screen->options->optionList as $option) {
                    
                    $processedOption = new StdClass;
                    // $processedOption->text = $option->text;
                    $processedOption->defects = new StdClass;
                    $processedOption->defects->defect = [];
                    $processedOption->defect_count = 0;
                    $processedOption->prohibitional_defect_count = 0;
                    $option->defect_count = 0;
                    $option->prohibitional_defect_count = 0;

                    if (!isset($option->question_type) || $option->question_type == "yesno") {
                        if(isset($option->defects->defect)) {
                            foreach ($option->defects->defect as $defect) {
                                $processedOptionListDefect = new StdClass;
                                if(isset($defect->read_only) && trim($defect->read_only) !=""){
                                    $check_json->preexisting_defect = $check_json->preexisting_defect + 1;
                                }
                                $dynamicDefectId = isset($option->defects->dynamic_defect_id) ? $option->defects->dynamic_defect_id : "";
                                $defect = $this->processDefect($defect, $reportDateTime, $trailerReferenceNumber,$dynamicDefectId);
                                $processedOptionListDefect->id = $defect->id;
                                $processedOptionListDefect->imageString = $defect->imageString;
                                $processedOptionListDefect->image_exif = $defect->image_exif;
                                // $processedOptionListDefect->safety_notes = $defect->safety_notes;
                                $processedOptionListDefect->selected = $defect->selected;
                                if (isset($defect->defect_id)) {
                                    \Log::info('Set defect id for' . $defect->defect_id);
                                    $processedOptionListDefect->defect_id = $defect->defect_id;
                                }

                                if($defect->selected == "yes"){
                                    if( $defect->prohibitional == "yes" ){
                                        $option->prohibitional_defect_count = $option->prohibitional_defect_count + 1;
                                    }
                                    else{
                                        $option->defect_count = $option->defect_count + 1;
                                    }
                                    $check_json->total_defect = $check_json->total_defect + 1;
                                }                        
                                // Push processed defect to the processed screen object
                                array_push($processedOption->defects->defect, $processedOptionListDefect);
                            } 
                        }
                    }
                    $processedOption->prohibitional_defect_count = $option->prohibitional_defect_count;
                    $processedOption->defect_count = $option->defect_count;
                    array_push($processedScreen->options->optionList, $processedOption);

                    if (isset($option->question_type) && $option->question_type == "media") {
                        if(isset($option->images)) {
                            $this->addInformationImage($option, $check);
                        }
                    }
                    if (isset($option->question_type) && $option->question_type == "dropdown") {
                        $processedOption->dropdowns = $option->dropdowns;
                    }
                    if (isset($option->question_type) && $option->question_type == "multiinput") {
                        $processedOption->inputs = $option->inputs;
                    }
                    if (isset($option->question_type) && $option->question_type == "media_based_on_selection") {
                        if(isset($option->images)) {
                            $this->addInformationImage($option, $check);
                        }
                        $processedOption->media_label_text = $option->media_label_text;
                        $processedOption->media_dependent_on_answer = $option->media_dependent_on_answer;
                    }
                }
            }
            if ($screen->_type == "multiselect") {
                // \Log::info("screen type mutiselect");
                foreach ($screen->options->optionList as $option) {
                    
                    $processedOption = new StdClass;
                    $processedOption->text = $option->text;
                    $processedOption->answer = $option->answer;
                    array_push($processedScreen->options->optionList, $processedOption);   
                }
            }
            if ($screen->_type == "media") {
                if(isset($screen->images)) {
                    $this->addInformationImage($screen, $check);
                }
            }
            if ($screen->_type == "dropdown") {
                $processedScreen->dropdowns = $screen->dropdowns;
            }
            if ($screen->_type == "multiinput") {
                $processedScreen->inputs = $screen->inputs;
            }
            if ($screen->_type == "media_based_on_selection") {
                $processedScreen->media_label_text = $screen->media_label_text;
                $processedScreen->media_dependent_on_answer = $screen->media_dependent_on_answer;
                if(isset($screen->images)) {
                    $this->addInformationImage($screen, $check);
                }
            }
            // Push processed screen to processedJson object
            array_push($processedJson->screens->screen, $processedScreen);
        }
        $processedJson->total_defect = $check_json->total_defect;
        $processedJson->preexisting_defect = $check_json->preexisting_defect;
        return ['json' => json_encode($check_json), 'is_trailer_attached' => $isTrailerAttached, 'trailer_reference_number' => $trailerReferenceNumber];
    }

    protected function processDefect($defect, $reportDateTime, $trailerReferenceNumber,$dynamicDefectId = "")
    {
        // \Log::info("inside processDefects");
        if($defect->selected == "yes")
        {
            if(isset($defect->read_only) && trim($defect->read_only) !="" && $defect->read_only)
            {
                $defect->selected = "no";
                $defect->imageString = "";
                $defect->image_exif = "";
                $defect->textString = "";
                unset($defect->read_only);

                if( (!isset($defect->defect_id) || $defect->defect_id == null || $defect->defect_id == '') && isset($defect->temp_id) && $defect->temp_id !== '') {
                    $existingDefect = Defect::where('temp_id', $defect->temp_id)->first();
                    if($existingDefect) {
                        $defect->defect_id = $existingDefect->id;
                    }
                }
                // unset($defect->defect_id);
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
                    if(isset($defect->temp_id) && $defect->temp_id !== '') {
                        $newDefect->temp_id = $defect->temp_id;
                    }

                    if ($dynamicDefectId != "" && $defect->id == $dynamicDefectId) {
                        $newDefect->title = $defect->text;
                    }
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
                    if($reportDateTime) {
                        $newDefect->report_datetime = $reportDateTime;
                    }
                    $newDefect->save();
                    $this->addDefectImage($newDefect, $defect);   
                    
                    ///Logic to mark defects as duplicate
                    if ( !($dynamicDefectId != "" && $defect->id == $dynamicDefectId) ) {
                        $defectlist = Defect::where(['vehicle_id'=>$this->vehicle_id,'defect_master_id'=>$defect->id])->whereNotIn('status',['Resolved'])->get();
                        if ($defectlist->count() > 1) {
                            $duplicateDefectListIds = $defectlist->pluck('id');
                            Defect::whereIn('id',$duplicateDefectListIds)->update(['duplicate_flag'=>true]);
                        }
                    }
                    ////

                    $defectHistory = new DefectHistory();
                    $defectHistory->defect_id = $newDefect->id;
                    $defectHistory->type = "system";
                    $defectHistory->comments = "created defect";
                    $defectHistory->created_by = $this->user_id;
                    $defectHistory->updated_by = $this->user_id;
                    if($reportDateTime) {
                        $defectHistory->report_datetime = $reportDateTime;
                    }
                    $defectHistory->save();

                    if($trailerReferenceNumber) {
                        $defectHistory = new DefectHistory();
                        $defectHistory->defect_id = $newDefect->id;
                        $defectHistory->type = "system";
                        $defectHistory->comments = 'defect associated with trailer with reference number "'. $trailerReferenceNumber . '"';
                        $defectHistory->created_by = $this->user_id;
                        $defectHistory->updated_by = $this->user_id;
                        if($reportDateTime) {
                            $defectHistory->report_datetime = $reportDateTime;
                        }
                        $defectHistory->save();
                    }

                    $defect->defect_id = $newDefect->id;

                    $defectEmailNotification = User::with('regions')->whereHas('roles', function ($query) {
                                                    $query->where('name', '=', 'Defect email notifications');
                                                })->get();
                    
                    $defectEmail = [];
                    $defectFirstName = [];
                    $Roles = Role::where('name','Defect email notifications')->first();                    
                    $link = config('app.url').'/defects/'.$newDefect->id;
                    $settings = DB::table('settings')->where('key','defect_email_notification')->first();
                    $settings = json_decode(json_encode($settings),true);
                                         
                    if($settings['key'] == 'defect_email_notification' && $settings['value'] == 1) {    
                        $registrationNumber = json_decode($newDefect,true);
                        $vehicleRegistrationNumber = Defect::where('status','=','Reported')->where('id',$registrationNumber['id'])->with('vehicle')->first()->toArray();
                        $defectsStatus = $vehicleRegistrationNumber['status'];
                        $registration = $vehicleRegistrationNumber['vehicle']['registration'];
                        $vehicleRegionId = $vehicleRegistrationNumber['vehicle']['vehicle_region_id'];

                        $defectEmailNotification->each(function ($item, $key) use(&$defectEmail, &$defectFirstName, &$link, &$registration, &$vehicleRegionId, &$defectsStatus, &$registrationNumber) {   
                            $userRegionsIds = $item->regions->lists('id')->toArray();
                            
                            if (count($userRegionsIds) > 0 && in_array($vehicleRegionId, $userRegionsIds)) {
                                if (filter_var($item->email, FILTER_VALIDATE_EMAIL)) {
                                    Mail::queue('emails.defect_reported', ['userName' => $item->first_name, 'emailAddress' => $item->email, 'link' => $link, 'registration' => $registration,'defectsStatus' => $defectsStatus], function ($message) use ($item, &$link, $registration) {
                                    $message->to($item->email, $item->first_name, $link, $registration);

                                        $message->subject('fleetmastr - defect notification '.$registration);
                                    });
                                }
                            }
                        });
                    }
                }
            }
        }
        
        return $defect;
    }

    private function addDefectImage($newDefect, $defect, $update=false)
    {
        // \Log::info("inside addDefectImage");
        if ($defect->_image == "yes" || $defect->imageString !== "")
        {
            if (starts_with($defect->imageString, 'data:image')) {
                $this->addDefectImageFromString($newDefect, $defect, $update);
                return;                
            }
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
                    // \Log::info('already uploaded and processed image');
                    // \Log::info($media_record);
                    //$newDefect->imageString = $media_record->getUrl();
                    //$defect->imageString = $media_record->getUrl();
                    $defect->imageString = str_replace($temp_id, $media_record->getUrl(), $defect->imageString);
                }
                else{
                    // check if temporary image id already exists in temporary image table
                    \Log::info("Process json: check for tempid" . $temp_id);
                    $tempImage = TemporaryImage::where('temp_id', $temp_id)->first(); 
                    if ($tempImage) {
                        // corresponding image has already been uploaded, simply update 
                        // the model id and model type in media table to reflect the defect id
                        // \Log::info('found image form temp id in temporary_images table');
                        // \Log::info($tempImage);

                        $media = $tempImage->getMedia()->first();
                        // \Log::info('$media');
                        // \Log::info($media);
                        if (!$media) {
                            return $this->response->error('There was an error while saving the image.', 404);
                        }
                        $media->model_id = $newDefect->id;
                        $media->model_type = Defect::class;
                        $media->save();                      
                        //$newDefect->imageString = $media->getUrl()
                        //$defect->imageString = $media->getUrl()
                        $defect->imageString = str_replace($temp_id,$media->getUrl(), $defect->imageString);
                        // remove TemporaryImage model
                    }
                    else {
                        //add an entry in temporary images table without adding image.
                        \Log::info('NO image found for temp id in temporary_images table - binding with model');
                        $tempImage = new TemporaryImage();
                        $tempImage->model_id = $newDefect->id;
                        $tempImage->model_type = Defect::class;
                        $tempImage->temp_id = $temp_id;
                        $tempImage->save();
                        // \Log::info('Added row in temporary images table');
                        // \Log::info($tempImage);               
                        
                    }
                }
            }
        }
    }

    /**
     * This function will be called when we get the image in base64 encoded
     * string. For eg. when the user uploads image from the checks page
     * on the desktop site.
     *  
     * @param mixed  $newDefect The new defect model object.
     * @param mixed  $defect    The current defect model object.
     * @param boolean $update   Whether the defect is getting updated or created.
     */
    private function addDefectImageFromString($newDefect, $defect, $update=false) {
        // \Log::info('addDefectImageFromString');
        // If the string starts with http, the image is already uploaded, no need to process
        if ($update) {
            $media = $newDefect->getMedia();
            $media[0]->delete();
        }
        $imageString = $defect->imageString;            
        // Fetch Exif data.
        $dateTaken="";
        $lat = "";
        $long = "";
        $imei = "";
        $ext = ".png";
        $mimeType = "image/png";

        list($ext, $data) = explode(',', $imageString, 2);
        if (preg_match("/data:(.*);base64/", $ext, $matches)) {
            $mimeType =  $matches[1]; 
        }

        $ext = str_replace('data:image/', '', $ext);
        $ext = str_replace(';base64', '', $ext);
        $ext = "." . $ext;
        $imageString = $data;
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
        // \Log::info('resized image and saved to');
        Storage::delete($localImgFileName); 
        $overlayOnImage = env('OVERLAY_ON_IMAGE', true);
        $userInfo = User::find($newDefect->created_by);
        // \Log::info('$mimeType');
        if (preg_match('#^image/(jpe?g|png|gif)$#i', $mimeType)) {
            // \Log::info('matched mimetype');
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
        imagefilledrectangle($overlayImage, 0, 0, imagesx($overlayImage), 60, $boxColor);
        $white = imagecolorallocate($overlayImage, 255, 255, 255);
        $font_path = public_path() . '/fonts/DroidSansMono.ttf';
        $text = Carbon::now('Europe/London')->format('H:i d M Y');
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

        $newDefect->addMedia(storage_path('app').'/'.$imgFileName)
            ->withCustomProperties(['mime-type' => $mimeType, 'created-date'=>$dateTaken])
            ->toCollectionOnDisk('defects', 'S3_uploads');

        $media = $newDefect->getMedia();
        $mediaUrl = $media[0]->getUrl();
        $defect->imageString = $mediaUrl;               
    }


    public function recreateCheckJson($check)
    {
        // \Log::info("recreateCheckJson");
        $action = [
            'Return Check' => 'checkin',
            'Vehicle Check' => 'checkout',
            'Defect Report' => 'defect',
        ];
        // \Log::info('check this');
        // \Log::info($check->vehicle->type->vehicle_category);
        // TODO: Check for parcel van
        $survey_ques = Survey::where('vehicle_category',$check->vehicle->type->vehicle_category)
            ->where('action',$action[$check->type])
            ->select('id','vehicle_type')
            ->get();

        $survey_ques_id = 0;
        foreach ($survey_ques as $survey_que) {
            $vtypeArr = explode(',', $survey_que->vehicle_type);
            if(in_array($check->vehicle->type->id, $vtypeArr)){
                $survey_ques_id = $survey_que->id;
            }
        }

        $survey = Survey::find($survey_ques_id);
        // \Log::info('found below json');
        // \Log::info($survey->screen_json);
        $json = json_decode($check->json);
        $recreatedJson = json_decode($survey->screen_json);

        $recreatedJson->status = $json->status;
        $recreatedJson->total_defect = $json->total_defect;         

        foreach ($recreatedJson->screens->screen as $currentScreenIndex => $screen) {
            // \Log::info('$currentScreenIndex');
            // \Log::info($currentScreenIndex);
            $jsonScreen = $json->screens->screen[$currentScreenIndex];
            $screen->answer = $jsonScreen->answer;
            $screen->defect_count = (isset($jsonScreen->defect_count)) ? $jsonScreen->defect_count : 0;            
            $screen->prohibitional_defect_count = (isset($jsonScreen->prohibitional_defect_count)) ? $jsonScreen->prohibitional_defect_count : 0;

            if ($screen->_type == "yesno") {
                foreach ($screen->defects->defect as $currentDefectIndex => $defect) {
                    $jsonDefect = $jsonScreen->defects->defect[$currentDefectIndex];
                    $defect->id = $jsonDefect->id;
                    $defect->imageString = $jsonDefect->imageString;
                    $defect->image_exif = $jsonDefect->image_exif;
                    $defect->selected = $jsonDefect->selected;
                    if (isset($jsonDefect->defect_id)) {
                        $defect->defect_id = $jsonDefect->defect_id;
                    }
                }
            }

            if ($screen->_type == "list") {
                // \Log::info('start');
                // \Log::info(json_encode($screen));
                // \Log::info('end');
                foreach ($screen->options->optionList as $currentOptionIndex => $option) {
                    // \Log::info('$currentOptionIndex');
                    // \Log::info($currentOptionIndex);
                    $jsonOption = $jsonScreen->options->optionList[$currentOptionIndex];
                    $option->defect_count = $jsonOption->defect_count;
                    $option->prohibitional_defect_count = $jsonOption->prohibitional_defect_count;
                    foreach ($option->defects->defect as $currentDefectIndex => $defect) {
                        // \Log::info('$currentDefectIndex');
                        // \Log::info($currentDefectIndex);
                        $jsonDefect = $jsonOption->defects->defect[$currentDefectIndex];
                        $defect->id = $jsonDefect->id;
                        $defect->imageString = $jsonDefect->imageString;
                        $defect->image_exif = $jsonDefect->image_exif;
                        $defect->selected = $jsonDefect->selected;
                        if (isset($jsonDefect->defect_id)) {
                            $defect->defect_id = $jsonDefect->defect_id;
                        }
                    }
                }
            }

            if ($screen->_type == "multiselect") {
                foreach ($screen->options->optionList as $currentOptionIndex => $option) {
                    $jsonOption = $jsonScreen->options->optionList[$currentOptionIndex];
                    $option->text = $jsonOption->text;
                    $option->answer = $jsonOption->answer;
                }
            }
        }        
        return json_encode($recreatedJson);
    }

    private function addInformationImage($option, $check)
    {
        $temp_id_string = $option->images;
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
            $media_record = Media::where('name', $temp_id)->where('model_type', Check::class)->first();  
            if ($media_record) {
                $option->images = str_replace($temp_id, $media_record->getUrl(), $option->images);
            }
            else{
                // check if temporary image id already exists in temporary image table
                \Log::info("Process json: check for tempid" . $temp_id);
                $tempImage = TemporaryImage::where('temp_id', $temp_id)->first(); 
                if ($tempImage) {
                    $media = $tempImage->getMedia()->first();
                    if (!$media) {
                        return $this->response->error('There was an error while saving the image.', 404);
                    }
                    $media->model_id = $check->id;
                    $media->model_type = Check::class;
                    $media->save();
                    $option->images = str_replace($temp_id,$media->getUrl(), $option->images);
                    // remove TemporaryImage model
                }
                else {
                    //add an entry in temporary images table without adding image.
                    \Log::info('NO image found for temp id in temporary_images table - binding with model');
                    $tempImage = new TemporaryImage();
                    $tempImage->model_id = $check->id;
                    $tempImage->model_type = Check::class;
                    $tempImage->temp_id = $temp_id;
                    $tempImage->save();
                }
            }
        }
    }
}
?>
