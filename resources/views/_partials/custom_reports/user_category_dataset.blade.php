@if(isset($reportDataSet['App\Models\User']))
    <div id="accordion1" class="panel-group accordion">
        <div class="panel panel-default">
            <div class="panel-heading bg-red-rubine">
                <h4 class="panel-title">
                    <a class="accordion-toggle accordion-toggle-styled" data-toggle="collapse" data-parent="#accordion1" href="#report_users">
                        User
                    </a>
                </h4>
            </div>
            <div id="report_users" class="panel-collapse collapse in" aria-expanded="true">
                <div class="panel-body scroller padding0" data-height="300px">
                    <table class="table table-striped table-hover custom-table-striped js-custom-reports-accordian-table">
                        <?php
                            $slug = $report->slug;
                            $disabledForReports = ['standard_fleet_cost_report', 'standard_vehicle_journey_report', 'standard_user_journey_report', 'standard_vehicle_location_report'];
                        ?>
                        @foreach($reportDataSet['App\Models\User'] as $key => $data)
                            <?php
                                $isDisabled = in_array($slug, $disabledForReports) ? true : false;
                            ?>
                            @include('_partials.custom_reports.data_set_element', ['isDisabled' => $isDisabled])
                        @endforeach
                    </table>
                </div>
            </div>
        </div>                                            
    </div>
@endif