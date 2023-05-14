<div class="hrmc-edit-data">
    <div class="row">
    	<div class="col-md-12">
            <div class="custom-responsive-table">
                <table class="table table-condensed table-hover hrmctable">
                    <thead>
                        <tr>
                            <th>CO2 Emissions g/km</th>
                            <th>CO2 % Electric & Petrol</th>
                            <th>CO2 % Diesel</th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <input type="hidden" class="co2_values_count" name="co2_values_count" value="{{count($hmrcco2data[0]->co2_values)}}">
                        @foreach($hmrcco2data[0]->co2_values as $key => $val)
                        <?php $value = json_decode($val) ?>
                            <tr id="row_{{$key}}">
                                <td>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <input type="text" name="co2_emission_{{$key}}" value="{{ $value->co2_emission }}" class="co2_emission form-control">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <input type="text" name="co2_per_electric_petrol_{{$key}}" value="{{ $value->co2_per_electric_petrol }}" class="co2_per_electric_petrol form-control">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <input type="text" name="co2_per_diesel_{{$key}}" value="{{ $value->co2_per_diesel }}" class="co2_per_diesel form-control">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <a href="#" class="btn btn-h-45 btn-link delete-co2-row-button" title="" data-confirm-msg="Are you sure you would like to delete this record?" onclick="remove_row('row_{{$key}}')">
                                                <i class="jv-icon jv-dustbin"></i>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-3">
                    <button type="button" id="add_row_btn" class="btn red-rubine add_row_btn btn-blocks">Add row</button>
                </div>
            </div>
            <div class="margin-top-20"></div>
            <div class="form-group row">
                <div class="col-md-12">
                    <label>Comments:</label>
                    <textarea name="comments" class="col-md-12 form-control" maxlength='500'onkeyup="countChar(this)">{{$hmrcco2data[0]->comments}}</textarea> 
                    <div id="charNum" class="font-blue text-right">{{500 - strlen($hmrcco2data[0]->comments)}}</div>
                </div>       
            </div>
            {{-- <div class="row">
                <div class="col-md-4">
                    Comments:
                </div>
                <div class="col-md-12"> &nbsp;</div>               
                <div class="col-md-12">                
                    <textarea name="comments" class="col-md-12 form-control" maxlength='500'onkeyup="countChar(this)">{{$hmrcco2data[0]->comments}}</textarea>        
                </div>
                <div class="col-md-12 text-right">
                    <div id="charNum" class="font-blue">{{500 - strlen($hmrcco2data[0]->comments)}}</div>
                </div>
            </div> --}}
        </div>
    </div>
</div>