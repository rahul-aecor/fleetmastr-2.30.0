<div class="row">
    <div class="col-md-12">
        <p class="margin-bottom-20">The date below confirms the start date when the DVSA earned recognition scheme commenced. This date will be used to automatically create your reporting periods.</p>
    </div>
</div>

<form class="form-horizontal display-settings" id="dvsa_configuration_form" role="form" action="/settings/storeDVSAConfiguration" method="POST" novalidate>    
    <div class="row">
        <div class="col-md-12">
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row">
                    <div class="col-md-10">
                        @if(!auth()->user()->isRoleAssigned('manage dvsa configurations'))
                            <div class="form-group d-flex row" style="margin-top: 14px;">
                        @else
                            <div class="form-group d-flex row">
                        @endif
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <label for="dvsa_joining_period" class="control-label align-self-center pt-0 w-100">
                                        Joining period:</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                @if(!auth()->user()->isRoleAssigned('manage dvsa configurations'))
                                    <label class="margin-0">{{ setting('dvsa_joining_period') }}</label>
                                @else
                                    <select class="form-control select2me" id="dvsa_joining_period" name="dvsa_joining_period" placeholder="Select">
                                        <option></option>
                                        @for($i=1;$i<=13;$i++)
                                            <option value="Period {{ $i }}" {{ setting('dvsa_joining_period') == ('Period '.$i) ? 'selected' : '' }}>Period {{ $i }}</option>
                                        @endfor
                                    </select>
                                @endif
                            </div>
                        </div>
                        <div class="form-group d-flex row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <label for="dvsa_joining_year" class="control-label align-self-center pt-0 w-100">
                                        Joining year:</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                @if(!auth()->user()->isRoleAssigned('manage dvsa configurations'))
                                    <label class="margin-0">{{ setting('dvsa_joining_year') }}</label>
                                @else
                                    <select class="form-control select2me" id="dvsa_joining_year" name="dvsa_joining_year" placeholder="Select">
                                        <option></option>
                                        @for($i=2021;$i<=2030;$i++)
                                            <option value="{{ $i }}" {{ setting('dvsa_joining_year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                @endif
                            </div>
                        </div>
                        <div class="form-group d-flex row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <label for="android_version" class="control-label align-self-center pt-0 w-100">
                                        Commencement date:</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                @if(!auth()->user()->isRoleAssigned('manage dvsa configurations'))
                                    <label class="margin-0">{{ setting('dvsa_commencement_date') }}</label>
                                @else
                                    <div class="input-group date js-dvsa-commencement-date">
                                        <input type="text" size="16" class="form-control " id="dvsa_commencement_date" name="dvsa_commencement_date" value="{{ setting('dvsa_commencement_date') }}">
                                        <span class="input-group-btn">
                                        <button class="btn default date-set grey-gallery btn-h-45" type="button"><i class="jv-icon jv-calendar"></i></button>
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="form-group d-flex row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <label for="ios_version" class="control-label align-self-center pt-0 w-100">
                                    Operator ID:</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                @if(!auth()->user()->isRoleAssigned('manage dvsa configurations'))
                                    <label class="margin-0">{{ setting('dvsa_operator_id') }}</label>
                                @else
                                    <input type="text" name="dvsa_operator_id" id="dvsa_operator_id" class="form-control" value="{{ setting('dvsa_operator_id') }}">
                                @endif
                            </div>
                        </div>                      
                    </div>
                </div>
            </div>

            @if(auth()->user()->isRoleAssigned('manage dvsa configurations'))
                <div class="row mt-2 pt15">
                    <div class="col-md-4 col-md-offset-4">
                        <button type="submit" id="dvsa_configuration_submit" class="btn red-rubine btn-padding btn-block">Save</button>
                    </div>
                </div>
            @endif
        </div>    
    </div>
</form>