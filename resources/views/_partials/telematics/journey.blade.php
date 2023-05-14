<form class="form margin-bottom-20 telematicsSearchForm js-telematics-search-form-height telematics-search-form-height">
    <div class="row align-items-center">
        <div class="col-md-12">
            <div class="row gutters-tiny">
                <div class="col-md-2">
                    <div class="check_search">
                        <div class="form-group margin-bottom0">
                            <input type="text" class="form-control" name="regionFilterJourney" id="regionFilterJourney" placeholder="All regions">
                        </div>
                        <div class="form-group has-error">
                            <span class="help-block journeyFilter-error"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 telematics_registrationJourney">
                    <div class="check_search">
                        <div class="form-group margin-bottom0">
                            <input type="text" class="form-control" name="registration" id="registrationJourney" placeholder="Vehicle registration">
                        </div>
                    </div>
                </div>
                <div class="col-md-4 telematics_lastnameJourney">
                    <div class="d-flex">
                        <div class="flex-grow-1 margin-right-10" style="width: calc(100% - 109px);">
                            <div class="check_search">
                                <div class="form-group margin-bottom0">
                                    <input type="text" class="form-control" name="lastname" id="lastnameJourney" placeholder="User">
                                </div>
                            </div>
                        </div>
                        <div class="" style="flex-shrink: 0">
                            <div class="form-group margin-bottom0">
                                <div class="d-flex mb-0">
                                    <button class="btn red-rubine btn-h-45" type="button" id="searchTypeJourney" onclick="getJourneyTabData()">
                                    <i class="jv-icon jv-search"></i>
                                    </button>
                                    <button class="btn btn-success grey-gallery btn-h-45" type="button" onclick="clearJourneyFilter()">
                                    <i class="jv-icon jv-close"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <div class="input-group">
                            {!! Form::text('range',  $defaultDateRange, ['class' => 'form-control', 'id' =>'journeyDateRangeFilter', 'placeholder' => 'Report date' , 'readonly']) !!}
                            <span class="input-group-addon js-daterangepicker-button" id="basic-addon1"><i class="jv-icon jv-calendar font-weight-700"></i></span>
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
                    Journeys
                    {{-- <span class="locationSearchSpan" style="display:none">
                    <label class="locationSearchLabel"></label>
                    <a class="font-red-rubine bootbox-close-button locationSearchClose" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                    </a>
                    </span> --}}

                    {{-- <div class="general-badge routeAnalysisBadge routeAnalysisSpan" style="font-size:11px;margin-left:8px;border-radius:0;padding:5px;cursor:pointer;">
                        <span class="routeAnalysisLabel" id="showRouteAnalysis">Route Analysis <i class="jv-icon jv-route icon-big"></i></span>
                    </div> --}}
                    <div class="general-badge routeAnalysisBadge routeAnalysisSpan" id="showRouteAnalysis">Route analysis&nbsp;<i class="jv-icon jv-route"></i></div>
                    <div class="c-badge locationSearchBadge locationSearchSpan" style="display:none; margin-left: 8px;">
                        <span class="locationSearchLabel"></span>
                        <button type="button" class="locationSearchClose" aria-label="Close">
                            <svg stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="actions new_btn align-self-end">
                    <a href="javascript:void(0)" onclick="clickJourneyResetGrid()" class="btn red-rubine btn-padding">Reset columns</a>
                        {{-- <span onclick="clickJourneyShowHideColumn()" class="m5 jv-icon jv-chevron-square-down js-show-hide-col-bt"></span> --}}
                    <!-- <span onclick="clickCustomRefresh();" class="m5 jv-icon jv-reload"></span> -->
                        <span onclick="exportJourneyData();" class="m5 jv-icon jv-download"></span>
                    <a href="#" class="config btn btn-sm gray_btn_border table-group-action-submit title_right_btn js-user-information-only">Search location <i class="jv-icon jv-search m5"></i> </a>
                </div>
            </div>
            <div class="portlet-body">
                <div class="jqgrid-wrapper journeyJqGridWrraper">
                    <table id="journeyJqGrid" class="table-striped table-bordered table-hover" data-type="journeys"></table>
                    <div id="journeyJqGridPager" class="multiple-action jqGridPagination"></div>
                </div>
                <div class="JourneyMapView" style="display: none;">
                    <div class="position-relative">
                        <div class="align-items-center d-flex justify-content-center margin-bottom-25">
                            <div class="text-center">
                                <div class="h4 font-weight-700 text-muted">Vehicle registration</div>
                                <div class="c-badge is-lg closeBtn margin-0">
                                    <span class="small js-registration-number"></span>
                                    <button type="button" class="" aria-label="Close">
                                        <svg stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                            <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1833.1247468251486!2d-0.7269395876919833!3d51.33212270893198!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x48742fdb63b9ca69%3A0x7955c399f5c23a95!2sM3%2C%20Camberley%20GU15%2C%20UK!5e0!3m2!1sen!2sin!4v1601981209547!5m2!1sen!2sin" width="100%" height="600" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe> --}}
                    {{--<iframe src="https://www.google.com/maps/embed?pb=!1m26!1m12!1m3!1d1274350.751608435!2d-1.5465460942996945!3d51.57529051063752!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!4m11!3e0!4m5!1s0x487699785886a271%3A0xf334bdc9283a1d74!2s179%20Fairford%20Rd%2C%20Tilehurst%2C%20Reading%20RG31%206QT%2C%20UK!3m2!1d51.4668005!2d-1.0502208!4m3!3m2!1d51.711632599999994!2d0.3158343!5e0!3m2!1sen!2sin!4v1602506929919!5m2!1sen!2sin" width="100%" height="600" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>--}}
                    <div class="position-relative" style="overflow: hidden;">
                        <div id="journey_map_canvas" style="width: 100%;height: 600px;"></div>
                        <button class="btn red-rubine btnCollapsible expanded" id="btnJourneyTabCollapsible">
                            <i class="icon-arrow-right"></i>
                        </button>
                        <div class="journey-timeline-wrapper-sidebar journey-timeline-wrapper-sidebar-ext is-left active">
                            <div class="journey-timeline-wrapper-sidebar-body">
                                <div class="row">
                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p><strong>Journey: </strong><span id="js-jd-distance"></span>&nbsp; miles</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Driving: </strong><span id="js-jd-driving"> </span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Idling: </strong><span id="js-jd-idling"> </span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Odometer (start):<br></strong><span id="js-jd-odometer_start"> </span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Odometer (end):<br></strong><span id="js-jd-odometer-end"> </span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="journey-timeline margin-top-30" id="journeyTimeline">
                                </div>
                            </div>
                        </div>
                        @include('_partials.telematics.journey_chart')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- seach location modal --}}
