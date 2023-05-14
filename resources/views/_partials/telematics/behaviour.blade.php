<form class="form margin-bottom-20 telematicsSearchForm js-telematics-search-form-height telematics-search-form-height">
    <div class="row gutters-tiny">
        <div class="col-md-8">
            <div class="row gutters-tiny">
                <div class="col-md-3">
                    <div class="check_search">
                        <div class="form-group margin-bottom0">
                            <input type="text" class="form-control" name="typeFilterBehaviour" id="typeFilterBehaviour" placeholder="Select">
                        </div>
                        <div class="form-group has-error">
                            <span class="help-block behaviourFilter-error"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="check_search">
                        <div class="form-group margin-bottom0">
                            <input type="text" class="form-control" name="regionFilterBehaviour" id="regionFilterBehaviour" placeholder="All regions (Users)">
                        </div>
                        <div class="form-group has-error">
                            <span class="help-block behaviourFilter-error"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-grow-1 margin-right-10" style="width: calc(100% - 109px);">
                            <div class="check_search telematics_registrationBehaviour" style="display: none;">
                                <div class="form-group margin-bottom0">
                                    <input type="text" class="form-control" name="registration" id="registrationBehaviour" placeholder="Vehicle search">
                                </div>
                            </div>
                            <div class="check_search telematics_lastnameBehaviour">
                                <div class="form-group margin-bottom0">
                                    <input type="text" class="form-control" name="lastnameBehaviour" id="lastnameBehaviour" placeholder="User search">
                                </div>
                            </div>
                        </div>
                        <div style="flex-shrink: 0">
                            <div class="form-group margin-bottom0">
                                <div class="d-flex mb-0">
                                    <button class="btn red-rubine btn-h-45" type="button" id="searchTypeBehaviour" onclick="filterBehaviorTabData()">
                                        <i class="jv-icon jv-search"></i>
                                    </button>
                                    <button class="btn btn-success grey-gallery btn-h-45" type="button" onclick="clearSearch()">
                                        <i class="jv-icon jv-close"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group margin-bottom0">
                <div class="input-group">
                    {!! Form::text('behaviourDaterange', $defaultDateRange, ['class' => 'form-control', 'id' => 'behaviourDaterange', 'placeholder' => 'Report date' , 'readonly']) !!}
                    <span class="input-group-addon js-daterangepicker-button" id="basic-addon1"><i class="jv-icon jv-calendar font-weight-700"></i></span>
                </div>
            </div>
        </div>
    </div>
</form>


