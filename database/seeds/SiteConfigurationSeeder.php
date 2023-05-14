<?php

use Illuminate\Database\Seeder;

class SiteConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	setting([
            'android_version' => env('APK_VERSION'),
            'ios_version' => env('IOS_VERSION'),
            'show_resolve_defect' => env('SHOW_RESOLVE_DEFECT'),
            'is_incident_reports_enabled' => env('IS_INCIDENT_REPORTS_ENABLED'),
            'is_trailer_feature_enabled' => env('IS_TRAILER_FEATURE_ENABLED'),
            'is_offline_in_android' => env('IS_OFFLINE_IN_ANDROID'),
            'is_offline_in_ios' => env('IS_OFFLINE_IN_IOS'),
            'is_telematics_enabled' => env('IS_TELEMATICS_ENABLED'),
            'is_testfairy_feedback_enabled' => env('IS_TESTFAIRY_FEEDBACK_ENABLED'),
            'android_update_prompt_message' => '<p>A <strong>fleet</strong>mastr update is available. Please update the version you are using.</p>',
            'ios_update_prompt_message' => '<p>A <strong>fleet</strong>mastr update is available. Please update the version you are using.</p>',
        ])->save();
    }
}
