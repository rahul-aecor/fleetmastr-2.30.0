<form class="form-horizontal display-settings" id="fuel_benefit_form" role="form" action="/settings/fuel/store" method="POST" novalidate>
    <div class="row">
      <div class="col-md-12">
            {{ csrf_field() }}
            <div class="form-body">
                <p class="margin-bottom-20">Complete the values below.</p>

                <div class="row">
                    <div class="col-md-10">
                        <div class="form-group d-flex row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <label for="cash_equivalent" class="control-label align-self-center pt-0 w-100">
                                    Fuel benefit cash equivalent <br>(Commercial vehicles):</label>
                                    <span class="currency"> &pound;</span>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <input type="text" name="cash_equivalent" id="cash_equivalent" class="form-control" value="{{$fuelBenefitData['cash_equivalent']}}">
                            </div>
                        </div>
                        <div class="form-group d-flex row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <label for="fuel_benefit_noncommercial" class="control-label align-self-center pt-0 w-100">
                                    Fuel benefit charge<br>(Non commercial vehicles):</label>
                                    <span class="currency"> &pound;</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="fuel_benefit_noncommercial" id="fuel_benefit_noncommercial" class="form-control" value="{{$fuelBenefitData['fuel_benefit_noncommercial']}}">
                            </div>
                        </div>
                        <div class="form-group d-flex row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <label for="fuel_benefit_commercial" class="control-label align-self-center pt-0 w-100">
                                    Fuel benefit charge<br>(Commercial vehicles):</label>
                                    <span class="currency"> &pound;</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="fuel_benefit_commercial" id="fuel_benefit_commercial" class="form-control" value="{{$fuelBenefitData['fuel_benefit_commercial']}}">
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row mt-2 pt15">
                <div class="col-md-4 col-md-offset-4">
                    <button type="submit" id="fuel_benefit_submit" class="btn red-rubine btn-padding btn-block" name="submit">Save</button>
                </div>
            </div>
      </div>    
    </div><!-- /.modal-content -->
</form>