<div class="row behaviour-container-div">
    <div class="col-md-12">
        <div class="portlet box telematics-card-wrapper marginbottom0">
            <div class="portlet-title">
                <div class="caption">Driver Behaviour Score</div>
            </div>
            <div class="portlet-body">
                <div class="row row-deck">
                    <div class="col-md-5">
                        <div class="card card-statistics justify-content-center">
                            <div class="card-body">
                                <!-- <div class="pie-chart-wrapper d-flex align-items-center flex-column h-100 justify-content-center">
                                    <div style="width: 100%;height: 120px;">
                                        <div id="overallscore-chart" style="height: 120px; position: relative;"></div>
                                    </div>
                                </div> -->

                                <div class="row d-flex align-items-center">
                                    <div class="col-md-6 border-r">
                                        <div class="pie-chart-wrapper text-center">
                                            <div class="dashboard-pie-chart-stat overallscore-chart" id="overallscore-chart" data-percent="0">
                                                <span class="d-flex align-items-center justify-content-center flex-column">
                                                    <span class="d-flex align-items-center"><span class="overall-score-percentage d-none"></span><span class="margin_left"> %</span></span>
                                                    <span class="d-flex align-items-center score-status text-success overall-chart-score-trend d-none">

                                                    </span>
                                                </span>
                                            </div>
                                            <div class="mt-2">Overall Score</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex flex-column justify-content-between">
                                            <div class="pie-chart-wrapper text-center">
                                                <div class="dashboard-pie-chart-stat safetyscore-chart" id="safetyscore-chart" data-percent="0">
                                                    <span class="d-flex align-items-center justify-content-center flex-column">
                                                        <span class="d-flex align-items-center"><span class="safety-score-percentage d-none"></span><span class="margin_left"> %</span></span>
                                                        <span class="d-flex align-items-center score-status safety-chart-score-trend">

                                                        </span>
                                                    </span>
                                                </div>
                                                <div class="mt-2">Safety Score</div>
                                            </div>

                                            <div class="pie-chart-wrapper text-center">
                                                <div class="dashboard-pie-chart-stat efficiencyscore-chart" id="efficiencyscore-chart" data-percent="0">
                                                    <span class="d-flex align-items-center justify-content-center flex-column">
                                                        <span class="d-flex align-items-center"><span class="efficiency-score-percentage d-none"></span><span class="margin_left"> %</span></span>
                                                        <span class="d-flex align-items-center score-status text-success efficiency-chart-score-trend">

                                                        </span>
                                                    </span>
                                                </div>
                                                <div class="mt-2">Efficiency Score</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card card-score-history">
                            <div class="card-header">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="card-title">Score History</div>
                                    <div>
                                        {{-- <div class="dropdown dropdownFilter">
                                            <button class="btn btn-link padding0 dropdown-toggle" type="button" id="scoreHistoryFilter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                            Filter
                                                <span class="jv-icon jv-down"></span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="scoreHistoryFilter" id="scoreHistoryFilter">
                                                <li><a href="#">All</a></li>
                                                <li><a href="#">Overall score</a></li>
                                                <li><a href="#">Safety score</a></li>
                                                <li><a href="#">Efficiency score</a></li>
                                            </ul>
                                        </div> --}}
                                        <div id="chart-legend"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="myChart"></canvas>
                                <!-- <img src="{{ asset('img/chart/score-history.png') }}" class="img-responsive"> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="portlet box telematics-card-wrapper marginbottom0">
            <div class="portlet-title">
                <div class="caption js-safety-score-caption">Safety Score</div>
                <div class="actions new_btn align-self-end">
                    <!-- <span onclick="clickExport();" class="m5 jv-icon jv-download"></span> -->
                    <span onclick="exportSafetyEfficiencyScore('safety');" id="spanExportSafetyScore" class="m5 jv-icon jv-download"></span>
                </div>
            </div>
            <div class="portlet-body">
                <div class="card">
                    <div class="card-body">
                        <div class="safety-score-header score-card d-flex margin-bottom-20">
                            <a href="javascript:void(0);" class="card score-movement">
                                <div class="card-header safetyDiv">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="card-title">Safety</span>
                                        <div class="text-right">
                                            <h3 class="margin-0 font-weight-700 safetyScoreAvgDiv"></h3>
                                            <span class="label-results label-danger safetyScoreTrendDiv">

                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <a href="javascript:void(0);" class="card score-movement">
                                <div class="card-header acclDiv">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="card-title">Acceleration</span>
                                        <div class="text-right">
                                            <h3 class="margin-0 font-weight-700 accelerationScoreAvgDiv"></h3>
                                            <span class="label-results label-danger accelerationScoreTrendDiv">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <a href="javascript:void(0);" class="card score-movement">
                                <div class="card-header brakingDiv">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="card-title">Braking</span>
                                        <div class="text-right">
                                            <h3 class="margin-0 font-weight-700 brakingScoreAvgDiv"></h3>
                                            <span class="label-results label-success brakingScoreTrendDiv">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <a href="javascript:void(0);" class="card score-movement">
                                <div class="card-header corneringDiv">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="card-title">Cornering</span>
                                        <div class="text-right">
                                            <h3 class="margin-0 font-weight-700 corneringScoreAvgDiv"></h3>
                                            <span class="label-results label-danger corneringScoreTrendDiv">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <a href="javascript:void(0);" class="card score-movement">
                                <div class="card-header speedingDiv">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="card-title">Speeding</span>
                                        <div class="text-right">
                                            <h3 class="margin-0 font-weight-700 speedingScoreAvgDiv"></h3>
                                            <span class="label-results label-danger speedingScoreTrendDiv">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="jqgrid-wrapper bg-white">
                            <table id="safetyscoreJqGrid" class="table-striped table-bordered table-hover"></table>
                            <div id="safetyscoreJqGridPager" class="multiple-action jqGridPagination"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="portlet box telematics-card-wrapper marginbottom0">
            <div class="portlet-title">
                <div class="caption js-efficiency-score-caption">Efficiency Score</div>
                <div class="actions new_btn align-self-end">
                    <!-- <span onclick="clickExport();" class="m5 jv-icon jv-download"></span> -->
                    <span onclick="exportSafetyEfficiencyScore('efficiency');" id="spanExportEfficiencyScore" class="m5 jv-icon jv-download"></span>
                </div>
            </div>
            <div class="portlet-body">
                <div class="card">
                    <div class="card-body">
                        <div class="efficiency-score-header score-card d-flex margin-bottom-20">
                            <a href="javascript:void(0);" class="card score-movement">
                                <div class="card-header efficiencyDiv">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="card-title">Efficiency</span>
                                        <div class="text-right">
                                            <h3 class="margin-0 font-weight-700 efficiencyScoreAvgDiv"></h3>
                                            <span class="label-results label-danger efficiencyScoreTrendDiv">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <a href="javascript:void(0);" class="card score-movement">
                                <div class="card-header rpmDiv">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="card-title">RPM</span>
                                        <div class="text-right">
                                            <h3 class="margin-0 font-weight-700 rpmScoreAvgDiv"></h3>
                                            <span class="label-results label-danger rpmScoreTrendDiv">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <a href="javascript:void(0);" class="card score-movement">
                                <div class="card-header idleDiv">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="card-title">Idle</span>
                                        <div class="text-right">
                                            <h3 class="margin-0 font-weight-700 idleScoreAvgDiv"></h3>
                                            <span class="label-results label-success idleScoreTrendDiv">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="jqgrid-wrapper bg-white">
                            <table id="efficiencyscoreJqGrid" class="table-striped table-bordered table-hover"></table>
                            <div id="efficiencyscoreJqGridPager" class="multiple-action jqGridPagination"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="portlet box telematics-card-wrapper marginbottom0">
            <div class="portlet-title">
                <div class="caption">Key Data</div>
            </div>
            <div class="portlet-body">
                <div class="row row-deck">
                    <div class="col-md-6">
                        <div class="card card-score-history">
                            <div class="card-header">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="card-title">Distance Driven (Miles)</div>
                                    <div id="chart1-legend"></div>
                                </div>
                            </div>
                            <div class="card-body">
                                {{--<img src="{{ asset('img/chart/distance.png') }}" class="img-responsive">--}}
                                <div class="wrapper col-2">
                                    <canvas id="chart-1" style="height: 300px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-score-history">
                            <div class="card-header">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="card-title">Driving Time (HH:MM:SS)</div>
                                    <div id="chart2-legend"></div>
                                </div>
                            </div>
                            <div class="card-body">
                                {{--<img src="{{ asset('img/chart/driving-time.png') }}" class="img-responsive">--}}
                                <div class="wrapper col-2"><canvas id="chart-2" style="height: 300px;"></canvas></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row deck margin-top-30">
                    <div class="col-md-6">
                        <div class="card card-score-history">
                            <div class="card-header">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="card-title">Fuel Used (Litres)</div>
                                    <div id="chart3-legend"></div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="wrapper col-2"><canvas id="chart-3" style="height: 300px;"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-score-history">
                            <div class="card-header">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="card-title">CO2 Emissions (Kg)</div>
                                    <div id="chart4-legend"></div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="wrapper col-4"><canvas id="chart-4" style="height: 300px;"></canvas></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>