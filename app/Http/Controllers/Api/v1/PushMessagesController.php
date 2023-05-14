<?php

namespace App\Http\Controllers\Api\v1;

use Log;
use App\Models\User;
use App\Models\Message;
use App\Models\MessageRecipient;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MessageReceiptRequest;
use JWTAuth;

class PushMessagesController extends APIController
{
    /**
     * Acknowledge the receipt of the push message.
     *
     * This endpoint will be called whenever a push message is
     * delivered to the client. Update the status to 'Delivered'
     * and return the corresponding GCM message id back to the app.
     *
     * @param $id
     * @return mixed
     */
    public function postAcknowledgePushReceipt(MessageReceiptRequest $request)
    {
        \Log::info('received acknowledge request for message id ');
        \Log::info($request->all());
        $message_id = $request->message_id;
        $email = $request->email;
        $status = $request->status;
        
        $user = JWTAuth::parseToken()->toUser();
        if($user){
            $message_rec = MessageRecipient::where(['user_id'=>$user->id,'message_id'=>$message_id])->first();
            $message_rec->status = $status;
            if ($message_rec->save()) {
                \Log::info("Saved the data");
                return response()->json(['status'=>'success']);
            }
        }
        
        return $this->response->error('Error saving response.');
    }	
}