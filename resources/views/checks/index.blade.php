@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/bootstrap-daterangepicker/daterangepicker-bs3.css') }}" rel="stylesheet" type="text/css"/>    
@endsection

@section('scripts')
    <script src="{{ elixir('js/bootstrap-daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/checks.js') }}" type="text/javascript"></script>
@endsection

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <!-- <div class="page-bar">
        @if(isset($flowFromPage) && $flowFromPage=='vehicleSearch')            
            {!! Breadcrumbs::render('search_details_checks', $id) !!}
        @endif
    </div> -->
    <div class="tabbable-custom tabbable-rubine nav-justified margin-bottom0">
        <ul class="nav nav-tabs nav-justified">
            <li class="active">
                <a href="#quick_search" data-toggle="tab">
                Quick Search </a>
            </li>
            <li>
                <a href="#advanced_search" data-toggle="tab">
                Advanced Search </a>
            </li>            
        </ul>
        <div class="tab-content rl-padding">
            <div class="tab-pane active" id="quick_search">
                <form class="form row d-flex align-items-center gutters-tiny" id="checks-quick-filter-form">
                    <div class="col-md-3">
                        <div class="check_search">
                            @if(Auth::user()->isUserInformationOnly())
                            <div class="form-group margin-bottom0">
                                {!! Form::text('registration1', null, ['class' => 'form-control', 'placeholder' => 'Vehicle registration', 'id' => 'registration1']) !!}
                                <small class="text-danger">{{ $errors->first('registration') }}</small>
                            </div>
                            @else
                            <div class="form-group margin-bottom0">
                                {!! Form::text('registration', null, ['class' => 'form-control', 'placeholder' => 'Vehicle registration', 'id' => 'vehicle-registration']) !!}
                                <small class="text-danger">{{ $errors->first('registration') }}</small>
                            </div>
                            @endif                  
                        </div>
                    </div>
                    <div>
                        <span class="text-center">or</span>
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex">
                            <div class="flex-grow-1 margin-right-15" style="width: calc(100% - 109px);">
                                <div class="check_search">
                                    <div class="form-group margin-bottom0">                                
                                        {!! Form::text('checks_created_by', null, ['class' => 'form-control data-filter', 'placeholder' => 'Created by', 'id' => 'checks_created_by']) !!}
                                    </div>
                                </div>
                            </div>
                            <div style="flex-shrink: 0">
                                <div class="form-group margin-bottom0">
                                    <div class="d-flex mb-0">
                                        <button class="btn btn-h-45 red-rubine js-quick-search-btn" type="submit">
                                            <i class="jv-icon jv-search"></i>
                                        </button>
                                        <button class="btn grid-clear-btn btn-h-45 grey-gallery " id="" style="margin-right: 0">
                                            <i class="jv-icon jv-close"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="row js-quick-search-error-msg" style="display: none">
                    <div class="col-md-12">
                        <div class="form-group has-error">
                          <span class="help-block help-block-error"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="advanced_search">
                <div class="row gutters-tiny">
                    <div class="col-md-12">
                        <form class="form" id="checks-advanced-filter-form">
                            <div class="row gutters-tiny">
                                <div class="col-md-3">
                                    @if(Auth::user()->isUserInformationOnly())
                                        <div class="form-group">
                                            {!! Form::text('region',  null, ['class' => 'form-control', 'placeholder' => 'All regions','id' => 'region','disabled'=>'disabled']) !!}
                                        <small class="text-danger">{{ $errors->first('region') }}</small>
                                        </div>
                                    @else 
                                        {{-- <div class="form-group">
                                        {!! Form::select('region', config('config-variables.userAccessibleRegions'), null, ['id' => 'region', 'class' => 'form-control select2-vehicle-region']) !!}
                                        <small class="text-danger">{{ $errors->first('region') }}</small>
                                        </div> --}}
                                        <div class="form-group">
                                             {!! Form::select('region', $region_for_select, null, ['id' => 'region', 'class' => 'form-control select2-vehicle-region']) !!}
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-2">
                                    {!! Form::select('checkType', $vehicleCheckType, null, ['id' => 'checkType', 'class' => 'form-control select2-check-type', 'data-placeholder' => 'Select check type']) !!}
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::select('status', ['' => '', 'All' => 'All', 'RoadWorthy' => 'Roadworthy', 'SafeToOperate' => 'Safe to operate', 'UnsafeToOperate' => 'Unsafe to operate'], null, ['id' => 'status', 'class' => 'form-control select2-vehicle-status']) !!}
                                        <small class="text-danger">{{ $errors->first('status') }}</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex">
                                        <div class="flex-grow-1 margin-right-15" style="width: calc(100% - 109px);">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    {!! Form::text('range', null, ['class' => 'form-control bg-white cursor-pointer', 'placeholder' => 'Date', 'readonly' => 'readonly']) !!}
                                                    <span class="input-group-addon js-daterangepicker-button" id="basic-addon1"><i class="jv-icon jv-calendar font-weight-700"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="" style="flex-shrink: 0">
                                            <div class="form-group margin-bottom0">
                                                <div class="d-flex mb-0">
                                                    <button class="btn btn-h-45 red-rubine" type="submit">
                                                        <i class="jv-icon jv-search"></i>
                                                    </button>
                                                    <button class="btn grid-clear-btn btn-h-45 grey-gallery">
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
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="portlet box marginbottom0">
                <div class="portlet-title">
                    <div class="caption blue_bracket" style="min-width: 350px;">
                        Recent Vehicle Checks&nbsp;<span id="selected-region-name">All Regions</span>
                    </div>
                    {{-- <div class="actions new_btn align-self-end"> --}}
                    <div class="actions new_btn">
                        <a href="javascript:void(0)" onclick="clickResetGrid()" class="btn red-rubine btn-padding">Reset columns</a>
                        <span onclick="clickShowHideColumn()" class="m5 jv-icon jv-chevron-square-down js-show-hide-col-bt"></span>
                        {{-- <span onclick="clickSearch();" class="m5 jv-icon jv-search"></span> --}}
                        <span onclick="clickRefresh();" class="m5 jv-icon jv-reload"></span> 
                        <span onclick="clickExport();" class="m5 jv-icon jv-download"></span>
                        <!-- <a href="{{ route('checks.create') }}" class="config btn btn-sm gray_btn_border table-group-action-submit title_right_btn"><i class="jv-icon jv-plus"></i> Add new check</a> -->
                        <!-- <button onclick="clickExport();" class="btn grey-gallery btn-sm">Export</button> -->
                    </div>  
                </div>
                <div class="portlet-body">
                    <div class="jqgrid-wrapper checks-page">
                        <table id="jqGrid" class="table-striped table-bordered table-hover check-table" data-type="checks"></table>
                        <div id="jqGridPager"></div>    
                    </div> 
                </div>
            </div>
        </div>
    </div>
@endsection