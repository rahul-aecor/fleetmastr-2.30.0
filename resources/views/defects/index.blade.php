@extends('layouts.default')

@section('plugin-styles')
    <link href="{{ elixir('css/bootstrap-daterangepicker/daterangepicker-bs3.css') }}" rel="stylesheet" type="text/css"/>    
@endsection

@section('scripts')
    <script src="{{ elixir('js/bootstrap-daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/defects.js') }}" type="text/javascript"></script>
@endsection

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <!-- <div class="page-bar">
        @if(isset($flowFromPage) && $flowFromPage=='vehicleSearch')            
            {!! Breadcrumbs::render('search_details_defects', $id) !!}
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
                <form class="form row d-flex align-items-center" id="defects-quick-filter-form">
                    <div class="col-md-3">
                        <div class="defect_search">
                            @if(Auth::user()->isUserInformationOnly())
                                <div class="form-group margin-bottom0">
                                    {!! Form::text('registration1', null, ['class' => 'form-control data-filter', 'placeholder' => 'Vehicle registration', 'id' => 'registration1']) !!}
                                </div>   
                            @else 
                                <div class="form-group margin-bottom0">
                                    {!! Form::text('registration', null, ['class' => 'form-control data-filter', 'placeholder' => 'Vehicle registration', 'id' => 'registration']) !!}
                                </div> 
                            @endif                      
                        </div>
                    </div>
                    <div class="px-4">
                        <span class="text-center">or</span>
                    </div>
                    <div class="col-md-3">
                        <div class="defect_search">
                            <div class="form-group margin-bottom0">                                
                                {!! Form::text('defect_id', null, ['class' => 'form-control', 'placeholder' => 'Enter defect ID', 'id' => 'defect_id']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="px-4">
                        <span class="text-center">or</span>
                    </div>
                    <div class="col-md-4" style="padding-right: 0">
                        <div class="defect_search">
                            <div class="form-group margin-bottom0">                                
                                {!! Form::text('driver_id', null, ['class' => 'form-control data-filter', 'placeholder' => 'Created by', 'id' => 'driver_id']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="defect_search">
                            <div class="form-group margin-bottom0">
                                <!-- {!! Form::text('defect_id', null, ['class' => 'form-control', 'placeholder' => 'Enter defect ID', 'id' => 'defect_id']) !!} -->
                                 <button class="btn btn-h-45 red-rubine pull-left js-quick-search-btn" type="submit">
                                    <i class="jv-icon jv-search"></i>
                                </button>
                                  <button class="btn grid-clear-btn btn-h-45 grey-gallery " id="vehicle-registration" style="margin-right: 0">
                                    <i class="jv-icon jv-close"></i>
                                </button>
                            </div>
                        </div>
                    </div>                    
                    {{--<div class="col-md-2">--}}
                        {{--<a href="{{ url('/defects/create') }}" class="btn blue btn-block grey-gallery"><i class="fa fa-plus"></i> Report defect</a>--}}
                    {{--</div>--}}
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
            <div class="row">
                <div class="col-md-12">
                    <form class="form" id="defects-advanced-filter-form">
                        <div class="row">
                            <div class="col-lg-3 col-md-12 col-sm-12">
                                @if(Auth::user()->isUserInformationOnly())
                                    <div class="form-group">
                                        {!! Form::text('region',  null, ['class' => 'form-control', 'placeholder' => 'All regions','id' => 'region','disabled'=>'disabled']) !!}
                                    <small class="text-danger">{{ $errors->first('region') }}</small>
                                    </div>
                                @else 
                                    <div class="form-group">
                                    {!! Form::select('region', $vehicleListing, null, ['id' => 'region', 'class' => 'form-control select2-vehicle-region']) !!}
                                    <small class="text-danger">{{ $errors->first('region') }}</small>
                                    </div>
                                @endif
                            </div>
                            <div class="col-lg-2 col-md-12 col-sm-12">
                                @if(Auth::user()->isUserInformationOnly())
                                    <div class="form-group">
                                        {!! Form::text('workshop_users2', null, ['class' => 'form-control data-filter', 'placeholder' => 'Allocated to', 'id' => 'workshop_users2']) !!}
                                    </div>
                                @elseif(Auth::user()->isWorkshopManager())
                                     <div class="form-group">
                                        {!! Form::text('workshop_users1', null, ['class' => 'form-control data-filter', 'placeholder' => 'My defects', 'id' => 'workshop_users1', 'disabled'=>'disabled']) !!}
                                    </div>
                                @else
                                    <div class="form-group">
                                        {!! Form::text('workshop_users', null, ['class' => 'form-control data-filter', 'placeholder' => 'Allocated to', 'id' => 'workshop_users']) !!}
                                    </div>
                                @endif

                                {{-- @if(Auth::user()->isWorkshopManager())
                                     <div class="form-group">
                                        {!! Form::text('workshop_users1', null, ['class' => 'form-control data-filter', 'placeholder' => 'My defects', 'id' => 'workshop_users1', 'disabled'=>'disabled']) !!}
                                    </div>
                                @endif --}}

                                
                                
                                {{-- @if(Auth::user()->isWorkshopManager())
                                    <div class="form-group">
                                        {!! Form::text('workshop_users1', null, ['class' => 'form-control data-filter', 'placeholder' => 'My defects', 'id' => 'workshop_users1', 'disabled'=>'disabled']) !!}
                                    </div>
                                @else
                                    <div class="form-group">
                                        {!! Form::text('workshop_users', null, ['class' => 'form-control data-filter', 'placeholder' => 'Allocated to', 'id' => 'workshop_users']) !!}
                                    </div>
                                @endif --}}
                            </div>
                            <div class="col-lg-2 col-md-12 col-sm-12">
                                @if(Auth::user()->isWorkshopManager())
                                <div class="form-group">
                                    {!! Form::select('status', ['' => '','Allocated' => 'Allocated', 'Under repair' => 'Under repair', 'Repair rejected' => 'Repair rejected', 'Discharged' => 'Discharged'], null, ['id' => 'status', 'class' => 'form-control']) !!}
                                </div>
                                @else
                                <div class="form-group">
                                    {!! Form::select('status', ['' => '','All' => 'All', 'Reported' => 'Reported', 'Acknowledged' => 'Acknowledged', 'Allocated' => 'Allocated', 'Under repair' => 'Under repair', 'Repair rejected' => 'Repair rejected', 'Discharged' => 'Discharged', 'Resolved' => 'Resolved'], null, ['id' => 'status', 'class' => 'form-control']) !!}
                                </div>
                                @endif
                            </div>
                            <div class="col-lg-3 col-md-12 col-sm-12">
                                <div class="form-group">
                                    <div class="input-group">
                                        {!! Form::text('range', null, ['class' => 'form-control', 'placeholder' => 'Date', 'readonly' => 'readonly']) !!}
                                        <span class="input-group-addon js-daterangepicker-button" id="basic-addon1"><i class="jv-icon jv-calendar font-weight-700"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="search_option">
                                    <!-- {!! Form::submit('Search', ['class' => 'btn btn-success grey-gallery']) !!}
                                    <span class="btn btn-success grey-gallery grid-clear-btn">Clear</span> -->
                                        <button class="btn btn-h-45 red-rubine pull-left" type="submit">
                                            <i class="jv-icon jv-search"></i>
                                        </button>
                                        <button class="btn grid-clear-btn btn-h-45 grey-gallery" style="margin-right: 0">
                                            <i class="jv-icon jv-close"></i>
                                        </button>
                                </div>
                            </div>
                            <!-- <div class="col-md-1">
                               <span class="btn btn-success grey-gallery grid-clear-btn">Clear</span>
                            </div> -->
                            {{--<div class="col-md-2">--}}
                                {{--<a href="{{ url('/defects/create') }}" class="btn blue btn-block grey-gallery"><i class="fa fa-plus"></i> Report defect</a>--}}
                            {{--</div>--}}
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
                <div class="portlet-title bg-red-rubine">
                    <div class="caption blue_bracket" style="min-width: 350px;">
                        Defects List&nbsp;<span id="selected-region-name">All Regions</span>
                    </div>
                    <?php if (!Auth::user()->isWorkshopManager()) {
                        ?>
                        <div class="actions new_btn align-self-end">
                            <a href="javascript:void(0)" onclick="clickResetGrid()" class="btn red-rubine btn-padding">Reset columns</a>
                            <span onclick="clickShowHideColumn()" class="m5 jv-icon jv-chevron-square-down js-show-hide-col-bt"></span>
                            {{-- <span onclick="clickSearch();" class="m5 fa fa-search"></span> --}}
                            <span onclick="clickRefresh();" class="m5 jv-icon jv-reload"></span> 
                            <span onclick="clickExport();" class="m5 jv-icon jv-download"></span>
                            <a href="{{ route('checks.create') }}" class="config btn btn-sm gray_btn_border table-group-action-submit title_right_btn"><i class="jv-icon jv-plus"></i> Add new defect</a>
                            <!-- <a href="{{ url('/defects/create') }}" class="btn blue grey-gallery btn-sm"><i class="fa fa-plus"></i> Report defect</a> -->
                            <!-- <button onclick="clickExport();" class="btn grey-gallery btn-sm">Export</button> -->
                        </div>
                        <?php
                    } ?>
                    

                </div>
                <div class="portlet-body">
                    <div class="jqgrid-wrapper">
                        <table id="jqGrid" class="table-striped table-bordered table-hover check-table" data-type="vehiclesDefects"></table>
                        <div id="jqGridPager"></div>    
                    </div> 
                </div>
            </div>
        </div>
    </div>

@endsection