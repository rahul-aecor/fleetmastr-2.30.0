<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Log;
use App\Models\User;
use App\Models\Company;
use App\Models\Vehicle;
use App\Models\AppAccessLog;
use App\Transformers\UserTransformer;
use Carbon\Carbon;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class VersionController extends APIController
{
    public function check(Request $request)
    {
        // $imei = $request->get('imei');
        // Log::info("========================================================================");
        // Log::info("IMEI : " . $imei);
        // Log::info("========================================================================");
        // if(empty($imei)){
        //        return $this->response->errorBadRequest("Bad request. Requied IMEI number missing.");
        // }
        // $user = User::where('imei','=',$imei)->first();
        $user_email = trim(strtolower($request->get('user_email')));
        Log::info("========================================================================");
        Log::info("User Email : " . $user_email);
        Log::info("========================================================================");
        Log::info('Request is from agent ' . $request->server('HTTP_USER_AGENT'));
        Log::info("========================================================================");
        
        $app_access = AppAccessLog::firstOrNew(['email' => $user_email]);
        // $app_access->email = $user_email;
        // $app_access->app_version='1.6.1';
        $app_access->updated_at = Carbon::now();
        $app_access->save();
        
        if(empty($user_email)){
            return $this->response->errorBadRequest("Access denied. An authorised email address is required to access the App.");
        }
        
        if (filter_var($request->get('user_email'), FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
        } else {
            $field = 'username';
        }

        $user = User::where($field,'=',$user_email)->first();
        $regno_list = implode(",",Vehicle::lists('registration')->toArray());
        $workshopCompanies = Company::where('user_type', 'Workshop')->select('name', 'id')->orderBy('name')->get()->unique('name')->values()->all();

        // echo "<pre>"; print_r($regno_list); exit;
        if(!empty($user)){
            $app_access->is_valid = 1;
            $app_access->save();

            // if ($request->headers->has('User-Agent')) {
            //     $user->user_agent =  $request->header('User-Agent');
            //     $user->save();
            // }

            $survey_json_version = \DB::table('survey_json_version')->first();
            return $this->response->item($user, new UserTransformer)
                ->addMeta('version',$survey_json_version->version)
                ->addMeta('oncall',env('SHOW_ONCALL'))
                ->addMeta('is_resolve_defect', setting('show_resolve_defect'))
                ->addMeta('is_offline_in_android', setting('is_offline_in_android'))
                ->addMeta('is_offline_in_ios', setting('is_offline_in_ios'))
                ->addMeta('is_android_testfairy_feedback_enabled', setting('is_android_testfairy_feedback_enabled'))
                ->addMeta('is_ios_testfairy_feedback_enabled', setting('is_ios_testfairy_feedback_enabled'))
                ->addMeta('is_android_testfairy_video_capture_enabled', setting('is_android_testfairy_video_capture_enabled'))
                ->addMeta('is_ios_testfairy_video_capture_enabled', setting('is_ios_testfairy_video_capture_enabled'))
                ->addMeta('is_trailer_feature_enabled', setting('is_trailer_feature_enabled'))
                ->addMeta('is_incident_reports_enabled', setting('is_incident_reports_enabled'))
                ->addMeta('is_telematics_enabled', setting('is_telematics_enabled'))
                ->addMeta('android_update_prompt_message', setting('android_update_prompt_message'))
                ->addMeta('ios_update_prompt_message', setting('ios_update_prompt_message'))
                ->addMeta('unread_message_count',$user->unreadMessageCount())
                ->addMeta('apk_version',setting('android_version'))
                ->addMeta('registration_numbers',$regno_list)
                ->addMeta('workshop_companies', $workshopCompanies);
        }
        else{
            // return $this->response->errorForbidden('IMEI Number does not have permission to access the app.');
            return $this->response->errorForbidden('Access denied. An authorised email address is required to access the App.');
        }
    }

    public function testjwt(Request $request)
    {

		$user = JWTAuth::parseToken()->authenticate();
		// echo "<pre>"; print_r($user); exit;
    }

    public function apkVersion(){
        if(env('APK_DOWNLOAD_URL') != null){
            $apk_version_url = env('APK_DOWNLOAD_URL')."/".setting('android_version')."/fleetmastr.apk";
            $ios_version_url = env('IOS_DOWNLOAD_URL')."/".setting('ios_version')."/manifest.plist";
            return response()->json([
                'apk_version' => setting('android_version'),
                'apk_version_url' => $apk_version_url,
                'ios_version' => setting('ios_version'),
                'ios_version_url' => $ios_version_url,
                'enable_logs_ios' => env('ENABLE_LOGS_IOS', "0"),
                'enable_logs_android' => env('ENABLE_LOGS_ANDROID', "0"),
                'android_update_prompt_message' => setting('android_update_prompt_message'),
                'ios_update_prompt_message' => setting('ios_update_prompt_message')
            ]);        
        }
        else
        {
            return response()->json([
                'apk_version' => setting('android_version'),
                'ios_version' => setting('ios_version'),
                'enable_logs_ios' => env('ENABLE_LOGS_IOS', "0"),
                'enable_logs_android' => env('ENABLE_LOGS_ANDROID', "0"),
                'android_update_prompt_message' => setting('android_update_prompt_message'),
                'ios_update_prompt_message' => setting('ios_update_prompt_message')
            ]);        
        }    
    }    

    public function projectConfigration()
    {
        $regno_list = implode(",",Vehicle::lists('registration')->toArray());
        $workshopCompanies = Company::where('user_type', 'Workshop')->select('name', 'id')->orderBy('name')->get()->unique('name')->values()->all();
        $apk_version_url = env('APK_DOWNLOAD_URL')."/".setting('android_version')."/fleetmastr.apk";
        $ios_version_url = env('IOS_DOWNLOAD_URL')."/".setting('ios_version')."/manifest.plist";
        $survey_json_version = \DB::table('survey_json_version')->first();
        $jsonValue = [
            'version' => $survey_json_version->version,
            'oncall' => env('SHOW_ONCALL'),
            'is_resolve_defect' => setting('show_resolve_defect'),
            'is_offline_in_android' => setting('is_offline_in_android'),
            'is_offline_in_ios' => setting('is_offline_in_ios'),
            'is_android_testfairy_feedback_enabled' => setting('is_android_testfairy_feedback_enabled'),
            'is_ios_testfairy_feedback_enabled' => setting('is_ios_testfairy_feedback_enabled'),
            'is_android_testfairy_video_capture_enabled' => setting('is_android_testfairy_video_capture_enabled'),
            'is_ios_testfairy_video_capture_enabled' => setting('is_ios_testfairy_video_capture_enabled'),
            'is_trailer_feature_enabled' => setting('is_trailer_feature_enabled'),
            'is_incident_reports_enabled' => setting('is_incident_reports_enabled'),
            'is_telematics_enabled' => setting('is_telematics_enabled'),
            'android_update_prompt_message' => setting('android_update_prompt_message'),
            'ios_update_prompt_message' => setting('ios_update_prompt_message'),
            'apk_version' => setting('android_version'),
            'registration_numbers' => $regno_list,
            'workshop_companies' => $workshopCompanies,
            'ios_version' => setting('ios_version'),
            'enable_logs_ios' => env('ENABLE_LOGS_IOS', "0"),
            'enable_logs_android' => env('ENABLE_LOGS_ANDROID', "0"),
	        'minimum_service_interval_for_notification' => config('config-variables.minimum_service_interval'),
            'is_mdm_client' => env('IS_MDM_CLIENT', false),
            'is_apple_review' => env('IS_APPLE_REVIEW', false)
        ];
        if(env('APK_DOWNLOAD_URL') != null){
            $jsonValue['apk_version_url'] = $apk_version_url;
            $jsonValue['ios_version_url'] = $ios_version_url;
        }
        return response()->json($jsonValue);
    }
}
