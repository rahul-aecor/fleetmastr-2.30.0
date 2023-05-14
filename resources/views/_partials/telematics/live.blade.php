<!--<form class="form margin-bottom-20 telematicsSearchForm js-telematics-search-form-height telematics-search-form-height">
    <div class="row">
        <div class="col-md-12">
            <div class="row gutters-tiny">
                <div class="col-md-3">
                    <div class="check_search">
                        <div class="form-group margin-bottom0">
                            <div class="check_search">
                                <div class="form-group margin-bottom0">
                                    <input type="text" class="form-control js-region-telematics-live" name="regionFilterTelematicsLive" id="regionFilterTelematicsLive" placeholder="All regions">
                                </div>
                                <div class="form-group has-error">
                                    <span class="help-block filterIncident-error"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 telematics_registrationIncident">
                    <div class="check_search">
                        <div class="form-group margin-bottom0">
                            <input type="text" class="form-control js-registration-telematics-live" name="registrationTelematicsLive" id="registrationTelematicsLive" placeholder="Vehicle registration">
                        </div>
                    </div>
                </div>
                <div class="col-md-3 telematics_lastnameIncident">
                    <div class="check_search">
                        <div class="form-group margin-bottom0">
                            <input type="text" class="form-control js-name-telematics-live" name="nameTelematicsLive" id="lastnameTelematicsLive" placeholder="User">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex">
                        <div class="flex-grow-1 margin-right-10" style="width: calc(100% - 109px);">
                            <div class="check_search">
                                <div class="form-group margin-bottom0">
                                    <input type="text" class="form-control jSearchVehicleTypeLive" name="telematics_search_vehicle_type" id="telematics_search_vehicle_type" placeholder="Select type">
                                </div>
                            </div>
                        </div>
                        <div style="flex-shrink: 0">
                            <div class="form-group margin-bottom0">
                                <div class="d-flex mb-0">
                                    <button class="btn red-rubine btn-h-45" type="button" id="searchType"  v-on:click="plotSearchedVehicleMap">
                                        <i class="jv-icon jv-search"></i>
                                    </button>
                                    <button class="btn btn-success grey-gallery btn-h-45" type="button" v-on:click="clearLiveVehicleSearch">
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
</form> -->

