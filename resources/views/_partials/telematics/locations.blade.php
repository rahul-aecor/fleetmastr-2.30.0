<form class="form margin-bottom-20 telematicsSearchForm telematics-search-form-height">
    <div class="row align-items-center">
        <div class="col-md-9">
            <div class="row gutters-tiny">
                <div class="col-md-4">
                    <div class="check_search">
                        <div class="form-group margin-bottom0">
                            <input type="text" class="form-control" name="location" id="telematicsLocation" placeholder="Enter location name">
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="check_search">
                        <div class="form-group margin-bottom0">
                            <input type="text" class="form-control" name="category" id="telematicsCategory" placeholder="Enter category">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group margin-bottom0">
                        <div class="d-flex mb-0">
                            <button class="btn red-rubine btn-h-45" type="button" id="searchLocation">
                                <i class="jv-icon jv-search"></i>
                            </button>
                            <button class="btn btn-success grey-gallery btn-h-45" type="button" onclick="clearLocationFilter()">
                                <i class="jv-icon jv-close"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box user-list-portlet marginbottom0">
            <div class="portlet-title bg-red-rubine">
                <div class="caption">
                    Location List&nbsp;
                </div>
                <div class="actions new_btn align-self-end">
                    <a href="javascript:void(0)" onclick="clickResetLocationsGrid()" class="btn red-rubine btn-padding">Reset columns</a>
                    <span onclick="clickShowHideLocationsColumn()" class="m5 jv-icon jv-chevron-square-down js-show-hide-col-bt"></span>
                    <span onclick="clickRefreshLocationGrid();" class="m5 jv-icon jv-reload"></span>
                    <span onclick="exportLocationData();" class="m5 jv-icon jv-download"></span>
                    <a href="{{ route('locations.create') }}" data-toggle="modal" class="btn btn-sm gray_btn_border"><i class="jv-icon jv-plus "></i> Add new location</a>
                </div>
            </div>
            <div class="portlet-body work_table">
                <div class="jqgrid-wrapper locations_page_table">
                    <table id="jqGrid" class="table-striped table-bordered table-hover check-table" data-type="locations"></table>
                    <div id="jqGridPager"></div>
                </div>
            </div>
        </div>
    </div>
</div>