<div id="journey_search_location_modal" class="modal modal-fix  fade modal-overflow in journey-search-location-modal" tabindex="-1" data-backdrop="static" data-keyboard="false" aria-hidden="false">
    <form id="postcode_search_form" class="js-postcode-search-form form">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                    <h4 class="modal-title">Find Location</h4>
                    <a class="font-red-rubine bootbox-close-button" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                    </a>
                </div>
                <div class="modal-body row form-bordered align-items-center form-body form-label-center-fix">
                    <div class="form-group col-md-12">
                        <span>This will display a list of journeys that have passed through the postcode that you enter below during the date range that you have specified.</span>
                    </div>
                    <div class="form-group col-md-12 mb15">
                        <label class="col-md-3 control-label">Enter postcode*:</label>
                        <div class="col-md-5">
                            <input class="form-control" type="text" name="postcode" id="postcode" autofocus/>
                        </div>
                        <div class="col-md-2 align-self: flex-start;">
                            <button class="btn red-rubine btn-h-45 js-search-location" type="button">
                            <i class="jv-icon jv-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<div id="routeAnalysisConfirmModal" class="modal modal-fix  fade modal-overflow in routeAnalysisConfirmModal" tabindex="-1" data-backdrop="static" data-keyboard="false" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                <h4 class="modal-title">Route Analysis</h4>
                <a class="font-red-rubine bootbox-close-button" data-dismiss="modal" aria-label="Close">
                <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body row form-bordered align-items-center form-body form-label-center-fix">
                <div class="form-group col-md-12">
                    <span>To see a map view of journeys for a specific vehicle, please enter a vehicle registration and choose a pre-defined date range from the calendar options (e.g. Today, Yesterday or Last 7 days) or choose a custom date range of up to a maximum of 2 days.</span>
                    <br><br>
                    <span>Once you have entered valid criteria, the 'Route analysis' button will be enabled so that you can view your information.</span>
                </div>
                <div class="form-group col-md-12 mb15 btn-group">
                    <button class="btn white-btn text-center btn-padding-big w-100 route-analysis-show-btn" type="button" data-dismiss="modal" aria-label="Close">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>