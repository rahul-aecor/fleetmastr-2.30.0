<?php

namespace App\Http\Controllers;

use Auth;
use File;
use Storage;
use App\Http\Requests;
use App\Models\Template;
use App\Models\TemporaryImage;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\MediaLibrary\Media as Media;

class TemplatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $templates = Template::with(['groups.users', 'users', 'userdivisions.users'])->get();
        foreach($templates as $template) {
            $media = $template->getMedia();
            $template->attachment_docs = $media;
            foreach($template->attachment_docs as $key => $attachment) {
                $template->attachment_docs[$key]->filesize_for_display = $attachment->getHumanReadableSizeAttribute();
            }
        }
        return response()->json($templates);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $template = new Template();
        $template->name = htmlspecialchars_decode($request->name);
        $template->content = htmlspecialchars_decode($request->content);
        $template->type = $request->type;
        $template->priority = $request->priority;
        $template->surveys = $request->surveys;
        $template->questions = $request->questions;
        $template->standard_message = $request->standard_message;
        $template->acknowledgement_message = $request->type == 'standard' ? $request->acknowledgement_message : null;
        $template->is_acknowledgement_required = $request->type == 'standard' ? $request->is_acknowledgement_required : 0;
        $template->created_by = Auth::id();
        $template->save();

        if(isset($request->attachments) && $request->attachments != '') {
            $tempIds = explode(",", $request->attachments);
            $this->uploadAttachmentDocs($tempIds, $template);
        }

        $group_ids = array_pluck($request->groups, 'id');
        $user_ids = array_pluck($request->users, 'id');
        $divisions_ids = array_pluck($request->userdivisions, 'id');
        $template->groups()->sync($group_ids);    
        $template->users()->sync($user_ids);
        $template->userdivisions()->sync($divisions_ids);
        
        return $template; 
    }

    /**
     * Upload the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request) 
    {
        \Log::info('received upload processing');
        $tmpPath = storage_path('temporary');
        $uploadPath = storage_path('uploads');

        $request = new \Flow\Request();
        $config = new \Flow\Config(array(
            'tempDir' => $tmpPath
        ));
        $file = new \Flow\File($config, $request);
        $destinationFile = $uploadPath . DIRECTORY_SEPARATOR . $request->getFileName();

        if ($file->validateChunk()) {
            \Log::info('Trying to save the chunk');
            $file->saveChunk();
        }
        else {
            \Log::info('Invalid chunk');
            return response([
                'message' => 'An error occurred',
            ], 400);
        }
        if ($file->validateFile() && $file->save($destinationFile)) {
            \Log::info('Final file saved');
            \Log::info($destinationFile);            
            $ext = File::extension($destinationFile);
            $file_mime = File::mimeType($destinationFile);
            $localImgFileName = date("Ymdhis")  . '.' . $ext;
            // Uploading to S3
            $result = Storage::disk('S3_uploads')->getDriver()->put($localImgFileName, fopen($destinationFile, 'r+'), ['ContentType' => $file_mime]); 
            \Log::info('$result ' . $result);
            // Delete the local file
            File::delete($destinationFile);

            return response([
                'message' => 'OK',
                'url' => getenv('S3_DOMAIN_NAME').'/'.$localImgFileName,
                'imagename' => $localImgFileName
            ], 200);
        }

        return response([
            'message' => 'OK',
        ], 200);
    }

    /**
     * upload the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function uploadQuestionImage(Request $request)
    {
        \Log::info('received image/video upload request');
        \Log::info($request->all());
        \Log::info($_FILES);

        $imageString = $request['imageString'];
        $ext = ".png";
        $mimeType = "image/png";
        \Log::info($imageString);
        if (strpos($imageString, 'data:') === 0) {
            list($ext, $data) = explode(',', $imageString, 2);
            if (preg_match("/data:(.*);base64/", $ext, $matches)) {
                $mimeType =  $matches[1]; 
            }
            
            $ext = str_replace('data:image/', '', $ext);
            $ext = str_replace('data:video/', '', $ext);
            $ext = str_replace(';base64', '', $ext);
            $ext = ".".$ext;
            
            $imageString = $data;
            
        }
        $localImgFileName = date("Ymdhis")  . $ext;
        $result = Storage::disk('S3_uploads')->put($localImgFileName, base64_decode($imageString)); 
        \Log::info("result");

        return getenv('S3_DOMAIN_NAME').'/'.$localImgFileName;
    }    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        \Log::info($request->all());
        $template = Template::findOrFail($id);
        $template->name = htmlspecialchars_decode($request->name);
        $template->content = htmlspecialchars_decode($request->content);
        $template->type = $request->type;
        $template->priority = $request->priority;
        $template->surveys = $request->surveys;
        $template->questions = $request->questions;
        $template->standard_message = $request->standard_message;
        $template->acknowledgement_message = $request->type == 'standard' ? $request->acknowledgement_message : null;
        $template->is_acknowledgement_required = $request->type == 'standard' ? $request->is_acknowledgement_required : 0;
        $template->save();

        if(isset($request->attachments) && $request->attachments != '') {
            $tempIds = explode(",", $request->attachments);
            $medias = $template->getMedia();
            $updatedMedia = [];
            foreach($medias as $media) {
                if(!in_array($media->name, $tempIds)) {
                    $media->delete();
                } else {
                    $updatedMedia[] = $media->name;
                }
            }

            if(!empty($updatedMedia)) {
                $uploadDocs = array_diff($tempIds, $updatedMedia);
            } else {
                $uploadDocs = $tempIds;
            }

            if (!empty($uploadDocs)) {
                $this->uploadAttachmentDocs($uploadDocs, $template);
            }
        } else {
            if(isset($request->attachment_docs) && !empty($request->attachment_docs)) {
                $template->clearMediaCollection('template');
            }
        }

        $group_ids = array_pluck($request->groups, 'id');
        $user_ids = array_pluck($request->users, 'id');
        $divisions_ids = array_pluck($request->userdivisions, 'id');
        $template->groups()->sync($group_ids);
        $template->users()->sync($user_ids);
        $template->userdivisions()->sync($divisions_ids);
        
        return $template;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $template = Template::find($id);
        if ($template) {
            $template->delete();    
        }        
        return ['status' => 'success'];
    }

    public function uploadAttachment(Request $request)
    {
        // $name = rand();
        // return response([
        //         'message' => 'OK',
        //         'url' => 'https://dev-fleetmastr.s3.amazonaws.com/message/2021/09/997/20210902094257.xls',
        //         'imagename' => $name.'.xls',
        //         'uniqueid' => $name,
        //         'filesize' => 9
        //     ], 200);

        \Log::info('received upload processing');
        if (!empty($request->file())) {
            $fileName = $request->file('file')->getClientOriginalName();
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $availableExtension = ['doc', 'docx', 'pdf', 'xls', 'xlsx', 'ppt', 'pptx'];
            if(!in_array($ext, $availableExtension)) {
                return response([
                    'message' => 'Invalid format',
                ], 200);
            }

            $tempId = date("Ymdhis");
            $customFileName = preg_replace('/[^a-zA-Z0-9.]/', '_', $fileName);
            $tempImage = new TemporaryImage();
            $tempImage->model_type = Message::class;
            $tempImage->temp_id = $tempId;
            $tempImage->save();


            $fileToSave= $request->file('file')->getRealPath();
            $tempImage->addMedia($fileToSave)
                                ->setFileName($customFileName)
                                ->withCustomProperties(['mime-type' => $request->file('file')->getMimeType()])
                                ->toCollectionOnDisk('temporary_images', 'S3_uploads');

            $media = $tempImage->getMedia();

            return response([
                'message' => 'OK',
                'url' => $media[0]->getUrl(),
                'imagename' => $customFileName,
                'uniqueid' => $tempId,
                'filesize' => $media[0]->size,
                'filesize_for_display' => $media[0]->getHumanReadableSizeAttribute()
            ], 200);
        }

        return response([
            'message' => 'OK',
        ], 200);
    }

    public function uploadAttachmentDocs($tempIds, $template)
    {
        $tempImages = TemporaryImage::whereIn('temp_id', $tempIds)->get();
        if ($tempImages) {
            foreach($tempImages as $tempImage) {
                $media = $tempImage->getMedia();
                $template->addMediaFromUrl($media[0]->getUrl())
                ->withCustomProperties($media[0]->custom_properties)
                ->toCollectionOnDisk('template', 'S3_uploads');
                $tempImage->delete();
            }
        }
    }
}
