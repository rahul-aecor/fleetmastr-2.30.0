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
    @if(isset($telematicsValueDisplay))
      @foreach ($telematicsValueDisplay as $monthlyInsurance)
        @if($vehicle->is_telematics_cost_override == 0 && $monthlyInsurance['cost_to_date'] != '' && \Carbon\Carbon::parse($monthlyInsurance['cost_to_date'])->lt(\Carbon\Carbon::parse($vehicle->dt_added_to_fleet)) )
        {{--Do Nothing--}}
        @else
          <tr>
            <td>&pound;{{ number_format($monthlyInsurance['cost_value'],2) }}</td>
            <td>{{ (\Carbon\Carbon::parse($monthlyInsurance['cost_from_date'])->lt(\Carbon\Carbon::parse($vehicle->dt_added_to_fleet)) && $vehicle->is_telematics_cost_override == 0) ? \Carbon\Carbon::parse($vehicle->dt_added_to_fleet)->format('d M Y') : $monthlyInsurance['cost_from_date'] }} </td>

            @if($monthlyInsurance['cost_to_date'] != '')
              <td>{{ isset($monthlyInsurance['cost_to_date']) ? $monthlyInsurance['cost_to_date'] : ''}}</td>
            @else 
              <td>-</td>
            @endif
            <td> {{ $monthlyInsurance['cost_from_date'] == $telematicsFieldCurrentDateValue ? '(Current date)' : '' }}</td>
          </tr>
        @endif
      @endforeach
    @endif
  </tbody>
</table>
<input type="hidden" class="telematicsFieldCurrentCost" value="{{isset($telematicsFieldCurrentCost) ? $telematicsFieldCurrentCost : ''}}">
<div class="btn-group d-flex justify-content-center">
    <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Close</button>
</div>