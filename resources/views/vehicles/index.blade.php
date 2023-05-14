@extends('layouts.default')

@section('scripts')
    <script src="{{ elixir('js/jqgrid/jquery.jqGridHelper.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/vehicles.js') }}" type="text/javascript"></script>
@endsection

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <div class="tabbable-custom tabbable-rubine nav-justified margin-bottom0">
        <ul class="nav nav-tabs nav-justified">
            
            <li class="active">
                <a href="#quick_search" data-toggle="tab">
                Quick Search </a>
            </li>
            
            <li>
                <a href="#advanced_search" id="vs_advanced_search" data-toggle="tab">
                Advanced Search </a>
            </li>            
        
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="quick_search">
                <form id="vehicles-quick-filter-form">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="row align-items-center flex-grow-1">
                                    <div class="col-md-4">
                                        <div class="vehicle-select-box-wrapper vehicle_search_form d-flex mb-0">
                                            @if(Auth::user()->isUserInformationOnly())
                                                {!! Form::text('registration1', null, ['class' => 'form-control data-filter', 'placeholder' => 'Vehicle registration', 'id' => 'registration1']) !!}
                                            @else 
                                                {!! Form::text('registration', null, ['class' => 'form-control data-filter', 'placeholder' => 'Vehicle registration', 'id' => 'registration']) !!}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="vehicle-select-box-wrapper">
                                            <div class="form-group mb-0">
                                                {!! Form::select('status', $status_for_select, null, ['id' => 'status', 'class' => 'form-control select2-vehicle-status', 'data-placeholder' => 'Vehicle status']) !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        {!! Form::text('quickSearchLastName', null, ['class' => 'form-control data-filter', 'placeholder' => 'Last name', 'id' => 'quickSearchLastName']) !!}
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex">
                                        <div class="d-flex mb-0 margin-left-15" style="flex: none;">
                                            <button class="btn btn-h-45 red-rubine" type="submit">
                                                <i class="jv-icon jv-search"></i>
                                            </button>
                                        
                                            <button class="btn js-vehicle-grid-clear-btn btn-h-45 grey-gallery " style="margin-right: 0" onclick="clearVehicleGrid();">
                                                <i class="jv-icon jv-close"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <div class="col-md-4 work_ipadmargin">
                            <div class="form-group safari_change w-100 mb-0 btn-search-filter-wrapper">
                                {{-- <div class="btn-group work_btn_group pull-left" role="group">
                                </div> --}}
                                <div class="btn-group work_btn_group btn-group-equal" role="group">
                                  <span type="button" class="btn btn-default red-rubine js-work-filter-button" id="vehicle-filter-all">All</span>
                                  <span type="button" class="btn btn-default white-btn js-work-filter-button equal-width" id="vehicle-filter-today">Checked today</span>
                                  <span type="button" class="btn btn-default white-btn js-work-filter-button equal-width" id="vehicle-filter-not-today">Not checked today</span>
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
                <form id="vehicles-advanced-filter-form">
                    <div class="row">
{{--                         <div class="col-lg-2 col-md-12 col-sm-12">
                            <div class="form-group">
                                {!! Form::select('division', $division_for_select, null, ['id' => 'division', 'class' => 'form-control select2-vehicle-division', 'data-placeholder' => 'Vehicle division']) !!}
                            </div>      
                        </div> --}}
                        <div class="col-lg-4 col-md-12 col-sm-12">
                            @if(Auth::user()->isUserInformationOnly())
                                <div class="form-group">
                                    {!! Form::text('region', null, ['class' => 'form-control js-vehicle-region', 'placeholder' => 'All regions','id' => 'region','disabled'=>'disabled']) !!}
                                </div> 
                            @else 
                                <div class="form-group">
                                    {!! Form::select('region', $region_for_select, null, ['id' => 'region', 'class' => 'form-control select2-vehicle-region ']) !!}
                                </div>
                            @endif       
                        </div>
                        
                        <!-- <div class="col-md-1">
                            <div class="form-group">
                                {!! Form::select('category', $categories_for_select, null, ['id' => 'status', 'class' => 'form-control select2me', 'data-placeholder' => 'All HGV/Non-HGV']) !!}
                            </div>      
                        </div> -->
                        <div class="col-lg-2 col-md-12 col-sm-12">
                            @if(Auth::user()->isUserInformationOnly())
                                <div class="form-group">
                                    {!! Form::text('manufacturer1', null, ['id' => 'manufacturer1', 'class' => 'form-control data-filter', 'placeholder' => 'All manufacturers']) !!}
                                </div> 
                            @else 
                                <div class="form-group">
                                    {!! Form::text('manufacturer', null, ['id' => 'manufacturer', 'class' => 'form-control data-filter', 'placeholder' => 'All manufacturers']) !!}
                                </div> 
                            @endif
                        </div>
                        <div class="col-lg-2 col-md-12 col-sm-12">
                            @if(Auth::user()->isUserInformationOnly())
                                <div class="form-group">
                                    {!! Form::text('model1', null, ['id' => 'model1', 'class' => 'form-control data-filter', 'placeholder' => 'All models']) !!}
                                </div>
                            @else 
                                <div class="form-group">
                                    {!! Form::text('model', null, ['id' => 'model', 'class' => 'form-control data-filter', 'placeholder' => 'All models']) !!}
                                </div>
                            @endif
                        </div>
                        <div class="col-lg-2 col-md-12 col-sm-12">
                            @if(Auth::user()->isUserInformationOnly())
                                <div class="form-group">
                                    {!! Form::text('type1', null, ['id' => 'type1', 'class' => 'form-control data-filter', 'placeholder' => 'All types']) !!}
                                </div>   
                            @else 
                                <div class="form-group">
                                    {!! Form::text('type', null, ['id' => 'type', 'class' => 'form-control data-filter', 'placeholder' => 'All types']) !!}
                                </div> 
                            @endif   
                        </div>
                        <div class="search_option vehicle_search" style="padding-left: 0px;">
                            <div class="form-group">
                                <button class="btn btn-h-45 red-rubine pull-left" type="submit">
                                    <i class="jv-icon jv-search"></i>
                                </button>
                                <button class="btn js-vehicle-grid-clear-btn btn-h-45 grey-gallery " style="margin-right: 0" onclick="clearVehicleGrid();">
                                    <i class="jv-icon jv-close"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="portlet box marginbottom0">
                <div class="portlet-title">
                    <div class="caption blue_bracket">
                        Vehicle List&nbsp;<span id="selected-region-name">All Regions</span>
                        <div class="actions js-user-information-only">
                            <input type="hidden" id="show_archived_flag" value="1"/>
                            <label class="control-label mb-0" for="show_archived_vehicles">
                                <input type="checkbox" id="show_archived_vehicles" name="show_archived_vehicles">
                                <span class="vabottom">Show archived vehicles</span>
                            </label>
                        </div>
                    </div>
                    <div class="actions new_btn align-self-end">
                        <a href="javascript:void(0)" onclick="clickResetGrid()" class="btn red-rubine btn-padding">Reset columns</a>
                        <span onclick="clickShowHideColumn()" class="m5 jv-icon jv-chevron-square-down js-show-hide-col-bt"></span>
                        <span onclick="clickCustomRefresh();" class="m5 jv-icon jv-reload"></span> 
                        <span onclick="clickExport();" class="m5 jv-icon jv-download"></span>
                        <a href="{{ url('/vehicles/create') }}" class="config btn btn-sm gray_btn_border table-group-action-submit title_right_btn js-user-information-only"><i class="jv-icon jv-plus"></i> Add new vehicle</a>

                        <!-- <button onclick="clickExport();" class="btn grey-gallery btn-sm">Export</button> -->
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="jqgrid-wrapper">
                        <table id="jqGrid" class="table-striped table-bordered table-hover" data-type="vehicles"></table>
                        <div id="jqGridPager" class="multiple-action"></div>    
                    </div> 
                </div>
            </div>
        </div>
    </div>

@endsection