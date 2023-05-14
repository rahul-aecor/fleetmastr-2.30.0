<div class="journey-timeline-wrapper-sidebar-header" v-show="isLiveTabLeftSideHeaderShow" id="liveTabSideBarMainHeader">
    <div class="flex-grow-1">
        <div class="d-flex">
            <div class="flex-grow-1 search_for_distance_range" style="width: calc(100% - 100px);">
                <div class="sidebar-header-search">
                    <select class="form-control select2me header-search-dropdown margin-right-5" id="selectSearchCriteria">
                        <option value="vehicles">Vehicles</option>
                        {{-- <option value="users">Users</option> --}}
                        <option value="locations">Locations</option>
                    </select>
                    <input type="text" class="form-control margin-right-5" name="searchBoxLiveMap" id="searchBoxLiveMap" placeholder="Search">
                </div>
            </div>
            <div class="flex-shrink-0">
                <div class="d-flex mb-0">
                    {{-- <button class="btn red-rubine btn-h-45 flex-shrink-0 flex-grow-1 btn-w-44 btnVehicleMarkerShow mapui-header-btn" id="btnVehicleMarkerShow" style="margin-right:5px;">
                        <i class="fa fa-truck"></i>
                    </button>
                    <button class="btn live-map-btn-outline btn-h-45 flex-shrink-0 flex-grow-1 btn-w-44 btnLocationMarkerShow hide mapui-header-btn" id="btnLocationMarkerShow" style="width:45px;margin-right:0px;margin-left: 0px;">
                        <i class="fa fa-map-marker" style="font-size:18px;"></i>
                    </button> --}}

                    <button class="btn live-map-btn-outline btn-h-45 flex-shrink-0 flex-grow-1 btn-w-44 liveTabFilterFrontTabHeaderBtn mapui-header-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="19.126" viewBox="0 0 18 19.126">
                            <path id="filter" d="M0,33.466A1.466,1.466,0,0,1,1.466,32H16.534a1.464,1.464,0,0,1,1.136,2.391L11.813,41.58v8.41a1.137,1.137,0,0,1-1.136,1.136,1.011,1.011,0,0,1-.707-.278L6.718,48.3a1.41,1.41,0,0,1-.531-1.1V41.58L.33,34.391A1.459,1.459,0,0,1,0,33.466Zm1.932.221,5.753,7.059a.849.849,0,0,1,.19.534v5.78l2.25,1.786V41.281a.849.849,0,0,1,.19-.534l5.752-7.059Z" transform="translate(0 -32)" fill="currentColor"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <ul class="list-unstyled margin-bottom0 vehicle-status-wrapper d-flex margin-top-plus-8 margin-left-plus-16">
            <li><span class="vehicle-status driving"></span> <span class="margin-left-plus-4"> Driving: </span><span class="count driving_count">@{{ vehicles_used_today }}</span></li>
            <li class="ml-3"><span class="vehicle-status idling"></span><span class="margin-left-plus-4"> Idling: </span><span class="count idling_count">@{{vehicles_in_trasit}}</span></li>
            <li class="ml-3"><span class="vehicle-status stopped"></span> <span class="margin-left-plus-4">Stopped: </span><span class="count stopped_count">@{{vehicles_stationery}}</span> </li>
        </ul>
    </div>
</div>