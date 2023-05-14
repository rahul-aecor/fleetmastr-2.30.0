<div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
    <h4 class="modal-title">Alert Notification</h4>
    <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
        <i class="jv-icon jv-close"></i>
    </a>
</div>

<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group row">
                <div class="col-md-4">Status:</div>
                <div class="col-md-8">
                    {{$alertData->is_active == 1 ? 'Open' : 'Resolved'}}
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-4">Severity:</div>
                <div class="col-md-8">
                    {{$alertData->alerts->severity}}
                </div>
            </div>
            
            <div class="form-group row">
                <div class="col-md-4">Alert name:</div>
                <div class="col-md-8">
                    {{$alertData->alerts->name}}
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-4">Type:</div>
                <div class="col-md-8">
                    {{$alertData->alerts->type}}
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-4">Source:</div>
                <div class="col-md-8">
                    {{$alertData->alerts->source}}
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-4">Vehicle:</div>
                <div class="col-md-8">
                    {{ $alertData->vehicle->registration }}</a></td>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-4">User:</div>
                <div class="col-md-8">
                    {{ $alertData->user->first_name }} {{ $alertData->user->last_name }}
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-4">Alert date:</div>
                <div class="col-md-8">
                    {{$alertData->alerts->created_at}}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <div class="col-md-offset-2 col-md-8 ">
        <div class="btn-group pull-left width100">
            <button type="button" class="btn white-btn btn-padding col-md-12" 
            data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
