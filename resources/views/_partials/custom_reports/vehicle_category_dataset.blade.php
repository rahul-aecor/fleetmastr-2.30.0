@if(isset($reportDataSet['App\Models\Vehicle']))
    <div id="accordion2" class="panel-group accordion">
        <div class="panel panel-default">
            <div class="panel-heading bg-red-rubine">
                <h4 class="panel-title">
                    <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion2" href="#report_vehicles">
                        Vehicle
                    </a>
                </h4>
            </div>
            <div id="report_vehicles" class="panel-collapse collapse">
                <div class="panel-body scroller padding0" data-height="300px">
                    <table class="table table-striped table-hover custom-table-striped js-custom-reports-accordian-table">
                        <?php
                            $slug = $report->slug;
                            $disabledArr = ['Service Type', 'Service Date', 'Event Status'];
                            $disabledForReports = ['standard_fleet_cost_report', 'standard_vehicle_journey_report', 'standard_user_journey_report', 'standard_vehicle_location_report'];
                            /*$disabledArr = ['Lease Cost', 'Maintenance Cost', 'Depreciation Cost', 'Vehicle Tax', 'Insurance Cost', 'Telematics Cost', 'Manual Cost Adj', 'Fuel Type', 'Oil', 'AdBlue', 'Screen Wash', 'Fleet Livery', 'Defects', 'Total', 'Transfer'];*/
                        ?>
                        @foreach($reportDataSet['App\Models\Vehicle'] as $key => $data)
                            <?php
                                $isDisabled = in_array($slug, $disabledForReports) || in_array($data->title, $disabledArr) ? true : false;
                            ?>
                            @include('_partials.custom_reports.data_set_element', ['isDisabled' => $isDisabled])
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
@endif