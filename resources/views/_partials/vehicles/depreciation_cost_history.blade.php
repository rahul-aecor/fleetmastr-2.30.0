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
    @if(isset($vehicle->monthly_depreciation_cost))
      <?php $depreciationCostValue = json_decode($vehicle->monthly_depreciation_cost, true); ?>
        @foreach ($depreciationCostValue as $depreciation)
          <tr>
            <td>&pound;{{ number_format($depreciation['cost_value'],2) }}</td>
            <td>{{ $depreciation['cost_from_date']}} </td>

            @if($depreciation['cost_to_date'] != '')
              <td>{{ isset($depreciation['cost_to_date']) ? $depreciation['cost_to_date'] : ''}}</td>
            @else 
              <td>-</td>
            @endif
            <td> {{ $depreciation['cost_from_date'] == $depreciationCurrentDateValue ? '(Current date)' : '' }}</td>
          </tr>
      @endforeach
    @endif
  </tbody>
</table>
<input type="hidden" class="deperectionCurrentCost" value="{{isset($deperectionCurrentCost) ? $deperectionCurrentCost : ''}}">
<div class="btn-group d-flex justify-content-center">
    <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Close</button>
</div>