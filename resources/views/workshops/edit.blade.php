
{!! BootForm::openHorizontal(['md' => [4, 8]])->addClass('form-bordered form-validation workshop-user-form')->put()->action('/workshops/' . $user->id)->id('editUser') !!}
{!! BootForm::bind($user) !!}
<div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
    <h4 class="modal-title">Edit Workshop User</h4>
    <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
        <i class="jv-icon jv-close"></i>
    </a>
</div>
<div class="modal-body">
    <div class="tabbable-custom tabbable-rubine nav-justified margin-bottom0">
        <ul class="nav nav-tabs nav-justified">
            <li class="active">
                <a href="#edit_add_user" data-toggle="tab">
                Workshop User Details </a>
            </li>
        </ul>

        <div id="view-edit-company" class="modal modal-fix  fade" tabindex="-1" data-background="static" aria-labelledby="myModalLabel" data-width="630" data-height="400" aria-hidden="true">
            <div class="modal-dialog" style="margin-top: 0; margin-bottom: 0;">
                <div class="portlet box">
                    <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
                      <h4 class="modal-title">All Companies</h4>
                        <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                            <i class="jv-icon jv-close"></i>
                        </a>
                    </div>
                    <div class="table-wrapper-scroll-y my-custom-scrollbar">
                    <table class="table table-hover table-striped table-company">
                      <thead class="thead-dark">
                        <tr>
                          <th scope="col" width="70%">Company Name</th>
                          <th scope="col" class="text-center">Action</th>
                        </tr>
                      </thead>
                      <tbody id="view_all_edit_companies">
                      </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content rl-padding">
            <div class="tab-pane active" id="edit_add_user">
                <div class="row">
                    <div class="col-md-12">
                        <div class="clearfix">
                            <div class="col-md-6 userFirstCol">
                                {!! BootForm::hidden('id') !!}
                                {!! BootForm::text('First name*:', 'first_name') !!}
                                {!! BootForm::text('Last name*:', 'last_name') !!}

                                <div class="form-group row company--modal">
                                    <label class="col-md-4 control-label" for="company_id">Company*:</label>
                                    <div class="company--modal-col-55" style="width:46%;">
                                      <select class="form-control select2me " id="company_id1" name="company_id">
                                        <?php foreach ($companyList as $key => $company): ?>
                                            <option {{ $user->company_id === $key ? 'selected': '' }} value="{{ $key }}">{{ $company }}</option>
                                        <?php endforeach ?>
                                      </select>
                                    </div>

                                    <div class="company--modal-col desboard_thumbnail">
                                      <a href="#add-company" data-toggle="modal" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center" style="margin-right: 0">
                                            <i class="jv-icon jv-plus"></i>
                                        </a>
                                     </div>
                                     <div class="company--modal-col desboard_thumbnail">
                                       <a href="#view-edit-company" id="view_edit_company" data-path="workshop" data-toggle="modal" class="align-items-center btn btn-blue-color btn-h-45 d-flex justify-content-center" style="margin-right: 0">
                                             <i class="jv-icon jv-edit"></i>
                                         </a>
                                      </div>
                                </div>

                                {!! BootForm::text('Username/Email*:', 'email')->disabled('disabled') !!}
                                {!! BootForm::text('Mobile number:', 'mobile') !!}
                                {!! BootForm::text('Landline number:', 'landline') !!}
                            </div>
                            <div class="col-md-6 user-form-right-pane">
                                {!! BootForm::text('Address1:', 'address1') !!}
                                {!! BootForm::text('Address2:', 'address2') !!}
                                {!! BootForm::text('Town/City:', 'town_city') !!}
                                {!! BootForm::text('Postcode:', 'postcode') !!}
                                {!! BootForm::select('Enable account login:', 'enable_login')->options(['1' => 'Yes', '0' => 'No'])->addClass('select2me') !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <div class="col-md-offset-2 col-md-8 ">
        <div class="btn-group pull-left width100">
            <button type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn red-rubine btn-padding submit-button col-md-6">Update</button>
        </div>
    </div>
</div>
{!! BootForm::close() !!}
