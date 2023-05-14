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
      @if(isset($vehicle->maintenance_cost))
      <?php $maintenance_costs = json_decode($vehicle->maintenance_cost, true); ?>
        @foreach ($maintenance_costs as $maintenance_cost)
        <tr>
          <td>&pound;{{ number_format($maintenance_cost['cost_value'],2) }}</td>
          <td>{{ $maintenance_cost['cost_from_date'] }}</td>
          
          @if($maintenance_cost['cost_to_date'])
            <td>{{ isset($maintenance_cost['cost_to_date']) ? $maintenance_cost['cost_to_date'] : ''}}</td>  
          @else
          <td> - </td>
            {{-- <td>{{ Carbon\Carbon::now()->format('d M Y') }}</td> --}}
          @endif

          <td>{{ $maintenance_cost['cost_from_date'] == $maintenanceCurrentDateValue ? '(Current date)' : '' }}</td>
        </tr>
        @endforeach
      @endif
    </tbody>
  </table>
<input type="hidden" class="maintenanceCurrentCost" value="{{isset($currentMonthMaintenanceCost) ? $currentMonthMaintenanceCost : ''}}">
  <div class="btn-group d-flex justify-content-center">
    <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Close</button>
</div>