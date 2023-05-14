<form class="form margin-bottom-20 telematicsSearchForm telematics-search-form-height">
    <div class="row align-items-center gutters-tiny">
        <div class="col-md-9 zoneTabFilters zoneSizeAdjustment70">
           <div class="row gutters-tiny">
                <div class="col-md-2 zoneRegionFilterSize">
                    <div class="telematics_zones">
                        <div class="check_search">
                            <div class="form-group margin-bottom0">
                                <input type="text" class="form-control" name="regionFilterForZone" id="regionFilterForZone" placeholder="All regions">
                            </div>
                            <div class="form-group has-error">
                                <span class="help-block region-error"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 telematics_region_zones zoneAllZoneFilterSize">
                    <div class="telematics_status_zones">
                        <div class="check_search">
                            <div class="form-group margin-bottom0">
                                <input type="text" class="form-control" name="zoneFilter" id="zoneFilter" placeholder="All zones">
                            </div>
                            <div class="form-group has-error">
                                <span class="help-block zoneFilter-error"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert_setting">
                        <div class="check_search">
                            <div class="form-group margin-bottom0">
                                <input type="text" class="form-control" name="alertSetting" id="alertSetting" placeholder="Zone tracking">
                            </div>
                            <div class="form-group has-error">
                                <span class="help-block alertSetting-error"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex">
                        <div class="flex-grow-1 margin-right-10" style="width: calc(100% - 109px);">
                            <div class="check_search">
                                <div class="form-group margin-bottom0">
                                    <input type="text" class="form-control" name="status" id="status" placeholder="Zone status">
                                </div>
                                <div class="form-group has-error">
                                    <span class="help-block status-error"></span>
                                </div>
                            </div>
                        </div>
                        <div class="" style="flex-shrink: 0">
                            <div class="form-group margin-bottom0">
                                <div class="d-flex mb-0">
                                    <button class="btn red-rubine btn-h-45" type="button" id="searchTypeZone">
                                        <i class="jv-icon jv-search"></i>
                                    </button>
                                    <button class="btn btn-success grey-gallery btn-h-45" type="button" onclick="clearZonesFilter()">
                                        <i class="jv-icon jv-close"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
           </div>
        </div>
        <div class="col-md-9 zoneAlertTabFilters zoneSizeAdjustment70 d-none">
            <div class="row gutters-tiny">
                <div class="col-md-3">
                    <div class="telematics_zones">
                        <div class="check_search">
                            <div class="form-group margin-bottom0">
                                <input type="text" class="form-control" name="zoneAlertFilter" id="zoneAlertFilter" placeholder="Zone name">
                            </div>
                            <div class="form-group has-error">
                                <span class="help-block zoneAlertFilter-error"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 telematics_alert_type">
                    <div class="d-flex">
                        <div class="flex-grow-1 margin-right-10" style="width: calc(100% - 109px);">
                            <div class="check_search">
                                <div class="form-group margin-bottom0">
                                    <input type="text" class="form-control" name="alert_type" id="alert_type" placeholder="Alert type">
                                </div>
                                <div class="form-group has-error">
                                    <span class="help-block alert_type-error"></span>
                                </div>
                            </div>
                        </div>
                        <div class="" style="flex-shrink: 0">
                            <div class="form-group margin-bottom0">
                                <div class="d-flex mb-0">
                                    <button class="btn red-rubine btn-h-45" type="button" id="searchTypeZoneAlert" onclick="">
                                        <i class="jv-icon jv-search"></i>
                                    </button>
                                    <button class="btn btn-success grey-gallery btn-h-45" type="button" onclick="clearZoneAlertsFilter()">
                                        <i class="jv-icon jv-close"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 input-group zoneSizeAdjustment30">
            {!! Form::text('range',  $todayDateRange, ['class' => 'form-control', 'id' =>'zoneDateRangeFilter', 'placeholder' => 'Report date' , 'readonly']) !!}
            <span class="input-group-addon js-daterangepicker-button" id="basic-addon1"><i class="jv-icon jv-calendar font-weight-700"></i></span>
        </div>
    </div>
