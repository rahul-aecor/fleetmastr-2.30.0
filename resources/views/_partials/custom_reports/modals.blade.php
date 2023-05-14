<div class="modal fade default-modal" id="primary_index_modal" tabindex="-1" data-keyboard="false" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-red-rubine">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Primary Index</h4>
            </div>
            <div class="modal-body">
                <p style="font-size: 15px;">The primary index is the main data point in which your report will be sorted. This will be the first column that appears on your report.</p>
                <p style="font-size: 15px;">You can change the order in which the columns appear in your report by dragging and dropping them in to position.</p>
            </div>
            <div class="modal-footer">
                <div class="btn-group pull-left width100">
                    <button type="button" class="btn white-btn btn-padding col-md-12" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal to add company starts here -->
<div id="add-category" class="modal modal-fix  fade" tabindex="-1" data-background="static" aria-labelledby="myModalLabel" data-width="630" aria-hidden="true">
    <div class="modal-dialog" style="margin-top: 0; margin-bottom: 0;">
        <div class="portlet box">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
              <h4 class="modal-title">Add Category</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body" id="ajax-modal-content">
                @if (count($errors) > 0)
                    <div class="alert alert-danger bg-red-rubine">
                        <p><strong>Please complete the errors highlighted below.</strong></p>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                {!! BootForm::openHorizontal(['md' => [3, 9]])->addClass('form-bordered form-validation')->id('addCategory') !!}
                    {!! BootForm::text('Category*:', 'name')->addClass('add_category') !!}
                {!! BootForm::close() !!}
            </div>
            <div class="modal-footer">
            <div class="col-md-offset-2 col-md-8 ">
                    <div class="btn-group pull-left width100">
                        <button type="button" id="addCategoryCancel" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                        <button id="addCategoryBtn" type="submit" class="btn red-rubine btn-padding submit-button col-md-6">Save</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- Modal to add company starts here -->
<div id="view-category" class="modal modal-fix  fade" tabindex="-1" data-background="static" aria-labelledby="myModalLabel" data-width="630" aria-hidden="true">
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
                    <th scope="col" width="70%">Category Name</th>
                    <th scope="col" class="text-center">Action</th>
                  </tr>
                </thead>
                <tbody id="view_all_report_categories">
                </tbody>
              </table>
            </div>

        </div>
    </div>
</div>