@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal-bs3patch.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-modal/bootstrap-modal.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('plugin-scripts')
    <script src="{{ elixir('js/company.js') }}" type="text/javascript"></script>

    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modal.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-modal/bootstrap-modalmanager.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/bootstrap-editable/bootstrap-editable/js/bootstrap-editable.js') }}" type="text/javascript"></script>
@endsection

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    @if (count($errors) > 0)
        <div class="alert alert-danger bg-red-rubine">
            {{-- <p><strong>You have some form errors. Please check below.</strong></p>  --}}
            <p><strong>Please complete the errors highlighted below.</strong></p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row portlet-search">
        <div class="col-md-12">
            <div class="clearfix">
                <form class="form" id="defects-filter-form">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="row gutters-tiny">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::select('region', ['' => ''] + $userRegionsSearchData, null, ['id' => 'userregions', 'class' => 'form-control']) !!}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    {!! Form::text('quickSearchInput', null, ['class' => 'form-control data-filter', 'placeholder' => 'Search by name or email', 'id' => 'quickSearchInput']) !!}
                                </div>
                                <div class="col-md-4 telematics_lastnameJourney">
                                    <div class="d-flex">
                                        <div class="flex-grow-1 margin-right-10" style="width: calc(100% - 109px);">
                                            {!! Form::text('driver_tag', null, ['class' => 'form-control data-filter', 'placeholder' => 'Search by driver tag', 'id' => 'driverTag']) !!}
                                            {{-- {!! Form::select('driver_tag', $driverTag, null, ['id' => 'driverTag', 'class' => 'form-control select2-driver-tag']) !!} --}}
                                        </div>
                                        <div style="flex-shrink: 0">
                                            <div class="form-group margin-bottom0">
                                                <div class="d-flex mb-0">
                                                    <button class="btn red-rubine btn-h-45" type="submit" id="search">
                                                        <i class="jv-icon jv-search"></i>
                                                    </button>
                                                    <button class="btn btn-success grey-gallery grid-clear-btn-user btn-h-45">
                                                        <i class="jv-icon jv-close"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="portlet box user-list-portlet marginbottom0">
                <div class="portlet-title">
                    <div class="caption">
                        <div>User List</div>
                        <div class="actions filter-user">
                            <input type="hidden" id="show_deleted_flag" value="1"/>
                            <label class="control-label mb-0" for="show_deleted_users">
                                <input type="checkbox" id="show_deleted_users" name="show_deleted_users">
                                Show inactive users
                            </label>
                        </div>
                    </div>
                    <div class="actions new_btn align-self-end">
                        <a href="javascript:void(0)" onclick="clickResetGrid()" class="btn red-rubine btn-padding">Reset columns</a>
                        <span onclick="clickShowHideColumn()" class="m5 jv-icon jv-chevron-square-down js-show-hide-col-bt"></span>
                        <!-- <span onclick="clickSearch();" class="m5 fa fa-search"></span> -->
                        <span onclick="clickRefresh();" class="m5 jv-icon jv-reload"></span>
                        <span onclick="clickExport();" class="m5 jv-icon jv-download"></span>
                        <a href="#portlet-user" data-toggle="modal" class="config btn btn-sm gray_btn_border table-group-action-submit title_right_btn"><i class="jv-icon jv-plus "></i> Add new user</a>
                        <!-- <button onclick="clickExport();" class="btn grey-gallery btn-sm">Export</button> -->
                    </div>
                </div>
                <div class="portlet-body work_table">
                    <div class="jqgrid-wrapper user_page_table">
                        <table id="jqGrid" class="table-striped table-bordered table-hover check-table" data-type="users"></table>
                        <div id="jqGridPager"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal to edit record starts here -->
    <div id="user-edit" class="modal modal-fix  fade" tabindex="-1" data-width="1050" data-background="static" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="portlet box">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">
                        Edit User
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body" id="ajax-modal-content">
                    <!-- this content wil be loaded by ajax -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal to add company starts here -->
    <div id="add-company" class="modal modal-fix  fade" tabindex="-1" data-background="static" aria-labelledby="myModalLabel" data-width="630" aria-hidden="true">
        <div class="modal-dialog" style="margin-top: 0; margin-bottom: 0;">
            <div class="portlet box">
                <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                  <h4 class="modal-title">Add Company</h4>
                    <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                        <i class="jv-icon jv-close"></i>
                    </a>
                </div>
                <div class="modal-body" id="ajax-modal-content">
                    @if (count($errors) > 0)
                        <div class="alert alert-danger bg-red-rubine">
                            <!-- <p><strong>You have some form errors. Please check below.</strong></p>  -->
                            <p><strong>Please complete the errors highlighted below.</strong></p>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    {!! BootForm::openHorizontal(['md' => [3, 9]])->addClass('form-bordered form-validation')->id('addCompany') !!}
                        {!! BootForm::text('Company*:', 'name')->addClass('add_company') !!}
                        {{-- {!! BootForm::text('Abbreviation*:', 'abbreviation') !!} --}}
                    {!! BootForm::close() !!}
                </div>
                <div class="modal-footer">
                <div class="col-md-offset-2 col-md-8 ">
                        <div class="btn-group pull-left width100">
                            <button type="button" id="addWorkshopCompanyCancel" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                            <button id="addCompanyBtn" type="submit" class="btn red-rubine btn-padding submit-button col-md-6">Save</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal to add company starts here -->
    <div id="view-company" class="modal modal-fix  fade" tabindex="-1" data-background="static" aria-labelledby="myModalLabel" data-width="630" aria-hidden="true">
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
                    <tbody id="view_all_companies">
                    </tbody>
                  </table>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal to create new record starts here -->
    <div id="portlet-user" class="modal modal-fix  fade user_modal" tabindex="-1" data-width="1050" data-backdrop="static" data-keyboard="false">
        {!! BootForm::openHorizontal(['md' => [4, 8]])->addClass('form-bordered form-validation user-form')->action('/users')->id('addUser') !!}
        <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
            <h4 class="modal-title">Add New User</h4>
            <a class="font-red-rubine user-form-cancle" data-dismiss="modal" aria-label="Close">
                <i class="jv-icon jv-close"></i>
            </a>
        </div>
        <div class="modal-body">
            <!------------------- -->
            <div class="tabbable-custom tabbable-rubine nav-justified margin-bottom0">
                <ul class="nav nav-tabs nav-justified">
                    <li class="active">
                        <a href="#add_user" data-toggle="tab">
                        User Details </a>
                    </li>
                    <li>
                        <a href="#user_permission" data-toggle="tab">
                        User Permissions </a>
                    </li>
                    <li style="width: 170px;" id="message_permission_tab" class="d-none">
                        <a href="#message_permission" data-toggle="tab">
                        Message Permissions </a>
                    </li>
                    <li style="width: 160px;">
                        <a href="#vehicle_permission" data-toggle="tab">
                        Vehicle Permissions </a>
                    </li>
                    <li>
                        <a href="#add_user_notification" data-toggle="tab">
                        User Notifications </a>
                    </li>
                </ul>
                <div class="tab-content rl-padding">
                    <div class="tabErrorAlert">Please confirm mandatory information on all tabs.</div>
                    <div class="tab-pane active" id="add_user">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="clearfix">
                                    <div class="col-md-6 userFirstCol">
                                        {!! BootForm::text('First name*:', 'first_name') !!}
                                        {!! BootForm::text('Last name*:', 'last_name') !!}

                                        <div class="form-group row company--modal">
                                            <label class="col-md-4 control-label" for="company_id">Company*:</label>
                                            <div class="company--modal-col-55" style="width:46%;">
                                              <select class="form-control select2me " id="company_id" name="company_id">
                                                <?php foreach ($companyList as $key => $company): ?>
                                                    <option value="{{ $key }}">{{ $company }}</option>
                                                <?php endforeach ?>
                                              </select>
                                            </div>

                                            <div class="company--modal-col desboard_thumbnail">
                                              <a href="#add-company" data-toggle="modal" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center" style="margin-right: 0">
                                                    <i class="jv-icon jv-plus"></i>
                                                </a>
                                             </div>
                                             <div class="company--modal-col desboard_thumbnail">
                                               <a href="#view-company" id="view_company" data-path="user" data-toggle="modal" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center" style="margin-right: 0">
                                                     <i class="jv-icon jv-edit"></i>
                                                 </a>
                                              </div>
                                        </div>

                                        <!-- {!! BootForm::select('Company*:', 'company_id')->options($companyList)->addClass('select2me') !!} -->
                                        {!! BootForm::text('Email:', 'email') !!}

                                        {{-- <span id="pass_chk">
                                        {!! BootForm::password('Password*:', 'password') !!}
                                        </span>  --}}
                                        {!! BootForm::text('Job title:', 'job_title') !!}
                                        {!! BootForm::text('Mobile number:', 'mobile') !!}
                                        {!! BootForm::text('Landline number:', 'landline') !!}
                                        <div class="form-group">
                                            <div class="col-md-4 text-right" for="driver_tag_key">
                                                <label>Driver tag:</label>
                                            </div>
                                            <div class="col-md-8 roles-checkbox-wrapper">
                                                <label class="radio-default-overright margin-right-10">
                                                    <input class="form-check-input" name="driver_tag" type="radio" id="driverNoneKey" value="none">None
                                                </label>
                                                <label class="radio-default-overright margin-right-10">
                                                    <input class="form-check-input driver-tag-key" name="driver_tag" type="radio" id="driverDallasKey" value="dallas_key">Dallas key
                                                </label>
                                                <label class="radio-default-overright margin-right-10">
                                                    <input class="form-check-input" name="driver_tag" type="radio" id="driverRfidCard" value="rfid_card">RFID card
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group driver-tag-key">
                                            <div class="col-md-4 text-right">
                                                <label class="driver-tag-text-change">Dallas key:</label>
                                            </div>
                                            <div class="col-md-8">
                                                <input type="text" name="driver_tag_key" id="driver_tag_key" class="form-control">
                                            </div>
                                        </div>
                                        {!! BootForm::text('Fuel card number:', 'fuel_card_number') !!}
                                    </div>
                                    <div class="col-md-6 user-form-right-pane">
                                        {!! BootForm::text('ID:', 'engineer_id') !!}
                                        {{-- {!! BootForm::hidden('is_active')->value(0) !!} --}}
                                        {!! BootForm::text('Username*:', 'username')->readonly() !!}
                                         {!! BootForm::select('Enable account login:', 'enable_login')->options(['1' => 'Yes', '0' => 'No'])->addClass('select2me') !!}
                                        {{-- {!! BootForm::checkbox('Enable account login', 'is_active') !!} --}}
                                        {{-- {!! BootForm::hidden('is_lanes_account')->value(0) !!} --}}
                                        {{-- {!! BootForm::checkbox('Lanes account', 'is_lanes_account') !!} --}}
                                        {!! BootForm::text('IMEI number:', 'imei') !!}
                                        {{-- {!! BootForm::text('Line manager:', 'line_manager') !!} --}}
                                        {!! BootForm::text('Line manager:', 'line_manager')->addClass('js-line-manager')->placeholder('Select') !!}
                                        <div class="form-group js-line-manager-number-div" style="display: none">
                                            <label class="col-md-4 control-label" for="field_manager_phone">Line manager number:</label>
                                            <div class="col-md-8">
                                                <input type="text" name="field_manager_phone" id="field_manager_phone" class="form-control" readonly="readonly">
                                            </div>
                                        </div>
                                        {!! BootForm::select('Division:', 'user_division_id')->options(['' => ''] + $userDivisions)->addClass('select2me division-value')->placeholder('Select') !!}
                                        <div class="add-user-region-value">
                                            {!! BootForm::select('Region:', 'user_region_id')->options([])->addClass('select2me add-user-region')->placeholder('Select') !!}
                                        </div>
                                        {!! BootForm::select('Base location:', 'user_locations_id')->options([])->addClass('select2me add-user-base-location')->placeholder('Select') !!}
                                        <div class="form-group">
                                            <label class="col-md-4 control-label ">Fuel card issued:</label>
                                            <div class="col-md-4">
                                                <label class="checkbox-inline pt-0 fuel_card">
                                                  <input type="checkbox" id="fuel_card_issued" data-toggle="toggle" data-on="Yes" data-off="No"
                                                  name="fuel_card_issued">
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group js_fuel_card_personal_use" style="display: none">
                                            <label class="col-md-4 control-label ">Fuel card for personal use (BIK):</label>
                                            <div class="col-md-4">
                                                <label class="checkbox-inline pt-0 fuel_card">
                                                  <input type="checkbox" id="fuel_card_personal_use" data-toggle="toggle" data-on="Yes" data-off="No"
                                                  name="fuel_card_personal_use">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane user-permission-error-block mt-22" id="user_permission">
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
                                                    <input type="radio" name="roles[]" class="roles-types-radio" value="1" onclick="toggleSuperAdmin(this);">Super admin
                                                </label>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="radio-default-overright">
                                                    <input type="radio" name="roles[]" data-val="" class="roles-checkbox" value="14"  onclick="userInformationOnly(this);">User information only
                                                </label>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="radio-default-overright">
                                                <input type="radio" name="roles[]" data-val="" class="roles-checkbox" value="8"  onclick="AppAccessOnly(this);">App access only
                                                </label>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="radio-default-overright">
                                                <input type="radio" name="roles[]" data-val="" class="roles-checkbox" value=""  onclick="bespokeClick(this);">Bespoke access
                                                </label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="roles-checkbox-wrapper-error col-md-12"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-2 text-right" for="roles[]">
                                        <label>Desktop permissions:</label>
                                    </div>
                                    <div class="col-md-10 roles-checkbox-wrapper">
                                        <div class="row">
                                            @foreach (array_chunk($rolesList, ceil(count($rolesList) / 2), true) as $chunk)
                                                @foreach ($chunk as $index => $role)
                                                    @if($role == 'Dashboard (costs)' && !setting('is_fleetcost_enabled'))
                                                       <?php continue; ?>
                                                    @endif
                                                    <div class="">
                                                        @if ($index != 1 && $index != 14)
                                                            <label class="col-md-3 d-block">
                                                                <input type="checkbox" name="roles[]" data-val="{{ $role }}" class="roles-checkbox group {{ $role == 'Messaging' ? 'js-msg-checkbox' : ''}}" onclick="toggleOther(this);" value="{{ $index }}">
                                                            {{ ucfirst(strtolower($role)) }}</label>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @endforeach
                                        </div>
                                        <!-- <div class="row">
                                            <div class="roles-checkbox-wrapper-error col-md-12"></div>
                                        </div> -->
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-2 text-right" for="roles[]">
                                        <label>Mobile permissions:</label>
                                    </div>
                                    <div class="col-md-10 roles-checkbox-wrapper">
                                        <div class="row">
                                            @foreach (array_chunk($mobileRolesList, ceil(count($mobileRolesList) / 2), true) as $chunk)
                                                <div class="col-md-3">
                                                @foreach ($chunk as $index => $role)
                                                    <label>
                                                        <input type="checkbox" name="roles[]" data-val="{{ $role }}" class="roles-checkbox group" onclick="toggleOther(this);" value="{{ $index }}">
                                                    {{ $role }}</label>
                                                @endforeach
                                                </div>
                                            @endforeach
                                        </div>
                                        <!-- <div class="row">
                                            <div class="roles-checkbox-wrapper-error col-md-12"></div>
                                        </div> -->
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="tab-pane vehicle-permission-error-block mt-22" id="message_permission">
                        <div class="row">
                            <div class="col-md-12">
                                @if(env('IS_DIVISION_REGION_LINKED_IN_USER'))
                                    <div class="form-group row">
                                        <div class="col-md-2 text-right" for="accessible_regions[]"><label>Divisions and regions*:</label></div>
                                        <div class="col-md-10 message-accessible-regions-checkbox-wrapper">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="checkbox-list">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <label>
                                                                    <input type="checkbox" id="all_accessible_region_message" value="">
                                                                    All
                                                                </label>
                                                            </div>
                                                        </div>
                                                        @foreach ($userRegion as $division => $regions)
                                                            <div class="message_all_divisions">
                                                                <label>
                                                                    <input type="checkbox" name="message_accessible_divisions[]" class="message-accessible-divisions-checkbox message-divisions-group  message-division-{{ $division }}" value="{{ $division }}">
                                                                    {{ $userDivisions[$division] }}
                                                                </label>
                                                            </div>
                                                            <div class="nested-regions">
                                                                <label>
                                                                    <input type="checkbox" class="message_all_division_region" value="{{ $division }}" disabled="disabled">
                                                                    All
                                                                </label>

                                                                <div class="row">
                                                                    @foreach(array_chunk($regions, 2, true) as $chunk)
                                                                        @foreach($chunk as $region_id => $region_name)
                                                                            <div class="col-md-4">
                                                                                <div class="message_all_regions">
                                                                                    <label>
                                                                                        <input type="checkbox" name="message_accessible_regions[]" class="message-accessible-regions-checkbox-{{ $division }} message-regions-group" value="{{ $region_id }}" disabled="disabled">
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
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 message-accessible-regions-checkbox-wrapper-error"></div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="form-group row">
                                        <div class="col-md-2 text-right" for="accessible_regions[]"><label>Divisions and regions*:</label></div>
                                        <div class="col-md-10 message-accessible-regions-checkbox-wrapper">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="checkbox-list">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <label>
                                                                    <input type="checkbox" id="all_accessible_region_message" value="">
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
                                                                                <input type="checkbox" name="message_accessible_regions[]" class="message-accessible-regions-checkbox message-regions-group" value="{{ $division }}">
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
                                                <div class="col-md-12 message-accessible-regions-checkbox-wrapper-error"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane vehicle-permission-error-block mt-22" id="vehicle_permission">
                        <div class="row">
                            <div class="col-md-12">
                                @if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'))
                                    <div class="form-group row">
                                        <div class="col-md-2 text-right" for="accessible_regions[]"><label>Divisions and regions*:</label></div>
                                        <div class="col-md-10 accessible-regions-checkbox-wrapper">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="checkbox-list">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <label>
                                                                    <input type="checkbox" id="all_accessible_region" value="">
                                                                    All
                                                                </label>
                                                            </div>
                                                        </div>
                                                        @foreach ($allVehicleDivisionsList as $division => $regions)
                                                            <div class="all_divisions">
                                                                <label>
                                                                    <input type="checkbox" name="accessible_divisions[]" class="accessible-divisions-checkbox divisions-group  division-{{ $division }}" value="{{ $division }}">
                                                                    {{ $vehicleDivisions[$division] }}
                                                                </label>
                                                            </div>
                                                            <div class="nested-regions">
                                                                <label>
                                                                    <input type="checkbox" class="all_division_region" value="{{ $division }}" disabled="disabled">
                                                                    All
                                                                </label>

                                                                <div class="row">
                                                                    @foreach(array_chunk($regions, 2, true) as $chunk)
                                                                        @foreach($chunk as $region_id => $region_name)
                                                                            <div class="col-md-4">
                                                                                <div class="all_regions">
                                                                                    <label>
                                                                                        <input type="checkbox" name="accessible_regions[]" class="accessible-regions-checkbox-{{ $division }} regions-group" value="{{ $region_id }}" disabled="disabled">
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
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 accessible-regions-checkbox-wrapper-error"></div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="form-group row">
                                        <div class="col-md-2 text-right" for="accessible_regions[]"><label>Divisions and regions*:</label></div>
                                        <div class="col-md-10 accessible-regions-checkbox-wrapper">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="checkbox-list">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <label>
                                                                    <input type="checkbox" id="all_accessible_region" value="">
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
                                                                                <input type="checkbox" name="accessible_regions[]" class="accessible-regions-checkbox regions-group" value="{{ $division }}">
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
                                                <div class="col-md-12 accessible-regions-checkbox-wrapper-error"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane mt-22" id="add_user_notification">
                        <div class="form-group mb-10">
                            <div class="col-md-12 roles-checkbox-wrapper">
                                <label class="margin-bottom-10">
                                    Defect email notifications (all vehicles):
                                </label>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input class="form-check-input" name="newDefectEmailNotification" type="checkbox" id="newDefectEmailNotification" value="13">New defect and status updates
                                    </div>
                                </div>
                            </div>
                        </div>

                        @foreach ($userNoticationsList as $groupNotification => $notifications)
                             <div class="form-group mb-10">
                                <div class="col-md-12 roles-checkbox-wrapper">
                                    <label class="margin-bottom-10">
                                        {{ str_replace('_', ' ', ucfirst($groupNotification))}}
                                    </label>

                                    @foreach ($notifications as $key => $chunk)
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="checkbox" name="event_type[]" data-val="{{ $key }}" class="roles-checkbox " value="{{ $key }}">
                                            {{ $chunk }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        <!------------------- -->
        </div>
        <div class="modal-footer">
            <div class="col-md-offset-2 col-md-8 ">
                <div class="btn-group pull-left width100">
                    <button type="button" class="btn white-btn btn-padding col-md-6 user-form-cancel" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn red-rubine btn-padding submit-button col-md-6">Save</button>
                </div>
            </div>
            <!-- <button type="submit" class="btn red-rubine" id="submit-button">Save</button>
            <button type="button" class="btn grey-gallery" data-dismiss="modal">Cancel</button> -->
        </div>
        {!! BootForm::close() !!}
    </div>
@endsection

@push('scripts')
<script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/users.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/company-edit.js') }}" type="text/javascript"></script>

@endpush
