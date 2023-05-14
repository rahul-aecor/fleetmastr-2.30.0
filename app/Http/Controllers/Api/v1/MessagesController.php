<?php

namespace App\Http\Controllers\Api\v1;

use Log;
use Mail;
use JWTAuth;
use App\Models\User;
use App\Models\Message;
use App\Models\MessageRecipient;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MessageRequest;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Spatie\MediaLibrary\Media as Media;

class MessagesController extends APIController
{
    public function text(Request $request)
    {
        \Log::info('received push notification get message text request');
        \Log::info($request->all());
        $userId = JWTAuth::parseToken()->toUser()->id;
        if (isset($request->id)) {
            // get all the message along with message recipient if available
            $message = Message::with(['receiver' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])->where('id', $request->id)->first();

            \Log::info($message);
            if ($message) {
                if (isset($message->receiver[0])) {
                    $receiver = $message->receiver[0]->toArray();                
                    $receiver['response'] = json_decode($receiver['response']);
                    unset($receiver['response']->receiver);
                } else {
                    $receiver = null;
                }

                unset($message->receiver);

                if ($receiver != null && $receiver['response'] != null) {
                    if ($receiver['response']->type == 'survey') {
                        foreach ($receiver['response']->surveys as $key => $surveys) {
                            $html = $this->replaceImagesOrVideoUrl($surveys->text);
                            $receiver['response']->surveys[$key]->text = $html;
                            $receiver['response']->surveys[$key]->textComp = explode("<br>", $html);
                        }
                    } else {
                        foreach ($receiver['response']->questions as $key => $questions) {
                            $html = $this->replaceImagesOrVideoUrl($questions->question);
                            $receiver['response']->questions[$key]->question = $html;
                            $receiver['response']->questions[$key]->questionComp = explode("<br>", $html);
                        }
                    }
                }

                $message->recipients = $receiver;

                $messageNew = $message->toArray();
                $typeArray = ['multiple_choice', 'survey'];

                if (in_array($message->type, $typeArray)) {
                    if ($message->type == 'survey') {
                        $text = $message->surveys;
                        foreach ($text as $key => $value) {
                            if (isset($value['text'])) {
                                $value['text'] = $this->replaceImagesOrVideoUrl($value['text']);
                                $messageNew['surveys'][$key]['text'] = $value['text'];
                                $messageNew['surveys'][$key]['textComp'] = explode("<br>", $value['text']);
                            }
                        }
                    }

                    if ($message->type == 'multiple_choice') {
                        $text = $message->questions;
                        foreach ($text as $key => $value) {
                            $value['question'] = $this->replaceImagesOrVideoUrl(html_entity_decode($value['question']));
                            $messageNew['questions'][$key]['question'] = $value['question'];
                            $messageNew['questions'][$key]['questionComp'] = explode("<br>", $value['question']);
                        }
                    }
                } else {
                    $messageNew['acknowledgment_message'] = $message->is_acknowledgement_required == 1 ? $message->acknowledgement_message : null;

                    $messageRecipient = $message->receiver()->where('user_id', $userId)->first();
                    if(isset($messageRecipient) && $messageRecipient->response_received_at) {
                        $messageNew['is_acknowledged'] = true;
                    } else {
                        $messageNew['is_acknowledged'] = false;
                    }
                    $messageNew['is_acknowledgement_required'] = $message->is_acknowledgement_required == 1 ? true : false;

                    $media = $message->getMedia();
                    $attachments = [];
                    if(isset($media[0])) {
                        foreach($media as $key => $attachment) {
                            $attachments[$key]['id'] = $attachment->id;
                            $attachments[$key]['name'] = $attachment->file_name;
                            $attachments[$key]['size'] = $attachment->getHumanReadableSizeAttribute();
                        }
                    }
                    $messageNew['attachments'] = $attachments;
                }

                \Log::info('Message Log Details :: ' . json_encode($messageNew));
                return response()->json($messageNew);
            }
        }
        return $this->response->error('Error fetching message.');
    }

    private function replaceImagesOrVideoUrl($html) {

        $response = $this->getImagesAndVideosArray($html);

        if (count($response['imagesArray']) > 0) {
            foreach ($response['imagesArray'] as $url => $presignedUrl) {
                $html = str_replace($url, $presignedUrl, $html);
            }
        }

        if (count($response['videoArray']) > 0) {
            foreach ($response['videoArray'] as $url => $presignedUrl) {
                $html = str_replace($url, $presignedUrl, $html);
            }
        }

        return preg_replace('#<br>(\s*<br>)+#', '<br>', $html);
    }