<div class="row">
    <div class="col-md-12">
        <!-- <div class="portlet box">
            <div class="portlet-title">
                <div class="caption">
                    Live (GPS Tracking)
                </div>
                <div class="actions new_btn">
                    <label class="control-label ml0" for="show_stationery_vehicles">
                        <input type="checkbox" id="show_stationery_vehicles" name="show_stationery_vehicles" v-on:click="toggleShowStationeryVehicles">
                        <span class="vabottom">Show stationery vehicles</span>
                    </label>
                </div>
            </div>
        </div> -->
        
        <div id="mainDivLiveMapInterface">
            {{-- Start New mapui section --}}
            <div class="position-relative" style="overflow: hidden;">
                <div id="divLiveMapInterface" style="width: 100%;height: 700px;">
                    <div id="map_wrapper" class="map_wrapper">
                        <div id="map_canvas" class="mapping"></div>
                            <div class="row postcodefilter-btn-wrapper" id="postcodefilter-btn-wrapper" style="width:40px;">
                                    
                                {{-- <button class="btn js-map-tag-button live-map-tag-btn-outline">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="17.928" viewBox="0 0 18 17.928"><path id="tag-24" d="M89.748,90.864a1.116,1.116,0,1,1,1.116-1.116,1.116,1.116,0,0,1-1.116,1.116m11.446,2.946-8.117-8.045a1.479,1.479,0,0,0-1.049-.431H86.821a1.483,1.483,0,0,0-1.488,1.488v5.207a1.455,1.455,0,0,0,.439,1.049l8.11,8.045a1.516,1.516,0,0,0,1.056.439,1.485,1.485,0,0,0,1.049-.439l5.207-5.207a1.455,1.455,0,0,0,.439-1.049A1.489,1.489,0,0,0,101.194,93.81Z" transform="translate(-84.483 -84.483)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.7"/>
                                    </svg>
                                </button> --}}
                                <button class="btn red-rubine  btnVehicleMarkerShow mapui-header-btn" id="btnVehicleMarkerShow" style="top:0px;margin:0;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="17.774" height="14.399" viewBox="0 0 17.774 14.399">
                                        <path id="truck" d="M10.8,12.15c0,1.491,1.209,2.25,2.7,2.25a2.685,2.685,0,0,0,2.7-2.7A1.8,1.8,0,0,0,18,9.9V1.8A1.8,1.8,0,0,0,16.2,0H8.1A1.8,1.8,0,0,0,6.3,1.8v.9H5.1a1.573,1.573,0,0,0-1.114.461L1.361,5.785A1.573,1.573,0,0,0,.9,6.9V10.35h0a.675.675,0,1,0,0,1.35h.9a2.7,2.7,0,1,0,5.4,0ZM8.1,1.35h8.1a.451.451,0,0,1,.45.45V9.9a.451.451,0,0,1-.45.45h-.36a2.7,2.7,0,0,0-4.68,0H8.1a.451.451,0,0,1-.45-.45V1.8A.451.451,0,0,1,8.1,1.35ZM4.939,4.115A.223.223,0,0,1,5.1,4.05H6.3v2.7H2.306l.008-.011ZM2.254,8.064,6.3,8.072l.009,1.581a2.716,2.716,0,0,0-1.936-.677,2.9,2.9,0,0,0-2.048,1.086s-.073,0-.07,0S2.244,8.062,2.254,8.064ZM13.5,10.35a1.35,1.35,0,1,1-1.35,1.35A1.35,1.35,0,0,1,13.5,10.35Zm-9,2.7A1.35,1.35,0,1,1,5.85,11.7,1.35,1.35,0,0,1,4.5,13.049Z" transform="translate(-0.225)" fill="currentColor"/>
                                    </svg>
                                </button>
                                <button class="btn live-map-btn-outline btnLocationMarkerShow mapui-header-btn" id="btnLocationMarkerShow" style="top:10px;margin:0;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14.035" height="18.5" viewBox="0 0 14.035 18.5">
                                        <path id="location-dot" d="M9.587,6.768a2.82,2.82,0,1,1-2.82-2.82A2.819,2.819,0,0,1,9.587,6.768ZM6.768,8.459A1.692,1.692,0,1,0,5.076,6.768,1.692,1.692,0,0,0,6.768,8.459Zm6.768-1.692c0,3.081-4.124,8.565-5.932,10.828a1.065,1.065,0,0,1-1.671,0C4.092,15.333,0,9.848,0,6.768a6.768,6.768,0,0,1,13.535,0ZM6.768,1.128a5.638,5.638,0,0,0-5.64,5.64A6.186,6.186,0,0,0,1.712,9,21.835,21.835,0,0,0,3.248,11.9a52.716,52.716,0,0,0,3.52,4.938A52.956,52.956,0,0,0,10.289,11.9,21.925,21.925,0,0,0,11.822,9a6.143,6.143,0,0,0,.585-2.235A5.638,5.638,0,0,0,6.768,1.128Z" transform="translate(0.25 0.25)" fill="currentColor" stroke="currentColor" stroke-width="0.5"/>
                                    </svg>
                                </button>
                                <button class="btn live-map-btn-outline js-location-postcode-modal" id="btnGetJsLocationPostcode" style="top:20px;margin:0;">
                                    <?php /* ?><img src="{{ asset('img/map-marker.png') }}" alt="Map Marker" style="vertical-align: initial; width: 15px;"><?php */ ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18.005" viewBox="0 0 18 18.005">
                                        <path id="magnifying-glass" d="M17.727,16.563l-4.712-4.712A7.1,7.1,0,0,0,14.6,7.314a7.329,7.329,0,1,0-2.778,5.731l4.712,4.712a.972.972,0,0,0,.629.248.844.844,0,0,0,.6-.247A.822.822,0,0,0,17.727,16.563ZM1.688,7.314a5.627,5.627,0,1,1,5.627,5.627A5.633,5.633,0,0,1,1.688,7.314Z" transform="translate(0)" fill="currentColor"/>
                                    </svg>
                                    
                                </button> 
                            <div class="postcodefilter-content" style="display: none;">
                                <div class="portlet box margin-bottom0">
                                    <div class="portlet-title d-flex align-items-center justify-content-between">
                                        <div class="caption flex-grow-1">
                                            <h4 class="font-weight-700 margin-0">Postcode Search</h4>
                                            <div class="c-badge reset-postcodesearch-filter-div d-none">
                                                <span>Clear search</span>
                                                <button type="button" class="js-reset-postcodesearch-filter" aria-label="Close">
                                                    <svg stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                                        <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <a class="font-red-rubine closeBtn">
                                                <i class="jv-icon jv-close"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="portlet-body" style="padding: 10px;">
                                       {{--  <div class="row gutters-tiny align-items-center d-flex" style="margin-bottom: 30px;">
                                            <div class="col-md-8">
                                                <label class="control-label">Display locations:</label>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="checkbox-inline pt-0 location-toggle-wrapper" style="height: 34px">
                                                    <input type="checkbox" id="displayLocation" data-toggle="toggle" data-on="Yes" data-off="No" name="display_location">
                                                </label>
                                            </div>
                                        </div> --}}
                                        <div class="row gutters-tiny">
                                            {{-- <div class="col-md-12 mb15" style="font-size: 18px;"> Postcode search:</div> --}}
                                            <label class="col-md-12 mb15 control-label">Enter postcode:</label>
                                            <div class="col-md-8">
                                                <input class="form-control" type="text" id="postCodeFilter" placeholder="Enter postcode"/>
                                                <div class="has-error">
                                                    <div class="help-block zipCodeErr" style="display:none">Please enter a valid postcode</div>
                                                    <div class="help-block incompleteZipCodeErr" style="display:none">Please enter full postcode</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <button class="btn red-rubine btn-h-45 btn-block" type="button" id="searchPostcode"  v-on:click="plotByPostcode">
                                                    <i class="jv-icon jv-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                         @include('_partials.telematics.mapui.chart.live_tab_journey_chart')
                        
                    </div>       
                </div>
                {{-- start on map tags --}}
                <div class="filter-pills-wrapper" id="filterTagFiller">
                </div>
                {{-- end on map tags --}}
                <button class="btn red-rubine btnCollapsible live-tab-btn-collapsible expanded" id="btnLiveTabCollapsible">
                    <i class="icon-arrow-right"></i>
                </button>
                <div class="journey-timeline-wrapper-sidebar live-timeline-wrapper-sidebar is-left active divLiveTabFilterFrontTab" style="display:none;">
                    @include('_partials.telematics.mapui.filter.filter_sidebar')
                </div>
                <div class="journey-timeline-wrapper-sidebar live-timeline-wrapper-sidebar is-left active" id="divLiveTimeLineSidebar">
                       @include('_partials.telematics.mapui.header.main_header')
                </div>
                
            </div>
            
            {{-- End new mapui section --}}
            {{-- New section --}}
            
        </div>
        
    </div>
    
</div>
