<form class="form-horizontal repeater" role="form" id="forecastMilesForm" action="/settings/fleetMiles" method="POST">
    {{ csrf_field() }}
    <div class="row">
        <div class="col-md-12">
            @foreach($months as $key => $month)
                <div class="form-group row d-flex align-items-center">
                    <label class="col-md-2 control-label padding-top-0">{{ $month }}</label>
                    <div class="col-md-10 error-class">
                        <input type="text" name="month[{{ $key }}]" class="form-control forecast-miles-cost" value="{{ isset($fleetCostData['fleet_miles_per_month'][$key]) ? number_format($fleetCostData['fleet_miles_per_month'][$key]) : '' }}">
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="btn-group width100 margin-top-20">
        <button type="button" class="btn white-btn btn-padding col-md-6 forecastFleetCancle" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn red-rubine btn-padding col-md-6 submit-button">Save</button>
    </div>
</form>