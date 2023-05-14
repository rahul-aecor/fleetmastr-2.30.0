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
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-grow-1 margin-right-15" style="width: calc(100% - 109px);">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id='quickSearchInput' placeholder="Search by company name">
                                    </div>
                                </div>
                                <div style="flex-shrink: 0">
                                    <div class="form-group margin-bottom0">
                                        <div class="d-flex mb-0">
                                            <button class="btn red-rubine btn-h-45" type="submit" id="search">
                                                <i class="jv-icon jv-search"></i>
                                            </button>
                                            <button class="btn btn-success grey-gallery grid-clear-btn-workshop btn-h-45">
                                                <i class="jv-icon jv-close"></i>
                                            </button>
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
                        <div>Workshop User List</div>
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
                        <span onclick="clickRefresh();" class="m5 jv-icon jv-reload"></span>
                        <span onclick="clickExport();" class="m5 jv-icon jv-download"></span>
                        <a href="#portlet-user" data-toggle="modal" class="config btn btn-sm gray_btn_border table-group-action-submit title_right_btn"><i class="jv-icon jv-plus "></i> Add new workshop user</a>
                    </div>
                </div>
                <div class="portlet-body work_table">
                    <div class="jqgrid-wrapper user_page_table">
                        <table id="jqGrid" class="table-striped table-bordered table-hover check-table" data-type="workshops"></table>
                        <div id="jqGridPager"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal to edit record starts here -->
    <div id="workshop-user-edit" class="modal modal-fix  fade" tabindex="-1" data-width="1050" data-background="static" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="portlet box">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">
                        Edit Workshop User
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
                    {!! BootForm::close() !!}
                </div>
                <div class="modal-footer">
                <div class="col-md-offset-2 col-md-8 ">
                        <div class="btn-group pull-left width100">
                            <button id="addWorkshopCompanyCancel" type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                            <button id="addWorkShopCompanyBtn" type="submit" class="btn red-rubine btn-padding submit-button col-md-6">Save</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal to view all companies starts here -->
    <div id="view-company" class="modal modal-fix  fade" tabindex="-1" data-background="static" aria-labelledby="myModalLabel" data-width="630" data-height="400" aria-hidden="true">
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
    <div id="portlet-user" class="modal modal-fix  fade user_modal" tabindex="-1" data-width="1050" data-background="static">
        {!! BootForm::openHorizontal(['md' => [4, 8]])->addClass('form-bordered form-validation workshop-user-form')->action('/workshops')->id('addUser') !!}
        <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
            <h4 class="modal-title">Add New Workshop User</h4>
            <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                <i class="jv-icon jv-close"></i>
            </a>
        </div>
        <div class="modal-body">
            <div class="tabbable-custom tabbable-rubine nav-justified margin-bottom0">
                <div class="tab-content rl-padding">
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
                                               <a href="#view-company" id="view_company" data-path="workshop" data-toggle="modal" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center" style="margin-right: 0">
                                                     <i class="jv-icon jv-edit"></i>
                                                 </a>
                                              </div>
                                        </div>
                                        {!! BootForm::text('Username/Email*:', 'email') !!}
                                        {!! BootForm::text('Mobile number:', 'mobile') !!}
                                        {!! BootForm::text('Landline number:', 'landline') !!}
                                    </div>
                                    <div class="col-md-6 user-form-right-pane">
                                        {!! BootForm::text('Address1:', 'address1') !!}
                                        {!! BootForm::text('Address2:', 'address2') !!}
                                        {!! BootForm::text('Town/City:', 'town_city') !!}
                                        {!! BootForm::text('Postcode:', 'postcode') !!}
                                        {!! BootForm::select('Enable account login:', 'enable_login')->options(['1' => 'Yes', '0' => 'No'])->addClass('select2me') !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
        <div class="col-md-offset-2 col-md-8 ">
            <div class="btn-group pull-left width100">
                <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn red-rubine btn-padding submit-button col-md-6">Save</button>
            </div>
        </div>
        </div>
        {!! BootForm::close() !!}
    </div>
@endsection

@push('scripts')
<script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/workshop_user.js') }}" type="text/javascript"></script>
<script src="{{ elixir('js/company-edit.js') }}" type="text/javascript"></script>
@endpush
