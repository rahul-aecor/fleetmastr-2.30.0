	@extends('layouts.pdf')

@section('pdf_title')
  User Vehicle History
@endsection

@section('content')
	<div class="row" style="margin-top: 1px;">
		<div class="col-xs-7">
            <div class="portlet-title bg-red-rubine">
                <div class="caption">{{ $user->first_name .' '. $user->last_name  }}</div>
            </div>
            <table class="table table-striped table-summary">
            	<thead>
				    <tr>
				      <th scope="col">Registration</th>
				      <th scope="col">P11D List Price</th>
				      <th scope="col">From Date</th>
				      <th scope="col">To Date</th>
				      <th scope="col">Duration</th>
				    </tr>
				 </thead>
				 <tbody>
				 	@foreach($vehicleHistory as $history)
				 	<tr>
				 		<td> {{ $history->vehicle_history->registration }} </td>
				 		<td>
				 		@if($history->vehicle_history->P11D_list_price != null)
                        	&pound; {{ (floor($history->vehicle_history->P11D_list_price) == $history->vehicle_history->P11D_list_price) ? number_format($history->vehicle_history->P11D_list_price, 0) : number_format($history->vehicle_history->P11D_list_price, 2) }}
                    	@endif
				 		</td>	
				 		<td> {{ Carbon\Carbon::parse($history->from_date)->format('d M Y') }}</td>
				 		@if($history->to_date == NULL)
				 			<td>Current</td>
				 		@else
				 			<td> {{ Carbon\Carbon::parse($history->to_date)->format('d M Y') }}</td>
				 		@endif
				 		<td>{{ Carbon\Carbon::parse($history->from_date)->startofDay()->diffInDays(Carbon\Carbon::parse($history->to_date)->startofDay()) + 1 }} days</td>
				 	</tr>
				 	@endforeach
				 </tbody>
            </table>
		</div>
	</div>
@endsection