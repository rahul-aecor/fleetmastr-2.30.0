@include('_partials.telematics.mapui.header.filter_header')
<div class="end-border-bottom" id="eebDivLiveTabFilterFrontTab"></div>
<div class="row divLiveTabFilterFrontTab">
  <div class="col-md-12 top20">
    <div class="form-group">
      <div class="select_accordion livetab-section-accordion livetab_accordion" style="margin-right:5px;">
        {{-- Filter 1 --}}
        <div id="regionFilterAccordion" class="panel-group accordion livetab_accordion">
          <div class="panel-group accordion livetab-checkbox">
            <div class="panel panel-default">
              <div class="panel-heading bg-red-rubine">
                <h4 class="panel-title">
                  <label>
                    <div class="checker">
                      <span>
                      <input type="checkbox" name="liveTabRegionFilterAllCheckBox" id="liveTabRegionFilterAllCheckBox">
                      </span>
                    </div>
                  </label>
                  <a class="accordion-toggle accordion-toggle-styled collapse collapsed" data-toggle="collapse"
                    data-parent="#regionFilterAccordion" href="#showRegionAccordion" style="padding-left:0px;">
                    Region <span>(all)</span>
                  </a>
                </h4>
              </div>
              <div id="showRegionAccordion" class="panel-collapse collapse">
                <div class="panel-body scroller" data-height="200px">
                  <ul>
                    @if(!empty($_regionForSelect))
                      @foreach($_regionForSelect as $regionId=>$region)
                        <li>
                          <div class="checkbox-list">
                            <label>
                              <div class="checker">
                                <span class="liveTabRegionFilterCheckBox" data-region-id="{{$regionId}}" data-region-name="{{$region}}">
                                  <input type="checkbox" name="liveTabRegionFilterCheckBox">
                                </span>
                              </div>
                              {{$region}}
                            </label>
                          </div>
                        </li>
                      @endforeach
                      @else
                        <li class="text-center">No record found</li>
                      @endif
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
        {{-- Filter 2 --}}
        <div id="vehicleTypeFilterAccordion" class="panel-group accordion livetab_accordion">
          <div class="panel-group accordion livetab-checkbox">
            <div class="panel panel-default">
              <div class="panel-heading bg-red-rubine">
                <h4 class="panel-title">
                  <label>
                    <div class="checker">
                      <span>
                      <input type="checkbox" name="liveTabVehicleTypeFilterAllCheckBox" id="liveTabVehicleTypeFilterAllCheckBox">
                      </span>
                    </div>
                  </label>
                  <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse"
                    data-parent="#vehicleTypeFilterAccordion" href="#showVehicleTypeAccordion" style="padding-left:0px;">
                    Vehicle type <span>(all)</span>
                  </a>
                </h4>
              </div>
              <div id="showVehicleTypeAccordion" class="panel-collapse collapse">
                <div class="panel-body scroller" data-height="200px">
                  <ul>
                    @if(!empty($_vehicleTypeProfiles))
                      @foreach($_vehicleTypeProfiles as $vehicleTypeKey=>$vehicleType)
                        <li id="li_vt_{{$vehicleType->id}}" class=" liVehicleType">
                          <div class="checkbox-list">
                            <label>
                              <div class="checker">
                                <span class="liveTabVehicleTypeFilterCheckBox" data-vehicle-type-id="{{$vehicleType->id}}" data-vehicle-type-text="{{$vehicleType->text}}">
                                  <input type="checkbox" name="liveTabVehicleTypeFilterCheckBox">
                                </span>
                              </div>
                              {{$vehicleType->text}}
                            </label>
                          </div>
                        </li>
                        @endforeach
                        @else
                          <li class="text-center">No record found</li>
                        @endif
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
        {{-- Filter 3 --}}
        <?php /* ?>
        <div id="allLocationCategoryFilterAccordion" class="panel-group accordion livetab_accordion">
          <div class="panel-group accordion livetab-checkbox">
            <div class="panel panel-default">
              <div class="panel-heading bg-red-rubine">
                <h4 class="panel-title">
                  <label>
                    <div class="checker">
                      <span>
                      <input type="checkbox" name="liveTabAllLocationCategoryFilterAllCheckBox" id="liveTabAllLocationCategoryFilterAllCheckBox">
                      </span>
                    </div>
                  </label>
                  <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse"
                    data-parent="#allLocationCategoryFilterAccordion" href="#showAllLocationCategoryAccordion" style="padding-left:0px;">
                    Locations <span>(all)
                      
                    </span>
                  </a>
                </h4>
              </div>
              <div id="showAllLocationCategoryAccordion" class="panel-collapse collapse">
                <div class="panel-body scroller" data-height="200px">
                  <ul>
                    @if(empty(!$_allLocationCategory))
                      @foreach($_allLocationCategory as $lcKey=>$lc)
                        <li>
                          <div class="checkbox-list">
                            <label>
                              <div class="checker">
                                <span class="liveTabAllLocationCategoryFilterCheckBox" data-location-category-id="{{$lc['id']}}" data-location-category-text="{{$lc['text']}}">
                                  <input type="checkbox" name="liveTabAllLocationCategoryFilterCheckBox">
                                </span>
                              </div>
                              {{$lc['text']}}
                            </label>
                          </div>
                        </li>
                      @endforeach
                      @else
                        <li class="text-center">No record found</li>
                      @endif
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php */ ?>
      </div>
    </div>
  </div>
</div>