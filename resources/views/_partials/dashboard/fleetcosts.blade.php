<form class="display-settings" role="form" action="/settings/store" method="POST" novalidate>
    <div class="row">
      <div class="col-md-12">
            {{ csrf_field() }}
            <div class="form-body">
               <div class="row gutters-tiny d-flex margin-bottom-20">
                    <div class="col-md-12">
                        <div class="row gutters-tiny d-flex h-100">
                            <div class="col-md-4">
                                <div class="card h-100 fixed-height-card-h1">
                                    <div class="card-header">
                                        <span class="card-title">Fleet cost this month</span>
                                    </div>
                                    <div class="card-body h-100">
                                        <div class="row align-items-center d-flex h-100">
                                            <div class="col-xs-12">
                                                <div class="align-items-center d-flex flex-column h-100 justify-content-center">
                                                    <h1 class="count-number" id="monthly-fleet-cost"><i class="fa fa-spin fa-spinner"></i></h1>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 fixed-height-card-h1">
                                    <div class="card-header">
                                        <span class="card-title">Fleet miles this month</span>
                                    </div>
                                    <div class="card-body h-100">
                                        <div class="row align-items-center d-flex h-100">
                                            <div class="col-xs-12">
                                                <div class="align-items-center d-flex flex-column h-100 justify-content-center">
                                                    <h1 class="count-number" id="monthly-fleet-miles"><i class="fa fa-spin fa-spinner"></i></h1>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 fixed-height-card-h1">
                                    <div class="card-header">
                                        <span class="card-title">Fleet cost per mile this month</span>
                                    </div>
                                    <div class="card-body h-100">
                                        <div class="row align-items-center d-flex h-100">
                                            <div class="col-xs-12">
                                                <div class="align-items-center d-flex flex-column h-100 justify-content-center">
                                                    <h1 class="count-number" id="monthly-fleet-cost-per-mile"><i class="fa fa-spin fa-spinner"></i></h1>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 fixed-height-card-h1">
                                    <div class="card-header">
                                        <span class="card-title">Defects/Damage cost this month</span>
                                    </div>
                                    <div class="card-body h-100">
                                        <div class="row align-items-center d-flex h-100">
                                            <div class="col-xs-12">
                                                <div class="align-items-center d-flex flex-column h-100 justify-content-center">
                                                    <h1 class="count-number" id="monthly-defect-cost"><i class="fa fa-spin fa-spinner"></i></h1>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row margin-bottom-20">
                    <div class="col-sm-12">                        
                        <h4 class="dashboard-section-name font-weight-400">Select Period</h4>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fleetcostFromDate">From:</label>
                            <div class="input-group date date-input-field" id="start_date">
                                <input type="text" size="16" readonly class="form-control no-script" name="month_from" id="fleetcostFromDate">
                                <span class="input-group-btn">
                                    <button class="btn default date-set grey-gallery btn-h-45" type="button">
                                        <i class="jv-icon jv-calendar"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fleetcostToDate">To:</label>
                            <div class="input-group date date-input-field" id="end_date">
                                <input type="text" size="16" readonly class="form-control no-script" name="month_to">
                                <span class="input-group-btn">
                                    <button class="btn default date-set grey-gallery btn-h-45" type="button">
                                        <i class="jv-icon jv-calendar"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="d-block">&nbsp;</label>
                            <button type="button" id="fleetCostDataUpdate" class="btn red-rubine btn-block btn-h-45 btn-deactive">Update</button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 margin-bottom-20">
                        <div class="card">
                            <div class="card-header">
                                <span class="card-title">Monthly fleet cost</span>
                            </div>
                            <div class="card-body">
                                <canvas id="monthly_fleet_cost_chart" width="200" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 margin-bottom-20">
                        <div class="card">
                            <div class="card-header">
                                <span class="card-title">Cumulative fleet cost vs forecast</span>
                            </div>
                            <div class="card-body">
                                <canvas id="fleetcost_vs_forecast_chart" width="200" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 margin-bottom-20">
                        <div class="card">
                            <div class="card-header">
                                <span class="card-title">Monthly defect and damage cost</span>
                            </div>
                            <div class="card-body">
                                <canvas id="monthly_defect_damage_cost_chart" width="200" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 margin-bottom-20">
                        <div class="card">
                            <div class="card-header">
                                <span class="card-title">Cumulative defect and damage cost vs forecast</span>
                            </div>
                            <div class="card-body">
                                <canvas id="defect_damage_vs_forecast_chart" width="200" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 margin-bottom-20">
                        <div class="card">
                            <div class="card-header">
                                <span class="card-title">Monthly fleet miles vs forecast</span>
                            </div>
                            <div class="card-body">
                                <canvas id="monthly_fleet_miles_vs_forecast_chart" width="200" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <span class="card-title">Monthly cost per mile</span>
                            </div>
                            <div class="card-body">
                                <canvas id="monthly_cost_per_mile_chart" width="200" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
      </div>    
    </div><!-- /.modal-content -->
</form>
