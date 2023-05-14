<form class="form-horizontal display-settings" id="site_configuration_form" role="form" action="/settings/storeSiteConfiguration" method="POST" novalidate enctype="multipart/form-data">
    <input type="hidden" name="is_configuration_tab_enabled" value="{{ $isConfigurationTabEnabled }}">
    <div class="row">
      <div class="col-md-12">
            {{ csrf_field() }}
            <div class="form-body">
                <p class="margin-bottom-20"></p>
                <div class="row">
                    <div class="col-md-10">
                        <div class="form-group d-flex row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <label for="android_version" class="control-label align-self-center pt-0 w-100">
                                    Android version*:</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="android_version" id="android_version" class="form-control" value="{{ setting('android_version') }}">
                            </div>
                        </div>
                        <div class="form-group d-flex row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <label for="ios_version" class="control-label align-self-center pt-0 w-100">
                                    iOS version*:</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="ios_version" id="ios_version" class="form-control" value="{{ setting('ios_version') }}">
                            </div>
                        </div>
                        <div class="form-group d-flex row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <label for="android_update_prompt_message" class="control-label align-self-center pt-0 w-100">
                                    Android update prompt message:</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <textarea name="android_update_prompt_message" id="android_update_prompt_message" class="form-control simple">{{ setting('android_update_prompt_message') }}</textarea>
                                <span id="android_update_prompt_message_error" style="display: none;" class="has-error help-block help-block-error">Maximum character limit is of 500 characters.</span>
                            </div>
                        </div>
                        <div class="form-group d-flex row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <label for="ios_update_prompt_message" class="control-label align-self-center pt-0 w-100">
                                    iOS update prompt message:</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <textarea name="ios_update_prompt_message" id="ios_update_prompt_message" class="form-control simple">{{ setting('ios_update_prompt_message') }}</textarea>
                                <span id="ios_update_prompt_message_error" style="display: none;" class="has-error help-block help-block-error">Maximum character limit is of 500 characters.</span>
                            </div>
                        </div>

                        <div class="form-group d-flex align-items-center margin-top-20">
                            <label class="col-md-3 control-label align-self-center pt-0">Show resolve vehicle defect:</label>
                            <div class="col-md-4">
                                <label class="checkbox-inline pt-0 toggle_switch">
                                  <input type="checkbox" id="show_resolve_defect" data-toggle="toggle" data-on="Enabled" data-off="Disabled"
                                  name="show_resolve_defect" {{ setting('show_resolve_defect') == 1 ? 'checked' : '' }}>
                                </label>
                            </div>
                        </div>
                        <div class="form-group d-flex align-items-center margin-top-20">
                            <label class="col-md-3 control-label align-self-center pt-0">Report incident:</label>
                            <div class="col-md-4">
                                <label class="checkbox-inline pt-0 toggle_switch">
                                  <input type="checkbox" id="is_incident_reports_enabled" data-toggle="toggle" data-on="Enabled" data-off="Disabled"
                                  name="is_incident_reports_enabled" {{ setting('is_incident_reports_enabled') == 1 ? 'checked' : '' }}>
                                </label>
                            </div>
                        </div>
                        <div class="form-group d-flex align-items-center margin-top-20">
                            <label class="col-md-3 control-label align-self-center pt-0">Trailer check:</label>
                            <div class="col-md-4">
                                <label class="checkbox-inline pt-0 toggle_switch">
                                  <input type="checkbox" id="is_trailer_feature_enabled" data-toggle="toggle" data-on="Enabled" data-off="Disabled"
                                  name="is_trailer_feature_enabled" {{ setting('is_trailer_feature_enabled') == 1 ? 'checked' : '' }}>
                                </label>
                            </div>
                        </div>
                        <div class="form-group d-flex align-items-center margin-top-20">
                            <label class="col-md-3 control-label align-self-center pt-0">DVSA:</label>
                            <div class="col-md-4">
                                <label class="checkbox-inline pt-0 toggle_switch">
                                  <input type="checkbox" id="is_dvsa_enabled" data-toggle="toggle" data-on="Enabled" data-off="Disabled"
                                  name="is_dvsa_enabled" {{ setting('is_dvsa_enabled') == 1 ? 'checked' : '' }}>
                                </label>
                            </div>
                        </div>
                        <div class="form-group d-flex align-items-center margin-top-20">
                            <label class="col-md-3 control-label align-self-center pt-0">Telematics:</label>
                            <div class="col-md-4">
                                <label class="checkbox-inline pt-0 toggle_switch">
                                  <input type="checkbox" id="is_telematics_enabled" data-toggle="toggle" data-on="Enabled" data-off="Disabled"
                                  name="is_telematics_enabled" {{ setting('is_telematics_enabled') == 1 ? 'checked' : '' }}>
                                </label>
                            </div>
                        </div>
                        <div class="form-group d-flex align-items-center margin-top-20">
                            <label class="col-md-3 control-label align-self-center pt-0">Alert centre:</label>
                            <div class="col-md-4">
                                <label class="checkbox-inline pt-0 toggle_switch">
                                  <input type="checkbox" id="is_alertcentre_enabled" data-toggle="toggle" data-on="Enabled" data-off="Disabled"
                                  name="is_alertcentre_enabled" {{ setting('is_alertcentre_enabled') == 1 ? 'checked' : '' }}>
                                </label>
                            </div>
                        </div>
                        <div class="form-group d-flex align-items-center margin-top-20">
                            <label class="col-md-3 control-label align-self-center pt-0">Fleet cost:</label>
                            <div class="col-md-4">
                                <label class="checkbox-inline pt-0 toggle_switch">
                                  <input type="checkbox" id="is_fleetcost_enabled" data-toggle="toggle" data-on="Enabled" data-off="Disabled"
                                  name="is_fleetcost_enabled" {{ setting('is_fleetcost_enabled') == 1 ? 'checked' : '' }}>
                                </label>
                            </div>
                        </div>
                        <div class="form-group d-flex align-items-center margin-top-20">
                            <label class="col-md-3 control-label align-self-center pt-0">Android app offline:</label>
                            <div class="col-md-4">
                                <label class="checkbox-inline pt-0 toggle_switch">
                                  <input type="checkbox" id="is_offline_in_android" data-toggle="toggle" data-on="Enabled" data-off="Disabled"
                                  name="is_offline_in_android" {{ setting('is_offline_in_android') == 1 ? 'checked' : '' }}>
                                </label>
                            </div>
                        </div>
                        <div class="form-group d-flex align-items-center margin-top-20">
                            <label class="col-md-3 control-label align-self-center pt-0">iOS app offline:</label>
                            <div class="col-md-4">
                                <label class="checkbox-inline pt-0 toggle_switch">
                                  <input type="checkbox" id="is_offline_in_ios" data-toggle="toggle" data-on="Enabled" data-off="Disabled"
                                  name="is_offline_in_ios" {{ setting('is_offline_in_ios') == 1 ? 'checked' : '' }}>
                                </label>
                            </div>
                        </div>
                        <div class="form-group d-flex align-items-center margin-top-20">
                            <label class="col-md-3 control-label align-self-center pt-0">Android testfairy feedback:</label>
                            <div class="col-md-4">
                                <label class="checkbox-inline pt-0 toggle_switch">
                                  <input type="checkbox" id="is_android_testfairy_feedback_enabled" data-toggle="toggle" data-on="Enabled" data-off="Disabled"
                                  name="is_android_testfairy_feedback_enabled" {{ setting('is_android_testfairy_feedback_enabled') == 1 ? 'checked' : '' }}>
                                </label>
                            </div>
                        </div>
                        <div class="form-group d-flex align-items-center margin-top-20">
                            <label class="col-md-3 control-label align-self-center pt-0">Android testfairy video capture:</label>
                            <div class="col-md-4">
                                <label class="checkbox-inline pt-0 toggle_switch">
                                  <input type="checkbox" id="is_android_testfairy_video_capture_enabled" data-toggle="toggle" data-on="Enabled" data-off="Disabled"
                                  name="is_android_testfairy_video_capture_enabled" {{ setting('is_android_testfairy_video_capture_enabled') == 1 ? 'checked' : '' }}>
                                </label>
                            </div>
                        </div>
                        <div class="form-group d-flex align-items-center margin-top-20">
                            <label class="col-md-3 control-label align-self-center pt-0">iOS testfairy feedback:</label>
                            <div class="col-md-4">
                                <label class="checkbox-inline pt-0 toggle_switch">
                                  <input type="checkbox" id="is_ios_testfairy_feedback_enabled" data-toggle="toggle" data-on="Enabled" data-off="Disabled"
                                  name="is_ios_testfairy_feedback_enabled" {{ setting('is_ios_testfairy_feedback_enabled') == 1 ? 'checked' : '' }}>
                                </label>
                            </div>
                        </div>
                        <div class="form-group d-flex align-items-center margin-top-20">
                            <label class="col-md-3 control-label align-self-center pt-0">iOS testfairy video capture:</label>
                            <div class="col-md-4">
                                <label class="checkbox-inline pt-0 toggle_switch">
                                  <input type="checkbox" id="is_ios_testfairy_video_capture_enabled" data-toggle="toggle" data-on="Enabled" data-off="Disabled"
                                  name="is_ios_testfairy_video_capture_enabled" {{ setting('is_ios_testfairy_video_capture_enabled') == 1 ? 'checked' : '' }}>
                                </label>
                            </div>
                        </div>                          
                    </div>
                </div>
            </div>

            <div class="row mt-2 pt15">
                <div class="col-md-4 col-md-offset-4">
                    <button type="submit" id="site_configuration_submit" class="btn red-rubine btn-padding btn-block">Save</button>
                </div>
            </div>
      </div>    
    </div>
</form>
