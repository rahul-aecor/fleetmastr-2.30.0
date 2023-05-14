<form class="form-horizontal display-settings" id="accident_insurance_form" role="form" action="/settings/storeAccidentInsuranceDetail" method="POST" novalidate enctype="multipart/form-data">
    <div class="row">
      <div class="col-md-12">
            {{ csrf_field() }}
            <div class="form-body">
                <p class="margin-bottom-20">The below insurance information will display in the Report Incident section on the app.</p>
                <div class="row">
                    <div class="col-md-10">
                        <div class="form-group d-flex row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <label for="insurance_company" class="control-label align-self-center pt-0 w-100">
                                    Insurance company:</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="insurance_company" id="insurance_company" class="form-control" value="{{ $accidentInsuranceData['insurance_company'] or '' }}">
                            </div>
                        </div>
                        <div class="form-group d-flex row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <label for="telephone_number" class="control-label align-self-center pt-0 w-100">
                                    Telephone number:</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="telephone_number" id="telephone_number" class="form-control" value="{{ $accidentInsuranceData['telephone_number'] or '' }}">
                            </div>
                        </div>
                        <div class="form-group d-flex row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <label for="policy_number" class="control-label align-self-center pt-0 w-100">
                                    Policy number:</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="policy_number" id="policy_number" class="form-control" value="{{ $accidentInsuranceData['policy_number'] or '' }}">
                            </div>
                        </div>
                        <div class="form-group d-flex row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <label for="policy_name" class="control-label align-self-center pt-0 w-100">
                                    Policy name:</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="policy_name" id="policy_name" class="form-control" value="{{ $accidentInsuranceData['policy_name'] or '' }}">
                            </div>
                        </div>
                        <div class="form-group d-flex row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <label for="insurance_file_input_name" class="control-label align-self-center pt-0 w-100">
                                    Upload insurance certificate:</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="insurance_file_input_name" id="insurance_attachment_file_exists" value="{{ $accidentInsuranceMedia ? $accidentInsuranceMedia->file_name : '' }}" class="form-control fileupload insurance_attachment_exists" placeholder="Enter file name here" autocomplete="off" disabled="disabled" style="display: none;">
                                
                                <input type="text" name="insurance_file_input_name" id="insurance_attachment_file_new" class="form-control fileupload insurance_attachment_new_file" placeholder="Enter file name here" autocomplete="off" style="display: none;">
                            </div>
                            
                            <div class="col-md-5">
                                <div class="row d-flex align-items-center">
                                    <div class="col-md-12">
                                        <span class="btn red-rubine js-delete-insurance-certificate" style="display: none">Delete</span>
                                        <div class="js-insurance-certificate-select-file" style="display: none">
                                            <span class="btn red-rubine btn-file js-new-insurance-certificate-file">
                                                <span class="fileinput-new">Select file</span>
                                                <input type="file" name="insurance_certificate_attachment" class="select-insurance-certificate-file">
                                                <input type="hidden" name="is_certificate_deleted" id="deleted_certificate" value="">
                                            </span>
                                            
                                            <button class="fileinput-exists btn grey-gallery remove-insurance-certificate-file" style="display: none;" data-dismiss="fileinput">Remove</button>
                                            <div class="inline-block ml-3">
                                                <span class="js-file-name"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                     
                    </div>
                </div>
            </div>

            <div class="row mt-2 pt15">
                <div class="col-md-4 col-md-offset-4">
                    <button type="submit" id="accident_insurance_submit" class="btn red-rubine btn-padding btn-block">Save</button>
                </div>
            </div>
      </div>    
    </div>
</form>
