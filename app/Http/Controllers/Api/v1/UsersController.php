<?php

namespace App\Http\Controllers\Api\v1;

use Log;
use JWTAuth;
use App\Models\User;
use App\Models\Check;
use App\Models\UserLogoutState;
use App\Http\Requests;
use Carbon\Carbon as Carbon;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Tymon\JWTAuth\Exceptions\JWTException;

class UsersController extends APIController
{
    public function postRegisterPushService(Request $request)
    {
        \Log::info('received push notification registration request');
        \Log::info($request->all());
        if (filter_var($request->get('email'), FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
        } else {
            $field = 'username';
        }
        $user = JWTAuth::parseToken()->toUser();
        $user->push_registration_id = $request->registration_id;
        
        if ($user->save()) {
            \Cookie::queue(\Cookie::forget('classic-user'));
            return $this->response->array(['success' => true]);
        }

        return $this->response->error('Error while saving.');
    }

    /**
     * Authenticate user
     *
     * @return JSON
     */
    public function authenticateUser(Request $request)
    {
        \Log::info('authenticateUser');
        \Log::info($request->all());
        $email = $request->get('email');
        $password = $request->get('password');
        if (filter_var($request->get('email'), FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
        } else {
            $field = 'username';
        }
        /*if(empty($email)){
            return $this->response->errorBadRequest("Bad request. Required email missing.");
        }
        if(empty($password)){
            return $this->response->errorBadRequest("Bad request. Required password missing.");
        }*/

        $credentials = $request->only('email', 'password');
        if($field=='username'){
            $credentials[$field] = $request->get('email');
            unset($credentials['email']);
        }

        // $user = User::where(['email'=>$email])->first();
        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'These credentials do not match our records.'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'Something went wrong, please try again.'], 500);
        }


        $user = User::where([$field=>$email])->first();

        if($user) {
            \Log::info('User found');
            \Log::info($user);
            if(\Hash::check($password, $user->password)) {
                $user->enable_login = 1;
                $user->is_app_installed = 1;
                $user->last_login = Carbon::now();
                if ($request->headers->has('User-Agent')) {
                    $user->user_agent =  $request->header('User-Agent');
                }
                $user->save();
                return $this->response->array([
                    'token'=> $token, 
                    'data' => $user,
                    'default_password' => $user->is_default_password,
                    'is_incident_reports_enabled' => setting('is_incident_reports_enabled'),
                    'is_resolve_defect' => setting('show_resolve_defect'),
                    'last_logout_state' => $this->returnLogoutState($user->id),
                    'message' => "success",
                    'unread_message_count' => $user->unreadMessageCount(),
                    "status_code" => 200
                ]);
            } else {
                return $this->response->error('These credentials do not match our records.', 500);
            }
        } else {
            return $this->response->error('This email address is not recognised.', 500);
        }        
    }

    public function forgotPassword(Request $request)
    {
        \Log::info($request->all());
        $this->validate($request, ['email' => 'required|email']);

        $email = $request['email'];
        $user = User::where(['email'=>$email])->first();

        if (!$user){
            return $this->response->errorBadRequest('This email does not match our records.');
        }

        if($user->is_verified != 1){
            return $this->response->errorBadRequest('Your account is inactive. Please verify your account by clicking on the link sent you in email.');
        }

        $response = Password::sendResetLink($request->only('email'), function (Message $message) {
            $message->subject('fleetmastr - reset your account password');
        });

        switch ($response) {
            case Password::RESET_LINK_SENT:
                return $this->response->array([
                    'message' => "An email has been sent to you with a password reset link",
                    "status_code" => 200
                ]);

            case Password::INVALID_USER:
                return $this->response->errorBadRequest(trans($response));
        }
    }

    public function changePassword(Request $request)
    {
        \Log::info($request->all());
        $user = User::find($request['id']);
        if($user->is_lanes_account == 1) {
            $response = ['success' => false, 'message' => 'You are not authorized to change your password.'];
            return json_encode($response);
        }

        if(!isset($request['new_password']) || trim($request['new_password']) == '') {
            $response = ['success' => false, 'message' => 'Please provide new password to change your old password.'];
            return json_encode($response);
        }

        if(isset($request['new_password']) && trim($request['new_password']) == env('DEFAULT_PASSWORD')) {
            return response()->json(['error' => 'New password can not be a default password.'], 500);
        }

        $user->is_verified = 1;
        $user->is_default_password = 0;
        $user->password = bcrypt($request['new_password']);
        $user->save();

        return $this->response->array([
            'message' => "Password has been updated",
            "status_code" => 200
        ]);
    }

    public function storeLogoutState(Request $request) {
        $user = JWTAuth::parseToken()->toUser();
        if (isset($request->vehicle_id)) {
            if ($request->action == 'takeout') {
                $userLogoutStatesTodel = UserLogoutState::where(['user_id'=>$user->id,'action'=>'takeout'])->delete();
            }
            $userLogoutState = new UserLogoutState();
            $userLogoutState->user_id = $user->id;
            $userLogoutState->vehicle_id = $request->vehicle_id;
            $userLogoutState->action = isset($request->action) ? $request->action : null;
            $userLogoutState->save();
            $responseArray = ['success' => true, 'message' => 'User logout state is saved.'];
            return response()->json($responseArray, 200);
        }
        else{
            return response()->json(['error' => 'Please send valid vehicle_id value.'], 500);
        }

    }

    private function returnLogoutState($user_id) {
        $userLogoutState = UserLogoutState::where('user_id',$user_id)->with('vehicle')->first();            
        if ($userLogoutState != null) {
            $lastTakeoutCheck = Check::where('vehicle_id',$userLogoutState->vehicle_id)->where('type','Vehicle Check')->orderBy('created_at','DESC')->select('created_at')->first();
            $lastTakeoutCheckDatetime = $lastTakeoutCheck != null ? Carbon::parse($lastTakeoutCheck->created_at)->format('H:m:i d M y') : null;
            
            $vehicleId=null; $vehicleData=[];
            if($userLogoutState->vehicle_id){
                $vehicleService=new \App\Services\VehicleService();
                $vehicleId=$userLogoutState->vehicle_id;
                $_vehicleData=$vehicleService->getVehicleById($vehicleId);
                if($_vehicleData){
                    $vehicleData=$_vehicleData;
                }
            }
            $userLogoutStateResponse = ["vehicle_id"=>$userLogoutState->vehicle_id,"action"=>$userLogoutState->action,"last_check_datetime"=>$lastTakeoutCheckDatetime,'vehicle_data'=>$vehicleData];
            return $userLogoutStateResponse;
        }
        else{
             return null;
        }
    }

}
