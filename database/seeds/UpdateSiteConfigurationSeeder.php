<?php

use Illuminate\Database\Seeder;

class UpdateSiteConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        setting()->forget('is_testfairy_feedback_enabled');
    	setting([
            'is_android_testfairy_feedback_enabled' => 0,
            'is_ios_testfairy_feedback_enabled' => 0,
            'is_android_testfairy_video_capture_enabled' => 0,
            'is_ios_testfairy_video_capture_enabled' => 0
        ])->save();
    }
}
