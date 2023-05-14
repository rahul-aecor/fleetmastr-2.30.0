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
    @if(isset($fleetCostData['annual_insurance_cost']))
      @foreach ($fleetCostData['annual_insurance_cost'] as $fleetCost)
          <tr>
            <td>&pound;{{number_format($fleetCost['cost_value'],2)}}</td>
            <td>{{ $fleetCost['cost_from_date']}} </td>
            @if($fleetCost['cost_to_date'] != '')
              <td>{{ isset($fleetCost['cost_to_date']) ? $fleetCost['cost_to_date'] : ''}}</td>
            @else 
              <td>-</td>
            @endif
            <td> {{ $fleetCost['cost_from_date'] == $insuranceCurrentDate ? '(Current date)' : '' }}</td>
          </tr>
      @endforeach
    @endif
  </tbody>
</table>
<div class="btn-group d-flex justify-content-center">
    <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Close</button>
</div>