
{!! BootForm::openHorizontal(['md' => [4, 8]])->addClass('form-bordered form-validation user-form')->put()->action('/users/' . $user->id)->id('editUser') !!}
{!! BootForm::bind($user) !!}
<div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
    <h4 class="modal-title">Edit User
        <span class="app-version">
            <span class="list-item margin-right-10" title="{{ $appDevice }}">
                    Device: {{ $appDevice }}
                </span>
                <span class="list-item margin-right-10" title="{{ $appVersion }}">
                    App: {{ $appVersion }}
                </span>
                <span class="list-item" title="{{ $user->last_login }}">
                    Last login: {{ $user->last_login }}
                </span>
        </span>
    </h4>
    <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
        <i class="jv-icon jv-close"></i>
    </a>
</div>
<div class="modal-body">
    <div class="tabbable-custom tabbable-rubine margin-bottom0">
        <ul class="nav nav-tabs nav-justified">
            <li class="active">
                <a href="#edit_add_user" data-toggle="tab">
                User Details </a>
            </li>
            <li>
                <a href="#edit_user_permission" data-toggle="tab">
                User Permissions </a>
            </li>
            <li style="width: 170px;" id="edit_message_permission_tab" class="{{ (!in_array('1', $user->role_id) && !in_array($messageRoleId,$givenRolesArray)) ? 'd-none' : ''}}">
                <a href="#edit_message_permission" data-toggle="tab">
                Message Permissions </a>
            </li>
            <li style="width: 160px;">
                <a href="#edit_vehicle_permission" data-toggle="tab">
                Vehicle Permissions </a>
            </li>
            <li>
                <a href="#edit_user_notification" data-toggle="tab">
                User Notifications </a>
            </li>
        </ul>

        <div id="view-edit-company" class="modal modal-fix  fade" tabindex="-1" data-background="static" aria-labelledby="myModalLabel" data-width="630" aria-hidden="true">
            <div class="modal-dialog" style="margin-top: 0; margin-bottom: 0;">
                <div class="portlet box">
                    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                      <h4 class="modal-title">All Companies</h4>
                        <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                            <i class="jv-icon jv-close"></i>
                        </a>
                    </div>
                    <div class="table-wrapper-scroll-y my-custom-scrollbar">
                      <table class="table table-hover table-striped table-company">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col" width="70%">Company Name</th>
                            <th scope="col" class="text-center">Action</th>
                          </tr>
                        </thead>
                        <tbody id="view_all_edit_companies">
                        </tbody>
                      </table>
                    </div>

                </div>
            </div>
        </div>


        <div class="tab-content rl-padding">
            <div class="tabErrorAlert">Please confirm mandatory information on all tabs.</div>
            <div class="tab-pane active" id="edit_add_user">
                <div class="row">
                    <div class="col-md-12">
                        <div class="clearfix">
                            <div class="col-md-6 userFirstCol">
                                {!! BootForm::hidden('id') !!}
                                {!! BootForm::text('First name*:', 'first_name') !!}
                                {!! BootForm::text('Last name*:', 'last_name') !!}

                                <div class="form-group row company--modal">
                                    <label class="col-md-4 control-label" for="company_id">Company*:</label>
                                    <div class="company--modal-col-55" style="width:46%;">
                                      <select class="form-control select2me " id="company_id1" name="company_id">
                                        <?php foreach ($companyList as $key => $company): ?>
                                            <option {{ $user->company_id == $key ? 'selected': '' }} value="{{ $key }}">{{ $company }}</option>
                                        <?php endforeach ?>
                                      </select>
                                    </div>

                                    <div class="company--modal-col desboard_thumbnail">
                                      <a href="#add-company" data-toggle="modal" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center" style="margin-right: 0">
                                            <i class="jv-icon jv-plus"></i>
                                        </a>
                                     </div>
                                     <div class="company--modal-col desboard_thumbnail">
                                       <a href="#view-edit-company" id="view_edit_company" data-path="user" data-toggle="modal" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center" style="margin-right: 0">
                                             <i class="jv-icon jv-edit"></i>
                                         </a>
                                      </div>
                                </div>

                                {!! BootForm::text('Email:', 'email') !!}

                                {!! BootForm::text('Job title:', 'job_title') !!}
                                {!! BootForm::text('Mobile number:', 'mobile') !!}
                                {!! BootForm::text('Landline number:', 'landline') !!}

                                <div class="form-group">
                                    <div class="col-md-4 text-right" for="driver_tag_key">
                                        <label>Driver tag:</label>
                                    </div>
                                    <div class="col-md-8 roles-checkbox-wrapper">
                                        <label class="radio-default-overright margin-right-10">
                                            <input class="form-check-input" name="driver_tag" type="radio" id="driverNoneKey" value="none" {{ $user->driver_tag == 'none' ? 'checked': ''}}>None
                                        </label>
                                        <label class="radio-default-overright margin-right-10">
                                            <input class="form-check-input driver-tag-key" name="driver_tag" type="radio" id="driverDallasKey" value="dallas_key"  {{ $user->driver_tag == 'dallas_key' ? 'checked': ''}}>Dallas key
                                        </label>
                                        <label class="radio-default-overright margin-right-10">
                                            <input class="form-check-input" name="driver_tag" type="radio" id="driverRfidCard" value="rfid_card" {{ $user->driver_tag == 'rfid_card' ? 'checked': ''}}>RFID card
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group driver-tag-key">
                                    <div class="col-md-4 text-right">
                                        <label class="driver-tag-text-change">Dallas key:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="driver_tag_key" id="driver_tag_key" class="form-control" value="{{$user->driver_tag_key}}">
                                    </div>
                                </div>
                                {!! BootForm::text('Fuel card number:', 'fuel_card_number') !!}
                            </div>
                            <div class="col-md-6 user-form-right-pane">
                                {!! BootForm::text('ID:', 'engineer_id') !!}
                                {{-- {!! BootForm::hidden('is_active')->value(0) !!} --}}
                                {{-- {!! BootForm::checkbox('Enable account login', 'is_active') !!} --}}
                                {!! BootForm::text('Username*:', 'username')->defaultValue($user->email)->readonly()->attribute('autocomplete','off') !!}
                                {!! BootForm::select('Enable account login:', 'enable_login')->options(['1' => 'Yes', '0' => 'No'])->select($user->enable_login)->addClass('select2me') !!}

                                <div class="form-group">
                                    <label class="col-md-4 control-label ">Created:</label>
                                    <div class="col-md-4 control-label" style="text-align:left;">
                                        <label class="mb-0">
                                           {{ $user->created_at->format('d M Y') }}
                                        </label>
                                    </div>
                                    @if($user->is_verified == 1)
                                        <div class="col-md-4 control-label" style="text-align:left;">
                                            <a href="#" data-reset-url="/users/resetpasswordadmin/{{ $user->id }}"
                                            class="font-blue js-user-password-reset-btn" title="" data-confirm-msg="Are you sure you would like to reset the user's password?"><label class="mb-0"><u>Password reset</u></label></a>
                                        </div>
                                    @endif
                                </div>
                                {{-- {!! BootForm::hidden('is_lanes_account')->value(0) !!} --}}
                                {{-- {!! BootForm::checkbox('Lanes Account', 'is_lanes_account') !!} --}}
                                {!! BootForm::text('IMEI number:', 'imei') !!}
                                {!! BootForm::text('Line manager:', 'line_manager')->addClass('js-line-manager')->placeholder('Select') !!}
                                <div class="form-group js-line-manager-number-div" style="display: none">
                                    <label class="col-md-4 control-label" for="field_manager_phone">Line manager number:</label>
                                    <div class="col-md-8">
                                        <input type="text" name="field_manager_phone" id="field_manager_phone" class="form-control" readonly="readonly" value="{{ $user->field_manager_phone }}">
                                    </div>
                                </div>
                                {!! BootForm::select('Division:', 'user_division_id')->options(['' => ''] + $userDivisions)->addClass('select2me division-value')->placeholder('Select') !!}
                                <div class="user-region-value">

                                    {!! BootForm::select('Region:', 'user_region_id')->options([])->addClass('select2me user-region-value edit-user-region-value')->placeholder('Select') !!}
                                </div>
                                <input type="hidden" id="selectd_region" value="{{ $user->user_region_id }}">
                                {!! BootForm::select('Base location:', 'user_locations_id')->options([])->addClass('select2me add-user-base-location edit-user-base-location')->placeholder('Select') !!}
                                <input type="hidden" id="selectd_base_location" value="{{ $user->user_locations_id }}">
                                <div class="form-group align-items-center js_private_use_days" style="display: none;">
                                    <label class="col-md-4 control-label ">Private use days previous  tax year:</label>
                                    <div class="col-md-4">
                                        <label class="pt-0">
                                          {{ $privateUseDaysPrev }}
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-4 control-label ">Fuel card issued:</label>
                                    <div class="col-md-4">
                                        <label class="checkbox-inline pt-0">
                                          <input type="checkbox" id="fuelCardIssued" data-toggle="toggle" data-on="Yes" data-off="No"
                                          name="fuel_card_issued" {{ $user->fuel_card_issued == 1 ? 'checked' : '' }}>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group js_fuel_card_personal_use" style="display: none">
                                    <label class="col-md-4 control-label ">Fuel card for personal use (BIK):</label>
                                    <div class="col-md-4">
                                        <label class="checkbox-inline pt-0">
                                          <input type="checkbox" id="fuelCardPersonalUse" data-toggle="toggle" data-on="Yes" data-off="No"
                                          name="fuel_card_personal_use" {{ $user->fuel_card_personal_use == 1 ? 'checked' : '' }}>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane user-permission-error-block mt-22" id="edit_user_permission">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="col-md-2 text-right" for="roles[]">
                                <label>Admin permissions*:</label>
                            </div>
                            <div class="col-md-10 roles-checkbox-wrapper">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="radio-default-overright">
                                            <input type="radio" name="roles[]" class="roles-types-radio" value="1" onclick="toggleSuperAdmin(this);" <?php if (in_array('1', $user->role_id)) echo "checked=checked"; ?> >Super admin
                                        </label>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="radio-default-overright">
                                            <input type="radio" name="roles[]" data-val="" class="roles-checkbox" value="14"  onclick="userInformationOnly(this);" <?php if (in_array('14', $user->role_id)) echo "checked=checked"; ?> >User information only
                                        </label>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="radio-default-overright">
                                            <input type="radio" name="roles[]" data-val="App access only" class="roles-checkbox" value="8"  onclick="AppAccessOnly(this);" <?php if (in_array('8', $user->role_id) && count($user->role_id) === 1) echo "checked=checked"; ?> >App access only
                                        </label>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="radio-default-overright">
                                            <input type="radio" name="roles[]" data-val="" class="roles-checkbox" value=""  onclick="bespokeClick(this);"
                                            <?php if ($user->isHavingBespokeAccess()) echo "checked=checked"; ?>
                                            >Bespoke access
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="roles-checkbox-wrapper-error col-md-12"></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-2 text-right"><label>Desktop permissions:</label></div>
                            <div class="col-md-10 roles-checkbox-wrapper">
                                <div class="row">
                                    @foreach (array_chunk($rolesList, ceil(count($rolesList) / 2), true) as $chunk)
                                        @foreach ($chunk as $index => $role)
                                            <?php
                                                $messageClass = $role == 'Messaging' ? 'js-msg-checkbox' : '';
                                            ?>
                                            @if($role == 'Dashboard (costs)' && !setting('is_fleetcost_enabled'))
                                                <?php continue; ?>
                                            @endif
                                            @if ($index != 1 && $index != 8 && $index != 14)
                                                <label class="d-block col-md-3">
                                                    @if (in_array('1', $user->role_id))
                                                        <input type="checkbox" name="roles[]" data-val="{{ $role }}" value="{{ $index }}" class="roles-checkbox-edit group {{ $messageClass }}" disabled="disabled" checked="checked" onclick="toggleOther(this);">{{ ucfirst(strtolower($role)) }}
                                                    @elseif (in_array('14', $user->role_id))
                                                        <?php $userInfoOnlyRoleIds = $givenRolesArray;?>
                                                        <input type="checkbox" name="roles[]" data-val="{{ $role }}" value="{{ $index }}" class="roles-checkbox-edit group {{ $messageClass }}" disabled="disabled" @if(in_array($index, $userInfoOnlyRoleIds)) checked="checked" @endif onclick="toggleOther(this);" >  {{ ucfirst(strtolower($role)) }}
                                                    @elseif ($user->isHavingBespokeAccess())
                                                        <input type="checkbox" name="roles[]" data-val="{{ $role }}" value="{{ $index }}" class="roles-checkbox-edit group {{ $messageClass }}" @if(in_array($index,$givenRolesArray)) checked="checked" @endif onclick="toggleOther(this);">{{ ucfirst(strtolower($role)) }}
                                                    @else
                                                        <input type="checkbox" name="roles[]" data-val="{{ $role }}" value="{{ $index }}" class="roles-checkbox-edit group {{ $messageClass }}" disabled="disabled" @if(in_array($index,$givenRolesArray)) checked="checked" @endif onclick="toggleOther(this);" >{{ ucfirst(strtolower($role)) }}
                                                    @endif
                                                </label>
                                            @endif
                                        @endforeach
                                    @endforeach
                                </div>
                                <!-- <div class="row">
                                    <div class="roles-checkbox-wrapper-error col-md-12"></div>
                                </div> -->
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-2 text-right" for="roles[]">
                                <label>Mobile permissions:</label>
                            </div>
                            <div class="col-md-10 roles-checkbox-wrapper">
                                <div class="row">
                                    @foreach (array_chunk($mobileRolesList, ceil(count($mobileRolesList) / 2), true) as $chunk)
                                        @foreach ($chunk as $index => $role)
                                            <label class="d-block col-md-3">
                                                <input type="checkbox" name="roles[]" data-val="{{ $role }}" class="roles-checkbox group js-app-access" onclick="toggleOther(this);" value="{{ $index }}" @if(in_array($index,$givenRolesArray) || in_array('1', $givenRolesArray)) checked="checked" @endif>
                                            {{ $role }}</label>
                                        @endforeach
                                    @endforeach
                                </div>
                                <!-- <div class="row">
                                    <div class="roles-checkbox-wrapper-error col-md-12"></div>
                                </div> -->
                            </div>
                        </div>
                        <div class="form-group">

                            <div class="col-md-2 text-right">
                                <label>Workshop manager:</label>
                            </div>
                            <div class="col-md-10">
                                <label>
                                    <input class="js-workshopmanager-edit form-check-input" name="workshopmanager" type="checkbox" id="workshopmanager"  @if(in_array($workshopsManagerId,$givenRolesArray)) checked="checked"  @endif value="12">
                                    Check the box to add this user to the Workshops section
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane vehicle-permission-error-block mt-22" id="edit_message_permission">
                <div class="row">
                    <div class="col-md-12">
                        @if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'))
                            <div class="form-group">
                                <div class="col-md-2 text-right" for="accessible_regions[]"><label>Divisions and regions*:</label></div>
                                <div class="col-md-10 message-accessible-regions-checkbox-wrapper">
                                    <div class="checkbox-list">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label>
                                                        <input type="checkbox" id="all_accessible_region_message" value="" {{ (count($user->messageRegions) == count($userRegion)) ? 'checked="checked"' : '' }}>
                                                        All
                                                    </label>
                                                </div>
                                            </div>
                                            @foreach ($userRegion as $division => $regions)
                                                <div class="message_all_divisions">
                                                    <label>
                                                        <input type="checkbox" name="message_accessible_divisions[]" class="message-accessible-divisions-checkbox message-divisions-group message-division-{{ $division }}" value="{{ $division }}" {{ !is_null($user->messageDivisions->pluck('id')->toArray()) && in_array($division, $user->messageDivisions->pluck('id')->toArray()) ? 'checked="checked"' : '' }}>
                                                        {{ $userDivisions[$division] }}
                                                    </label>
                                                </div>
                                                <div class="nested-regions">
                                                    <label>
                                                        <input type="checkbox" class="message_all_division_region" value="{{ $division }}" {{ !is_null($user->messageDivisions->pluck('id')->toArray()) && in_array($division, $user->messageDivisions->pluck('id')->toArray()) ? '' : 'disabled="disabled"' }}>
                                                        All
                                                    </label>

                                                    <div class="row">
                                                        @foreach(array_chunk($regions, 2, true) as $chunk)
                                                            @foreach($chunk as $region_id => $region_name)
                                                                <div class="col-md-4">
                                                                    <div class="message_all_regions">
                                                                        <label>
                                                                            <input type="checkbox" name="message_accessible_regions[]" class="message-accessible-regions-checkbox-{{ $division }} message-regions-group" value="{{ $region_id }}" {{ !is_null($user->messageRegions->pluck('id')->toArray()) && in_array($region_id, $user->messageRegions->pluck('id')->toArray()) ? 'checked="checked"' : '' }} {{ !is_null($user->messageDivisions->pluck('id')->toArray()) && !in_array($division, $user->messageDivisions->pluck('id')->toArray()) ? 'disabled="disabled"' : '' }}>
                                                                            {{ $region_name }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    <div class="row">
                                        <div class="message-accessible-regions-checkbox-wrapper-error col-md-12"></div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="form-group">
                                <div class="col-md-2 text-right" for="accessible_regions[]"><label>Divisions and regions*:</label></div>
                                <div class="col-md-10 message-accessible-regions-checkbox-wrapper">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="checkbox-list">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label>
                                                            <input type="checkbox" id="all_accessible_region_message" value="" {{ (count($user->messageRegions) == count($userRegion)) ? 'checked="checked"' : '' }}>
                                                            All
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    @foreach(array_chunk($userRegion, 2, true) as $chunk)
                                                        @foreach($chunk as $division => $regions)
                                                            <div class="col-md-4">
                                                                <div class="message_all_regions">
                                                                    <label>
                                                                        <input type="checkbox" name="message_accessible_regions[]" class="message-accessible-regions-checkbox message-regions-group" value="{{ $division }}" {{ !is_null($user->messageRegions->pluck('id')->toArray()) && in_array($division, $user->messageRegions->pluck('id')->toArray()) ? 'checked="checked"' : '' }}>
                                                                        {{ $regions }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="message-accessible-regions-checkbox-wrapper-error col-md-12"></div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>

            <div class="tab-pane vehicle-permission-error-block mt-22" id="edit_vehicle_permission">
                <div class="row">
                    <div class="col-md-12">
                        @if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'))
                            <div class="form-group">
                                <div class="col-md-2 text-right" for="accessible_regions[]"><label>Divisions and regions*:</label></div>
                                <div class="col-md-10 accessible-regions-checkbox-wrapper">
                                    <?php /*
                                    <div class="row">
                                        <?php
                                                $vehicleRegionsValArray = [];
                                                if(env('BRAND_NAME')=='skanska')
                                                {
                                                    foreach (config('config-variables.vehicleRegions') as $vehicleRegionsVal)
                                                    {
                                                       if(empty($vehicleRegionsVal)==false)
                                                        {
                                                           foreach($vehicleRegionsVal as $keys=>$vehicleRegionsValues)
                                                            {
                                                                $vehicleRegionsValArray[$keys]=$vehicleRegionsValues;
                                                            }
                                                       }
                                                       else{
                                                        $vehicleRegionsValArray['']='';
                                                       }
                                                    }
                                                    $vehicleRegions = $vehicleRegionsValArray;
                                                }
                                                else
                                                {
                                                   $vehicleRegions = config('config-variables.vehicleRegions');

                                                }
                                                asort($vehicleRegions);

                                            ?>
                                        @foreach (array_chunk($vehicleRegions, ceil(count($vehicleRegions) / 2), true) as $chunk)
                                            <div class="checkbox-list col-md-6">
                                                @foreach ($chunk as $key => $region)
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <label>
                                                            @if ($key == '')
                                                                <input {{ count($user->accessible_regions) == (count($vehicleRegions) - 1) ? 'checked="checked"' : '' }} type="checkbox" name="accessible_regions[]" class="accessible-regions-checkbox" value="" onclick="toggleAllRegions(this);">
                                                                All
                                                            @else
                                                                <input {{ !is_null($user->accessible_regions) && in_array($key, $user->accessible_regions) ? 'checked="checked"' : '' }} type="checkbox" name="accessible_regions[]" class="accessible-regions-checkbox regions-group" value="{{ $key }}">
                                                                {{ $region }}
                                                            @endif
                                                        </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach

                                    </div>
                                    */ ?>
                                        <div class="checkbox-list">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label>
                                                        <input type="checkbox" id="all_accessible_region" value="" {{ (count($user->regions) == count($allVehicleDivisionsList)) ? 'checked="checked"' : '' }}>
                                                        All
                                                    </label>
                                                </div>
                                            </div>
                                            @foreach ($allVehicleDivisionsList as $division => $regions)
                                                <div class="all_divisions">
                                                    <label>
                                                        <input type="checkbox" name="accessible_divisions[]" class="accessible-divisions-checkbox divisions-group  division-{{ $division }}" value="{{ $division }}" {{ !is_null($user->divisions->pluck('id')->toArray()) && in_array($division, $user->divisions->pluck('id')->toArray()) ? 'checked="checked"' : '' }}>
                                                        {{ $vehicleDivisions[$division] }}
                                                    </label>
                                                </div>
                                                <div class="nested-regions">
                                                    <label>
                                                        <input type="checkbox" class="all_division_region" value="{{ $division }}" {{ !is_null($user->divisions->pluck('id')->toArray()) && in_array($division, $user->divisions->pluck('id')->toArray()) ? '' : 'disabled="disabled"' }}>
                                                        All
                                                    </label>

                                                    <div class="row">
                                                        @foreach(array_chunk($regions, 2, true) as $chunk)
                                                            @foreach($chunk as $region_id => $region_name)
                                                                <div class="col-md-4">
                                                                    <div class="all_regions">
                                                                        <label>
                                                                            <input type="checkbox" name="accessible_regions[]" class="accessible-regions-checkbox-{{ $division }} regions-group" value="{{ $region_id }}" {{ !is_null($user->regions->pluck('id')->toArray()) && in_array($region_id, $user->regions->pluck('id')->toArray()) ? 'checked="checked"' : '' }} {{ !is_null($user->divisions->pluck('id')->toArray()) && !in_array($division, $user->divisions->pluck('id')->toArray()) ? 'disabled="disabled"' : '' }}>
                                                                            {{ $region_name }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    <div class="row">
                                        <div class="accessible-regions-checkbox-wrapper-error col-md-12"></div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="form-group">
                                <div class="col-md-2 text-right" for="accessible_regions[]"><label>Divisions and regions*:</label></div>
                                <div class="col-md-10 accessible-regions-checkbox-wrapper">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="checkbox-list">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label>
                                                            <input type="checkbox" id="all_accessible_region" value="" {{ (count($user->regions) == count($allVehicleDivisionsList)) ? 'checked="checked"' : '' }}>
                                                            All
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    @foreach(array_chunk($allVehicleDivisionsList, 2, true) as $chunk)
                                                        @foreach($chunk as $division => $regions)
                                                            <div class="col-md-4">
                                                                <div class="all_regions">
                                                                    <label>
                                                                        <input type="checkbox" name="accessible_regions[]" class="accessible-regions-checkbox regions-group" value="{{ $division }}" {{ !is_null($user->regions->pluck('id')->toArray()) && in_array($division, $user->regions->pluck('id')->toArray()) ? 'checked="checked"' : '' }}>
                                                                        {{ $regions }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="accessible-regions-checkbox-wrapper-error col-md-12"></div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
            <div class="tab-pane user-permission-error-block mt-22" id="edit_user_notification">
                <div class="form-group mb-10">
                    <div class="col-md-12">
                        <label class="margin-bottom-10">Defect email notifications (based on vehicle permissions):</label>
                    </div>
                    <div class="col-md-12 roles-checkbox-wrapper">
                        <input class="form-check-input" name="defectEmailNotification"
                            type="checkbox" id="defectEmailNotification" @if(in_array($defectEmailNotificationId,$givenRolesArray)) checked="checked" @endif  value="13">New defect and status updates
                    </div>
                </div>
                @if(count($userNotifications) > 0)
                        @foreach ($userNotifications as $groupNotification => $notifications)
                            <div class="form-group mb-10">
                                <div class="col-md-12 roles-checkbox-wrapper">
                                    <label class="margin-bottom-10">
                                        {{ str_replace('_', ' ', ucfirst($groupNotification))}}
                                    </label>

                                    @foreach ($notifications as $key => $chunk)
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="checkbox" name="event_type[]"  class="roles-checkbox" value="{{ $chunk['eventTypeKey'] }}" @if($chunk['is_enabled'] == 1) checked="checked" @endif>
                                            {{ $chunk['eventTypeEvent'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                @else
                    <div class="form-group mb-10">
                        @foreach ($userNotifications as $groupNotification => $notifications)
                            <div class="col-md-12 roles-checkbox-wrapper">
                                <label class="margin-bottom-10">
                                    {{ str_replace('_', ' ', ucfirst($groupNotification))}}
                                </label>

                                @foreach ($notifications as $key => $chunk)
                                    <div class="row">
                                        <label class="col-md-12">
                                            <input type="checkbox" name="event_type[]" data-val="{{ $key }}" class="roles-checkbox" value="{{ $key }}" >
                                    {{ $chunk }}</label>
                                </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <div class="col-md-offset-2 col-md-8 ">
        <div class="btn-group pull-left width100">
            <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn red-rubine btn-padding submit-button col-md-6">Update</button>
        </div>
    </div>
            <!-- <button type="submit" class="btn red-rubine" id="submit-button">Save</button>
            <button type="button" class="btn grey-gallery" data-dismiss="modal">Cancel</button> -->
</div>
   <!--  <button type="submit" class="btn red-rubine" id="submit-button">Update</button>
    <button type="button" class="btn grey-gallery" data-dismiss="modal">Cancel</button> -->

{!! BootForm::close() !!}
