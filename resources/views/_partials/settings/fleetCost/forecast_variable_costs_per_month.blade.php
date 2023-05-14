 <form class="form-horizontal repeater" role="form" action="/settings/variableCost" method="POST" id="fleetVariableCostForm">
    {{ csrf_field() }}
    <div class="row">
        <div class="col-md-12">
            @foreach($months as $key => $month)
                <div class="form-group row d-flex align-items-center">
                    <label class="col-md-2 control-label padding-top-0">{{ $month }} &pound;</label>
                    <div class="col-md-10 error-class">
                        <input type="text" name="month[{{ $key }}]" class="form-control forecast-per-month" value="{{ isset($fleetCostData['forecast_cost_per_month'][$key]) ? number_format($fleetCostData['forecast_cost_per_month'][$key],2) : '' }}">
                    </div>
                </div>
            @endforeach
        </div>
    </div> 
    
    <div class="btn-group width100 margin-top-20">
        <button type="button" class="btn white-btn btn-padding col-md-6 forecastFleetCancle" data-dismiss="modal">Cancel</button>
        <button id="fleetVariableCostSave" type="submit" class="btn red-rubine btn-padding submit-button col-md-6">Save</button>
    </div>
</form>