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
      @if(isset($vehicle->lease_cost))
      <?php $lease_costs = json_decode($vehicle->lease_cost, true); ?>
        @foreach ($lease_costs as $lease_cost)
        <tr>
          <td>&pound;{{ number_format($lease_cost['cost_value'],2) }}</td>
          <td>{{ $lease_cost['cost_from_date'] }}</td>
          
          @if($lease_cost['cost_to_date'] != '')
            <td>{{ isset($lease_cost['cost_to_date']) ? $lease_cost['cost_to_date'] : ''}}</td>  
          @else
            <td>-</td>
          @endif

          <td>{{ $lease_cost['cost_from_date'] == $leaseCurrentDate ? '(Current date)' : '' }}</td>
        </tr>
        @endforeach
      @endif
    </tbody>
  </table>
  <input type="hidden" class="leaseCurrentCost" value="{{isset($currentMonthLeaseCost) ?  $currentMonthLeaseCost : '' }}">
  <div class="btn-group d-flex justify-content-center">
    <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Close</button>
</div>