    public function getImagesAndVideosArray($text) {

        $response = [];

        // create new DOMDocument
        $DOMDocument = new \DOMDocument('1.0', 'UTF-8');

        // set error level
        $internalErrors = libxml_use_internal_errors(true);

        $DOMDocument->loadHTML($text);

        $images = $DOMDocument->getElementsByTagName('img');

        $videos = $DOMDocument->getElementsByTagName('video');
        libxml_use_internal_errors($internalErrors);

        $imagesArray = [];

        $s3 = \Storage::disk('S3_uploads');
        $client = $s3->getDriver()->getAdapter()->getClient();
        $expiry = "+60 seconds";

        foreach ($images as $image) {

            try {
                $filPath = explode(env('S3_DOMAIN_NAME') . "/", $image->getAttribute('src'))[1];
                $command = $client->getCommand('GetObject', [
                    'Bucket' => env('S3_UPLOADS_BUCKET'),
                    'Key' => $filPath
                ]);
                $request = $client->createPresignedRequest($command, $expiry);
                $url = (string)$request->getUri();
                $imagesArray[$image->getAttribute('src')] = $url;
            } catch (\Exception $exception) {
                $imagesArray[$image->getAttribute('src')] = $image->getAttribute('src');
            }
        }


        $videoArray = [];

        foreach ($videos as $video) {

            try {
                $videoSource =$video->getElementsByTagName('source');
                $filPath = explode(env('S3_DOMAIN_NAME') . "/", $videoSource->item(0)->getAttribute('src'))[1];
                $command = $client->getCommand('GetObject', [
                    'Bucket' => env('S3_UPLOADS_BUCKET'),
                    'Key' => $filPath
                ]);
                $request = $client->createPresignedRequest($command, $expiry);
                $url = (string)$request->getUri();
                $videoArray[$videoSource->item(0)->getAttribute('src')] = $url;
            } catch (\Exception $exception) {
                $videoArray[$videoSource->item(0)->getAttribute('src')] = $videoSource->item(0)->getAttribute('src');
            }
        }

        $response['imagesArray'] = $imagesArray;
        $response['videoArray'] = $videoArray;
        return $response;


    }

    public function storeResponse(MessageRequest $request)
    {
        \Log::info('received push notification store message request');
        \Log::info($request->all());
        $message_id = $request->message_id;
        $resp_json = $request->resp_json;

        $decodedJson = json_decode($resp_json, true);

        if($decodedJson['type'] == 'multiple_choice') {
            foreach ($decodedJson['questions'] as $key => $value) {
                preg_match_all('@src="([^"]+)"@' , $value['question'], $match);
                if($match) {
                    $imageUrls = array_pop($match);
                    foreach ($imageUrls as $key => $url) {                
                        $unSignedUrl = replacePreSignedWithNormalUrl($url);
                        $decodedJson['questions'][$key]['question'] = str_replace($url, $unSignedUrl, $decodedJson['questions'][$key]['question']);
                        if(isset($decodedJson['questions'][$key]['questionComp'])) {
                            $decodedJson['questions'][$key]['questionComp'] = str_replace($url, $unSignedUrl, $decodedJson['questions'][$key]['questionComp']);
                        }
                    }
                }
            }
        } else if($decodedJson['type'] == 'survey') {
            foreach ($decodedJson['surveys'] as $key => $value) {
                preg_match_all('@src="([^"]+)"@' , $value['text'], $match);
                if($match) {
                    $imageUrls = array_pop($match);
                    foreach ($imageUrls as $key => $url) {                
                        $unSignedUrl = replacePreSignedWithNormalUrl($url);
                        $decodedJson['surveys'][$key]['text'] = str_replace($url, $unSignedUrl, $decodedJson['surveys'][$key]['text']);
                        if(isset($decodedJson['surveys'][$key]['textComp'])) {
                            $decodedJson['surveys'][$key]['textComp'] = str_replace($url, $unSignedUrl, $decodedJson['surveys'][$key]['textComp']);
                        }
                    }
                }
            }
        }

        $user = JWTAuth::parseToken()->toUser();
        if ($user) {
            $message_rec = MessageRecipient::where(['user_id'=>$user->id,'message_id'=>$message_id])->first();
            $message_rec->response = json_encode($decodedJson);
            // $message_rec->response = $resp_json;
            $message_rec->response_received_at = date('Y-m-d H:i:s');
            if ($message_rec->save()) {
                \Log::info("Saved the data");
                return response()->json(['status'=>'success']);
            }
        }

        return $this->response->error('Error saving response.');
    }

    public function fetchAll(Request $request)
    {
        if (!$request->id) {
            throw new BadRequestHttpException('User ID not found in request');
        }
        $user = JWTAuth::parseToken()->toUser();
        $messages = MessageRecipient::with('message')->where('user_id', $user->id)->get()->toArray();
        return response()->json($messages);
    }

    public function getMessageAttachmentUrl($mediaid, Request $request)
    {
        $media = Media::find($mediaid);
        if(isset($media)) {
            $response['url'] = getPresignedUrl($media);
            return response()->json($response);
        }

        return $this->response->error('No media found.');
    }

    public function acknowledgeMessage(Request $request)
    {
        $user = JWTAuth::parseToken()->toUser();
        $message = MessageRecipient::where('message_id', $request->message_id)
                                ->where('user_id', $user->id)
                                ->first();

        if(isset($message)) {
            $message->response_received_at = date('Y-m-d H:i:s');
            $message->save();
            $response['message'] = "Message has been acknowledged successfully.";
            $response['success'] = true;
            return response()->json($response);
        }

        return $this->response->error('No message found.');
    }
}
