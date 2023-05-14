<div class="row">
	<div class="col-md-12">
        <div class="custom-responsive-table custom-responsive-table-detail">
            <table class="table table-condensed table-striped table-hover custom-table-striped table-hmrcdetail">
                <thead>
                    <tr>
                        <th>CO2 Emissions g/km</th>
                        <th>CO2 % Electric & Petrol</th>
                        <th>CO2 % Diesel</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hmrcco2data[0]->co2_values as $key => $val)
                    <?php $value = json_decode($val) ?>
                        <tr>
                            <td>{{ $value->co2_emission }}</td>
                            <td>{{ $value->co2_per_electric_petrol }}%</td>
                            <td>{{ $value->co2_per_diesel }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <input type="hidden" class="curr_tax_year" value="">
        </div>
        <div class="row">
            <span class="col-md-4">Comments:</span>
            <div class="col-md-12">&nbsp;</div>
            <div class="col-md-12">                
            {{$hmrcco2data[0]->comments}}
            </div>
        </div>
    </div>
    <!-- <div class="row">
        <span class="col-md-4">Comments:</span>
        <div class="col-md-12">                
        <textarea name="comments" class="col-md-12" maxlength='500'>{{$hmrcco2data[0]->comments}}</textarea>
        </div>
    </div> -->
    
</div> 
