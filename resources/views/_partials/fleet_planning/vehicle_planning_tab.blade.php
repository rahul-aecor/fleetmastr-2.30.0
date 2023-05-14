<div class="tab-pane active" id="vehicles_planning">
    <div class="row">
        <div class="col-md-12">
            <form class="row gutters-tiny" id="vehicles-planning-filter-form">
                <div class="col-lg-8">
                    <div class="row gutters-tiny">
                        <div class="col-lg-4 col-md-12 col-sm-12">
                            @if(Auth::user()->isUserInformationOnly())
                                <div class="form-group">
                                    {!! Form::text('region', null, ['class' => 'form-control', 'placeholder' => 'All regions','id' => 'region','disabled'=>'disabled']) !!}
                                </div>    
                            @else   
                                <div class="form-group">
                                    {!! Form::select('region', $region_for_select, null, ['id' => 'region', 'class' => 'form-control select2-vehicle-region']) !!}
                                </div>
                                
                            @endif
                        </div>
                        <div class="col-lg-4 col-md-12 col-sm-12">
                            <div class="form-group">
                                {!! Form::text('registration', null, ['class' => 'form-control', 'placeholder' => 'Vehicle registration', 'id' => 'vehicle-registration']) !!}
                                <small class="text-danger">{{ $errors->first('registration') }}</small>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-12 col-sm-12">
                            <div class="form-group">
                                {!! Form::select('search_for', $maintenanceEvents, null, ['id' => 'search_for', 'class' => 'form-control select2me', 'data-placeholder' => 'Select search']) !!}
                                <small class="text-danger">{{ $errors->first('search_for') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex">
                        <div class="flex-grow-1 margin-right-15 select-time-period-group" style="display: none; width: calc(100% - 109px);">
                            <div class="form-group">
                                {!! Form::select('search_for_date_range', ['' => '', 'Date passed' => 'Date passed', 'Next 7 days' => 'Next 7 days', '8-14 days time' => '8-14 days time', '15-30 days time' => '15-30 days time'], null, ['id' => 'search_for_date_range', 'class' => 'form-control select2me', 'data-placeholder' => 'Select time period']) !!}
                                <small class="text-danger">{{ $errors->first('search_for_date_range') }}</small>
                            </div>
                        </div>
                        <div class="flex-grow-1 margin-right-15 search_for_distance_range" style="display: none; width: calc(100% - 109px);">
                            <div class="form-group">
                                {!! Form::select('search_for_distance_range', [''=>'','Exceeded' => 'Service exceeded', '0-1000' => 'Due in <= 1,000', '1001-2000' => 'Due in 1,001-2,000', '2001-3000' => 'Due in 2,001-3,000', '3000Plus' => 'Due in >= 3,001'], null, ['id' => 'search_for_distance_range', 'class' => 'form-control select2me', 'data-placeholder' => 'Select odometer reading']) !!}
                                <small class="text-danger">{{ $errors->first('search_for_date_range') }}</small>
                            </div>
                        </div>
                        <div class="vehicle_search" style="flex-shrink: 0">
                            <button class="btn btn-h-45 red-rubine vertical-fix pull-left" type="submit">
                                <i class="jv-icon jv-search"></i>
                            </button>
                            <button class="btn vehiclegrid-clear-btn btn-h-45 grey-gallery vertical-fix" style="margin-right: 0">
                                <i class="jv-icon jv-close"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="portlet box">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">
                        Vehicle List&nbsp;<span>(<span id="selected-region-name">All Regions</span>)&nbsp;&nbsp;</span>
                    </div>
                    <div class="actions new_btn align-self-end">
                        <a href="javascript:void(0)" onclick="clickResetGrid()" class="btn red-rubine btn-padding">Reset columns</a>
                        <span onclick="clickCustomRefresh();" class="m5 jv-icon jv-reload"></span> 
                        <span onclick="clickExport();" class="m5 jv-icon jv-download"></span>
                        @if(Auth::user()->isUserInformationOnly())
                        @else
                            <a href="{{ url('/vehicles/create') }}" class="config btn btn-sm gray_btn_border table-group-action-submit title_right_btn"><i class="jv-icon jv-plus "></i> Add vehicle</a>
                        @endif
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="jqgrid-wrapper">
                        <table id="jqGrid" class="table-striped table-bordered table-hover" data-type="fleet_planning"></table>
                        <div id="jqGridPager" class="multiple-action"></div>    
                    </div> 
                </div>
            </div>
        </div>
    </div>
</div>