<div id="vehicle_insurance_cost_history" class="modal fade default-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
            <h4 class="modal-title">Vehicle Insurance Cost History</h4>
            <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
            </a>
          </div>
          <div class="modal-body">
            <table class="table table-cost">
              <thead>
                <tr>
                  <th scope="col" class="border-bottom-0">Monthly Cost<span> &pound;</span></th>
                  <th scope="col" class="border-bottom-0">From</th>
                  <th scope="col" class="border-bottom-0">To</th>
                  <th class="border-bottom-0"></th>
                </tr>
              </thead>
              <tbody>
                @if(isset($vehicleType->annual_insurance_cost))
                <?php $vehicle_insurance_costs = json_decode($vehicleType->annual_insurance_cost, true); ?>
                  @foreach ($vehicle_insurance_costs as $vehicle_insurance_cost)
                  <tr>
                    <td>&pound;{{ number_format($vehicle_insurance_cost['cost_value'],2) }}</td>
                    <td>{{ $vehicle_insurance_cost['cost_from_date'] }}</td>
                    
                    @if($vehicle_insurance_cost['cost_to_date'] != '')
                      <td>{{ isset($vehicle_insurance_cost['cost_to_date']) ? $vehicle_insurance_cost['cost_to_date'] : ''}}</td>  
                    @else
                      <td>-</td>
                    @endif
                    <td>{{ $vehicle_insurance_cost['cost_from_date'] == $currentMonthVehicleInsuranceDateValue ? '(Current date)' : '' }}</td>
                  </tr>
                  @endforeach
                @endif
              </tbody>
            </table>
            <input type="hidden" class="currentMonthVehicleInsuranceCost" value="{{$currentMonthVehicleInsuranceCost}}">
            <div class="btn-group d-flex justify-content-center">
              <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->