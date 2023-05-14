<form class="form margin-bottom-20 telematicsSearchForm js-telematics-search-form-height telematics-search-form-height">
    <div class="row gutters-tiny">
        <div class="col-md-8">
            <div class="row gutters-tiny">
                <div class="col-md-4">
                    <div class="check_search">
                        <div class="form-group">
                            <div class="form-group margin-bottom0">
                                <input type="text" class="form-control incident-reset-filter js-reset-filter-value" name="regionFilterIncident" id="regionFilterIncident" placeholder="All regions">
                            </div>
                            <div class="form-group has-error">
                                <span class="help-block filterIncident-error"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8 telematics_incidentType">
                    <div class="d-flex">
                        <div class="flex-grow-1 margin-right-10">
                            <div class="check_search">
                                <div class="form-group">
                                    <input type="text" class="form-control incident-reset-filter js-reset-filter-value" name="incidentTypeFilter" id="incidentTypeFilter" placeholder="Incident type">
                                </div>
                            </div>
                        </div>
                        <div style="flex-shrink: 0">
                            <div class="form-group margin-bottom0">
                                <div class="d-flex mb-0">
                                    <button class="btn red-rubine btn-h-45" type="button" id="searchTypeIncidents" onclick="filterIncidentData()">
                                        <i class="jv-icon jv-search"></i>
                                    </button>
                                    <button class="btn btn-success grey-gallery btn-h-45" type="button" onclick="clearIncidentFilter()">
                                        <i class="jv-icon jv-close"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row gutters-tiny collapse" id="collapseExample">
                <div class="col-md-4 telematics_registrationIncident">
                    <div class="check_search">
                        <div class="form-group">
                            <input type="text" class="form-control incident-reset-filter js-reset-filter-value" name="registration" id="registrationIncident" placeholder="Vehicle registration">
                        </div>
                    </div>
                </div>
                <div class="col-md-4 telematics_lastnameIncident">
                    <div class="check_search">
                        <div class="form-group">
                            <input type="text" class="form-control incident-reset-filter js-reset-filter-value" name="lastnameIncident" id="lastnameIncident" placeholder="User">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="text-right">
                        <div class="c-badge incident-filter-hide font-weight-700 d-none margin-right-10">
                            <span onclick="incidentResetFilter();">Reset filter</span>
                            <button type="button" class="locationSearchClose" aria-label="Close">
                                <svg stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                    <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7"></path>
                                </svg>
                            </button>
                        </div>
                        <a class="js-show-hide-advanced-search" style="color: var(--primary-color);min-width: 130px;display:inline-block;"  role="button" data-toggle="collapse" href="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                            <span class="open-cont">Show advanced search</span>
                            <span class="close-cont">Hide advanced search</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                {!! Form::text('range',  $defaultDateRange, ['class' => 'form-control','id'=>'incidentDateRange', 'placeholder' => 'Report date' , 'readonly']) !!}
                <span class="input-group-addon js-daterangepicker-button" id="basic-addon1"><i class="jv-icon jv-calendar font-weight-700"></i></span>
            </div>
        </div>
    </div>
</form>

<div class="tabbable-custom tabbable-rubine nav-justified d-none" id="incidentTabSelect">
    <ul class="nav nav-tabs nav-justified">
        <li class="active">
            <a href="#incidentTab" data-toggle="tab" aria-controls="incidentTab" class="incidentTab incidentDataTab" role="tab">Incident Data</a>
        </li>
        <li>
            <!-- <a href="#incidentmapTab" data-toggle="tab" class="incidentmapTab" aria-controls="incidentmapTab" role="tab">Incident Map</a> -->
            <a href="#incidentmapTab" data-toggle="tab" class="incidentmapTab hide" aria-controls="incidentmapTab" role="tab"></a>
        </li>
    </ul>
</div>
<div class="row">
    <div class="tab-content vertical-tabbing-content col-md-12">
        <div role="tabpanel" class="tab-pane active" id="incidentTab">
            <div class="portlet box marginbottom0">
                <div class="portlet-title has-tabview">
                    <div class="caption">
                        <div class="caption-tabs">
                            <a href="javascript:void(0)" class="caption-tab is-active incident_tab">Incident Data</a>
                            <!-- <a href="javascript:void(0)" class="caption-tab incident_map_tab">Incident Map</a> -->
                            <button type="button" class="rounded-lg d-none incidentType">
                            </button>
                        </div>
                    </div>
                    <div class="actions new_btn align-self-end">
                        <span onclick="exportIncidentsJqGrid();" class="m5 jv-icon jv-download"></span>
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="jqgrid-wrapper">
                        <table id="incidentJqGrid" class="table-striped table-bordered table-hover"></table>
                        <div id="incidentJqGridPager" class="multiple-action jqGridPagination"></div>
                    </div>
                </div>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="incidentmapTab">
            <div class="portlet box marginbottom0">
                <div class="portlet-title has-tabview">
                    <div class="caption">
                        <div class="caption-tabs">
                            <a href="javascript:void(0)" class="caption-tab incident_tab">Incident Data</a>
                            <button type="button" class="rounded-lg d-none incidentType">
                            </button>
                            <!-- <a href="javascript:void(0)" class="caption-tab is-active incident_map_tab">Incident Map</a> -->
                        </div>
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="position-relative">
                        <div id="mapCanvasIncident" style="width: 100%;height: 600px;"  class=""></div>
                        {{--<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d2494.4347058699595!2d-0.7978821!3d51.3031262!3m2!1i1024!2i768!4f70.1!3m3!1m2!1s0x48742fdb63b9ca69%3A0x7955c399f5c23a95!2sM3%2C%20United%20Kingdom!5e0!3m2!1sen!2sin!4v1602497272881!5m2!1sen!2sin" width="100%" height="600" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>--}}
                        
                        {{-- <iframe src="https://www.google.com/maps/embed?q=51.279256, -0.943340&z=15&output=embed" width="100%" height="600" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe> --}}
                        
                       {{-- @include('_partials.telematics.incidentModalTemp')--}}
                       <!--  @include('_partials.telematics.hotspot') -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>