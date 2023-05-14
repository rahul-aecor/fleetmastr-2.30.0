<div id="changePassword" class="modal fade default-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
        <h4 class="modal-title">Change Password</h4>
        <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                <i class="jv-icon jv-close"></i>
        </a>
      </div>
      <div class="modal-body">
        <form id="frmChangePassword" class="form-horizontal" role="form">
            <div class="form-body">
                <div class="form-group">
                    <label class="col-md-10 control-label" style="text-align:left;">To change your password please complete the fields below:</label>
                </div>
                <div class="form-group">
                    <label for="old_password" class="col-md-4 control-label">Current password</label>
                    <div class="col-md-8">
                        <input type="password" name="old_password" id="old_password" class="form-control">
                        <div class="form-control-focus"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password" class="col-md-4 control-label">New password</label>
                    <div class="col-md-8">
                        <input type="password" name="password" id="new_password" class="form-control">
                        <div class="form-control-focus"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password_confirmation" class="col-md-4 control-label">Confirm new password</label>
                    <div class="col-md-8">
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                        <div class="form-control-focus"></div>
                    </div>
                </div>
                <div id="pwdError" class="red"></div>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <div class="btn-group pull-left width100">
            <button id="closeChangePasswordDialog" type="button" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
            <button id="btnUpdatePassword" type="button" class="btn red-rubine btn-padding col-md-6 submit-button">Save</button>            
        </div>
        
        
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->