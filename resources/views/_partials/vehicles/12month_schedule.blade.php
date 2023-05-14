<div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
    <h4 class="modal-title">12 Month Schedule</h4>
    <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
        <i class="jv-icon jv-close"></i>
    </a>
</div>
<div class="modal-body">
	<table class="table table-striped table-bordered table-hover custom-table-striped">
		<tbody>
	        @for ($dt = $start; $end->diffInDays($dt, false) < 0; $dt->addMonth())
				<tr>
					<td>{{$dt->format('F Y')}}</td>
					<td>
						@if(isset($maintenanceList[$dt->format('F Y')]))
							@foreach($maintenanceList[$dt->format('F Y')] as $history)
								{{$history['value']}}<br>
							@endforeach
						@endif
					</td>
				</tr>
	        @endfor
		</tbody>
	</table>
</div>
<div class="modal-footer">
    <div class="col-md-offset-2 col-md-8">
        <div class="btn-group pull-left width100">
            <button type="button" class="btn white-btn col-md-12 btn-padding" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>