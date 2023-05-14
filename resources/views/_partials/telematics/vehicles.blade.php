<form class="form margin-bottom-20 telematicsSearchForm telematics-search-form-height">
    <div class="row align-items-center">
        <div class="col-md-12">
            <div class="row gutters-tiny">
                <div class="col-md-2">
                    <div class="check_search">
                        <div class="form-group margin-bottom0">
                            <input type="text" class="form-control" name="regionFilterVehicleField" id="regionFilterVehicleField" placeholder="All regions">
                        </div>
                        <div class="form-group has-error">
                            <span class="help-block journeyFilter-error"></span>
                        </div>
                    </div>
                </div>

                

                <div class="col-md-2">
                    <div class="check_search">
                        <div class="form-group margin-bottom0">
                            <input type="text" class="form-control jSearchVehicleTypeLive" name="telematics_search_vehicle_type_v" id="telematics_search_vehicle_type_v" placeholder="Select type">
                        </div>
                        <div class="form-group has-error">
                            <span class="help-block vehicleFilter-error"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 telematics_registrationVehicle">
                    <div class="check_search">
                        <div class="form-group margin-bottom0">
                            <input type="text" class="form-control" name="registration" id="registrationVehicle" placeholder="Vehicle registration">
                        </div>
                    </div>
                </div>
                <div class="col-md-4 telematics_lastnameJourney">
                    <div class="d-flex">
                        <div class="" style="flex-shrink: 0">
                            <div class="form-group margin-bottom0">
                                <div class="d-flex mb-0">
                                    <button class="btn red-rubine btn-h-45" type="button" id="searchTypeVehicle" onclick="getVehicleTabData()">
                                        <i class="jv-icon jv-search"></i>
                                    </button>
                                    <button class="btn btn-success grey-gallery btn-h-45" type="button" onclick="clearVehicleFilter()">
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

<div class="row">
    <div class="col-md-12">
        <div class="portlet box telematics-card-wrapper marginbottom0">
            <div class="portlet-title">
                <div class="caption">
                    Vehicle List&nbsp;<span id="selected-region-name">(Telematics)</span>&nbsp;&nbsp;
                </div>
                <div class="actions new_btn align-self-end">
                    <!-- <a href="javascript:void(0)" onclick="clickResetVehiclesGrid()" class="btn red-rubine btn-padding">Reset columns</a>
                    <span onclick="clickShowHideVehiclesColumn()" class="m5 jv-icon jv-chevron-square-down js-show-hide-col-bt"></span>
                    <span onclick="clickRefreshVehicleGrid();" class="m5 jv-icon jv-reload"></span> -->
                    <span onclick="exportVehicleData();" class="m5 jv-icon jv-download"></span>
                </div>
            </div>
            <div class="portlet-body">
                <div class="jqgrid-wrapper vehicleJqGridWrraper">
                    <table id="vehicleJqGrid" class="table-striped table-bordered table-hover"></table>
                    <div id="vehicleJqGridPager" class="multiple-action jqGridPagination"></div>
                </div>
            </div>
        </div>
    </div>
</div>