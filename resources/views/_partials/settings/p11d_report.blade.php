<div class="row" id="nextYearText" @if($showP11dFinalse == 'true') style="display:none;" @endif>
	<div class="col-md-10">	
		<div class="row">
		    <div class="col-md-12">
		        <div class="form-text">The P11D Benefits in Kind Report ( {{$taxyear}} ) will be available to finalise from 6th April {{ explode('-',$taxyear)[1]}}.</div>            
		    </div>
		</div>
	</div>
</div>
<div class="row" id="finalizeDiv" @if($showP11dFinalse == 'false') style="display:none;" @endif>
	<form id="finalizeReport" class="form-horizontal display-settings" role="form" action="/settings/finalizeReport" method="POST" novalidate>
	    <div class="col-md-12">
	    	{{ csrf_field() }}
	        <div class="form-group row">
	            <div class="col-md-8">
	                <input class="form-check-input" name="finalizeReportFlag" type="checkbox"
	                id="finalizeReportFlag">
	                Finalise the P11D Benefits in Kind Report ({{$evaluationYear}})
                    <input type="hidden" name="evaluationYear" id="evaluationYear" value="{{$evaluationYear}}">
	            </div>
	        </div>
	    </div>
	</form>
</div>
<div id="finalizeReportConfirm" class="modal modal-fix  fade" tabindex="-1" data-width="635" data-background="static" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="portlet box">
                <div class="portlet-title bg-red-rubine">
                    <div class="caption">
                        Confirmation
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <div>Finalise the P11D Report <b>Note:</b> This cannot be restored.</div>
                </div>
                <div class="modal-footer">
                <div class="btn-group pull-left width100">
                     <button type="button" id='finalizeReportCancelButton' class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn red-rubine btn-padding submit-button col-md-6"  id="finalizeReportConfirmButton">Confirm</button>
               </div>
            </div>
            </div>
        </div>
    </div>