</form>
<div class="tabbable-custom tabbable-rubine nav-justified d-none" id="zonesTabSelect">
    <ul class="nav nav-tabs horizontal-tabbing nav-justified">
        <li class="active">
            <a href="#zonesTab" data-toggle="tab" aria-controls="zonesTab" class="zonesTab" role="tab">Zones</a>
        </li>
        <li>
            <a href="#zoneAlertTab" data-toggle="tab" class="zoneAlertTab" aria-controls="zoneAlertTab" role="tab">Zone Alerts</a>
        </li>
    </ul>
</div>
<div class="row">
    <div class="tab-content vertical-tabbing-content col-md-12">
        <div role="tabpanel" class="tab-pane active" id="zonesTab">
            <div class="portlet box marginbottom0">
                <div class="portlet-title has-tabview">
                    <div class="caption">
                        <div class="caption-tabs">
                            <a href="javascript:void(0)" class="caption-tab is-active zones_tab">Zones</a>
                            <a href="javascript:void(0)" class="caption-tab zone_alert_tab">Zone Alerts</a>
                        </div>
                    </div>
                    <div class="actions new_btn align-self-end">
                        <a href="javascript:void(0)" onclick="clickZoneResetGrid()" class="btn red-rubine btn-padding">Reset columns</a>
                        <span onclick="clickZoneShowHideColumn()" class="m5 jv-icon jv-chevron-square-down js-show-hide-col-bt"></span>
                        <span onclick="clickCustomRefresh();" class="m5 jv-icon jv-reload"></span> 
                        <span onclick="exportToExcel();" class="m5 jv-icon jv-download"></span>
                        <a href="{{ url('/telematics/createZone') }}" class="config btn btn-sm gray_btn_border table-group-action-submit title_right_btn"><i class="jv-icon jv-plus"></i> Add new zone</a>

                        <!-- <button onclick="clickExport();" class="btn grey-gallery btn-sm">Export</button> -->
                    </div>
                </div>
                <div class="portlet-body work_table">
                    <div class="jqgrid-wrapper user_page_table">
                        <table id="zoneJqGrid" class="table-striped table-bordered table-hover check-table" data-type="telematicsZones"></table>
                        <div id="zoneJqGridPager"></div>
                    </div>
                </div>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="zoneAlertTab">
            <div class="portlet box marginbottom0">
                <div class="portlet-title has-tabview">
                    <div class="caption">
                        <div class="caption-tabs">
                            <a href="javascript:void(0)" class="caption-tab zones_tab">Zones</a>
                            <a href="javascript:void(0)" class="caption-tab is-active zone_alert_tab">Zone Alerts</a>
                        </div>
                    </div>
                    <div class="actions pull-left js-user-information-only">
                        
                    </div>    
                    <div class="actions new_btn align-self-end">
                        <a href="javascript:void(0)" onclick="clickZoneAlertResetGrid()" class="btn red-rubine btn-padding">Reset columns</a>
                        <span onclick="clickZoneAlertShowHideColumn()" class="m5 jv-icon jv-chevron-square-down js-show-hide-col-bt"></span>
                        <span onclick="clickZoneAlertsRefresh();" class="m5 jv-icon jv-reload"></span> 
                        <span onclick="clickCustomExport();" class="m5 jv-icon jv-download"></span>
                        <a href="{{ url('/telematics/createZone') }}" class="config btn btn-sm gray_btn_border table-group-action-submit title_right_btn"><i class="jv-icon jv-plus"></i> Add new zone</a>
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="jqgrid-wrapper zoneAlertJqgridWrapper">
                        <table id="zoneAlertJqGrid" class="table-striped table-bordered table-hover" data-type="telematicsZoneAlerts"></table>
                        <div id="zoneAlertJqGridPager" class="multiple-action jqGridPagination"></div>
                    </div>
                    <div class="position-relative zoneMapView d-none">
                        <div class="position-relative">
                            <div class="align-items-center d-flex justify-content-center margin-bottom-25">
                                <div class="text-center">
                                    <div class="c-badge is-lg closeBtn margin-0">
                                        <span class="small js-registration-number">Close</span>
                                        <button type="button" class="" aria-label="Close">
                                            <svg stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                                <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="pull-right closeBtn font-red-rubine">Close (<span></span>)</div> --}}
                        <div id="mapCanvasZoneAlerts" class="mt-2" style="width: 100%;height: 600px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
